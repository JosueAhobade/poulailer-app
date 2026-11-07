<?php

namespace Tests\Feature;

use App\Models\EggCollection;
use App\Models\EggSale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EggSaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_recording_a_collection(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('eggs.collections.store'), [
                'collection_date' => '2026-11-05',
                'collected_count' => 390,
                'broken_count' => 6,
            ])
            ->assertRedirect(route('eggs.collections.index'));

        $this->assertSame(384, EggCollection::sole()->remainingCount());
    }

    public function test_only_one_collection_per_date(): void
    {
        EggCollection::factory()->create(['collection_date' => '2026-11-05']);

        $this->actingAs(User::factory()->create())
            ->post(route('eggs.collections.store'), ['collection_date' => '2026-11-05', 'collected_count' => 10])
            ->assertSessionHasErrors('collection_date');
    }

    public function test_broken_eggs_cannot_exceed_collected(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('eggs.collections.store'), [
                'collection_date' => '2026-11-05',
                'collected_count' => 10,
                'broken_count' => 11,
            ])
            ->assertSessionHasErrors('broken_count');
    }

    public function test_sale_takes_eggs_from_the_chosen_days(): void
    {
        $older = EggCollection::factory()->create(['collection_date' => '2026-11-03', 'collected_count' => 100, 'broken_count' => 10]);
        $newer = EggCollection::factory()->create(['collection_date' => '2026-11-04', 'collected_count' => 100]);

        $this->actingAs(User::factory()->create())
            ->post(route('eggs.sales.store'), [
                'sale_date' => '2026-11-05',
                'customer' => 'Maman Rose',
                'price_mode' => 'tray',
                'price' => 2400,
                'is_paid' => '1',
                'items' => [$older->id => 90, $newer->id => 30],
            ])
            ->assertRedirect();

        $sale = EggSale::sole();

        $this->assertSame(120, $sale->quantity);
        $this->assertSame('9600.00', $sale->total_amount);
        $this->assertSame('80.00', $sale->unit_price);
        $this->assertSame(0, $older->fresh()->remainingCount());
        $this->assertSame(70, $newer->fresh()->remainingCount());
    }

    public function test_price_per_egg(): void
    {
        $collection = EggCollection::factory()->create(['collection_date' => '2026-11-04', 'collected_count' => 100]);

        $this->actingAs(User::factory()->create())
            ->post(route('eggs.sales.store'), [
                'sale_date' => '2026-11-05',
                'price_mode' => 'egg',
                'price' => 85,
                'items' => [$collection->id => 12],
            ]);

        $this->assertSame('1020.00', EggSale::sole()->total_amount);
        $this->assertFalse(EggSale::sole()->is_paid);
    }

    public function test_cannot_sell_more_than_remaining_for_a_day(): void
    {
        $collection = EggCollection::factory()->create(['collection_date' => '2026-11-04', 'collected_count' => 50, 'broken_count' => 5]);

        $this->actingAs(User::factory()->create())
            ->post(route('eggs.sales.store'), [
                'sale_date' => '2026-11-05',
                'price_mode' => 'egg',
                'price' => 80,
                'items' => [$collection->id => 46],
            ])
            ->assertSessionHasErrors("items.{$collection->id}");

        $this->assertDatabaseCount('egg_sales', 0);
    }

    public function test_cannot_sell_eggs_collected_after_the_sale_date(): void
    {
        $collection = EggCollection::factory()->create(['collection_date' => '2026-11-06', 'collected_count' => 50]);

        $this->actingAs(User::factory()->create())
            ->post(route('eggs.sales.store'), [
                'sale_date' => '2026-11-05',
                'price_mode' => 'egg',
                'price' => 80,
                'items' => [$collection->id => 10],
            ])
            ->assertSessionHasErrors("items.{$collection->id}");
    }

    public function test_sale_requires_at_least_one_quantity(): void
    {
        $collection = EggCollection::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('eggs.sales.store'), [
                'sale_date' => '2026-11-05',
                'price_mode' => 'egg',
                'price' => 80,
                'items' => [$collection->id => 0],
            ])
            ->assertSessionHasErrors('items');
    }

    public function test_deleting_a_sale_puts_eggs_back_in_stock(): void
    {
        $collection = EggCollection::factory()->create(['collected_count' => 100]);
        $sale = $this->sellFrom($collection, 40);

        $this->actingAs(User::factory()->create())
            ->delete(route('eggs.sales.destroy', $sale))
            ->assertRedirect(route('eggs.sales.index'));

        $this->assertSame(100, $collection->fresh()->remainingCount());
    }

    public function test_collection_cannot_be_reduced_below_what_was_sold(): void
    {
        $collection = EggCollection::factory()->create(['collection_date' => '2026-11-04', 'collected_count' => 100]);
        $this->sellFrom($collection, 80);

        $this->actingAs(User::factory()->create())
            ->put(route('eggs.collections.update', $collection), [
                'collection_date' => '2026-11-04',
                'collected_count' => 90,
                'broken_count' => 15,
            ])
            ->assertSessionHasErrors('collected_count');

        $this->assertSame(100, $collection->fresh()->collected_count);
    }

    public function test_collection_with_sales_cannot_be_deleted(): void
    {
        $collection = EggCollection::factory()->create();
        $this->sellFrom($collection, 10);

        $this->actingAs(User::factory()->create())
            ->delete(route('eggs.collections.destroy', $collection))
            ->assertSessionHas('error');

        $this->assertModelExists($collection);
    }

    public function test_sale_form_lists_only_days_with_remaining_eggs(): void
    {
        $soldOut = EggCollection::factory()->create(['collection_date' => '2026-11-01', 'collected_count' => 30]);
        $this->sellFrom($soldOut, 30);
        EggCollection::factory()->create(['collection_date' => '2026-11-02', 'collected_count' => 30]);

        $this->actingAs(User::factory()->create())
            ->get(route('eggs.sales.create'))
            ->assertOk()
            ->assertViewHas('collections', fn ($collections) => $collections->pluck('collection_date')
                ->map->toDateString()->all() === ['2026-11-02']);
    }

    private function sellFrom(EggCollection $collection, int $quantity): EggSale
    {
        $sale = EggSale::create([
            'sale_date' => $collection->collection_date,
            'quantity' => $quantity,
            'unit_price' => 80,
            'total_amount' => $quantity * 80,
        ]);

        $sale->items()->create(['egg_collection_id' => $collection->id, 'quantity' => $quantity]);

        return $sale;
    }
}

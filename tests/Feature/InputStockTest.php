<?php

namespace Tests\Feature;

use App\Enums\StockMovementType;
use App\Models\DailyReport;
use App\Models\Input;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InputStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_an_input_with_initial_stock_records_a_purchase(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('inputs.store'), [
                'name' => 'Provende ponte',
                'category' => 'feed',
                'unit' => 'kg',
                'initial_stock' => 500,
                'initial_unit_price' => 350,
            ])
            ->assertRedirect();

        $input = Input::sole();

        $this->assertSame(500.0, $input->currentStock());
        $this->assertSame(StockMovementType::Purchase, $input->movements()->sole()->type);
    }

    public function test_daily_report_deducts_served_feed_from_stock(): void
    {
        $feed = $this->feedWithStock(500);

        $this->actingAs(User::factory()->create())
            ->post(route('reports.store'), [
                'report_date' => '2026-11-01',
                'birds_count' => 455,
                'feed_quantity' => 48.5,
                'feed_input_id' => $feed->id,
                'feed_consumed' => 45,
            ])
            ->assertRedirect();

        $this->assertSame(451.5, $feed->fresh()->currentStock());
    }

    public function test_updating_consumption_does_not_duplicate_the_stock_exit(): void
    {
        $feed = $this->feedWithStock(500);
        $report = DailyReport::factory()->create(['feed_quantity' => 50, 'feed_input_id' => $feed->id]);

        $this->actingAs(User::factory()->create())
            ->put(route('reports.consumption.update', $report), ['feed_consumed' => 47]);

        $this->assertSame(450.0, $feed->fresh()->currentStock());
        $this->assertSame(1, $report->stockMovements()->count());
    }

    public function test_changing_served_quantity_resyncs_the_stock_exit(): void
    {
        $feed = $this->feedWithStock(500);
        $report = DailyReport::factory()->create(['feed_quantity' => 50, 'feed_input_id' => $feed->id]);

        $report->update(['feed_quantity' => 40]);
        $this->assertSame(460.0, $feed->fresh()->currentStock());

        $report->update(['feed_input_id' => null]);
        $this->assertSame(500.0, $feed->fresh()->currentStock());
    }

    public function test_report_rejects_an_input_that_is_not_feed(): void
    {
        $vaccine = Input::factory()->vaccine()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('reports.store'), [
                'report_date' => '2026-11-01',
                'birds_count' => 455,
                'feed_quantity' => 50,
                'feed_input_id' => $vaccine->id,
            ])
            ->assertSessionHasErrors('feed_input_id');
    }

    public function test_treatment_from_stock_deducts_quantity_and_takes_input_name(): void
    {
        $medicine = Input::factory()->medicine()->create(['name' => 'Oxytétracycline']);
        $medicine->movements()->create(['type' => StockMovementType::Purchase, 'quantity' => 200, 'movement_date' => '2026-10-01']);
        $report = DailyReport::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('treatments.store', $report), [
                'input_id' => $medicine->id,
                'quantity_used' => 25,
            ])
            ->assertRedirect(route('reports.show', $report));

        $this->assertSame('Oxytétracycline', $report->treatments()->sole()->name);
        $this->assertSame(175.0, $medicine->fresh()->currentStock());
    }

    public function test_treatment_from_stock_requires_quantity_used(): void
    {
        $medicine = Input::factory()->medicine()->create();
        $report = DailyReport::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('treatments.store', $report), ['input_id' => $medicine->id])
            ->assertSessionHasErrors('quantity_used');
    }

    public function test_vaccination_from_stock_deducts_doses(): void
    {
        $vaccine = Input::factory()->vaccine()->create();
        $vaccine->movements()->create(['type' => StockMovementType::Purchase, 'quantity' => 1000, 'movement_date' => '2026-10-01']);
        $report = DailyReport::factory()->create(['birds_count' => 455]);

        $this->actingAs(User::factory()->create())
            ->post(route('vaccinations.store', $report), [
                'input_id' => $vaccine->id,
                'quantity_used' => 500,
                'birds_count' => 455,
            ])
            ->assertRedirect();

        $this->assertSame(500.0, $vaccine->fresh()->currentStock());
    }

    public function test_loss_and_inventory_adjustment(): void
    {
        $feed = $this->feedWithStock(500);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('inputs.movements.store', $feed), [
            'type' => 'loss',
            'movement_date' => '2026-11-02',
            'quantity' => 20,
        ]);
        $this->assertSame(480.0, $feed->fresh()->currentStock());

        $this->actingAs($user)->post(route('inputs.movements.store', $feed), [
            'type' => 'adjustment',
            'movement_date' => '2026-11-03',
            'counted_stock' => 470,
        ]);
        $this->assertSame(470.0, $feed->fresh()->currentStock());
    }

    public function test_consumption_cannot_be_entered_manually(): void
    {
        $feed = $this->feedWithStock(500);

        $this->actingAs(User::factory()->create())
            ->post(route('inputs.movements.store', $feed), [
                'type' => 'consumption',
                'movement_date' => '2026-11-02',
                'quantity' => 20,
            ])
            ->assertSessionHasErrors('type');
    }

    public function test_synced_movements_cannot_be_deleted_by_hand(): void
    {
        $feed = $this->feedWithStock(500);
        $report = DailyReport::factory()->create(['feed_quantity' => 50, 'feed_input_id' => $feed->id]);

        $this->actingAs(User::factory()->create())
            ->delete(route('movements.destroy', $report->stockMovements()->sole()))
            ->assertSessionHas('error');

        $this->assertSame(450.0, $feed->fresh()->currentStock());
    }

    public function test_input_used_by_reports_cannot_be_deleted(): void
    {
        $feed = $this->feedWithStock(500);
        DailyReport::factory()->create(['feed_quantity' => 50, 'feed_input_id' => $feed->id]);

        $this->actingAs(User::factory()->create())
            ->delete(route('inputs.destroy', $feed))
            ->assertSessionHas('error');

        $this->assertModelExists($feed);
    }

    public function test_index_flags_inputs_below_alert_threshold(): void
    {
        $this->feedWithStock(50, Input::factory()->feed()->withAlertThreshold(100)->create(['name' => 'Provende démarrage']));
        $this->feedWithStock(500, Input::factory()->feed()->withAlertThreshold(100)->create());

        $this->actingAs(User::factory()->create())
            ->get(route('inputs.index'))
            ->assertOk()
            ->assertViewHas('lowStockCount', 1)
            ->assertSee('Provende démarrage');
    }

    private function feedWithStock(float $quantity, ?Input $feed = null): Input
    {
        $feed ??= Input::factory()->feed()->create();

        $feed->movements()->create([
            'type' => StockMovementType::Purchase,
            'quantity' => $quantity,
            'movement_date' => '2026-10-01',
        ]);

        return $feed;
    }
}

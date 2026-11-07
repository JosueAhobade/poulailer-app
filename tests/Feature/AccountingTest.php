<?php

namespace Tests\Feature;

use App\Enums\StockMovementType;
use App\Enums\TransactionCategory;
use App\Models\EggCollection;
use App\Models\EggSale;
use App\Models\Input;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_combines_egg_sales_input_purchases_and_manual_entries(): void
    {
        $this->travelTo('2026-11-15 10:00:00');

        EggCollection::factory()->create(['collection_date' => '2026-11-02', 'collected_count' => 1050, 'broken_count' => 50]);
        $this->eggSale('2026-11-05', 600, 48000, isPaid: true);
        $this->eggSale('2026-11-06', 300, 24000, isPaid: false);

        $feed = Input::factory()->feed()->create();
        $feed->movements()->create(['type' => StockMovementType::Purchase, 'quantity' => 100, 'unit_price' => 350, 'movement_date' => '2026-11-03']);
        $feed->movements()->create(['type' => StockMovementType::Purchase, 'quantity' => 50, 'movement_date' => '2026-11-04']);

        Transaction::factory()->expense(TransactionCategory::Labor)->create(['transaction_date' => '2026-11-10', 'amount' => 30000]);
        Transaction::factory()->income(TransactionCategory::BirdSale)->create(['transaction_date' => '2026-11-12', 'amount' => 10000]);
        Transaction::factory()->expense()->create(['transaction_date' => '2026-10-31', 'amount' => 99999]);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('accounting.index'))
            ->assertOk();

        $summary = $response->viewData('summary');

        $this->assertSame(82000.0, $summary['income']);
        $this->assertSame(65000.0, $summary['expense']);
        $this->assertSame(17000.0, $summary['result']);
        $this->assertSame(24000.0, $summary['unpaid_sales']);
        $this->assertSame(1, $summary['purchases_without_price']);
        $this->assertSame(1000, $summary['eggs_produced']);
        $this->assertSame(900, $summary['eggs_sold']);
        $this->assertSame(65.0, $summary['cost_per_egg']);
        $this->assertSame(80.0, $summary['average_egg_price']);
        $this->assertSame(35000.0, $summary['expense_by_category']['Achat : Provende']);

        $response->assertSee('rentable');
        $this->assertCount(5, $response->viewData('journal'));
    }

    public function test_loss_making_period_is_flagged(): void
    {
        Transaction::factory()->expense()->create(['transaction_date' => '2025-03-10', 'amount' => 20000]);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('accounting.index', ['period' => 'custom', 'from' => '2025-03-01', 'to' => '2025-03-31']));

        $this->assertSame(-20000.0, $response->viewData('summary')['result']);
        $response->assertSee('déficitaire');
    }

    public function test_custom_period_requires_dates(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('accounting.index', ['period' => 'custom']))
            ->assertSessionHasErrors(['from', 'to']);
    }

    public function test_admin_records_an_expense(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('transactions.store'), [
                'type' => 'expense',
                'category' => 'utilities',
                'transaction_date' => '2026-11-05',
                'label' => 'Facture électricité',
                'amount' => 12500,
            ])
            ->assertRedirect(route('accounting.index'));

        $transaction = Transaction::sole();
        $this->assertSame(TransactionCategory::Utilities, $transaction->category);
        $this->assertTrue($transaction->author->is($admin));
    }

    public function test_category_must_match_the_type(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('transactions.store'), [
                'type' => 'income',
                'category' => 'labor',
                'transaction_date' => '2026-11-05',
                'label' => 'Incohérent',
                'amount' => 1000,
            ])
            ->assertSessionHasErrors('category');
    }

    public function test_admin_updates_and_deletes_an_entry(): void
    {
        $transaction = Transaction::factory()->expense()->create(['amount' => 1000]);
        $this->actingAs(User::factory()->admin()->create());

        $this->put(route('transactions.update', $transaction), [
            'type' => 'expense',
            'category' => 'transport',
            'transaction_date' => '2026-11-05',
            'label' => 'Taxi-moto marché',
            'amount' => 1500,
        ])->assertRedirect(route('accounting.index'));

        $this->assertSame('1500.00', $transaction->fresh()->amount);

        $this->get(route('transactions.edit', $transaction))->assertOk();

        $this->delete(route('transactions.destroy', $transaction));
        $this->assertModelMissing($transaction);
    }

    private function eggSale(string $date, int $quantity, int $total, bool $isPaid): EggSale
    {
        return EggSale::create([
            'sale_date' => $date,
            'quantity' => $quantity,
            'unit_price' => $total / $quantity,
            'total_amount' => $total,
            'is_paid' => $isPaid,
        ]);
    }
}

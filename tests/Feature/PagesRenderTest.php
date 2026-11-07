<?php

namespace Tests\Feature;

use App\Enums\StockMovementType;
use App\Models\DailyReport;
use App\Models\EggCollection;
use App\Models\EggSale;
use App\Models\Input;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_management_pages_render_with_data(): void
    {
        $feed = Input::factory()->feed()->withAlertThreshold(1000)->create();
        $feed->movements()->create(['type' => StockMovementType::Purchase, 'quantity' => 500, 'movement_date' => now()->subDays(3), 'unit_price' => 350, 'supplier' => 'Coopérative']);
        $medicine = Input::factory()->medicine()->create();
        $vaccine = Input::factory()->vaccine()->create();

        $report = DailyReport::factory()->create(['report_date' => now()->toDateString(), 'feed_input_id' => $feed->id, 'feed_consumed' => 40]);
        $report->treatments()->create(['name' => $medicine->name, 'input_id' => $medicine->id, 'quantity_used' => 5]);
        $report->vaccinations()->create(['vaccine_name' => $vaccine->name, 'input_id' => $vaccine->id, 'quantity_used' => 455, 'birds_count' => 455]);

        $collection = EggCollection::factory()->create(['collection_date' => now()->subDay()->toDateString(), 'collected_count' => 300]);
        $sale = EggSale::create(['sale_date' => now(), 'quantity' => 60, 'unit_price' => 80, 'total_amount' => 4800, 'is_paid' => false]);
        $sale->items()->create(['egg_collection_id' => $collection->id, 'quantity' => 60]);

        $this->actingAs(User::factory()->admin()->create());

        $urls = [
            route('dashboard'),
            route('reports.index'),
            route('reports.create'),
            route('reports.show', $report),
            route('reports.consumption.edit', $report),
            route('treatments.create', $report),
            route('vaccinations.create', $report),
            route('inputs.index'),
            route('inputs.index', ['category' => 'vaccine']),
            route('inputs.create'),
            route('inputs.show', $feed),
            route('inputs.edit', $feed),
            route('inputs.movements.create', $feed),
            route('inputs.movements.create', [$feed, 'type' => 'adjustment']),
            route('eggs.collections.index'),
            route('eggs.collections.create'),
            route('eggs.collections.show', $collection),
            route('eggs.collections.edit', $collection),
            route('eggs.sales.index'),
            route('eggs.sales.create'),
            route('eggs.sales.show', $sale),
            route('accounting.index'),
            route('accounting.index', ['period' => 'all']),
            route('transactions.create', ['type' => 'income']),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }
}

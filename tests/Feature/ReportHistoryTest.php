<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\EggCollection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $report = DailyReport::factory()->create();

        $this->get(route('reports.index'))->assertRedirect(route('login'));
        $this->get(route('reports.show', $report))->assertRedirect(route('login'));
    }

    public function test_history_is_paginated_newest_first(): void
    {
        DailyReport::factory()
            ->count(DailyReport::count() + 16)
            ->sequence(fn ($sequence) => ['report_date' => now()->subDays($sequence->index)->toDateString()])
            ->create();

        $response = $this->actingAs(User::factory()->create())->get(route('reports.index'));

        $response->assertOk();
        $this->assertCount(15, $response->viewData('reports'));
        $this->assertTrue($response->viewData('reports')->first()->report_date->isToday());
        $response->assertSee('page=2', false);

        $secondPage = $this->get(route('reports.index', ['page' => 2]));
        $this->assertCount(1, $secondPage->viewData('reports'));
    }

    public function test_history_can_be_filtered_by_date_range(): void
    {
        DailyReport::factory()->create(['report_date' => '2026-10-01']);
        DailyReport::factory()->create(['report_date' => '2026-10-15']);
        DailyReport::factory()->create(['report_date' => '2026-11-01']);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('reports.index', ['from' => '2026-10-10', 'to' => '2026-10-31']));

        $this->assertSame(['2026-10-15'], $response->viewData('reports')
            ->map(fn (DailyReport $report) => $report->report_date->toDateString())
            ->all());
    }

    public function test_detail_page_shows_all_report_information(): void
    {
        $report = DailyReport::factory()->create([
            'report_date' => '2026-10-20',
            'temperature' => 31.5,
            'birds_weight' => 1.85,
            'observations' => 'Poules un peu agitées le matin',
        ]);
        $report->treatments()->create(['name' => 'Anticoccidien', 'reason' => 'Diarrhée']);
        $report->vaccinations()->create(['vaccine_name' => 'Gumboro', 'birds_count' => 400, 'batch_number' => 'LOT-42']);
        EggCollection::factory()->create(['collection_date' => '2026-10-20', 'collected_count' => 380]);

        $this->actingAs(User::factory()->create())
            ->get(route('reports.show', $report))
            ->assertOk()
            ->assertSee('31,5 °C')
            ->assertSee('1,85 kg')
            ->assertSee('Poules un peu agitées le matin')
            ->assertSee('Anticoccidien')
            ->assertSee('Diarrhée')
            ->assertSee('Gumboro')
            ->assertSee('LOT-42')
            ->assertSee('380');
    }

    public function test_history_rows_link_to_the_detail_page(): void
    {
        $report = DailyReport::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('reports.index'))
            ->assertSee('data-href="'.route('reports.show', $report).'"', false);
    }
}

<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_covers_all_reports_since_the_first_one_by_default(): void
    {
        $this->travelTo('2026-11-10 12:00:00');
        DailyReport::factory()->create(['report_date' => '2026-09-01']);
        DailyReport::factory()->create(['report_date' => '2026-11-10']);

        $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

        $response->assertOk()
            ->assertViewHas('periodKey', 'all')
            ->assertViewHas('period', 71)
            ->assertSee('depuis le début (71 jours)');

        $this->assertCount(71, $response->viewData('chartData')['labels']);
        $this->assertSame('01/09', $response->viewData('chartData')['labels'][0]);
    }

    public function test_dashboard_can_be_limited_to_a_fixed_number_of_days(): void
    {
        $this->travelTo('2026-11-10 12:00:00');
        DailyReport::factory()->create(['report_date' => '2026-09-01']);

        $response = $this->actingAs(User::factory()->create())->get(route('dashboard', ['period' => 90]));

        $response->assertViewHas('periodKey', '90')->assertViewHas('period', 90);
        $this->assertCount(90, $response->viewData('chartData')['labels']);
    }

    public function test_unknown_period_falls_back_to_since_the_beginning(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('dashboard', ['period' => 1000]));

        $response->assertOk()->assertViewHas('periodKey', 'all');
    }
}

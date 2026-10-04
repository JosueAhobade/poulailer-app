<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyReport;
use Carbon\CarbonImmutable;

class DashboardController extends Controller
{
    public function index()
    {
        // Journée de référence : heure locale du poulailler (Bénin)
        $today = CarbonImmutable::now('Africa/Porto-Novo')
            ->startOfDay();

        $todayDate = $today->toDateString();

        // Rapport du jour
        $todayReport = DailyReport::where(
            'report_date',
            $todayDate
        )->first();

        // Dernier rapport disponible
        $latestReport = DailyReport::orderByDesc('report_date')
            ->first();

        // Mortalité cumulée dans les rapports enregistrés
        $totalDeathsRecorded = DailyReport::sum('deaths_count');

        // ------------------------------------------------
        // MOYENNES SUR LES 7 DERNIERS JOURS
        // ------------------------------------------------

        $reports7 = DailyReport::whereBetween('report_date', [
            $today->subDays(6)->toDateString(),
            $todayDate,
        ])->get();

        $feedRecorded = $reports7->filter(
            fn ($report) => $report->feed_quantity !== null
        );

        $waterRecorded = $reports7->filter(
            fn ($report) => $report->water_quantity !== null
        );

        $averageFeed = $feedRecorded->avg(
            fn ($report) => (float) $report->feed_quantity
        );

        $averageWater = $waterRecorded->avg(
            fn ($report) => (float) $report->water_quantity
        );

        $feedDays = $feedRecorded->count();
        $waterDays = $waterRecorded->count();

        // ------------------------------------------------
        // GRAPHIQUES : 14 DERNIERS JOURS
        // ------------------------------------------------

        $start = $today->subDays(13);

        $reports = DailyReport::whereBetween('report_date', [
            $start->toDateString(),
            $todayDate,
        ])
        ->orderBy('report_date')
        ->get()
        ->keyBy(fn ($report) => $report->report_date->format('Y-m-d'));

        $chartData = [
            'labels' => [],
            'water' => [],
            'feed' => [],
            'birds' => [],
        ];

        for ($i = 0; $i < 14; $i++) {

            $date = $start->addDays($i);
            $key = $date->toDateString();

            $report = $reports->get($key);

            $chartData['labels'][] = $date->format('d/m');

            $chartData['water'][] =
                $report && $report->water_quantity !== null
                    ? (float) $report->water_quantity
                    : null;

            $chartData['feed'][] =
                $report && $report->feed_quantity !== null
                    ? (float) $report->feed_quantity
                    : null;

            $chartData['birds'][] =
                $report ? (int) $report->birds_count : null;
        }

        // Disponibilité des données par graphique
        $hasWater = collect($chartData['water'])->contains(
            fn ($value) => $value !== null
        );

        $hasFeed = collect($chartData['feed'])->contains(
            fn ($value) => $value !== null
        );

        $hasBirds = collect($chartData['birds'])->contains(
            fn ($value) => $value !== null
        );

        // ------------------------------------------------
        // 5 DERNIERS RAPPORTS + INTERVENTIONS SANITAIRES
        // ------------------------------------------------

        $recentReports = DailyReport::withCount([
            'treatments',
            'vaccinations',
        ])
        ->orderByDesc('report_date')
        ->limit(5)
        ->get();

        return view('dashboard', compact(
            'today',
            'todayReport',
            'latestReport',
            'totalDeathsRecorded',
            'averageFeed',
            'averageWater',
            'feedDays',
            'waterDays',
            'chartData',
            'hasWater',
            'hasFeed',
            'hasBirds',
            'recentReports'
        ));
    }
}

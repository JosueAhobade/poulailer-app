<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Filtre sécurisé : uniquement 7, 14 ou 30 jours
        $period = filter_var(
            $request->query('period'),
            FILTER_VALIDATE_INT
        );

        $period = in_array($period, [7, 14, 30], true)
            ? $period
            : 14;

        // Date locale du poulailler
        $today = CarbonImmutable::now('Africa/Porto-Novo')
            ->startOfDay();

        $todayDate = $today->toDateString();

        $start = $today->subDays($period - 1);
        $startDate = $start->toDateString();

        // -----------------------------------------
        // INDICATEURS GÉNÉRAUX
        // -----------------------------------------

        $todayReport = DailyReport::where(
            'report_date',
            $todayDate
        )->first();

        $latestReport = DailyReport::where(
            'report_date',
            '<=',
            $todayDate
        )
        ->orderByDesc('report_date')
        ->first();

        $totalDeathsRecorded = DailyReport::where(
            'report_date',
            '<=',
            $todayDate
        )->sum('deaths_count');

        // -----------------------------------------
        // DONNÉES DE LA PÉRIODE
        // -----------------------------------------

        $rangeReports = DailyReport::whereBetween(
            'report_date',
            [$startDate, $todayDate]
        )
        ->orderBy('report_date')
        ->get();

        // Moyennes : on exclut les valeurs absentes
        $averageFeed = $rangeReports
            ->whereNotNull('feed_quantity')
            ->avg('feed_quantity');

        $averageWater = $rangeReports
            ->whereNotNull('water_quantity')
            ->avg('water_quantity');

        $averageFeedConsumed = $rangeReports
            ->whereNotNull('feed_consumed')
            ->avg('feed_consumed');

        $averageWaterConsumed = $rangeReports
            ->whereNotNull('water_consumed')
            ->avg('water_consumed');

        $feedDays = $rangeReports
            ->whereNotNull('feed_quantity')
            ->count();

        $waterDays = $rangeReports
            ->whereNotNull('water_quantity')
            ->count();

        $feedConsumedDays = $rangeReports
            ->whereNotNull('feed_consumed')
            ->count();

        $waterConsumedDays = $rangeReports
            ->whereNotNull('water_consumed')
            ->count();

        // -----------------------------------------
        // PRÉPARATION DES GRAPHIQUES
        // -----------------------------------------

        $reportsByDate = $rangeReports->keyBy(
            fn ($report) => $report->report_date->format('Y-m-d')
        );

        $chartData = [
            'labels' => [],

            'feedServed' => [],
            'feedConsumed' => [],

            'waterServed' => [],
            'waterConsumed' => [],

            'birds' => [],
        ];

        for ($i = 0; $i < $period; $i++) {

            $date = $start->addDays($i);
            $key = $date->toDateString();

            $report = $reportsByDate->get($key);

            $chartData['labels'][] = $date->format('d/m');

            $chartData['feedServed'][] =
                $report && $report->feed_quantity !== null
                    ? (float) $report->feed_quantity
                    : null;

            $chartData['feedConsumed'][] =
                $report && $report->feed_consumed !== null
                    ? (float) $report->feed_consumed
                    : null;

            $chartData['waterServed'][] =
                $report && $report->water_quantity !== null
                    ? (float) $report->water_quantity
                    : null;

            $chartData['waterConsumed'][] =
                $report && $report->water_consumed !== null
                    ? (float) $report->water_consumed
                    : null;

            $chartData['birds'][] =
                $report
                    ? (int) $report->birds_count
                    : null;
        }

        // -----------------------------------------
        // DISPONIBILITÉ DES DONNÉES
        // -----------------------------------------

        $hasFeed = $feedDays > 0 || $feedConsumedDays > 0;

        $hasWater = $waterDays > 0 || $waterConsumedDays > 0;

        $hasBirds = $rangeReports->isNotEmpty();

        // -----------------------------------------
        // DERNIÈRES ACTIVITÉS
        // -----------------------------------------

        $recentReports = DailyReport::withCount([
            'treatments',
            'vaccinations',
        ])
        ->where('report_date', '<=', $todayDate)
        ->orderByDesc('report_date')
        ->limit(5)
        ->get();

        return view('dashboard', compact(
            'period',
            'today',
            'todayReport',
            'latestReport',
            'totalDeathsRecorded',
            'averageFeed',
            'averageWater',
            'averageFeedConsumed',
            'averageWaterConsumed',
            'feedDays',
            'waterDays',
            'feedConsumedDays',
            'waterConsumedDays',
            'chartData',
            'hasFeed',
            'hasWater',
            'hasBirds',
            'recentReports'
        ));
    }
}

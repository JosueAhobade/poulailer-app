<?php

namespace App\Http\Controllers;

use App\Services\AccountingReport;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class AccountingController extends Controller
{
    public const PER_PAGE = 20;

    /**
     * @var array<string, string>
     */
    public const PERIODS = [
        'month' => 'Ce mois-ci',
        'last_month' => 'Mois dernier',
        'year' => 'Cette année',
        'last_year' => 'Année dernière',
        'all' => 'Depuis le début',
        'custom' => 'Personnalisée',
    ];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'period' => ['nullable', 'in:'.implode(',', array_keys(self::PERIODS))],
            'from' => ['nullable', 'required_if:period,custom', 'date'],
            'to' => ['nullable', 'required_if:period,custom', 'date', 'after_or_equal:from'],
        ]);

        $period = $filters['period'] ?? 'month';
        [$from, $to] = $this->resolvePeriod($period, $filters);

        $report = new AccountingReport($from, $to);
        $summary = $report->summary();

        $entries = $report->entries();
        $page = LengthAwarePaginator::resolveCurrentPage();
        $journal = new LengthAwarePaginator(
            $entries->forPage($page, self::PER_PAGE)->values(),
            $entries->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $chart = AccountingReport::monthly($to ?? $this->today());

        return view('accounting.index', [
            'periods' => self::PERIODS,
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'summary' => $summary,
            'journal' => $journal,
            'chart' => $chart,
        ]);
    }

    /**
     * @param  array{from?: string, to?: string}  $filters
     * @return array{0: ?CarbonImmutable, 1: ?CarbonImmutable}
     */
    private function resolvePeriod(string $period, array $filters): array
    {
        $today = $this->today();

        return match ($period) {
            'last_month' => [$today->subMonthNoOverflow()->startOfMonth(), $today->subMonthNoOverflow()->endOfMonth()],
            'year' => [$today->startOfYear(), $today->endOfYear()],
            'last_year' => [$today->subYear()->startOfYear(), $today->subYear()->endOfYear()],
            'all' => [null, null],
            'custom' => [CarbonImmutable::parse($filters['from']), CarbonImmutable::parse($filters['to'])],
            default => [$today->startOfMonth(), $today->endOfMonth()],
        };
    }

    private function today(): CarbonImmutable
    {
        return CarbonImmutable::now('Africa/Porto-Novo')->startOfDay();
    }
}

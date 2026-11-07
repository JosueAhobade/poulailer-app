<?php

namespace App\Http\Controllers;

use App\Enums\InputCategory;
use App\Models\DailyReport;
use App\Models\EggCollection;
use App\Models\Input;
use App\Models\StockMovement;
use App\Models\Treatment;
use App\Models\Vaccination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DailyReportController extends Controller
{
    public const PER_PAGE = 15;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $reports = DailyReport::withCount(['treatments', 'vaccinations'])
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('report_date', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('report_date', '<=', $to))
            ->orderByDesc('report_date')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('reports.index', compact('reports', 'filters'));
    }

    public function show(DailyReport $report): View
    {
        $report->load([
            'feedInput',
            'treatments.input',
            'vaccinations.input',
        ]);

        $stockMovements = StockMovement::with('input')
            ->where(function ($query) use ($report) {
                $query->whereMorphedTo('source', $report)
                    ->orWhere(fn ($query) => $query
                        ->where('source_type', (new Treatment)->getMorphClass())
                        ->whereIn('source_id', $report->treatments->modelKeys()))
                    ->orWhere(fn ($query) => $query
                        ->where('source_type', (new Vaccination)->getMorphClass())
                        ->whereIn('source_id', $report->vaccinations->modelKeys()));
            })
            ->orderBy('id')
            ->get();

        $eggCollection = EggCollection::withSoldCount()
            ->whereDate('collection_date', $report->report_date)
            ->first();

        $previousReport = DailyReport::where('report_date', '<', $report->report_date)
            ->orderByDesc('report_date')
            ->first(['id', 'report_date']);

        $nextReport = DailyReport::where('report_date', '>', $report->report_date)
            ->orderBy('report_date')
            ->first(['id', 'report_date']);

        return view('reports.show', compact(
            'report',
            'stockMovements',
            'eggCollection',
            'previousReport',
            'nextReport'
        ));
    }

    public function create(): View
    {
        $feedInputs = Input::ofCategory(InputCategory::Feed)
            ->withStock()
            ->orderBy('name')
            ->get();

        $lastFeedInputId = DailyReport::whereNotNull('feed_input_id')
            ->orderByDesc('report_date')
            ->value('feed_input_id');

        return view('reports.create', compact('feedInputs', 'lastFeedInputId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'report_date' => ['required', 'date', 'unique:daily_reports,report_date'],
            'birds_count' => ['required', 'integer', 'min:0'],
            'deaths_count' => ['nullable', 'integer', 'min:0'],
            'sick_count' => ['nullable', 'integer', 'min:0'],
            'feed_quantity' => ['nullable', 'numeric', 'min:0'],
            'feed_input_id' => [
                'nullable',
                Rule::exists('inputs', 'id')->where('category', InputCategory::Feed->value),
            ],
            'water_quantity' => ['nullable', 'numeric', 'min:0'],
            'birds_weight' => ['nullable', 'numeric', 'min:0'],
            'temperature' => ['nullable', 'numeric'],
            'observations' => ['nullable', 'string'],
            'feed_consumed' => ['nullable', 'numeric', 'min:0'],
            'water_consumed' => ['nullable', 'numeric', 'min:0'],
        ]);

        $report = DailyReport::create($validated);

        return redirect()
            ->route('reports.show', $report)
            ->with('success', 'Rapport enregistré avec succès.');
    }

    public function editConsumption(DailyReport $report): View
    {
        return view('reports.consumption', compact('report'));
    }

    public function updateConsumption(Request $request, DailyReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'feed_consumed' => ['nullable', 'numeric', 'min:0'],
            'water_consumed' => ['nullable', 'numeric', 'min:0'],
        ]);

        $report->update($validated);

        return redirect()
            ->route('reports.show', $report)
            ->with('success', 'Consommations mises à jour.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\InputCategory;
use App\Enums\StockMovementType;
use App\Models\Input;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InputController extends Controller
{
    /**
     * Fenêtre (en jours) utilisée pour estimer la consommation moyenne.
     */
    public const CONSUMPTION_WINDOW_DAYS = 14;

    public function index(Request $request): View
    {
        $category = InputCategory::tryFrom((string) $request->query('category'));

        $since = CarbonImmutable::now('Africa/Porto-Novo')
            ->subDays(self::CONSUMPTION_WINDOW_DAYS - 1)
            ->toDateString();

        $inputs = Input::withStock()
            ->withSum([
                'movements as recent_consumption' => fn ($query) => $query
                    ->where('type', StockMovementType::Consumption)
                    ->whereDate('movement_date', '>=', $since),
            ], 'quantity')
            ->when($category, fn ($query) => $query->ofCategory($category))
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $lowStockCount = $inputs->filter->isLowStock()->count();

        return view('inputs.index', [
            'inputs' => $inputs,
            'category' => $category,
            'lowStockCount' => $lowStockCount,
            'windowDays' => self::CONSUMPTION_WINDOW_DAYS,
        ]);
    }

    public function create(Request $request): View
    {
        $input = new Input([
            'category' => InputCategory::tryFrom((string) $request->query('category')) ?? InputCategory::Feed,
        ]);

        return view('inputs.form', compact('input'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateInput($request);

        $initial = $request->validate([
            'initial_stock' => ['nullable', 'numeric', 'gt:0'],
            'initial_unit_price' => ['nullable', 'numeric', 'min:0'],
            'initial_supplier' => ['nullable', 'string', 'max:255'],
        ]);

        $input = DB::transaction(function () use ($validated, $initial) {
            $input = Input::create($validated);

            if (! empty($initial['initial_stock'])) {
                $input->movements()->create([
                    'type' => StockMovementType::Purchase,
                    'quantity' => $initial['initial_stock'],
                    'movement_date' => CarbonImmutable::now('Africa/Porto-Novo')->toDateString(),
                    'unit_price' => $initial['initial_unit_price'] ?? null,
                    'supplier' => $initial['initial_supplier'] ?? null,
                    'notes' => 'Stock initial',
                ]);
            }

            return $input;
        });

        return redirect()
            ->route('inputs.show', $input)
            ->with('success', 'Intrant enregistré.');
    }

    public function show(Input $input): View
    {
        $input->loadSum('movements as stock', 'quantity');

        $movements = $input->movements()
            ->with('source')
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->paginate(20);

        $totals = $input->movements()
            ->selectRaw('type, sum(quantity) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        return view('inputs.show', compact('input', 'movements', 'totals'));
    }

    public function edit(Input $input): View
    {
        return view('inputs.form', compact('input'));
    }

    public function update(Request $request, Input $input): RedirectResponse
    {
        $input->update($this->validateInput($request, $input));

        return redirect()
            ->route('inputs.show', $input)
            ->with('success', 'Intrant mis à jour.');
    }

    public function destroy(Input $input): RedirectResponse
    {
        if ($input->movements()->whereNotNull('source_type')->exists()) {
            return back()->with(
                'error',
                'Cet intrant est utilisé dans des rapports journaliers : il ne peut pas être supprimé.'
            );
        }

        $input->delete();

        return redirect()
            ->route('inputs.index')
            ->with('success', 'Intrant supprimé.');
    }

    /**
     * @return array{name: string, category: string, unit: string, alert_threshold: ?string, notes: ?string}
     */
    private function validateInput(Request $request, ?Input $input = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('inputs')
                    ->where('category', $request->input('category'))
                    ->ignore($input),
            ],
            'category' => ['required', Rule::enum(InputCategory::class)],
            'unit' => ['required', 'string', 'max:30'],
            'alert_threshold' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}

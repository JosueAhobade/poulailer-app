<?php

namespace App\Http\Controllers;

use App\Models\EggCollection;
use App\Models\EggSale;
use App\Models\EggSaleItem;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EggCollectionController extends Controller
{
    public const PER_PAGE = 15;

    public function index(): View
    {
        $collections = EggCollection::withSoldCount()
            ->orderByDesc('collection_date')
            ->paginate(self::PER_PAGE);

        $monthStart = CarbonImmutable::now('Africa/Porto-Novo')->startOfMonth()->toDateString();

        $sellableTotal = (int) EggCollection::sum('collected_count') - (int) EggCollection::sum('broken_count');

        $summary = [
            'stock' => $sellableTotal - (int) EggSaleItem::sum('quantity'),
            'collected_month' => (int) EggCollection::whereDate('collection_date', '>=', $monthStart)->sum('collected_count'),
            'broken_month' => (int) EggCollection::whereDate('collection_date', '>=', $monthStart)->sum('broken_count'),
            'sold_month' => (int) EggSale::whereDate('sale_date', '>=', $monthStart)->sum('quantity'),
            'revenue_month' => (float) EggSale::whereDate('sale_date', '>=', $monthStart)->sum('total_amount'),
        ];

        return view('eggs.collections.index', compact('collections', 'summary'));
    }

    public function show(EggCollection $collection): View
    {
        $collection->loadSum('saleItems as sold_count', 'quantity');
        $collection->load(['saleItems.sale']);

        return view('eggs.collections.show', compact('collection'));
    }

    public function create(): View
    {
        $collection = new EggCollection([
            'collection_date' => CarbonImmutable::now('Africa/Porto-Novo')->toDateString(),
            'broken_count' => 0,
        ]);

        return view('eggs.collections.form', compact('collection'));
    }

    public function store(Request $request): RedirectResponse
    {
        EggCollection::create($this->validateCollection($request));

        return redirect()
            ->route('eggs.collections.index')
            ->with('success', 'Ramassage enregistré.');
    }

    public function edit(EggCollection $collection): View
    {
        return view('eggs.collections.form', compact('collection'));
    }

    public function update(Request $request, EggCollection $collection): RedirectResponse
    {
        $validated = $this->validateCollection($request, $collection);

        $sold = $collection->soldCount();

        if ($sold > $validated['collected_count'] - ($validated['broken_count'] ?? 0)) {
            throw ValidationException::withMessages([
                'collected_count' => "{$sold} œufs de ce ramassage ont déjà été vendus : le nombre d'œufs vendables ne peut pas être inférieur.",
            ]);
        }

        $collection->update($validated);

        return redirect()
            ->route('eggs.collections.show', $collection)
            ->with('success', 'Ramassage mis à jour.');
    }

    public function destroy(EggCollection $collection): RedirectResponse
    {
        if ($collection->saleItems()->exists()) {
            return back()->with('error', 'Des œufs de ce ramassage ont été vendus : supprimez d\'abord les ventes concernées.');
        }

        $collection->delete();

        return redirect()
            ->route('eggs.collections.index')
            ->with('success', 'Ramassage supprimé.');
    }

    /**
     * @return array{collection_date: string, collected_count: int, broken_count: ?int, notes: ?string}
     */
    private function validateCollection(Request $request, ?EggCollection $collection = null): array
    {
        return $request->validate([
            'collection_date' => [
                'required',
                'date',
                function (string $attribute, mixed $value, Closure $fail) use ($collection) {
                    $exists = EggCollection::whereDate('collection_date', $value)
                        ->when($collection, fn ($query) => $query->whereKeyNot($collection->getKey()))
                        ->exists();

                    if ($exists) {
                        $fail('Un ramassage existe déjà pour cette date : modifiez-le plutôt.');
                    }
                },
            ],
            'collected_count' => ['required', 'integer', 'min:0'],
            'broken_count' => ['nullable', 'integer', 'min:0', 'lte:collected_count'],
            'notes' => ['nullable', 'string'],
        ], [
            'broken_count.lte' => 'Le nombre d\'œufs cassés ne peut pas dépasser le nombre ramassé.',
        ]);
    }
}

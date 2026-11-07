<?php

namespace App\Http\Controllers;

use App\Models\EggCollection;
use App\Models\EggSale;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EggSaleController extends Controller
{
    public const PER_PAGE = 15;

    public function index(): View
    {
        $sales = EggSale::with('items.collection')
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE);

        $unpaidTotal = (float) EggSale::where('is_paid', false)->sum('total_amount');

        return view('eggs.sales.index', compact('sales', 'unpaidTotal'));
    }

    public function create(): View
    {
        $collections = EggCollection::available()->get();

        $today = CarbonImmutable::now('Africa/Porto-Novo')->toDateString();

        $lastUnitPrice = EggSale::latest('id')->value('unit_price');

        return view('eggs.sales.create', compact('collections', 'today', 'lastUnitPrice'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sale_date' => ['required', 'date'],
            'customer' => ['nullable', 'string', 'max:255'],
            'price_mode' => ['required', 'in:egg,tray'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_paid' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array'],
            'items.*' => ['nullable', 'integer', 'min:0'],
        ], [
            'items.required' => 'Indiquez au moins un jour de ramassage.',
        ]);

        $requested = collect($validated['items'])
            ->map(fn ($quantity) => (int) $quantity)
            ->filter(fn (int $quantity) => $quantity > 0);

        if ($requested->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Indiquez la quantité vendue pour au moins un jour de ramassage.',
            ]);
        }

        $sale = DB::transaction(function () use ($validated, $requested) {
            $collections = EggCollection::withSoldCount()
                ->whereKey($requested->keys())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $errors = [];

            foreach ($requested as $collectionId => $quantity) {
                $collection = $collections->get($collectionId);

                if (! $collection) {
                    $errors["items.{$collectionId}"] = 'Ramassage introuvable.';

                    continue;
                }

                $label = $collection->collection_date->format('d/m/Y');

                if ($collection->collection_date->toDateString() > $validated['sale_date']) {
                    $errors["items.{$collectionId}"] = "Les œufs du {$label} sont postérieurs à la date de vente.";
                } elseif ($quantity > $collection->remainingCount()) {
                    $errors["items.{$collectionId}"] = "Il ne reste que {$collection->remainingCount()} œufs du {$label}.";
                }
            }

            if ($errors !== []) {
                throw ValidationException::withMessages($errors);
            }

            $quantity = $requested->sum();

            $total = $validated['price_mode'] === 'tray'
                ? $quantity / EggSale::EGGS_PER_TRAY * (float) $validated['price']
                : $quantity * (float) $validated['price'];

            $unitPrice = $validated['price_mode'] === 'tray'
                ? (float) $validated['price'] / EggSale::EGGS_PER_TRAY
                : (float) $validated['price'];

            $sale = EggSale::create([
                'sale_date' => $validated['sale_date'],
                'customer' => $validated['customer'] ?? null,
                'quantity' => $quantity,
                'unit_price' => round($unitPrice, 2),
                'total_amount' => round($total, 2),
                'is_paid' => (bool) ($validated['is_paid'] ?? false),
                'notes' => $validated['notes'] ?? null,
            ]);

            $sale->items()->createMany(
                $requested->map(fn (int $quantity, $collectionId) => [
                    'egg_collection_id' => $collectionId,
                    'quantity' => $quantity,
                ])->values()->all()
            );

            return $sale;
        });

        return redirect()
            ->route('eggs.sales.show', $sale)
            ->with('success', 'Vente enregistrée.');
    }

    public function show(EggSale $sale): View
    {
        $sale->load(['items.collection']);

        return view('eggs.sales.show', compact('sale'));
    }

    public function markPaid(EggSale $sale): RedirectResponse
    {
        $sale->update(['is_paid' => true]);

        return back()->with('success', 'Vente marquée comme payée.');
    }

    public function destroy(EggSale $sale): RedirectResponse
    {
        $sale->delete();

        return redirect()
            ->route('eggs.sales.index')
            ->with('success', 'Vente supprimée : les œufs ont été remis en stock.');
    }
}

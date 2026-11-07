<?php

namespace App\Http\Controllers;

use App\Enums\StockMovementType;
use App\Models\Input;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function create(Request $request, Input $input): View
    {
        $input->loadSum('movements as stock', 'quantity');

        $type = StockMovementType::tryFrom((string) $request->query('type'));

        if (! in_array($type, StockMovementType::manual(), true)) {
            $type = StockMovementType::Purchase;
        }

        return view('stock-movements.create', compact('input', 'type'));
    }

    public function store(Request $request, Input $input): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::enum(StockMovementType::class)->only(StockMovementType::manual())],
            'movement_date' => ['required', 'date'],
            'quantity' => ['exclude_if:type,adjustment', 'required', 'numeric', 'gt:0'],
            'counted_stock' => ['exclude_unless:type,adjustment', 'required', 'numeric', 'min:0'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $type = StockMovementType::from($validated['type']);

        $quantity = match ($type) {
            StockMovementType::Purchase => (float) $validated['quantity'],
            StockMovementType::Loss => -(float) $validated['quantity'],
            StockMovementType::Adjustment => (float) $validated['counted_stock'] - $input->currentStock(),
        };

        if (round($quantity, 2) == 0.0) {
            return redirect()
                ->route('inputs.show', $input)
                ->with('success', 'Le stock compté correspond déjà au stock théorique : aucun ajustement nécessaire.');
        }

        $input->movements()->create([
            'type' => $type,
            'quantity' => $quantity,
            'movement_date' => $validated['movement_date'],
            'unit_price' => $type === StockMovementType::Purchase ? ($validated['unit_price'] ?? null) : null,
            'supplier' => $type === StockMovementType::Purchase ? ($validated['supplier'] ?? null) : null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('inputs.show', $input)
            ->with('success', 'Mouvement de stock enregistré.');
    }

    public function destroy(StockMovement $movement): RedirectResponse
    {
        if ($movement->isSynced()) {
            return back()->with(
                'error',
                'Ce mouvement provient d\'un rapport journalier : modifiez le rapport, pas le stock.'
            );
        }

        $input = $movement->input;

        $movement->delete();

        return redirect()
            ->route('inputs.show', $input)
            ->with('success', 'Mouvement supprimé.');
    }
}

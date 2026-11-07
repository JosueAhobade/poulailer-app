<?php

namespace App\Http\Controllers;

use App\Enums\TransactionCategory;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function create(Request $request): View
    {
        $type = TransactionType::tryFrom((string) $request->query('type')) ?? TransactionType::Expense;

        $transaction = new Transaction([
            'type' => $type,
            'transaction_date' => CarbonImmutable::now('Africa/Porto-Novo')->toDateString(),
        ]);

        return view('transactions.form', compact('transaction'));
    }

    public function store(Request $request): RedirectResponse
    {
        $transaction = new Transaction($this->validateTransaction($request));
        $transaction->author()->associate($request->user());
        $transaction->save();

        return redirect()
            ->route('accounting.index')
            ->with('success', $transaction->type->label().' enregistrée.');
    }

    public function edit(Transaction $transaction): View
    {
        return view('transactions.form', compact('transaction'));
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $transaction->update($this->validateTransaction($request));

        return redirect()
            ->route('accounting.index')
            ->with('success', 'Écriture mise à jour.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $transaction->delete();

        return redirect()
            ->route('accounting.index')
            ->with('success', 'Écriture supprimée.');
    }

    /**
     * @return array{type: string, category: string, transaction_date: string, label: string, amount: string, notes: ?string}
     */
    private function validateTransaction(Request $request): array
    {
        $type = TransactionType::tryFrom((string) $request->input('type'));

        return $request->validate([
            'type' => ['required', Rule::enum(TransactionType::class)],
            'category' => [
                'required',
                Rule::enum(TransactionCategory::class)->only($type ? TransactionCategory::forType($type) : []),
            ],
            'transaction_date' => ['required', 'date'],
            'label' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
        ], [
            'category.enum' => 'Cette catégorie ne correspond pas au type choisi.',
        ]);
    }
}

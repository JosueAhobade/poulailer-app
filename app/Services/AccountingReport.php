<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Enums\TransactionType;
use App\Models\EggCollection;
use App\Models\EggSale;
use App\Models\StockMovement;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Rassemble les recettes et dépenses du poulailler sur une période.
 *
 * Sources automatiques : ventes d'œufs (recettes) et achats d'intrants
 * avec prix unitaire (dépenses). S'y ajoutent les écritures manuelles.
 */
class AccountingReport
{
    public const EGG_SALES_LABEL = 'Ventes d\'œufs';

    /** @var Collection<int, array{date: CarbonImmutable, type: TransactionType, source: string, category: string, label: string, amount: float, unpaid: bool, url: ?string, transaction: ?Transaction}>|null */
    private ?Collection $entries = null;

    public function __construct(
        public readonly ?CarbonImmutable $from,
        public readonly ?CarbonImmutable $to,
    ) {}

    /**
     * Toutes les écritures de la période, les plus récentes d'abord.
     *
     * @return Collection<int, array{date: CarbonImmutable, type: TransactionType, source: string, category: string, label: string, amount: float, unpaid: bool, url: ?string, transaction: ?Transaction}>
     */
    public function entries(): Collection
    {
        return $this->entries ??= $this->eggSaleEntries()
            ->concat($this->purchaseEntries())
            ->concat($this->manualEntries())
            ->sortByDesc(fn (array $entry) => $entry['date']->format('Y-m-d').'-'.$entry['source'])
            ->values();
    }

    /**
     * @return array{income: float, expense: float, result: float, margin: ?float, income_by_category: Collection<string, float>, expense_by_category: Collection<string, float>, unpaid_sales: float, purchases_without_price: int, eggs_produced: int, eggs_sold: int, cost_per_egg: ?float, average_egg_price: ?float}
     */
    public function summary(): array
    {
        $entries = $this->entries();

        $income = (float) $entries->where('type', TransactionType::Income)->sum('amount');
        $expense = (float) $entries->where('type', TransactionType::Expense)->sum('amount');

        $eggsProduced = (int) $this->inRange(EggCollection::query(), 'collection_date')
            ->selectRaw('coalesce(sum(collected_count - broken_count), 0) as total')
            ->value('total');

        $eggSales = $entries->where('source', 'egg_sale');
        $eggsSold = (int) $this->inRange(EggSale::query(), 'sale_date')->sum('quantity');
        $eggRevenue = (float) $eggSales->sum('amount');

        return [
            'income' => $income,
            'expense' => $expense,
            'result' => $income - $expense,
            'margin' => $income > 0 ? ($income - $expense) / $income * 100 : null,
            'income_by_category' => $this->byCategory($entries->where('type', TransactionType::Income)),
            'expense_by_category' => $this->byCategory($entries->where('type', TransactionType::Expense)),
            'unpaid_sales' => (float) $eggSales->where('unpaid', true)->sum('amount'),
            'purchases_without_price' => $this->inRange(StockMovement::query(), 'movement_date')
                ->where('type', StockMovementType::Purchase)
                ->whereNull('unit_price')
                ->count(),
            'eggs_produced' => $eggsProduced,
            'eggs_sold' => $eggsSold,
            'cost_per_egg' => $eggsProduced > 0 ? $expense / $eggsProduced : null,
            'average_egg_price' => $eggsSold > 0 ? $eggRevenue / $eggsSold : null,
        ];
    }

    /**
     * Recettes, dépenses et résultat par mois, sur les $months mois finissant à $endMonth.
     *
     * @return array{labels: list<string>, income: list<float>, expense: list<float>, result: list<float>}
     */
    public static function monthly(CarbonImmutable $endMonth, int $months = 12): array
    {
        $start = $endMonth->startOfMonth()->subMonths($months - 1);

        $entries = (new self($start, $endMonth->endOfMonth()))
            ->entries()
            ->groupBy(fn (array $entry) => $entry['date']->format('Y-m'));

        $chart = ['labels' => [], 'income' => [], 'expense' => [], 'result' => []];

        for ($i = 0; $i < $months; $i++) {
            $month = $start->addMonths($i);
            $monthEntries = $entries->get($month->format('Y-m'), collect());

            $income = (float) $monthEntries->where('type', TransactionType::Income)->sum('amount');
            $expense = (float) $monthEntries->where('type', TransactionType::Expense)->sum('amount');

            $chart['labels'][] = $month->locale('fr')->translatedFormat('M Y');
            $chart['income'][] = round($income, 2);
            $chart['expense'][] = round($expense, 2);
            $chart['result'][] = round($income - $expense, 2);
        }

        return $chart;
    }

    private function eggSaleEntries(): Collection
    {
        return $this->inRange(EggSale::query(), 'sale_date')
            ->get()
            ->map(fn (EggSale $sale) => [
                'date' => CarbonImmutable::parse($sale->sale_date),
                'type' => TransactionType::Income,
                'source' => 'egg_sale',
                'category' => self::EGG_SALES_LABEL,
                'label' => sprintf(
                    'Vente de %s œufs%s',
                    number_format($sale->quantity, 0, ',', ' '),
                    $sale->customer ? ' — '.$sale->customer : ''
                ),
                'amount' => (float) $sale->total_amount,
                'unpaid' => ! $sale->is_paid,
                'url' => route('eggs.sales.show', $sale),
                'transaction' => null,
            ]);
    }

    private function purchaseEntries(): Collection
    {
        return $this->inRange(StockMovement::query(), 'movement_date')
            ->with('input')
            ->where('type', StockMovementType::Purchase)
            ->whereNotNull('unit_price')
            ->get()
            ->map(fn (StockMovement $movement) => [
                'date' => CarbonImmutable::parse($movement->movement_date),
                'type' => TransactionType::Expense,
                'source' => 'input_purchase',
                'category' => 'Achat : '.$movement->input->category->label(),
                'label' => sprintf(
                    '%s %s de %s%s',
                    number_format((float) $movement->quantity, 2, ',', ' '),
                    $movement->input->unit,
                    $movement->input->name,
                    $movement->supplier ? ' — '.$movement->supplier : ''
                ),
                'amount' => round((float) $movement->quantity * (float) $movement->unit_price, 2),
                'unpaid' => false,
                'url' => route('inputs.show', $movement->input),
                'transaction' => null,
            ]);
    }

    private function manualEntries(): Collection
    {
        return $this->inRange(Transaction::query(), 'transaction_date')
            ->get()
            ->map(fn (Transaction $transaction) => [
                'date' => CarbonImmutable::parse($transaction->transaction_date),
                'type' => $transaction->type,
                'source' => 'manual',
                'category' => $transaction->category->label(),
                'label' => $transaction->label,
                'amount' => (float) $transaction->amount,
                'unpaid' => false,
                'url' => route('transactions.edit', $transaction),
                'transaction' => $transaction,
            ]);
    }

    /**
     * @return Collection<string, float>
     */
    private function byCategory(Collection $entries): Collection
    {
        return $entries
            ->groupBy('category')
            ->map(fn (Collection $group) => (float) $group->sum('amount'))
            ->sortDesc();
    }

    /**
     * @template TBuilder of Builder
     *
     * @param  TBuilder  $query
     * @return TBuilder
     */
    private function inRange(Builder $query, string $column): Builder
    {
        return $query
            ->when($this->from, fn ($query, $from) => $query->whereDate($column, '>=', $from->toDateString()))
            ->when($this->to, fn ($query, $to) => $query->whereDate($column, '<=', $to->toDateString()));
    }
}

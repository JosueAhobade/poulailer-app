@extends('layouts.app')

@section('title', 'Comptabilité')

@php
    $money = fn ($value) => number_format((float) $value, 0, ',', ' ').' FCFA';
    $isProfitable = $summary['result'] >= 0;
@endphp

@section('content')

{{-- PÉRIODE & ACTIONS --}}

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-3">

    <form method="GET" action="{{ route('accounting.index') }}" class="d-flex flex-wrap align-items-end gap-2">
        <div>
            <label class="form-label small mb-1">Période</label>
            <select name="period" id="period" class="form-select">
                @foreach($periods as $value => $label)
                    <option value="{{ $value }}" @selected($period === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="custom-range {{ $period === 'custom' ? '' : 'd-none' }}">
            <label class="form-label small mb-1">Du</label>
            <input type="date" name="from" class="form-control" value="{{ request('from', $from?->toDateString()) }}">
        </div>

        <div class="custom-range {{ $period === 'custom' ? '' : 'd-none' }}">
            <label class="form-label small mb-1">Au</label>
            <input type="date" name="to" class="form-control" value="{{ request('to', $to?->toDateString()) }}">
        </div>

        <button type="submit" class="btn btn-outline-secondary">Afficher</button>
    </form>

    <div class="btn-list">
        <a href="{{ route('transactions.create', ['type' => 'income']) }}" class="btn btn-outline-success">+ Recette</a>
        <a href="{{ route('transactions.create', ['type' => 'expense']) }}" class="btn btn-outline-danger">+ Dépense</a>
    </div>

</div>

<div class="text-secondary mb-3">
    @if($from && $to)
        Du {{ $from->format('d/m/Y') }} au {{ $to->format('d/m/Y') }}
    @else
        Toutes les écritures enregistrées
    @endif
</div>

@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

{{-- RENTABILITÉ --}}

<div class="row row-cards mb-3">

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-secondary mb-1">💰 Recettes</div>
            <div class="h2 mb-0 text-success">{{ $money($summary['income']) }}</div>
            @if($summary['unpaid_sales'] > 0)
                <div class="text-secondary small">dont {{ $money($summary['unpaid_sales']) }} à encaisser</div>
            @endif
        </div></div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-secondary mb-1">🧾 Dépenses</div>
            <div class="h2 mb-0 text-danger">{{ $money($summary['expense']) }}</div>
        </div></div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 {{ $isProfitable ? 'bg-green-lt' : 'bg-red-lt' }}"><div class="card-body">
            <div class="mb-1">{{ $isProfitable ? '📈 Bénéfice' : '📉 Perte' }}</div>
            <div class="h2 mb-0">{{ $money($summary['result']) }}</div>
            <div class="small">
                @if($summary['margin'] !== null)
                    Marge : {{ number_format($summary['margin'], 1, ',', ' ') }} %
                @else
                    Aucune recette sur la période
                @endif
            </div>
        </div></div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-secondary mb-1">🥚 Coût de revient d'un œuf</div>
            <div class="h2 mb-0">
                {{ $summary['cost_per_egg'] !== null ? number_format($summary['cost_per_egg'], 1, ',', ' ').' FCFA' : '—' }}
            </div>
            <div class="text-secondary small">
                Prix de vente moyen :
                {{ $summary['average_egg_price'] !== null ? number_format($summary['average_egg_price'], 1, ',', ' ').' FCFA' : '—' }}
            </div>
        </div></div>
    </div>

</div>

<div class="alert {{ $isProfitable ? 'alert-success' : 'alert-danger' }}">
    @if($summary['income'] == 0 && $summary['expense'] == 0)
        Aucune recette ni dépense sur cette période.
    @elseif($isProfitable)
        ✅ Le poulailler est <strong>rentable</strong> sur cette période :
        il dégage {{ $money($summary['result']) }} de bénéfice.
    @else
        ⚠️ Le poulailler est <strong>déficitaire</strong> sur cette période :
        il manque {{ $money(abs($summary['result'])) }} pour couvrir les dépenses.
    @endif

    @if($summary['cost_per_egg'] !== null && $summary['average_egg_price'] !== null)
        <div class="mt-1 small">
            {{ number_format($summary['eggs_produced'], 0, ',', ' ') }} œufs vendables produits,
            {{ number_format($summary['eggs_sold'], 0, ',', ' ') }} vendus.
            @if($summary['average_egg_price'] > $summary['cost_per_egg'])
                Chaque œuf vendu rapporte en moyenne
                {{ number_format($summary['average_egg_price'] - $summary['cost_per_egg'], 1, ',', ' ') }} FCFA de plus qu'il ne coûte.
            @else
                Le prix de vente moyen est inférieur au coût de revient d'un œuf.
            @endif
        </div>
    @endif
</div>

@if($summary['purchases_without_price'] > 0)
    <div class="alert alert-warning">
        {{ $summary['purchases_without_price'] }} achat(s) d'intrants sur la période n'ont pas de prix unitaire :
        ils ne sont pas comptés dans les dépenses. Complétez-les depuis
        <a href="{{ route('inputs.index') }}">Intrants & stock</a> (supprimez puis ressaisissez l'entrée avec son prix).
    </div>
@endif

{{-- GRAPHIQUE 12 MOIS --}}

<div class="card mb-3">
    <div class="card-header"><h3 class="card-title">Évolution sur 12 mois</h3></div>
    <div class="card-body">
        <div style="position: relative; height: 280px">
            <canvas id="accountingChart" aria-label="Recettes et dépenses par mois" role="img"></canvas>
        </div>
    </div>
</div>

{{-- RÉPARTITION PAR CATÉGORIE --}}

<div class="row row-cards mb-3">
    @foreach([
        'Recettes par catégorie' => [$summary['income_by_category'], $summary['income'], 'bg-green'],
        'Dépenses par catégorie' => [$summary['expense_by_category'], $summary['expense'], 'bg-red'],
    ] as $title => [$categories, $total, $barClass])
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h3 class="card-title">{{ $title }}</h3></div>
                <div class="card-body">
                    @forelse($categories as $category => $amount)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>{{ $category }}</span>
                                <strong>{{ $money($amount) }}</strong>
                            </div>
                            <div class="progress progress-sm mt-1">
                                <div class="progress-bar {{ $barClass }}"
                                     style="width: {{ $total > 0 ? round($amount / $total * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-secondary">Rien sur cette période.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- JOURNAL --}}

<div class="card">
    <div class="card-header"><h3 class="card-title">Journal des écritures</h3></div>

    <div class="table-responsive">
        <table class="table table-hover table-vcenter card-table text-nowrap">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Catégorie</th>
                    <th>Libellé</th>
                    <th>Origine</th>
                    <th class="text-end">Montant</th>
                </tr>
            </thead>
            <tbody>
                @forelse($journal as $entry)
                    @php $isIncome = $entry['type'] === \App\Enums\TransactionType::Income; @endphp

                    <tr data-href="{{ $entry['url'] }}">
                        <td>{{ $entry['date']->format('d/m/Y') }}</td>
                        <td>{{ $entry['category'] }}</td>
                        <td class="text-wrap">
                            {{ $entry['label'] }}
                            @if($entry['unpaid'])
                                <span class="badge bg-red-lt">À encaisser</span>
                            @endif
                        </td>
                        <td>
                            @switch($entry['source'])
                                @case('egg_sale') <span class="badge bg-azure-lt">Vente d'œufs</span> @break
                                @case('input_purchase') <span class="badge bg-blue-lt">Achat d'intrant</span> @break
                                @default <span class="badge bg-secondary-lt">Saisie manuelle</span>
                            @endswitch
                        </td>
                        <td class="text-end fw-bold {{ $isIncome ? 'text-success' : 'text-danger' }}">
                            {{ $isIncome ? '+' : '−' }} {{ $money($entry['amount']) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-secondary py-4">Aucune écriture sur cette période.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($journal->hasPages())
        <div class="card-footer">{{ $journal->onEachSide(1)->links() }}</div>
    @endif
</div>

<div class="text-secondary small mt-2">
    Les ventes d'œufs et les achats d'intrants (avec prix) sont repris automatiquement.
    Saisissez ici le reste : salaires, électricité, achat de sujets, vente de poules réformées…
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
<script>
    (function () {
        const chart = {{ \Illuminate\Support\Js::from($chart) }};
        const fmt = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 });

        document.getElementById('period').addEventListener('change', function () {
            document.querySelectorAll('.custom-range').forEach(el => el.classList.toggle('d-none', this.value !== 'custom'));
        });

        new Chart(document.getElementById('accountingChart'), {
            data: {
                labels: chart.labels,
                datasets: [
                    { type: 'bar', label: 'Recettes', data: chart.income, backgroundColor: 'rgba(47, 179, 68, 0.7)', order: 2 },
                    { type: 'bar', label: 'Dépenses', data: chart.expense, backgroundColor: 'rgba(214, 57, 57, 0.7)', order: 2 },
                    { type: 'line', label: 'Résultat', data: chart.result, borderColor: '#206bc4', backgroundColor: '#206bc4', tension: 0.3, order: 1 },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    tooltip: { callbacks: { label: ctx => `${ctx.dataset.label} : ${fmt.format(ctx.parsed.y)} FCFA` } },
                },
                scales: { y: { ticks: { callback: value => fmt.format(value) } } },
            },
        });
    })();
</script>
@endpush

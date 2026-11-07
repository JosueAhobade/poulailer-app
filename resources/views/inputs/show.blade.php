@extends('layouts.app')

@section('title', $input->category->icon().' '.$input->name)

@php
    $fmt = fn ($value) => number_format((float) $value, 2, ',', ' ').' '.$input->unit;
    $stock = $input->currentStock();
@endphp

@section('content')

<div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
    <a href="{{ route('inputs.index') }}" class="btn btn-outline-secondary">← Tous les intrants</a>

    <div class="btn-list">
        <a href="{{ route('inputs.movements.create', [$input, 'type' => 'purchase']) }}" class="btn btn-success">
            + Entrée / achat
        </a>
        <a href="{{ route('inputs.movements.create', [$input, 'type' => 'loss']) }}" class="btn btn-outline-danger">
            Perte
        </a>
        <a href="{{ route('inputs.movements.create', [$input, 'type' => 'adjustment']) }}" class="btn btn-outline-warning">
            Inventaire
        </a>
        <a href="{{ route('inputs.edit', $input) }}" class="btn btn-outline-secondary">Modifier</a>
    </div>
</div>

<div class="row row-cards mb-3">

    <div class="col-12 col-md-4">
        <div class="card h-100"><div class="card-body">
            <div class="text-secondary mb-1">Stock actuel</div>
            <div class="h1 mb-0 {{ $stock < 0 ? 'text-danger' : '' }}">{{ $fmt($stock) }}</div>
            @if($input->isLowStock())
                <span class="badge bg-red-lt mt-1">Sous le seuil d'alerte ({{ $fmt($input->alert_threshold) }})</span>
            @elseif($input->alert_threshold !== null)
                <div class="text-secondary small">Seuil d'alerte : {{ $fmt($input->alert_threshold) }}</div>
            @endif
            @if($stock < 0)
                <div class="text-danger small mt-1">
                    Stock négatif : des entrées n'ont probablement pas été enregistrées.
                </div>
            @endif
        </div></div>
    </div>

    <div class="col-6 col-md-4">
        <div class="card h-100"><div class="card-body">
            <div class="text-secondary mb-1">Total entré</div>
            <div class="h2 mb-0 text-success">{{ $fmt($totals['purchase'] ?? 0) }}</div>
        </div></div>
    </div>

    <div class="col-6 col-md-4">
        <div class="card h-100"><div class="card-body">
            <div class="text-secondary mb-1">Total consommé</div>
            <div class="h2 mb-0">{{ $fmt(abs((float) ($totals['consumption'] ?? 0))) }}</div>
            @if(($totals['loss'] ?? 0) != 0)
                <div class="text-secondary small">Pertes : {{ $fmt(abs((float) $totals['loss'])) }}</div>
            @endif
        </div></div>
    </div>

</div>

@if($input->notes)
    <div class="card mb-3"><div class="card-body" style="white-space: pre-line">{{ $input->notes }}</div></div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Mouvements de stock</h3>
    </div>

    <div class="table-responsive">
        <table class="table table-vcenter card-table text-nowrap">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Détail</th>
                    <th class="text-end">Quantité</th>
                    <th class="text-end"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $movement)
                    @php $report = $movement->relatedReport(); @endphp

                    <tr @if($report) data-href="{{ route('reports.show', $report) }}" @endif>
                        <td>{{ $movement->movement_date->format('d/m/Y') }}</td>
                        <td><span class="badge {{ $movement->type->badgeClass() }}">{{ $movement->type->label() }}</span></td>
                        <td class="text-wrap">
                            {{ $movement->notes }}
                            @if($movement->supplier)
                                <div class="text-secondary small">Fournisseur : {{ $movement->supplier }}</div>
                            @endif
                            @if($movement->unit_price !== null)
                                <div class="text-secondary small">
                                    {{ number_format((float) $movement->unit_price, 0, ',', ' ') }} FCFA / {{ $input->unit }}
                                    — total {{ number_format((float) $movement->unit_price * (float) $movement->quantity, 0, ',', ' ') }} FCFA
                                </div>
                            @endif
                        </td>
                        <td class="text-end fw-bold {{ $movement->quantity < 0 ? 'text-danger' : 'text-success' }}">
                            {{ $movement->quantity > 0 ? '+' : '' }}{{ $fmt($movement->quantity) }}
                        </td>
                        <td class="text-end">
                            @if($movement->isSynced())
                                <span class="text-secondary small" title="Synchronisé avec le rapport journalier">🔗 auto</span>
                            @else
                                <form method="POST" action="{{ route('movements.destroy', $movement) }}"
                                      onsubmit="return confirm('Supprimer ce mouvement ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-ghost-danger">Supprimer</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-secondary py-4">Aucun mouvement.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($movements->hasPages())
        <div class="card-footer">{{ $movements->links() }}</div>
    @endif
</div>

<form method="POST" action="{{ route('inputs.destroy', $input) }}" class="mt-3 text-end"
      onsubmit="return confirm('Supprimer définitivement cet intrant et tout son historique de stock ?')">
    @csrf
    @method('DELETE')
    <button class="btn btn-ghost-danger btn-sm">Supprimer cet intrant</button>
</form>

@endsection

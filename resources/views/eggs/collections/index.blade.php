@extends('layouts.app')

@section('title', 'Œufs — ramassages')

@php
    $n = fn ($value) => number_format((int) $value, 0, ',', ' ');
    $trays = fn ($value) => intdiv(max((int) $value, 0), \App\Models\EggSale::EGGS_PER_TRAY);
@endphp

@section('content')

<div class="row row-cards mb-3">

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-secondary mb-1">🥚 Œufs en stock</div>
            <div class="h1 mb-0">{{ $n($summary['stock']) }}</div>
            <div class="text-secondary small">
                ≈ {{ $trays($summary['stock']) }} plateaux
                + {{ max($summary['stock'], 0) % \App\Models\EggSale::EGGS_PER_TRAY }} œufs
            </div>
        </div></div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-secondary mb-1">Ramassés ce mois</div>
            <div class="h2 mb-0">{{ $n($summary['collected_month']) }}</div>
            <div class="text-secondary small">dont {{ $n($summary['broken_month']) }} cassés</div>
        </div></div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-secondary mb-1">Vendus ce mois</div>
            <div class="h2 mb-0">{{ $n($summary['sold_month']) }}</div>
        </div></div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-secondary mb-1">Chiffre d'affaires du mois</div>
            <div class="h2 mb-0">{{ number_format($summary['revenue_month'], 0, ',', ' ') }} FCFA</div>
        </div></div>
    </div>

</div>

<div class="card">
    <div class="card-body">

        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mb-3">
            <a href="{{ route('eggs.sales.create') }}" class="btn btn-outline-success">+ Nouvelle vente</a>
            <a href="{{ route('eggs.collections.create') }}" class="btn btn-primary">+ Ramassage du jour</a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-vcenter text-nowrap">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="text-end">Ramassés</th>
                        <th class="text-end">Cassés</th>
                        <th class="text-end">Vendus</th>
                        <th class="text-end">Restants</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($collections as $collection)
                        @php $remaining = $collection->remainingCount(); @endphp

                        <tr data-href="{{ route('eggs.collections.show', $collection) }}">
                            <td>
                                <a href="{{ route('eggs.collections.show', $collection) }}" class="text-reset fw-bold">
                                    {{ $collection->collection_date->format('d/m/Y') }}
                                </a>
                            </td>
                            <td class="text-end">{{ $n($collection->collected_count) }}</td>
                            <td class="text-end">{{ $n($collection->broken_count) }}</td>
                            <td class="text-end">{{ $n($collection->soldCount()) }}</td>
                            <td class="text-end fw-bold">{{ $n($remaining) }}</td>
                            <td>
                                @if($remaining === 0)
                                    <span class="badge bg-secondary-lt">Écoulé</span>
                                @elseif($collection->soldCount() > 0)
                                    <span class="badge bg-yellow-lt">Partiellement vendu</span>
                                @else
                                    <span class="badge bg-green-lt">En stock</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">Aucun ramassage enregistré.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($collections->hasPages())
            <div class="mt-3">{{ $collections->onEachSide(1)->links() }}</div>
        @endif

    </div>
</div>

@endsection

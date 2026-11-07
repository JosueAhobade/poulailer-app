@extends('layouts.app')

@section('title', 'Ramassage du '.$collection->collection_date->format('d/m/Y'))

@php $n = fn ($value) => number_format((int) $value, 0, ',', ' '); @endphp

@section('content')

<div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
    <a href="{{ route('eggs.collections.index') }}" class="btn btn-outline-secondary">← Ramassages</a>

    <div class="btn-list">
        @if($collection->remainingCount() > 0)
            <a href="{{ route('eggs.sales.create') }}" class="btn btn-success">+ Vendre</a>
        @endif
        <a href="{{ route('eggs.collections.edit', $collection) }}" class="btn btn-outline-secondary">Modifier</a>
    </div>
</div>

<div class="row row-cards mb-3">
    @foreach([
        'Ramassés' => $collection->collected_count,
        'Cassés' => $collection->broken_count,
        'Vendus' => $collection->soldCount(),
        'Restants' => $collection->remainingCount(),
    ] as $label => $value)
        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-secondary mb-1">{{ $label }}</div>
                <div class="h2 mb-0">{{ $n($value) }}</div>
            </div></div>
        </div>
    @endforeach
</div>

@if($collection->notes)
    <div class="card mb-3"><div class="card-body" style="white-space: pre-line">{{ $collection->notes }}</div></div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Ventes ayant utilisé ces œufs</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-vcenter card-table text-nowrap">
            <thead>
                <tr>
                    <th>Date de vente</th>
                    <th>Client</th>
                    <th class="text-end">Œufs de ce jour</th>
                    <th class="text-end">Total de la vente</th>
                </tr>
            </thead>
            <tbody>
                @forelse($collection->saleItems->sortByDesc(fn ($item) => $item->sale->sale_date) as $item)
                    <tr data-href="{{ route('eggs.sales.show', $item->sale) }}">
                        <td>{{ $item->sale->sale_date->format('d/m/Y') }}</td>
                        <td>{{ $item->sale->customer ?? '—' }}</td>
                        <td class="text-end">{{ $n($item->quantity) }}</td>
                        <td class="text-end">{{ number_format((float) $item->sale->total_amount, 0, ',', ' ') }} FCFA</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-secondary py-4">Aucune vente pour l'instant.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($collection->saleItems->isEmpty())
    <form method="POST" action="{{ route('eggs.collections.destroy', $collection) }}" class="mt-3 text-end"
          onsubmit="return confirm('Supprimer ce ramassage ?')">
        @csrf
        @method('DELETE')
        <button class="btn btn-ghost-danger btn-sm">Supprimer ce ramassage</button>
    </form>
@endif

@endsection

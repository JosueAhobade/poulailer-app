@extends('layouts.app')

@section('title', 'Œufs — ventes')

@section('content')

@if($unpaidTotal > 0)
    <div class="alert alert-warning">
        💰 Reste à encaisser : <strong>{{ number_format($unpaidTotal, 0, ',', ' ') }} FCFA</strong>
    </div>
@endif

<div class="card">
    <div class="card-body">

        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('eggs.sales.create') }}" class="btn btn-primary">+ Nouvelle vente</a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-vcenter text-nowrap">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Client</th>
                        <th class="text-end">Œufs</th>
                        <th>Œufs du</th>
                        <th class="text-end">Prix / œuf</th>
                        <th class="text-end">Total</th>
                        <th>Paiement</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr data-href="{{ route('eggs.sales.show', $sale) }}">
                            <td>
                                <a href="{{ route('eggs.sales.show', $sale) }}" class="text-reset fw-bold">
                                    {{ $sale->sale_date->format('d/m/Y') }}
                                </a>
                            </td>
                            <td>{{ $sale->customer ?? '—' }}</td>
                            <td class="text-end">{{ number_format($sale->quantity, 0, ',', ' ') }}</td>
                            <td class="text-wrap">
                                @foreach($sale->items->sortBy(fn ($item) => $item->collection->collection_date) as $item)
                                    <span class="badge bg-azure-lt">
                                        {{ $item->collection->collection_date->format('d/m') }} : {{ $item->quantity }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="text-end">{{ number_format((float) $sale->unit_price, 2, ',', ' ') }}</td>
                            <td class="text-end fw-bold">{{ number_format((float) $sale->total_amount, 0, ',', ' ') }} FCFA</td>
                            <td>
                                @if($sale->is_paid)
                                    <span class="badge bg-green-lt">Payé</span>
                                @else
                                    <span class="badge bg-red-lt">À encaisser</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">Aucune vente enregistrée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
            <div class="mt-3">{{ $sales->onEachSide(1)->links() }}</div>
        @endif

    </div>
</div>

@endsection

@extends('layouts.app')

@section('title', 'Vente du '.$sale->sale_date->format('d/m/Y'))

@section('content')

<div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
    <a href="{{ route('eggs.sales.index') }}" class="btn btn-outline-secondary">← Ventes</a>

    @unless($sale->is_paid)
        <form method="POST" action="{{ route('eggs.sales.paid', $sale) }}">
            @csrf
            @method('PATCH')
            <button class="btn btn-success">Marquer comme payée</button>
        </form>
    @endunless
</div>

<div class="row row-cards">

    <div class="col-12 col-lg-5">
        <div class="card">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Client</dt>
                    <dd class="col-7">{{ $sale->customer ?? '—' }}</dd>

                    <dt class="col-5">Œufs vendus</dt>
                    <dd class="col-7">
                        {{ number_format($sale->quantity, 0, ',', ' ') }}
                        <span class="text-secondary small">
                            ({{ intdiv($sale->quantity, \App\Models\EggSale::EGGS_PER_TRAY) }} plateaux
                            + {{ $sale->quantity % \App\Models\EggSale::EGGS_PER_TRAY }})
                        </span>
                    </dd>

                    <dt class="col-5">Prix / œuf</dt>
                    <dd class="col-7">{{ number_format((float) $sale->unit_price, 2, ',', ' ') }} FCFA</dd>

                    <dt class="col-5">Total</dt>
                    <dd class="col-7 h3">{{ number_format((float) $sale->total_amount, 0, ',', ' ') }} FCFA</dd>

                    <dt class="col-5">Paiement</dt>
                    <dd class="col-7">
                        @if($sale->is_paid)
                            <span class="badge bg-green-lt">Payé</span>
                        @else
                            <span class="badge bg-red-lt">À encaisser</span>
                        @endif
                    </dd>

                    @if($sale->notes)
                        <dt class="col-5">Notes</dt>
                        <dd class="col-7" style="white-space: pre-line">{{ $sale->notes }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Provenance des œufs</h3></div>
            <div class="table-responsive">
                <table class="table table-hover table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Ramassage du</th>
                            <th class="text-end">Œufs vendus</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items->sortBy(fn ($item) => $item->collection->collection_date) as $item)
                            <tr data-href="{{ route('eggs.collections.show', $item->collection) }}">
                                <td>{{ $item->collection->collection_date->format('d/m/Y') }}</td>
                                <td class="text-end">{{ number_format($item->quantity, 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<form method="POST" action="{{ route('eggs.sales.destroy', $sale) }}" class="mt-3 text-end"
      onsubmit="return confirm('Supprimer cette vente ? Les œufs seront remis en stock.')">
    @csrf
    @method('DELETE')
    <button class="btn btn-ghost-danger btn-sm">Supprimer la vente</button>
</form>

@endsection

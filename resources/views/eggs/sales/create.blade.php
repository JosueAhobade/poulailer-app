@extends('layouts.app')

@section('title', 'Nouvelle vente d\'œufs')

@section('content')

@if($collections->isEmpty())

    <div class="alert alert-info">
        Aucun œuf disponible à la vente.
        <a href="{{ route('eggs.collections.create') }}">Enregistrer un ramassage</a>
    </div>

@else

<form method="POST" action="{{ route('eggs.sales.store') }}" id="sale-form">
    @csrf

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="row row-cards">

        <div class="col-12 col-lg-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Vente</h3></div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label">Date de vente *</label>
                        <input type="date" name="sale_date" id="sale-date" class="form-control" required
                               value="{{ old('sale_date', $today) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Client</label>
                        <input type="text" name="customer" class="form-control" value="{{ old('customer') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Prix *</label>
                        <div class="input-group">
                            <input type="number" name="price" id="price" class="form-control" required
                                   step="0.01" min="0" inputmode="decimal"
                                   value="{{ old('price', $lastUnitPrice !== null ? (float) $lastUnitPrice : '') }}">
                            <select name="price_mode" id="price-mode" class="form-select" style="max-width: 11rem">
                                <option value="egg" @selected(old('price_mode', 'egg') === 'egg')>FCFA / œuf</option>
                                <option value="tray" @selected(old('price_mode') === 'tray')>FCFA / plateau ({{ \App\Models\EggSale::EGGS_PER_TRAY }})</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-check">
                            <input type="hidden" name="is_paid" value="0">
                            <input type="checkbox" name="is_paid" value="1" class="form-check-input"
                                   @checked(old('is_paid', '1') === '1')>
                            <span class="form-check-label">Payé</span>
                        </label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                    </div>

                    <div class="bg-light rounded p-3 mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Œufs vendus</span>
                            <strong id="total-eggs">0</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Soit</span>
                            <span id="total-trays" class="text-secondary">0 plateau</span>
                        </div>
                        <div class="d-flex justify-content-between h3 mb-0 mt-2">
                            <span>Montant</span>
                            <span id="total-amount">0 FCFA</span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Enregistrer la vente</button>

                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Œufs vendus — de quel jour ?</h3></div>
                <div class="card-body border-bottom">
                    <label class="form-label">Répartition automatique (plus anciens d'abord)</label>
                    <div class="input-group">
                        <input type="number" id="auto-quantity" class="form-control" min="0"
                               inputmode="numeric" placeholder="Nombre total d'œufs">
                        <button type="button" id="auto-fill" class="btn btn-outline-primary">Répartir</button>
                    </div>
                    <small class="text-secondary">Vous pouvez ensuite ajuster chaque ligne à la main.</small>
                </div>

                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Ramassage du</th>
                                <th class="text-end">Restants</th>
                                <th style="width: 9rem">Vendus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($collections as $collection)
                                <tr data-collection-date="{{ $collection->collection_date->format('Y-m-d') }}">
                                    <td>
                                        {{ $collection->collection_date->locale('fr')->translatedFormat('D d/m/Y') }}
                                        @error("items.{$collection->id}")
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td class="text-end">{{ $collection->remainingCount() }}</td>
                                    <td>
                                        <input type="number"
                                               name="items[{{ $collection->id }}]"
                                               class="form-control form-control-sm sale-item @error("items.{$collection->id}") is-invalid @enderror"
                                               min="0"
                                               max="{{ $collection->remainingCount() }}"
                                               inputmode="numeric"
                                               value="{{ old("items.{$collection->id}") }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</form>

@endif

@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('sale-form');

        if (!form) {
            return;
        }

        const EGGS_PER_TRAY = {{ \App\Models\EggSale::EGGS_PER_TRAY }};
        const items = Array.from(form.querySelectorAll('.sale-item'));
        const price = document.getElementById('price');
        const priceMode = document.getElementById('price-mode');
        const saleDate = document.getElementById('sale-date');
        const fmt = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 });

        function isEligible(input) {
            return input.closest('tr').dataset.collectionDate <= saleDate.value;
        }

        function refresh() {
            const eggs = items.reduce((sum, input) => sum + (parseInt(input.value, 10) || 0), 0);
            const unit = parseFloat(price.value) || 0;
            const amount = priceMode.value === 'tray' ? eggs / EGGS_PER_TRAY * unit : eggs * unit;
            const trays = Math.floor(eggs / EGGS_PER_TRAY);
            const rest = eggs % EGGS_PER_TRAY;

            document.getElementById('total-eggs').textContent = fmt.format(eggs);
            document.getElementById('total-trays').textContent =
                `${trays} plateau${trays > 1 ? 'x' : ''}` + (rest ? ` + ${rest} œuf${rest > 1 ? 's' : ''}` : '');
            document.getElementById('total-amount').textContent = `${fmt.format(amount)} FCFA`;

            items.forEach(input => input.closest('tr').classList.toggle('opacity-50', !isEligible(input)));
        }

        document.getElementById('auto-fill').addEventListener('click', function () {
            let left = parseInt(document.getElementById('auto-quantity').value, 10) || 0;

            items.forEach(input => {
                const take = isEligible(input) ? Math.min(left, parseInt(input.max, 10)) : 0;
                input.value = take > 0 ? take : '';
                left -= take;
            });

            if (left > 0) {
                alert(`Stock insuffisant : il manque ${left} œufs.`);
            }

            refresh();
        });

        form.addEventListener('input', refresh);
        refresh();
    })();
</script>
@endpush

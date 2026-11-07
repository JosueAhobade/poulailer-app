@extends('layouts.app')

@section('title', ($transaction->exists ? 'Modifier ' : 'Nouvelle ').strtolower($transaction->type->label()))

@section('content')

@php $selectedType = \App\Enums\TransactionType::tryFrom(old('type', $transaction->type->value)) ?? $transaction->type; @endphp

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-body">

                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST"
                      action="{{ $transaction->exists ? route('transactions.update', $transaction) : route('transactions.store') }}">
                    @csrf
                    @if($transaction->exists)
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Type *</label>
                        <div class="btn-group w-100" role="group">
                            @foreach(\App\Enums\TransactionType::cases() as $type)
                                <input type="radio" class="btn-check" name="type" id="type-{{ $type->value }}"
                                       value="{{ $type->value }}" @checked($selectedType === $type)>
                                <label class="btn {{ $type === \App\Enums\TransactionType::Income ? 'btn-outline-success' : 'btn-outline-danger' }}"
                                       for="type-{{ $type->value }}">
                                    {{ $type->label() }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catégorie *</label>
                        <select name="category" id="category" class="form-select" required>
                            @foreach(\App\Enums\TransactionCategory::cases() as $category)
                                <option value="{{ $category->value }}"
                                        data-type="{{ $category->type()->value }}"
                                        @selected(old('category', $transaction->category?->value) === $category->value)>
                                    {{ $category->label() }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-secondary">
                            Les achats de provende, vaccins et médicaments se saisissent dans
                            <a href="{{ route('inputs.index') }}">Intrants & stock</a> (avec leur prix),
                            et les ventes d'œufs dans <a href="{{ route('eggs.sales.index') }}">Ventes</a> :
                            ils sont comptés automatiquement, ne les saisissez pas ici.
                        </small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" name="transaction_date" class="form-control" required
                                   value="{{ old('transaction_date', $transaction->transaction_date?->format('Y-m-d')) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Montant *</label>
                            <div class="input-group">
                                <input type="number" name="amount" class="form-control" required
                                       step="0.01" min="0" inputmode="decimal"
                                       value="{{ old('amount', $transaction->amount !== null ? (float) $transaction->amount : '') }}">
                                <span class="input-group-text">FCFA</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Libellé *</label>
                        <input type="text" name="label" class="form-control" required
                               value="{{ old('label', $transaction->label) }}"
                               placeholder="Ex : Salaire gardien novembre, facture SBEE, 20 poules réformées">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="2" class="form-control">{{ old('notes', $transaction->notes) }}</textarea>
                    </div>

                    <div class="d-grid d-md-block">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <a href="{{ route('accounting.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>

                @if($transaction->exists)
                    <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" class="mt-3 text-end"
                          onsubmit="return confirm('Supprimer cette écriture ?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-ghost-danger btn-sm">Supprimer</button>
                    </form>
                @endif

            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    (function () {
        const select = document.getElementById('category');

        function filterCategories() {
            const type = document.querySelector('input[name="type"]:checked').value;
            let firstVisible = null;

            Array.from(select.options).forEach(option => {
                const visible = option.dataset.type === type;
                option.hidden = !visible;
                option.disabled = !visible;
                if (visible && !firstVisible) firstVisible = option;
            });

            if (select.selectedOptions[0]?.disabled) {
                firstVisible.selected = true;
            }
        }

        document.querySelectorAll('input[name="type"]').forEach(radio => radio.addEventListener('change', filterCategories));
        filterCategories();
    })();
</script>
@endpush

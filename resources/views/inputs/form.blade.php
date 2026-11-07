@extends('layouts.app')

@section('title', $input->exists ? 'Modifier '.$input->name : 'Nouvel intrant')

@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">

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
                      action="{{ $input->exists ? route('inputs.update', $input) : route('inputs.store') }}">
                    @csrf
                    @if($input->exists)
                        @method('PUT')
                    @endif

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Catégorie *</label>
                            <select name="category" class="form-select" id="input-category" required>
                                @foreach(\App\Enums\InputCategory::cases() as $case)
                                    <option value="{{ $case->value }}"
                                        data-units="{{ implode(',', $case->suggestedUnits()) }}"
                                        @selected(old('category', $input->category?->value) === $case->value)>
                                        {{ $case->icon() }} {{ $case->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom *</label>
                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   value="{{ old('name', $input->name) }}"
                                   placeholder="Ex : Provende ponte, Newcastle HB1, Oxytétracycline"
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unité de stock *</label>
                            <input type="text"
                                   name="unit"
                                   class="form-control"
                                   list="unit-suggestions"
                                   value="{{ old('unit', $input->unit ?? 'kg') }}"
                                   required>
                            <datalist id="unit-suggestions"></datalist>
                            <small class="text-secondary">
                                Pour la provende, gardez « kg » : c'est l'unité de l'aliment servi dans les rapports.
                            </small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Seuil d'alerte</label>
                            <input type="number"
                                   name="alert_threshold"
                                   class="form-control"
                                   step="0.01"
                                   min="0"
                                   value="{{ old('alert_threshold', $input->alert_threshold) }}"
                                   placeholder="Ex : 100">
                            <small class="text-secondary">Une alerte s'affiche quand le stock passe sous ce niveau.</small>
                        </div>

                        @unless($input->exists)
                            <div class="col-12 mt-2 mb-2">
                                <h3 class="card-title mb-1">Stock initial (facultatif)</h3>
                                <p class="text-secondary small mb-0">Quantité actuellement en magasin.</p>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Quantité</label>
                                <input type="number" name="initial_stock" class="form-control"
                                       step="0.01" min="0" value="{{ old('initial_stock') }}">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Prix unitaire (FCFA)</label>
                                <input type="number" name="initial_unit_price" class="form-control"
                                       step="0.01" min="0" value="{{ old('initial_unit_price') }}">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Fournisseur</label>
                                <input type="text" name="initial_supplier" class="form-control"
                                       value="{{ old('initial_supplier') }}">
                            </div>
                        @endunless

                        <div class="col-12 mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="3" class="form-control">{{ old('notes', $input->notes) }}</textarea>
                        </div>

                    </div>

                    <div class="d-grid d-md-block">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <a href="{{ $input->exists ? route('inputs.show', $input) : route('inputs.index') }}"
                           class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    (function () {
        const select = document.getElementById('input-category');
        const list = document.getElementById('unit-suggestions');

        function refreshUnits() {
            const units = select.selectedOptions[0].dataset.units.split(',');
            list.innerHTML = units.map(unit => `<option value="${unit}">`).join('');
        }

        select.addEventListener('change', refreshUnits);
        refreshUnits();
    })();
</script>
@endpush

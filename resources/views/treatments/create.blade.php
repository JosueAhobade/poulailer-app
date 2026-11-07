@extends('layouts.app')

@section('title', 'Ajouter un traitement')

@section('content')

<div class="card">

    <div class="card-header">
        <h3 class="card-title">
            Traitement du {{ $report->report_date->format('d/m/Y') }}
        </h3>
    </div>

    <div class="card-body">

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('treatments.store', $report) }}"
        >

            @csrf

            <div class="mb-3">
                <label class="form-label">
                    Médicament du stock
                </label>

                <select name="input_id" class="form-select">
                    <option value="">— Hors stock (saisie libre) —</option>
                    @foreach($medicines as $medicine)
                        <option value="{{ $medicine->id }}"
                            @selected((string) old('input_id') === (string) $medicine->id)>
                            {{ $medicine->optionLabel() }}
                        </option>
                    @endforeach
                </select>

                <small class="text-secondary">
                    Si vous choisissez un médicament du stock, la quantité utilisée en sera déduite.
                    @if($medicines->isEmpty())
                        <a href="{{ route('inputs.create', ['category' => 'medicine']) }}">Ajouter un médicament</a>
                    @endif
                </small>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Nom du médicament
                </label>

                <input
                    type="text"
                    name="name"
                    class="form-control"
                    value="{{ old('name') }}"
                    placeholder="Laisser vide pour reprendre le nom du stock"
                >
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Dosage
                    </label>

                    <input
                        type="text"
                        name="dosage"
                        class="form-control"
                        placeholder="Ex : 1 g / litre"
                        value="{{ old('dosage') }}"
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Quantité totale utilisée
                    </label>

                    <input
                        type="number"
                        name="quantity_used"
                        class="form-control"
                        step="0.01"
                        min="0"
                        inputmode="decimal"
                        value="{{ old('quantity_used') }}"
                        placeholder="Dans l'unité du stock (g, ml, sachet…)"
                    >

                    <small class="text-secondary">Obligatoire si un médicament du stock est choisi.</small>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Mode d'administration
                </label>

                <select
                    name="administration_method"
                    class="form-select"
                >
                    <option value="">Sélectionner</option>
                    @foreach(['Eau', 'Aliment', 'Injection', 'Autre'] as $method)
                        <option value="{{ $method }}" @selected(old('administration_method') === $method)>
                            {{ $method }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Motif
                </label>

                <input
                    type="text"
                    name="reason"
                    class="form-control"
                    value="{{ old('reason') }}"
                >
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Nombre de sujets concernés
                </label>

                <input
                    type="number"
                    name="birds_count"
                    class="form-control"
                    min="0"
                    value="{{ old('birds_count') }}"
                >
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Notes
                </label>

                <textarea
                    name="notes"
                    class="form-control"
                    rows="3"
                >{{ old('notes') }}</textarea>
            </div>

            <div class="d-grid d-md-block">
                <button class="btn btn-primary">
                    Enregistrer
                </button>

                <a href="{{ route('reports.show', $report) }}"
                   class="btn btn-outline-secondary">
                    Annuler
                </a>
            </div>

        </form>

    </div>

</div>

@endsection
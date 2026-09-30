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
                    Nom du médicament
                </label>

                <input
                    type="text"
                    name="name"
                    class="form-control"
                    value="{{ old('name') }}"
                    required
                >
            </div>

            <div class="mb-3">
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

            <div class="mb-3">
                <label class="form-label">
                    Mode d'administration
                </label>

                <select
                    name="administration_method"
                    class="form-select"
                >
                    <option value="">Sélectionner</option>
                    <option value="Eau">Eau</option>
                    <option value="Aliment">Aliment</option>
                    <option value="Injection">Injection</option>
                    <option value="Autre">Autre</option>
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

            <button class="btn btn-primary">
                Enregistrer
            </button>

        </form>

    </div>

</div>

@endsection
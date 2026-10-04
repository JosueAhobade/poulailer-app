
@extends('layouts.app')

@section('title', 'Nouvelle vaccination')

@section('content')

<div class="card">

    <div class="card-header">
        <h3 class="card-title">
            Vaccination du {{ $report->report_date->format('d/m/Y') }}
        </h3>
    </div>

    <div class="card-body">

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST"
              action="{{ route('vaccinations.store', $report) }}">

            @csrf

            <div class="row">

                <div class="col-12 mb-3">
                    <label class="form-label">Nom du vaccin *</label>
                    <input type="text"
                           name="vaccine_name"
                           class="form-control"
                           value="{{ old('vaccine_name') }}"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Maladie ciblée</label>

                    <input type="text"
                           name="disease"
                           class="form-control"
                           value="{{ old('disease') }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Mode d'administration
                    </label>

                    <select name="administration_method"
                            class="form-select">

                        <option value="">Sélectionner</option>

                        @foreach([
                            'Eau de boisson',
                            'Goutte oculaire',
                            'Nébulisation',
                            'Injection',
                            'Autre'
                        ] as $method)

                            <option value="{{ $method }}"
                                @selected(old('administration_method') === $method)>
                                {{ $method }}
                            </option>

                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Dosage administré</label>

                    <input type="text"
                           name="dosage"
                           class="form-control"
                           value="{{ old('dosage') }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Nombre de sujets vaccinés *
                    </label>

                    <input type="number"
                           name="birds_count"
                           class="form-control"
                           min="1"
                           max="{{ $report->birds_count }}"
                           value="{{ old('birds_count', $report->birds_count) }}"
                           required>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">
                        Numéro de lot du vaccin
                    </label>

                    <input type="text"
                           name="batch_number"
                           class="form-control"
                           value="{{ old('batch_number') }}">
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">
                        Observations
                    </label>

                    <textarea name="notes"
                              rows="3"
                              class="form-control">{{ old('notes') }}</textarea>
                </div>

            </div>

            <div class="d-grid d-md-block">
                <button type="submit" class="btn btn-primary">
                    Enregistrer la vaccination
                </button>

                <a href="{{ route('reports.index') }}"
                   class="btn btn-outline-secondary">
                    Annuler
                </a>
            </div>

        </form>
    </div>
</div>

@endsection

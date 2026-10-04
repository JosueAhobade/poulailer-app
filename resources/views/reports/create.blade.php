@extends('layouts.app')

@section('title', 'Rapport journalier')

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Nouveau rapport</h3>
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

        <form method="POST" action="{{ route('reports.store') }}">
            @csrf

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">Date</label>
                    <input
                        type="date"
                        name="report_date"
                        class="form-control"
                        value="{{ old('report_date', date('Y-m-d')) }}"
                        required
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre de sujets</label>
                    <input
                        type="number"
                        name="birds_count"
                        class="form-control"
                        value="{{ old('birds_count', 455) }}"
                        min="0"
                        required
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Morts aujourd'hui</label>
                    <input
                        type="number"
                        name="deaths_count"
                        class="form-control"
                        value="{{ old('deaths_count', 0) }}"
                        min="0"
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Malades / isolés</label>
                    <input
                        type="number"
                        name="sick_count"
                        class="form-control"
                        value="{{ old('sick_count', 0) }}"
                        min="0"
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Aliment servi</label>
                    <div class="input-group">
                        <input
                            type="number"
                            step="0.01"
                            name="feed_quantity"
                            class="form-control"
                            value="{{ old('feed_quantity') }}"
                            min="0"
                        >
                        <span class="input-group-text">kg</span>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Eau servie</label>
                    <div class="input-group">
                        <input
                            type="number"
                            step="0.01"
                            name="water_quantity"
                            class="form-control"
                            value="{{ old('water_quantity') }}"
                            min="0"
                        >
                        <span class="input-group-text">L</span>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Température</label>
                    <div class="input-group">
                        <input
                            type="number"
                            step="0.1"
                            name="temperature"
                            class="form-control"
                            value="{{ old('temperature') }}"
                        >
                        <span class="input-group-text">°C</span>
                    </div>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">Observations</label>
                    <textarea
                        name="observations"
                        rows="4"
                        class="form-control"
                        placeholder="Comportement, problème observé, événement particulier..."
                    >{{ old('observations') }}</textarea>
                </div>


                <div class="col-12 mt-3 mb-3">

                    <h3 class="card-title">
                        Consommation estimée
                    </h3>

                    <p class="text-secondary small">
                        À renseigner après avoir vérifié les quantités restantes.
                        Calcul : reste du matin + quantité servie - reste du soir.
                    </p>

                </div>

                {{-- ALIMENT CONSOMMÉ --}}

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        🌽 Quantité d'aliment consommée
                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            name="feed_consumed"
                            class="form-control"
                            min="0"
                            step="0.01"
                            inputmode="decimal"
                            value="{{ old('feed_consumed') }}"
                            placeholder="Ex : 46.50"
                        >

                        <span class="input-group-text">kg</span>

                    </div>

                    <small class="text-secondary">
                        Laisser vide si non mesurée.
                    </small>

                </div>

                {{-- EAU CONSOMMÉE --}}

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        💧 Quantité d'eau consommée
                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            name="water_consumed"
                            class="form-control"
                            min="0"
                            step="0.01"
                            inputmode="decimal"
                            value="{{ old('water_consumed') }}"
                            placeholder="Ex : 86.00"
                        >

                        <span class="input-group-text">L</span>

                    </div>

                    <small class="text-secondary">
                        Laisser vide si non mesurée.
                    </small>

                </div>


            </div>

            <button type="submit" class="btn btn-primary w-100 w-md-auto">
                Enregistrer le rapport
            </button>
        </form>

    </div>
</div>

@endsection
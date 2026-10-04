
@extends('layouts.app')

@section('title', 'Compléter la consommation')

@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">

        <div class="card">

            <div class="card-header">
                <h3 class="card-title">
                    Consommation du
                    {{ $report->report_date->format('d/m/Y') }}
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

                <div class="alert alert-info">
                    Renseignez les quantités estimées
                    à partir des mesures effectuées sur place.
                </div>

                <form method="POST"
                      action="{{ route('reports.consumption.update', $report) }}">

                    @csrf
                    @method('PUT')

                    <div class="mb-4">

                        <label class="form-label">
                            Aliment consommé
                        </label>

                        <div class="text-secondary small mb-2">
                            Quantité servie :
                            {{ $report->feed_quantity ?? '—' }} kg
                        </div>

                        <div class="input-group">

                            <input type="number"
                                   name="feed_consumed"
                                   step="0.01"
                                   min="0"
                                   inputmode="decimal"
                                   class="form-control"
                                   value="{{ old('feed_consumed', $report->feed_consumed) }}">

                            <span class="input-group-text">kg</span>

                        </div>

                    </div>

                    <div class="mb-4">

                        <label class="form-label">
                            Eau consommée
                        </label>

                        <div class="text-secondary small mb-2">
                            Quantité servie :
                            {{ $report->water_quantity ?? '—' }} L
                        </div>

                        <div class="input-group">

                            <input type="number"
                                   name="water_consumed"
                                   step="0.01"
                                   min="0"
                                   inputmode="decimal"
                                   class="form-control"
                                   value="{{ old('water_consumed', $report->water_consumed) }}">

                            <span class="input-group-text">L</span>

                        </div>

                    </div>

                    <div class="d-grid gap-2">

                        <button type="submit"
                                class="btn btn-primary">
                            Mettre à jour
                        </button>

                        <a href="{{ route('reports.index') }}"
                           class="btn btn-outline-secondary">
                            Annuler
                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>
</div>

@endsection

@extends('layouts.app')

@section('title', 'Rapport du '.$report->report_date->format('d/m/Y'))

@php
    $kg = fn ($value) => $value !== null ? number_format((float) $value, 2, ',', ' ').' kg' : '—';
    $liters = fn ($value) => $value !== null ? number_format((float) $value, 2, ',', ' ').' L' : '—';
    $percent = fn ($consumed, $served) => ($consumed !== null && $served !== null && (float) $served > 0)
        ? number_format((float) $consumed / (float) $served * 100, 0, ',', ' ').' %'
        : null;
@endphp

@section('content')

<div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">

    <div class="btn-list">
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
            ← Historique
        </a>

        @if($previousReport)
            <a href="{{ route('reports.show', $previousReport) }}" class="btn btn-outline-secondary">
                ‹ {{ $previousReport->report_date->format('d/m') }}
            </a>
        @endif

        @if($nextReport)
            <a href="{{ route('reports.show', $nextReport) }}" class="btn btn-outline-secondary">
                {{ $nextReport->report_date->format('d/m') }} ›
            </a>
        @endif
    </div>

    <div class="btn-list">
        <a href="{{ route('treatments.create', $report) }}" class="btn btn-outline-primary">
            + Traitement
        </a>
        <a href="{{ route('vaccinations.create', $report) }}" class="btn btn-outline-primary">
            + Vaccin
        </a>
        <a href="{{ route('reports.consumption.edit', $report) }}" class="btn btn-outline-success">
            Compléter la consommation
        </a>
    </div>

</div>

{{-- CHIFFRES CLÉS --}}

<div class="row row-cards mb-3">

    <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-secondary mb-1">🐔 Sujets</div>
            <div class="h2 mb-0">{{ number_format($report->birds_count, 0, ',', ' ') }}</div>
        </div></div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-secondary mb-1">💀 Morts</div>
            <div class="h2 mb-0 {{ $report->deaths_count > 0 ? 'text-danger' : '' }}">
                {{ $report->deaths_count }}
            </div>
        </div></div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-secondary mb-1">🤒 Malades / isolés</div>
            <div class="h2 mb-0">{{ $report->sick_count }}</div>
        </div></div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-secondary mb-1">🥚 Œufs ramassés</div>
            <div class="h2 mb-0">
                @if($eggCollection)
                    <a href="{{ route('eggs.collections.show', $eggCollection) }}" class="text-reset">
                        {{ number_format($eggCollection->collected_count, 0, ',', ' ') }}
                    </a>
                @else
                    —
                @endif
            </div>
            @if($eggCollection)
                <div class="text-secondary small">
                    {{ $eggCollection->broken_count }} cassés ·
                    {{ $eggCollection->remainingCount() }} restants
                </div>
            @else
                <a href="{{ route('eggs.collections.create') }}" class="small">Saisir le ramassage</a>
            @endif
        </div></div>
    </div>

</div>

<div class="row row-cards mb-3">

    {{-- ALIMENT & EAU --}}

    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">Alimentation & abreuvement</h3>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-6">Provende utilisée</dt>
                    <dd class="col-6">
                        @if($report->feedInput)
                            <a href="{{ route('inputs.show', $report->feedInput) }}">{{ $report->feedInput->name }}</a>
                        @else
                            <span class="text-secondary">Non précisée</span>
                        @endif
                    </dd>

                    <dt class="col-6">Aliment servi</dt>
                    <dd class="col-6">{{ $kg($report->feed_quantity) }}</dd>

                    <dt class="col-6">Aliment consommé</dt>
                    <dd class="col-6">
                        {{ $kg($report->feed_consumed) }}
                        @if($percent($report->feed_consumed, $report->feed_quantity))
                            <span class="text-secondary small">({{ $percent($report->feed_consumed, $report->feed_quantity) }} du servi)</span>
                        @endif
                    </dd>

                    <dt class="col-6">Eau servie</dt>
                    <dd class="col-6">{{ $liters($report->water_quantity) }}</dd>

                    <dt class="col-6">Eau consommée</dt>
                    <dd class="col-6">
                        {{ $liters($report->water_consumed) }}
                        @if($percent($report->water_consumed, $report->water_quantity))
                            <span class="text-secondary small">({{ $percent($report->water_consumed, $report->water_quantity) }} du servi)</span>
                        @endif
                    </dd>

                    @if($report->feed_consumed !== null && $report->birds_count > 0)
                        <dt class="col-6">Aliment / sujet</dt>
                        <dd class="col-6">
                            {{ number_format((float) $report->feed_consumed * 1000 / $report->birds_count, 0, ',', ' ') }} g
                        </dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    {{-- CONDITIONS & OBSERVATIONS --}}

    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">Conditions & observations</h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-6">Température</dt>
                    <dd class="col-6">
                        {{ $report->temperature !== null ? number_format((float) $report->temperature, 1, ',', ' ').' °C' : '—' }}
                    </dd>

                    <dt class="col-6">Poids moyen des sujets</dt>
                    <dd class="col-6">{{ $kg($report->birds_weight) }}</dd>

                    <dt class="col-6">Saisi le</dt>
                    <dd class="col-6">{{ $report->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>

                    @if($report->updated_at && $report->updated_at->ne($report->created_at))
                        <dt class="col-6">Modifié le</dt>
                        <dd class="col-6">{{ $report->updated_at->format('d/m/Y H:i') }}</dd>
                    @endif
                </dl>

                <div class="fw-bold mb-1">Observations</div>
                @if($report->observations)
                    <div style="white-space: pre-line">{{ $report->observations }}</div>
                @else
                    <div class="text-secondary">Aucune observation.</div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- TRAITEMENTS --}}

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">💊 Traitements ({{ $report->treatments->count() }})</h3>
    </div>

    @if($report->treatments->isEmpty())
        <div class="card-body text-secondary">Aucun traitement ce jour.</div>
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Médicament</th>
                        <th>Dosage</th>
                        <th>Qté sortie du stock</th>
                        <th>Administration</th>
                        <th>Motif</th>
                        <th>Sujets</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report->treatments as $treatment)
                        <tr>
                            <td>
                                <span class="badge bg-blue-lt">{{ $treatment->name }}</span>
                                @if($treatment->input)
                                    <div class="small"><a href="{{ route('inputs.show', $treatment->input) }}">voir le stock</a></div>
                                @endif
                            </td>
                            <td>{{ $treatment->dosage ?? '—' }}</td>
                            <td>
                                @if($treatment->quantity_used !== null)
                                    {{ number_format((float) $treatment->quantity_used, 2, ',', ' ') }} {{ $treatment->input?->unit }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $treatment->administration_method ?? '—' }}</td>
                            <td>{{ $treatment->reason ?? '—' }}</td>
                            <td>{{ $treatment->birds_count ?? '—' }}</td>
                            <td class="text-wrap">{{ $treatment->notes ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- VACCINATIONS --}}

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">💉 Vaccinations ({{ $report->vaccinations->count() }})</h3>
    </div>

    @if($report->vaccinations->isEmpty())
        <div class="card-body text-secondary">Aucune vaccination ce jour.</div>
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Vaccin</th>
                        <th>Maladie</th>
                        <th>Dosage</th>
                        <th>Qté sortie du stock</th>
                        <th>Administration</th>
                        <th>Sujets</th>
                        <th>Rappel</th>
                        <th>Lot</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report->vaccinations as $vaccination)
                        <tr>
                            <td>
                                <span class="badge bg-purple-lt">{{ $vaccination->vaccine_name }}</span>
                                @if($vaccination->input)
                                    <div class="small"><a href="{{ route('inputs.show', $vaccination->input) }}">voir le stock</a></div>
                                @endif
                            </td>
                            <td>{{ $vaccination->disease ?? '—' }}</td>
                            <td>{{ $vaccination->dosage ?? '—' }}</td>
                            <td>
                                @if($vaccination->quantity_used !== null)
                                    {{ number_format((float) $vaccination->quantity_used, 2, ',', ' ') }} {{ $vaccination->input?->unit }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $vaccination->administration_method ?? '—' }}</td>
                            <td>{{ $vaccination->birds_count }}</td>
                            <td>{{ $vaccination->rappel ? 'Oui' : 'Non' }}</td>
                            <td>{{ $vaccination->batch_number ?? '—' }}</td>
                            <td class="text-wrap">{{ $vaccination->notes ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- SORTIES DE STOCK --}}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">📦 Sorties de stock liées à ce rapport</h3>
    </div>

    @if($stockMovements->isEmpty())
        <div class="card-body text-secondary">
            Aucune sortie d'intrant synchronisée. Sélectionnez une provende, un médicament
            ou un vaccin du stock lors de la saisie pour que le stock soit mis à jour automatiquement.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Intrant</th>
                        <th>Origine</th>
                        <th class="text-end">Quantité</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockMovements as $movement)
                        <tr data-href="{{ route('inputs.show', $movement->input) }}">
                            <td>{{ $movement->input->category->icon() }} {{ $movement->input->name }}</td>
                            <td>{{ $movement->notes }}</td>
                            <td class="text-end text-danger">
                                {{ number_format((float) $movement->quantity, 2, ',', ' ') }} {{ $movement->input->unit }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection

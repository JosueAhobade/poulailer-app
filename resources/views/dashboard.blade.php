
@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')

{{-- EN-TÊTE --}}

<div class="d-flex flex-column flex-sm-row
            justify-content-between align-items-sm-center
            gap-3 mb-4">

    <div>
        <h2 class="mb-1">Vue générale du poulailler</h2>

        <div class="text-secondary">
            {{ $today->locale('fr')->translatedFormat('l d F Y') }}
            — Heure du Bénin
        </div>
    </div>

    <div class="d-grid d-sm-block">
        <a href="{{ route('reports.create') }}"
           class="btn btn-primary">
            + Nouveau rapport
        </a>
    </div>

</div>

{{-- STATUT DU RAPPORT DU JOUR --}}

@if($todayReport)

    <div class="alert alert-success mb-4">
        ✅ Le rapport du jour a bien été enregistré.
    </div>

@else

    <div class="alert alert-warning mb-4">
        <strong>Rapport du jour non renseigné.</strong>

        <div class="mt-1">
            Les indicateurs quotidiens seront actualisés
            après sa saisie.
        </div>
    </div>

@endif


{{-- INDICATEURS PRINCIPAUX --}}

<div class="row row-cards mb-4">

    {{-- EFFECTIF --}}

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">

                <div class="text-secondary mb-2">
                    🐔 Effectif du dernier rapport
                </div>

                <div class="h1 mb-1">

                    {{ $latestReport
                        ? number_format($latestReport->birds_count, 0, ',', ' ')
                        : '—'
                    }}

                </div>

                <div class="text-secondary small">

                    @if($latestReport)
                        Au {{ $latestReport->report_date->format('d/m/Y') }}
                    @else
                        Aucun rapport enregistré
                    @endif

                </div>

            </div>
        </div>
    </div>

    {{-- MORTALITÉ --}}

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">

                <div class="text-secondary mb-2">
                    💀 Mortalité aujourd'hui
                </div>

                <div class="h1 mb-1">

                    {{ $todayReport
                        ? $todayReport->deaths_count
                        : '—'
                    }}

                </div>

                <div class="text-secondary small">
                    Décès cumulés enregistrés :
                    {{ $totalDeathsRecorded }}
                </div>

                @if($todayReport)
                    <div class="text-secondary small mt-1">
                        {{ $todayReport->sick_count }}
                        sujet(s) malade(s) / isolé(s)
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- ALIMENT --}}

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">

                <div class="text-secondary mb-2">
                    🌽 Aliment servi aujourd'hui
                </div>

                <div class="h1 mb-1">

                    @if($todayReport?->feed_quantity !== null)
                        {{ number_format(
                            (float) $todayReport->feed_quantity,
                            2, ',', ' '
                        ) }}
                        <span class="fs-4">kg</span>
                    @else
                        —
                    @endif

                </div>

                <div class="text-secondary small">
                    Moyenne sur 7 jours :

                    @if($averageFeed !== null)
                        <strong>
                            {{ number_format($averageFeed, 2, ',', ' ') }}
                            kg/j
                        </strong>
                        ({{ $feedDays }} j renseignés)
                    @else
                        —
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- EAU --}}

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">

                <div class="text-secondary mb-2">
                    💧 Eau servie aujourd'hui
                </div>

                <div class="h1 mb-1">

                    @if($todayReport?->water_quantity !== null)
                        {{ number_format(
                            (float) $todayReport->water_quantity,
                            2, ',', ' '
                        ) }}
                        <span class="fs-4">L</span>
                    @else
                        —
                    @endif

                </div>

                <div class="text-secondary small">
                    Moyenne sur 7 jours :

                    @if($averageWater !== null)
                        <strong>
                            {{ number_format($averageWater, 2, ',', ' ') }}
                            L/j
                        </strong>
                        ({{ $waterDays }} j renseignés)
                    @else
                        —
                    @endif
                </div>

            </div>
        </div>
    </div>

</div>


{{-- GRAPHIQUES DES CONSOMMATIONS --}}

<div class="row row-cards mb-4">

    {{-- EAU --}}

    <div class="col-12 col-lg-6">

        <div class="card h-100">

            <div class="card-header">
                <h3 class="card-title">
                    💧 Évolution de l'eau servie
                </h3>
            </div>

            <div class="card-body">

                <div class="text-secondary small mb-3">
                    Quantité distribuée en litres —
                    14 derniers jours
                </div>

                @if($hasWater)

                    <div style="position:relative;height:260px;width:100%">
                        <canvas id="waterChart"
                                role="img"
                                aria-label="Courbe de l'eau distribuée"></canvas>
                    </div>

                @else

                    <div class="text-center text-secondary py-5">
                        Aucune donnée d'eau sur cette période.
                    </div>

                @endif

            </div>

        </div>

    </div>

    {{-- ALIMENT --}}

    <div class="col-12 col-lg-6">

        <div class="card h-100">

            <div class="card-header">
                <h3 class="card-title">
                    🌽 Évolution de l'aliment servi
                </h3>
            </div>

            <div class="card-body">

                <div class="text-secondary small mb-3">
                    Quantité distribuée en kilogrammes —
                    14 derniers jours
                </div>

                @if($hasFeed)

                    <div style="position:relative;height:260px;width:100%">
                        <canvas id="feedChart"
                                role="img"
                                aria-label="Courbe de l'aliment distribué"></canvas>
                    </div>

                @else

                    <div class="text-center text-secondary py-5">
                        Aucune donnée d'aliment sur cette période.
                    </div>

                @endif

            </div>

        </div>

    </div>

</div>


{{-- EFFECTIF + RAPPORTS RÉCENTS --}}

<div class="row row-cards mb-4">

    {{-- ÉVOLUTION DE L'EFFECTIF --}}

    <div class="col-12 col-lg-7">

        <div class="card h-100">

            <div class="card-header">
                <h3 class="card-title">
                    🐔 Évolution du cheptel
                </h3>
            </div>

            <div class="card-body">

                <p class="text-secondary small">
                    Nombre de sujets renseigné dans les rapports,
                    sur les 14 derniers jours.
                </p>

                @if($hasBirds)

                    <div style="position:relative;height:260px;width:100%">
                        <canvas id="birdsChart"
                                role="img"
                                aria-label="Évolution de l'effectif"></canvas>
                    </div>

                @else

                    <div class="text-center text-secondary py-5">
                        Pas encore de données.
                    </div>

                @endif

            </div>

        </div>

    </div>

    {{-- DERNIERS RAPPORTS --}}

    <div class="col-12 col-lg-5">

        <div class="card h-100">

            <div class="card-header">
                <h3 class="card-title">
                    Dernières activités
                </h3>

                <div class="card-actions">
                    <a href="{{ route('reports.index') }}">
                        Tout voir
                    </a>
                </div>
            </div>

            <div class="list-group list-group-flush">

                @forelse($recentReports as $report)

                    <div class="list-group-item">

                        <div class="d-flex justify-content-between mb-2">

                            <strong>
                                {{ $report->report_date->format('d/m/Y') }}
                            </strong>

                            <span class="badge bg-green-lt">
                                {{ $report->birds_count }} sujets
                            </span>

                        </div>

                        <div class="small text-secondary">
                            🌽
                            {{ $report->feed_quantity ?? '—' }} kg

                            &nbsp; | &nbsp;

                            💧
                            {{ $report->water_quantity ?? '—' }} L
                        </div>

                        <div class="mt-2">

                            @if($report->treatments_count > 0)
                                <span class="badge bg-blue-lt me-1">
                                    💊 {{ $report->treatments_count }}
                                    traitement(s)
                                </span>
                            @endif

                            @if($report->vaccinations_count > 0)
                                <span class="badge bg-purple-lt">
                                    💉 {{ $report->vaccinations_count }}
                                    vaccin(s)
                                </span>
                            @endif

                        </div>

                    </div>

                @empty

                    <div class="card-body text-secondary text-center">
                        Aucune activité enregistrée.
                    </div>

                @endforelse

            </div>

        </div>

    </div>

</div>

@endsection

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>

<script>
    // Données transmises directement depuis Laravel
    const chartData = {{ \Illuminate\Support\Js::from($chartData) }};

    /**
     * Générateur réutilisable de courbes.
     */
    function renderLineChart(
        elementId,
        values,
        label,
        unit,
        color,
        beginAtZero = true,
        integerValues = false
    ) {
        const canvas = document.getElementById(elementId);

        if (!canvas) return;

        new Chart(canvas, {
            type: 'line',

            data: {
                labels: chartData.labels,

                datasets: [{
                    label: label,
                    data: values,

                    borderColor: color,
                    backgroundColor: color,

                    borderWidth: 2,
                    tension: 0.3,

                    pointRadius: 3,
                    pointHoverRadius: 5,

                    spanGaps: false,
                    fill: false
                }]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                interaction: {
                    mode: 'index',
                    intersect: false
                },

                plugins: {
                    legend: {
                        display: false
                    },

                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = new Intl.NumberFormat(
                                    'fr-FR',
                                    { maximumFractionDigits: 2 }
                                ).format(context.parsed.y);

                                return `${label} : ${value} ${unit}`;
                            }
                        }
                    }
                },

                scales: {
                    x: {
                        grid: {
                            display: false
                        },

                        ticks: {
                            maxTicksLimit: 7,
                            maxRotation: 0
                        }
                    },

                    y: {
                        beginAtZero: beginAtZero,
                        grace: '5%',

                        ticks: integerValues
                            ? { precision: 0 }
                            : {}
                    }
                }
            }
        });
    }

    // Eau
    renderLineChart(
        'waterChart',
        chartData.water,
        'Eau servie',
        'L',
        '#207cc7'
    );

    // Aliment
    renderLineChart(
        'feedChart',
        chartData.feed,
        'Aliment servi',
        'kg',
        '#2f9e44'
    );

    // Cheptel
    renderLineChart(
        'birdsChart',
        chartData.birds,
        'Effectif',
        'sujets',
        '#f59f00',
        false,
        true
    );

</script>

@endpush

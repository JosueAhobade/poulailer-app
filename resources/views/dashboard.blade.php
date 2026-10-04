
@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')

{{-- EN-TÊTE --}}


<div class="d-flex flex-column flex-md-row
            justify-content-between align-items-md-center
            gap-3 mb-4">

    <div>
        <h2 class="mb-1">
            Vue générale du poulailler
        </h2>

        <div class="text-secondary">
            {{ $today->locale('fr')->translatedFormat('l d F Y') }}
            — Heure du Bénin
        </div>
    </div>

    <div class="d-flex flex-column flex-sm-row gap-2">

        {{-- FILTRE DE PÉRIODE --}}

        <form method="GET"
              action="{{ route('dashboard') }}"
              class="d-flex gap-2">

            <select
                name="period"
                class="form-select"
                aria-label="Période du tableau de bord"
                onchange="this.form.requestSubmit()"
            >

                <option value="7" @selected($period == 7)>
                    7 derniers jours
                </option>

                <option value="14" @selected($period == 14)>
                    14 derniers jours
                </option>

                <option value="30" @selected($period == 30)>
                    30 derniers jours
                </option>

            </select>

            <button type="submit"
                    class="btn btn-outline-secondary">
                OK
            </button>

        </form>

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

        {{-- GRAPHIQUE EAU --}}

        <div class="col-12 col-lg-6">

            <div class="card h-100">

                <div class="card-header">

                    <h3 class="card-title">
                        💧 Évolution de la consommation d'eau
                    </h3>

                </div>

                <div class="card-body">

                    <p class="text-secondary small mb-3">
                        Eau servie et consommée sur les
                        {{ $period }} derniers jours.
                    </p>

                    @if($hasWater)

                        <div style="position: relative; height: 300px; width: 100%;">

                            <canvas
                                id="waterChart"
                                role="img"
                                aria-label="Graphique de consommation d'eau"
                            ></canvas>

                        </div>

                    @else

                        <div class="text-center text-secondary py-5">

                            Aucune donnée d'eau disponible
                            sur cette période.

                        </div>

                    @endif

                </div>

            </div>

        </div>


        {{-- GRAPHIQUE ALIMENT --}}

        <div class="col-12 col-lg-6">

            <div class="card h-100">

                <div class="card-header">

                    <h3 class="card-title">
                        🌽 Évolution de la consommation d'aliment
                    </h3>

                </div>

                <div class="card-body">

                    <p class="text-secondary small mb-3">
                        Aliment servi et consommé sur les
                        {{ $period }} derniers jours.
                    </p>

                    @if($hasFeed)

                        <div style="position: relative; height: 300px; width: 100%;">

                            <canvas
                                id="feedChart"
                                role="img"
                                aria-label="Graphique de consommation d'aliment"
                            ></canvas>

                        </div>

                    @else

                        <div class="text-center text-secondary py-5">

                            Aucune donnée alimentaire disponible
                            sur cette période.

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
                    sur les {{ $period }} derniers jours.
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
    const chartData = {{ \Illuminate\Support\Js::from($chartData) }};

    /**
     * Génère une courbe comparative.
     *
     * Une série peut être entièrement absente :
     * elle ne sera pas affichée dans la légende.
     */
    function renderComparisonChart(
        elementId,
        series,
        unit,
        beginAtZero = true,
        integerValues = false
    ) {
        const canvas = document.getElementById(elementId);

        if (!canvas) return;

        const datasets = series
            .filter(item =>
                item.values.some(value => value !== null)
            )
            .map(item => ({
                label: item.label,
                data: item.values,

                borderColor: item.color,
                backgroundColor: item.color,

                // Servie = pointillés / consommée = continu
                borderDash: item.dashed ? [6, 4] : [],

                borderWidth: 2,
                tension: 0.25,

                pointRadius: chartData.labels.length > 14 ? 2 : 3,
                pointHoverRadius: 5,

                spanGaps: false,
                fill: false
            }));

        if (datasets.length === 0) return;

        new Chart(canvas, {
            type: 'line',

            data: {
                labels: chartData.labels,
                datasets: datasets
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
                        display: datasets.length > 1,
                        position: 'bottom',

                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 20
                        }
                    },

                    tooltip: {
                        callbacks: {
                            label: function(context) {

                                if (context.parsed.y === null) {
                                    return 'Non renseigné';
                                }

                                const value = new Intl.NumberFormat(
                                    'fr-FR',
                                    {
                                        maximumFractionDigits: 2
                                    }
                                ).format(context.parsed.y);

                                return (
                                    context.dataset.label
                                    + ' : '
                                    + value
                                    + ' '
                                    + unit
                                );
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
                            maxTicksLimit: 8,
                            maxRotation: 0,
                            autoSkip: true
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

    // ----------------------------------------
    // GRAPHIQUE EAU
    // ----------------------------------------

    renderComparisonChart(
        'waterChart',
        [
            {
                label: 'Eau servie',
                values: chartData.waterServed,
                color: '#7db7e8',
                dashed: true
            },
            {
                label: 'Eau consommée',
                values: chartData.waterConsumed,
                color: '#1769aa',
                dashed: false
            }
        ],
        'L'
    );

    // ----------------------------------------
    // GRAPHIQUE ALIMENT
    // ----------------------------------------

    renderComparisonChart(
        'feedChart',
        [
            {
                label: 'Aliment servi',
                values: chartData.feedServed,
                color: '#99d4a2',
                dashed: true
            },
            {
                label: 'Aliment consommé',
                values: chartData.feedConsumed,
                color: '#278342',
                dashed: false
            }
        ],
        'kg'
    );

    // ----------------------------------------
    // GRAPHIQUE EFFECTIF
    // ----------------------------------------

    renderComparisonChart(
        'birdsChart',
        [
            {
                label: 'Effectif',
                values: chartData.birds,
                color: '#d28a19',
                dashed: false
            }
        ],
        'sujets',
        false,
        true
    );

</script>

@endpush

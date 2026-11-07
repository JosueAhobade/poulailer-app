@extends('layouts.app')

@section('title', 'Historique des rapports')

@section('content')

<div class="card">
    <div class="card-body">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-3">

            <form method="GET"
                  action="{{ route('reports.index') }}"
                  class="d-flex flex-wrap align-items-end gap-2">

                <div>
                    <label class="form-label small mb-1">Du</label>
                    <input type="date"
                           name="from"
                           class="form-control"
                           value="{{ $filters['from'] ?? '' }}">
                </div>

                <div>
                    <label class="form-label small mb-1">Au</label>
                    <input type="date"
                           name="to"
                           class="form-control"
                           value="{{ $filters['to'] ?? '' }}">
                </div>

                <button type="submit" class="btn btn-outline-secondary">
                    Filtrer
                </button>

                @if(! empty($filters))
                    <a href="{{ route('reports.index') }}" class="btn btn-link">
                        Réinitialiser
                    </a>
                @endif

            </form>

            <a href="{{ route('reports.create') }}"
               class="btn btn-primary">
                + Nouveau rapport
            </a>

        </div>

        <div class="text-secondary small mb-2">
            Cliquez sur une ligne pour voir le détail complet du rapport.
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-vcenter text-nowrap">

                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Sujets</th>
                        <th>Morts</th>
                        <th>Malades</th>
                        <th>Aliment servi / consommé</th>
                        <th>Eau servie / consommée</th>
                        <th>Soins</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($reports as $report)

                    <tr data-href="{{ route('reports.show', $report) }}">

                        <td>
                            <a href="{{ route('reports.show', $report) }}" class="text-reset fw-bold">
                                {{ $report->report_date->format('d/m/Y') }}
                            </a>
                        </td>

                        <td>{{ number_format($report->birds_count, 0, ',', ' ') }}</td>

                        <td>
                            @if($report->deaths_count > 0)
                                <span class="text-danger fw-bold">{{ $report->deaths_count }}</span>
                            @else
                                0
                            @endif
                        </td>

                        <td>{{ $report->sick_count }}</td>

                        <td>
                            {{ $report->feed_quantity !== null ? number_format((float) $report->feed_quantity, 2, ',', ' ') : '—' }}
                            /
                            {{ $report->feed_consumed !== null ? number_format((float) $report->feed_consumed, 2, ',', ' ') : '—' }}
                            kg
                        </td>

                        <td>
                            {{ $report->water_quantity !== null ? number_format((float) $report->water_quantity, 2, ',', ' ') : '—' }}
                            /
                            {{ $report->water_consumed !== null ? number_format((float) $report->water_consumed, 2, ',', ' ') : '—' }}
                            L
                        </td>

                        <td>
                            @if($report->treatments_count > 0)
                                <span class="badge bg-blue-lt">💊 {{ $report->treatments_count }}</span>
                            @endif

                            @if($report->vaccinations_count > 0)
                                <span class="badge bg-purple-lt">💉 {{ $report->vaccinations_count }}</span>
                            @endif

                            @if($report->treatments_count + $report->vaccinations_count === 0)
                                <span class="text-secondary">—</span>
                            @endif
                        </td>

                        <td class="text-end">
                            <div class="btn-list justify-content-end flex-nowrap">
                                <a href="{{ route('treatments.create', $report) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    + Traitement
                                </a>

                                <a href="{{ route('vaccinations.create', $report) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    + Vaccin
                                </a>

                                <a href="{{ route('reports.consumption.edit', $report) }}"
                                   class="btn btn-sm btn-outline-success">
                                    Consommation
                                </a>
                            </div>
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="8" class="text-center text-secondary py-4">
                            Aucun rapport pour cette période.
                        </td>
                    </tr>

                @endforelse

                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                <div class="text-secondary small">
                    Rapports {{ $reports->firstItem() }} à {{ $reports->lastItem() }}
                    sur {{ $reports->total() }}
                </div>

                {{ $reports->onEachSide(1)->links() }}
            </div>
        @endif

    </div>
</div>

@endsection

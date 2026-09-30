@extends('layouts.app')

@section('title', 'Historique des rapports')

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <a
                href="{{ route('reports.create') }}"
                class="btn btn-primary"
            >
                + Nouveau rapport
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-vcenter text-nowrap">

                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Sujets</th>
                        <th>Morts</th>
                        <th>Malades</th>
                        <th>Aliment</th>
                        <th>Eau</th>
                        <th>Traitements</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($reports as $report)

                    <tr>
                        <td>
                            {{ $report->report_date->format('d/m/Y') }}
                        </td>

                        <td>
                            {{ $report->birds_count }}
                        </td>

                        <td>
                            {{ $report->deaths_count }}
                        </td>

                        <td>
                            {{ $report->sick_count }}
                        </td>

                        <td>
                            {{ $report->feed_quantity ?? '-' }} kg
                        </td>

                        <td>
                            {{ $report->water_quantity ?? '-' }} L
                        </td>

                        <td>
                            @forelse($report->treatments as $treatment)

                                <div class="mb-2">

                                    <span class="badge bg-blue-lt">
                                        {{ $treatment->name }}
                                    </span>

                                    @if($treatment->dosage)
                                        <div class="text-secondary small">
                                            {{ $treatment->dosage }}
                                        </div>
                                    @endif

                                    @if($treatment->administration_method)
                                        <div class="text-secondary small">
                                            {{ $treatment->administration_method }}
                                        </div>
                                    @endif

                                </div>

                            @empty

                                <span class="text-secondary">
                                    Aucun
                                </span>

                            @endforelse
                        </td>

                        <td>

                            @if($report->treatments->count() > 0)

                                <span class="badge bg-green-lt me-2">
                                    💊 {{ $report->treatments->count() }}
                                </span>

                            @endif

                            <a
                                href="{{ route('treatments.create', $report) }}"
                                class="btn btn-sm btn-outline-primary"
                            >
                                + Traitement
                            </a>

                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="8" class="text-center text-secondary py-4">
                            Aucun rapport pour le moment.
                        </td>
                    </tr>

                @endforelse

                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection
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

        <a href="{{ route('reports.create') }}" class="btn btn-primary mb-3">
            + Nouveau rapport
        </a>

        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Sujets</th>
                    <th>Morts</th>
                    <th>Malades</th>
                    <th>Aliment</th>
                    <th>Eau</th>
                </tr>
            </thead>

            <tbody>
                @forelse($reports as $report)
                    <tr>
                        <td>{{ $report->report_date->format('d/m/Y') }}</td>
                        <td>{{ $report->birds_count }}</td>
                        <td>{{ $report->deaths_count }}</td>
                        <td>{{ $report->sick_count }}</td>
                        <td>{{ $report->feed_quantity }} kg</td>
                        <td>{{ $report->water_quantity }} L</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            Aucun rapport pour le moment.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>
</div>

@endsection
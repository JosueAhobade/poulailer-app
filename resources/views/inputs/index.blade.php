@extends('layouts.app')

@section('title', 'Intrants & stock')

@section('content')

<div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">

    <div class="btn-list">
        <a href="{{ route('inputs.index') }}"
           class="btn {{ $category === null ? 'btn-dark' : 'btn-outline-secondary' }}">
            Tous
        </a>
        @foreach(\App\Enums\InputCategory::cases() as $case)
            <a href="{{ route('inputs.index', ['category' => $case->value]) }}"
               class="btn {{ $category === $case ? 'btn-dark' : 'btn-outline-secondary' }}">
                {{ $case->icon() }} {{ $case->label() }}
            </a>
        @endforeach
    </div>

    <a href="{{ route('inputs.create', $category ? ['category' => $category->value] : []) }}"
       class="btn btn-primary">
        + Nouvel intrant
    </a>

</div>

@if($lowStockCount > 0)
    <div class="alert alert-warning">
        ⚠️ {{ $lowStockCount }} intrant(s) sous le seuil d'alerte : pensez à réapprovisionner.
    </div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover table-vcenter card-table text-nowrap">
            <thead>
                <tr>
                    <th>Intrant</th>
                    <th>Catégorie</th>
                    <th class="text-end">Stock actuel</th>
                    <th class="text-end">Conso. {{ $windowDays }} j</th>
                    <th class="text-end">Autonomie estimée</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inputs as $input)
                    @php
                        $stock = $input->currentStock();
                        $dailyUse = abs((float) $input->recent_consumption) / $windowDays;
                    @endphp

                    <tr data-href="{{ route('inputs.show', $input) }}">
                        <td>
                            <a href="{{ route('inputs.show', $input) }}" class="text-reset fw-bold">
                                {{ $input->name }}
                            </a>
                            @if($input->isLowStock())
                                <span class="badge bg-red-lt ms-1">Stock bas</span>
                            @endif
                        </td>
                        <td>{{ $input->category->icon() }} {{ $input->category->label() }}</td>
                        <td class="text-end fw-bold {{ $stock < 0 ? 'text-danger' : '' }}">
                            {{ number_format($stock, 2, ',', ' ') }} {{ $input->unit }}
                        </td>
                        <td class="text-end">
                            {{ number_format(abs((float) $input->recent_consumption), 2, ',', ' ') }} {{ $input->unit }}
                        </td>
                        <td class="text-end">
                            @if($dailyUse > 0 && $stock > 0)
                                ≈ {{ (int) floor($stock / $dailyUse) }} jours
                            @else
                                <span class="text-secondary">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('inputs.movements.create', $input) }}"
                               class="btn btn-sm btn-outline-success">
                                + Entrée
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-4">
                            Aucun intrant enregistré.
                            <a href="{{ route('inputs.create') }}">Ajouter la première provende, un vaccin ou un médicament.</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="text-secondary small mt-2">
    Le stock est calculé à partir des entrées (achats), pertes, ajustements d'inventaire
    et des sorties synchronisées avec les rapports journaliers, traitements et vaccinations.
</div>

@endsection

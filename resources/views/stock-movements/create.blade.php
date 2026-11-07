@extends('layouts.app')

@section('title', $type->label().' — '.$input->name)

@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">

        <div class="card">
            <div class="card-body">

                <div class="mb-3 text-secondary">
                    Stock théorique actuel :
                    <strong>{{ number_format($input->currentStock(), 2, ',', ' ') }} {{ $input->unit }}</strong>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('inputs.movements.store', $input) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Type de mouvement</label>
                        <div class="btn-group w-100" role="group">
                            @foreach(\App\Enums\StockMovementType::manual() as $case)
                                <a href="{{ route('inputs.movements.create', [$input, 'type' => $case->value]) }}"
                                   class="btn {{ $type === $case ? 'btn-dark' : 'btn-outline-secondary' }}">
                                    {{ $case->label() }}
                                </a>
                            @endforeach
                        </div>
                        <input type="hidden" name="type" value="{{ $type->value }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date *</label>
                        <input type="date" name="movement_date" class="form-control" required
                               value="{{ old('movement_date', now('Africa/Porto-Novo')->toDateString()) }}">
                    </div>

                    @if($type === \App\Enums\StockMovementType::Adjustment)
                        <div class="mb-3">
                            <label class="form-label">Stock réellement compté *</label>
                            <div class="input-group">
                                <input type="number" name="counted_stock" class="form-control" required
                                       step="0.01" min="0" inputmode="decimal" value="{{ old('counted_stock') }}">
                                <span class="input-group-text">{{ $input->unit }}</span>
                            </div>
                            <small class="text-secondary">L'écart avec le stock théorique sera enregistré comme ajustement.</small>
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="form-label">
                                {{ $type === \App\Enums\StockMovementType::Purchase ? 'Quantité reçue' : 'Quantité perdue' }} *
                            </label>
                            <div class="input-group">
                                <input type="number" name="quantity" class="form-control" required
                                       step="0.01" min="0" inputmode="decimal" value="{{ old('quantity') }}">
                                <span class="input-group-text">{{ $input->unit }}</span>
                            </div>
                            @if($input->unit === 'kg' && $type === \App\Enums\StockMovementType::Purchase)
                                <small class="text-secondary">Ex : 10 sacs de 50 kg = 500 kg.</small>
                            @endif
                        </div>
                    @endif

                    @if($type === \App\Enums\StockMovementType::Purchase)
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Prix unitaire (FCFA / {{ $input->unit }})</label>
                                <input type="number" name="unit_price" class="form-control"
                                       step="0.01" min="0" value="{{ old('unit_price') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fournisseur</label>
                                <input type="text" name="supplier" class="form-control" value="{{ old('supplier') }}">
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="2" class="form-control"
                                  placeholder="{{ $type === \App\Enums\StockMovementType::Loss ? 'Ex : sacs mouillés, flacon périmé' : '' }}">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-grid d-md-block">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <a href="{{ route('inputs.show', $input) }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

@endsection

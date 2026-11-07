@extends('layouts.app')

@section('title', $collection->exists ? 'Modifier le ramassage du '.$collection->collection_date->format('d/m/Y') : 'Ramassage d\'œufs')

@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-body">

                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST"
                      action="{{ $collection->exists ? route('eggs.collections.update', $collection) : route('eggs.collections.store') }}">
                    @csrf
                    @if($collection->exists)
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Date du ramassage *</label>
                        <input type="date" name="collection_date" class="form-control" required
                               value="{{ old('collection_date', $collection->collection_date?->format('Y-m-d')) }}">
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Œufs ramassés *</label>
                            <input type="number" name="collected_count" class="form-control" required
                                   min="0" inputmode="numeric"
                                   value="{{ old('collected_count', $collection->collected_count) }}">
                        </div>

                        <div class="col-6 mb-3">
                            <label class="form-label">Dont cassés / impropres</label>
                            <input type="number" name="broken_count" class="form-control"
                                   min="0" inputmode="numeric"
                                   value="{{ old('broken_count', $collection->broken_count) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="2" class="form-control">{{ old('notes', $collection->notes) }}</textarea>
                    </div>

                    <div class="d-grid d-md-block">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <a href="{{ $collection->exists ? route('eggs.collections.show', $collection) : route('eggs.collections.index') }}"
                           class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

@endsection

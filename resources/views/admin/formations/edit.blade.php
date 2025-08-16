@extends('admin.layouts.app')

@section('title', 'Modifier la formation')
@section('page-title', 'Modifier: ' . $formation->title)

@section('content')
<form action="{{ route('admin.formations.update', $formation) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations de la formation</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="pack_id" class="form-label">Pack de formation</label>
                        <select class="form-select @error('pack_id') is-invalid @enderror" id="pack_id" name="pack_id" required>
                            @foreach($packs as $pack)
                                <option value="{{ $pack->id }}" {{ old('pack_id', $formation->pack_id) == $pack->id ? 'selected' : '' }}>
                                    {{ $pack->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('pack_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Titre de la formation</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title', $formation->title) }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4">{{ old('description', $formation->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="video_url" class="form-label">URL de la vidéo</label>
                            <input type="url" class="form-control @error('video_url') is-invalid @enderror" 
                                   id="video_url" name="video_url" value="{{ old('video_url', $formation->video_url) }}">
                            @error('video_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="duration_minutes" class="form-label">Durée (minutes)</label>
                            <input type="number" class="form-control @error('duration_minutes') is-invalid @enderror" 
                                   id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', $formation->duration_minutes) }}" 
                                   min="0">
                            @error('duration_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Paramètres</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="order" class="form-label">Ordre dans le pack</label>
                        <input type="number" class="form-control @error('order') is-invalid @enderror" 
                               id="order" name="order" value="{{ old('order', $formation->order) }}" min="0">
                        @error('order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                               value="1" {{ old('is_active', $formation->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Formation active</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.formations.show', $formation) }}" class="btn btn-secondary">Retour</a>
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </div>
        </div>
    </div>
</form>
@endsection
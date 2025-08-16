@extends('admin.layouts.app')

@section('title', 'Créer une formation')
@section('page-title', 'Nouvelle Formation')

@php
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['title' => 'Formations', 'url' => route('admin.formations.index')],
        ['title' => 'Nouvelle', 'url' => '']
    ];
@endphp

@section('content')
<form action="{{ route('admin.formations.store') }}" method="POST">
    @csrf
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations de la formation</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="pack_id" class="form-label">Pack de formation <span class="text-danger">*</span></label>
                        <select class="form-select @error('pack_id') is-invalid @enderror" id="pack_id" name="pack_id" required>
                            <option value="">Choisir un pack</option>
                            @foreach($packs as $pack)
                                <option value="{{ $pack->id }}" {{ old('pack_id', request('pack_id')) == $pack->id ? 'selected' : '' }}>
                                    {{ $pack->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('pack_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Titre de la formation <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Description détaillée du contenu de la formation</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="video_url" class="form-label">URL de la vidéo</label>
                            <input type="url" class="form-control @error('video_url') is-invalid @enderror" 
                                   id="video_url" name="video_url" value="{{ old('video_url') }}" 
                                   placeholder="https://example.com/video.mp4">
                            @error('video_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="duration_minutes" class="form-label">Durée (minutes)</label>
                            <input type="number" class="form-control @error('duration_minutes') is-invalid @enderror" 
                                   id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', 0) }}" 
                                   min="0">
                            @error('duration_minutes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                               id="order" name="order" value="{{ old('order', 0) }}" min="0">
                        @error('order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Position de cette formation dans le pack</div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                               value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Formation active</label>
                        <div class="form-text">Les formations inactives ne sont pas visibles</div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            Après création
                        </h6>
                        <p class="mb-0">Vous pourrez ajouter des modules à cette formation depuis la page de détails.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.formations.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Retour
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>
                    Créer la formation
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
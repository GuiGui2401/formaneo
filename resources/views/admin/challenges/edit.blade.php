@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Modifier le défi</h1>
        <a href="{{ route('admin.challenges.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Détails du défi</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.challenges.update', $challenge->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="title">Titre *</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $challenge->title) }}" required>
                            @error('title')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="reward">Récompense (FCFA) *</label>
                            <input type="number" step="0.01" class="form-control" id="reward" name="reward" value="{{ old('reward', $challenge->reward) }}" required>
                            @error('reward')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="target">Cible (objectif)</label>
                            <input type="number" class="form-control" id="target" name="target" value="{{ old('target', $challenge->target) }}" placeholder="Ex: 5 pour 5 parrainages">
                            @error('target')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required>{{ old('description', $challenge->description) }}</textarea>
                    @error('description')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="icon_name">Nom de l'icône</label>
                            <input type="text" class="form-control" id="icon_name" name="icon_name" value="{{ old('icon_name', $challenge->icon_name) }}" placeholder="Ex: trophy, star, medal">
                            <small class="form-text text-muted">Utilisez des noms d'icônes FontAwesome ou Material Icons</small>
                            @error('icon_name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="image">Image du défi</label>
                            @if($challenge->image_url)
                                <div class="mb-2">
                                    <img src="{{ asset($challenge->image_url) }}" alt="{{ $challenge->title }}" style="max-width: 200px; max-height: 150px;" class="img-thumbnail">
                                </div>
                            @endif
                            <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
                            <small class="form-text text-muted">Laissez vide pour conserver l'image actuelle</small>
                            @error('image')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="expires_at">Date d'expiration</label>
                            <input type="datetime-local" class="form-control" id="expires_at" name="expires_at"
                                value="{{ old('expires_at', $challenge->expires_at ? $challenge->expires_at->format('Y-m-d\TH:i') : '') }}">
                            @error('expires_at')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="order">Ordre d'affichage</label>
                            <input type="number" class="form-control" id="order" name="order" value="{{ old('order', $challenge->order) }}">
                            @error('order')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mt-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" {{ old('is_active', $challenge->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Actif</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                    <a href="{{ route('admin.challenges.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

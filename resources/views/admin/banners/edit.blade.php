@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Modifier la bannière</h1>
        <a href="{{ route('admin.banners.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informations de la bannière</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="title">Titre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                           id="title" name="title" value="{{ old('title', $banner->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="type">Type <span class="text-danger">*</span></label>
                    <select class="form-control @error('type') is-invalid @enderror"
                            id="type" name="type" required>
                        <option value="">Sélectionner un type</option>
                        <option value="image" {{ old('type', $banner->type) === 'image' ? 'selected' : '' }}>Image</option>
                        <option value="video" {{ old('type', $banner->type) === 'video' ? 'selected' : '' }}>Vidéo</option>
                        <option value="document" {{ old('type', $banner->type) === 'document' ? 'selected' : '' }}>Document</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Fichier actuel</label>
                    <div class="mb-2">
                        @if ($banner->type === 'image')
                            <img src="{{ asset('storage/' . $banner->file_path) }}" alt="{{ $banner->title }}" style="max-width: 300px;">
                        @elseif ($banner->type === 'video')
                            <i class="fas fa-video fa-3x text-primary"></i>
                            <p>{{ basename($banner->file_path) }}</p>
                        @else
                            <i class="fas fa-file fa-3x text-secondary"></i>
                            <p>{{ basename($banner->file_path) }}</p>
                        @endif
                    </div>
                    <a href="{{ asset('storage/' . $banner->file_path) }}" target="_blank" class="btn btn-sm btn-info">
                        <i class="fas fa-download"></i> Télécharger
                    </a>
                </div>

                <div class="form-group">
                    <label for="file">Nouveau fichier (optionnel)</label>
                    <input type="file" class="form-control-file @error('file') is-invalid @enderror"
                           id="file" name="file">
                    <small class="form-text text-muted">Laissez vide pour conserver le fichier actuel. Taille maximale : 20 MB</small>
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="3">{{ old('description', $banner->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="order">Ordre d'affichage</label>
                    <input type="number" class="form-control @error('order') is-invalid @enderror"
                           id="order" name="order" value="{{ old('order', $banner->order) }}" min="0">
                    <small class="form-text text-muted">Les bannières sont affichées par ordre croissant</small>
                    @error('order')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                    <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

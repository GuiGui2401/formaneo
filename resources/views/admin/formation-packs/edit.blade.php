@extends('admin.layouts.app')

@section('title', 'Modifier le pack de formation')
@section('page-title', 'Modifier le Pack: ' . $pack->name)

@php
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['title' => 'Packs de Formation', 'url' => route('admin.formation-packs.index')],
        ['title' => $pack->name, 'url' => route('admin.formation-packs.show', $pack)],
        ['title' => 'Modifier', 'url' => '']
    ];
@endphp

@section('content')
<form action="{{ route('admin.formation-packs.update', $pack) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="row">
        <!-- Informations principales -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informations principales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nom du pack <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $pack->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="author" class="form-label">Auteur <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('author') is-invalid @enderror" 
                                   id="author" 
                                   name="author" 
                                   value="{{ old('author', $pack->author) }}" 
                                   required>
                            @error('author')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="5" 
                                  required>{{ old('description', $pack->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="price" class="form-label">Prix (FCFA) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control @error('price') is-invalid @enderror" 
                                       id="price" 
                                       name="price" 
                                       value="{{ old('price', $pack->price) }}" 
                                       min="0" 
                                       step="0.01" 
                                       required>
                                <span class="input-group-text">FCFA</span>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="total_duration" class="form-label">Durée totale (minutes)</label>
                            <input type="number" 
                                   class="form-control @error('total_duration') is-invalid @enderror" 
                                   id="total_duration" 
                                   name="total_duration" 
                                   value="{{ old('total_duration', $pack->total_duration) }}" 
                                   min="0">
                            @error('total_duration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="students_count" class="form-label">Nombre d'étudiants</label>
                            <input type="number" 
                                   class="form-control @error('students_count') is-invalid @enderror" 
                                   id="students_count" 
                                   name="students_count" 
                                   value="{{ old('students_count', $pack->students_count) }}" 
                                   min="0">
                            @error('students_count')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="rating" class="form-label">Note (0-5)</label>
                            <input type="number" 
                                   class="form-control @error('rating') is-invalid @enderror" 
                                   id="rating" 
                                   name="rating" 
                                   value="{{ old('rating', $pack->rating) }}" 
                                   min="0" 
                                   max="5" 
                                   step="0.1">
                            @error('rating')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="order" class="form-label">Ordre d'affichage</label>
                            <input type="number" 
                                   class="form-control @error('order') is-invalid @enderror" 
                                   id="order" 
                                   name="order" 
                                   value="{{ old('order', $pack->order) }}" 
                                   min="0">
                            @error('order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Image et médias -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-image me-2"></i>
                        Image de couverture
                    </h5>
                </div>
                <div class="card-body">
                    @if($pack->thumbnail_url)
                        <div class="mb-3">
                            <label class="form-label">Image actuelle</label>
                            <div>
                                <img src="{{ $pack->thumbnail_url }}" alt="{{ $pack->name }}" class="img-thumbnail" style="max-width: 300px;">
                            </div>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <label for="thumbnail" class="form-label">Nouvelle image (optionnel)</label>
                        <input type="file" 
                               class="form-control @error('thumbnail') is-invalid @enderror" 
                               id="thumbnail" 
                               name="thumbnail" 
                               accept="image/*"
                               onchange="previewImage(this)">
                        @error('thumbnail')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Laissez vide pour conserver l'image actuelle</div>
                    </div>
                    
                    <!-- Prévisualisation de la nouvelle image -->
                    <div id="imagePreview" class="mt-3" style="display: none;">
                        <label class="form-label">Aperçu de la nouvelle image</label>
                        <div>
                            <img id="preview" src="" alt="Aperçu" class="img-thumbnail" style="max-width: 300px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Paramètres et options -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        Paramètres
                    </h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $pack->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Pack actif
                        </label>
                        <div class="form-text">Si désactivé, le pack ne sera pas visible sur l'application</div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_featured" 
                               name="is_featured" 
                               value="1"
                               {{ old('is_featured', $pack->is_featured) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_featured">
                            Mettre en vedette
                        </label>
                        <div class="form-text">Les packs en vedette apparaissent en premier</div>
                    </div>
                </div>
            </div>
            
            <!-- Statistiques -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Informations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $pack->formations->count() }}</h4>
                                <small class="text-muted">Formations</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <h4 class="text-success">{{ number_format($pack->price) }}</h4>
                            <small class="text-muted">Prix (FCFA)</small>
                        </div>
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-info">{{ $pack->students_count }}</h4>
                                <small class="text-muted">Étudiants</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-warning">{{ $pack->rating }}/5</h4>
                            <small class="text-muted">Note</small>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="small text-muted">
                        <div><strong>Créé:</strong> {{ $pack->created_at->format('d/m/Y à H:i') }}</div>
                        <div><strong>Modifié:</strong> {{ $pack->updated_at->format('d/m/Y à H:i') }}</div>
                        <div><strong>Slug:</strong> <code>{{ $pack->slug }}</code></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Actions -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.formation-packs.show', $pack) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Retour au pack
                    </a>
                    
                    <a href="{{ route('admin.formation-packs.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-1"></i>
                        Liste des packs
                    </a>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-danger" onclick="if(confirm('Êtes-vous sûr ?')) { document.getElementById('deleteForm').submit(); }">
                        <i class="fas fa-trash me-1"></i>
                        Supprimer
                    </button>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        Mettre à jour
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Formulaire de suppression caché -->
<form id="deleteForm" action="{{ route('admin.formation-packs.destroy', $pack) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
// Prévisualisation de l'image
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Validation du formulaire
document.querySelector('form').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const author = document.getElementById('author').value.trim();
    const description = document.getElementById('description').value.trim();
    const price = document.getElementById('price').value;
    
    if (!name || !author || !description || !price) {
        e.preventDefault();
        alert('Veuillez remplir tous les champs obligatoires.');
        return false;
    }
    
    if (parseFloat(price) < 0) {
        e.preventDefault();
        alert('Le prix ne peut pas être négatif.');
        return false;
    }
});
</script>
@endpush
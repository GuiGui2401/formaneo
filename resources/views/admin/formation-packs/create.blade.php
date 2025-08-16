@extends('admin.layouts.app')

@section('title', 'Créer un pack de formation')
@section('page-title', 'Nouveau Pack de Formation')

@php
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['title' => 'Packs de Formation', 'url' => route('admin.formation-packs.index')],
        ['title' => 'Nouveau Pack', 'url' => '']
    ];
@endphp

@section('content')
<form action="{{ route('admin.formation-packs.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
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
                                   value="{{ old('name') }}" 
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
                                   value="{{ old('author') }}" 
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
                                  required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Décrivez le contenu et les objectifs du pack de formation</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Prix (FCFA) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control @error('price') is-invalid @enderror" 
                                       id="price" 
                                       name="price" 
                                       value="{{ old('price') }}" 
                                       min="0" 
                                       step="0.01" 
                                       required>
                                <span class="input-group-text">FCFA</span>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="total_duration" class="form-label">Durée totale (minutes)</label>
                            <input type="number" 
                                   class="form-control @error('total_duration') is-invalid @enderror" 
                                   id="total_duration" 
                                   name="total_duration" 
                                   value="{{ old('total_duration', 0) }}" 
                                   min="0">
                            @error('total_duration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Durée estimée pour compléter tout le pack</div>
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
                    <div class="mb-3">
                        <label for="thumbnail" class="form-label">Image de couverture</label>
                        <input type="file" 
                               class="form-control @error('thumbnail') is-invalid @enderror" 
                               id="thumbnail" 
                               name="thumbnail" 
                               accept="image/*"
                               onchange="previewImage(this)">
                        @error('thumbnail')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Formats acceptés: JPG, PNG, GIF. Taille maximale: 2MB</div>
                    </div>
                    
                    <!-- Prévisualisation de l'image -->
                    <div id="imagePreview" class="mt-3" style="display: none;">
                        <img id="preview" src="" alt="Aperçu" class="img-thumbnail" style="max-width: 300px;">
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
                    <div class="mb-3">
                        <label for="order" class="form-label">Ordre d'affichage</label>
                        <input type="number" 
                               class="form-control @error('order') is-invalid @enderror" 
                               id="order" 
                               name="order" 
                               value="{{ old('order', 0) }}" 
                               min="0">
                        @error('order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Plus le nombre est petit, plus le pack sera affiché en premier</div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}>
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
                               {{ old('is_featured') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_featured">
                            Mettre en vedette
                        </label>
                        <div class="form-text">Les packs en vedette apparaissent en premier</div>
                    </div>
                </div>
            </div>
            
            <!-- Statistiques (pour information) -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Informations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Après la création du pack, vous pourrez :
                        <ul class="mb-0 mt-2">
                            <li>Ajouter des formations</li>
                            <li>Organiser les modules</li>
                            <li>Configurer les paramètres avancés</li>
                            <li>Voir les statistiques d'utilisation</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Actions -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.formation-packs.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Retour
                </a>
                
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" onclick="saveDraft()">
                        <i class="fas fa-save me-1"></i>
                        Sauvegarder comme brouillon
                    </button>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>
                        Créer le pack
                    </button>
                </div>
            </div>
        </div>
    </div>
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

// Génération automatique du slug (optionnel)
document.getElementById('name').addEventListener('input', function() {
    // Vous pouvez ajouter ici une logique pour générer automatiquement un slug
    // basé sur le nom du pack si nécessaire
});

// Sauvegarde comme brouillon
function saveDraft() {
    // Désactiver temporairement le pack avant soumission
    document.getElementById('is_active').checked = false;
    
    // Soumettre le formulaire
    document.querySelector('form').submit();
}

// Validation côté client
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
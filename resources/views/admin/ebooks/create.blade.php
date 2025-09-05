@extends('admin.layouts.app')

@section('title', 'Créer un ebook')
@section('page-title', 'Nouvel Ebook')

@php
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['title' => 'Ebooks', 'url' => route('admin.ebooks.index')],
        ['title' => 'Nouvel Ebook', 'url' => '']
    ];
@endphp

@section('content')
<form action="{{ route('admin.ebooks.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <div class="row">
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
                            <label for="title" class="form-label">Titre <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="author" class="form-label">Auteur</label>
                            <input type="text" 
                                   class="form-control @error('author') is-invalid @enderror" 
                                   id="author" 
                                   name="author" 
                                   value="{{ old('author') }}">
                            @error('author')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="4">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Décrivez le contenu et les objectifs de cet ebook</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">Catégorie</label>
                            <input type="text" 
                                   class="form-control @error('category') is-invalid @enderror" 
                                   id="category" 
                                   name="category" 
                                   value="{{ old('category') }}">
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="pages" class="form-label">Nombre de pages</label>
                            <input type="number" 
                                   class="form-control @error('pages') is-invalid @enderror" 
                                   id="pages" 
                                   name="pages" 
                                   value="{{ old('pages') }}" 
                                   min="1">
                            @error('pages')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Image de couverture et PDF -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-image me-2"></i>
                        Médias
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cover_image" class="form-label">Image de couverture</label>
                            <input type="file" 
                                   class="form-control @error('cover_image') is-invalid @enderror" 
                                   id="cover_image" 
                                   name="cover_image" 
                                   accept="image/*"
                                   onchange="previewImage(this)">
                            @error('cover_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Formats acceptés: JPG, PNG, GIF. Taille maximale: 2MB</div>
                            
                            <!-- Prévisualisation de l'image -->
                            <div id="imagePreview" class="mt-3" style="display: none;">
                                <label class="form-label">Aperçu de l'image</label>
                                <div>
                                    <img id="preview" src="" alt="Aperçu" class="img-thumbnail" style="max-width: 200px;">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="pdf_file" class="form-label">Fichier PDF</label>
                            <input type="file" 
                                   class="form-control @error('pdf_file') is-invalid @enderror" 
                                   id="pdf_file" 
                                   name="pdf_file" 
                                   accept=".pdf">
                            @error('pdf_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Fichier PDF de l'ebook. Taille maximale: 10MB</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
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
                        <label for="price" class="form-label">Prix (FCFA)</label>
                        <div class="input-group">
                            <input type="number" 
                                   class="form-control @error('price') is-invalid @enderror" 
                                   id="price" 
                                   name="price" 
                                   value="{{ old('price', 0) }}" 
                                   min="0" 
                                   step="0.01">
                            <span class="input-group-text">FCFA</span>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-text">Mettez 0 pour un ebook gratuit</div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Ebook actif
                        </label>
                        <div class="form-text">Si désactivé, l'ebook ne sera pas visible sur l'application</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="rating" class="form-label">Note (0-5)</label>
                        <input type="number" 
                               class="form-control @error('rating') is-invalid @enderror" 
                               id="rating" 
                               name="rating" 
                               value="{{ old('rating', 0) }}" 
                               min="0" 
                               max="5" 
                               step="0.1">
                        @error('rating')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Après la création de l'ebook, vous pourrez :
                        <ul class="mb-0 mt-2">
                            <li>Voir les statistiques de téléchargement</li>
                            <li>Gérer les achats des utilisateurs</li>
                            <li>Modifier les paramètres</li>
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
                <a href="{{ route('admin.ebooks.index') }}" class="btn btn-secondary">
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
                        Créer l'ebook
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

// Sauvegarde comme brouillon
function saveDraft() {
    // Désactiver temporairement l'ebook avant soumission
    document.getElementById('is_active').checked = false;
    
    // Soumettre le formulaire
    document.querySelector('form').submit();
}

// Validation côté client
document.querySelector('form').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    
    if (!title) {
        e.preventDefault();
        alert('Veuillez remplir le titre de l\'ebook.');
        return false;
    }
});
</script>
@endpush
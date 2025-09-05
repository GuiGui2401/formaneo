@extends('admin.layouts.app')

@section('title', 'Modifier l\'ebook')
@section('page-title', 'Modifier: ' . $ebook->title)

@php
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['title' => 'Ebooks', 'url' => route('admin.ebooks.index')],
        ['title' => $ebook->title, 'url' => route('admin.ebooks.show', $ebook)],
        ['title' => 'Modifier', 'url' => '']
    ];
@endphp

@section('content')
<form action="{{ route('admin.ebooks.update', $ebook) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
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
                                   value="{{ old('title', $ebook->title) }}" 
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
                                   value="{{ old('author', $ebook->author) }}">
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
                                  rows="4">{{ old('description', $ebook->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">Catégorie</label>
                            <input type="text" 
                                   class="form-control @error('category') is-invalid @enderror" 
                                   id="category" 
                                   name="category" 
                                   value="{{ old('category', $ebook->category) }}">
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
                                   value="{{ old('pages', $ebook->pages) }}" 
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
                            <label class="form-label">Image de couverture actuelle</label>
                            @if($ebook->cover_image_url)
                                <div class="mb-2">
                                    <img src="{{ $ebook->cover_image_url }}" alt="{{ $ebook->title }}" class="img-thumbnail" style="max-width: 200px;">
                                </div>
                            @else
                                <div class="mb-2">
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 200px; height: 250px;">
                                        <i class="fas fa-book text-muted"></i>
                                    </div>
                                </div>
                            @endif
                            
                            <label for="cover_image" class="form-label">Nouvelle image (optionnel)</label>
                            <input type="file" 
                                   class="form-control @error('cover_image') is-invalid @enderror" 
                                   id="cover_image" 
                                   name="cover_image" 
                                   accept="image/*"
                                   onchange="previewImage(this)">
                            @error('cover_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Laissez vide pour conserver l'image actuelle</div>
                            
                            <!-- Prévisualisation de la nouvelle image -->
                            <div id="imagePreview" class="mt-3" style="display: none;">
                                <label class="form-label">Aperçu de la nouvelle image</label>
                                <div>
                                    <img id="preview" src="" alt="Aperçu" class="img-thumbnail" style="max-width: 200px;">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fichier PDF actuel</label>
                            @if($ebook->pdf_url)
                                <div class="mb-2">
                                    <a href="{{ $ebook->pdf_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-file-pdf me-1"></i>
                                        Voir le PDF actuel
                                    </a>
                                </div>
                            @endif
                            
                            <label for="pdf_file" class="form-label">Nouveau fichier PDF (optionnel)</label>
                            <input type="file" 
                                   class="form-control @error('pdf_file') is-invalid @enderror" 
                                   id="pdf_file" 
                                   name="pdf_file" 
                                   accept=".pdf">
                            @error('pdf_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Laissez vide pour conserver le PDF actuel</div>
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
                                   value="{{ old('price', $ebook->price) }}" 
                                   min="0" 
                                   step="0.01">
                            <span class="input-group-text">FCFA</span>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-text">
                            @if($ebook->price > 0)
                                Ebook payant
                            @else
                                Ebook gratuit
                            @endif
                        </div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $ebook->is_active) ? 'checked' : '' }}>
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
                               value="{{ old('rating', $ebook->rating) }}" 
                               min="0" 
                               max="5" 
                               step="0.1">
                        @error('rating')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Statistiques -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Statistiques
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $ebook->downloads }}</h4>
                                <small class="text-muted">Téléchargements</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <h4 class="text-success">{{ number_format($ebook->price, 0, ',', ' ') }} FCFA</h4>
                            <small class="text-muted">Prix</small>
                        </div>
                        <div class="col-12">
                            <h4 class="text-warning">{{ $ebook->rating }}/5</h4>
                            <small class="text-muted">Note moyenne</small>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="small text-muted">
                        <div><strong>Créé:</strong> {{ $ebook->created_at->format('d/m/Y à H:i') }}</div>
                        <div><strong>Modifié:</strong> {{ $ebook->updated_at->format('d/m/Y à H:i') }}</div>
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
                    <a href="{{ route('admin.ebooks.show', $ebook) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Retour à l'ebook
                    </a>
                    
                    <a href="{{ route('admin.ebooks.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-1"></i>
                        Liste des ebooks
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
<form id="deleteForm" action="{{ route('admin.ebooks.destroy', $ebook) }}" method="POST" style="display: none;">
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
    const title = document.getElementById('title').value.trim();
    
    if (!title) {
        e.preventDefault();
        alert('Veuillez remplir le titre de l\'ebook.');
        return false;
    }
});
</script>
@endpush
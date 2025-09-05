@extends('admin.layouts.app')

@section('title', $ebook->title)
@section('page-title', $ebook->title)

@php
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['title' => 'Ebooks', 'url' => route('admin.ebooks.index')],
        ['title' => $ebook->title, 'url' => '']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-book me-2"></i>
                        {{ $ebook->title }}
                    </h5>
                    <div>
                        @if($ebook->is_active)
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-danger">Inactif</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        @if($ebook->cover_image_url)
                            <img src="{{ $ebook->cover_image_url }}" alt="{{ $ebook->title }}" class="img-fluid rounded">
                        @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 300px;">
                                <i class="fas fa-book fa-3x text-muted"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <div class="mb-3">
                            <h6 class="text-muted">Description</h6>
                            <p>{{ $ebook->description ?? 'Aucune description disponible.' }}</p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Auteur</h6>
                                <p>{{ $ebook->author ?? 'Non spécifié' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Catégorie</h6>
                                <p>
                                    <span class="badge bg-secondary">{{ $ebook->category ?? 'Non catégorisé' }}</span>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Nombre de pages</h6>
                                <p>{{ $ebook->pages ?? 'Non spécifié' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Note</h6>
                                <p>
                                    @if($ebook->rating > 0)
                                        <span class="text-warning">
                                            {{ $ebook->rating }}/5
                                            <i class="fas fa-star ms-1"></i>
                                        </span>
                                    @else
                                        <span class="text-muted">Non noté</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Fichier PDF -->
        @if($ebook->pdf_url)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-pdf me-2"></i>
                        Fichier PDF
                    </h5>
                </div>
                <div class="card-body">
                    <a href="{{ $ebook->pdf_url }}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-download me-1"></i>
                        Télécharger le PDF
                    </a>
                </div>
            </div>
        @endif
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-tag me-2"></i>
                    Informations commerciales
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border-end">
                            <h4 class="text-success">{{ number_format($ebook->price, 0, ',', ' ') }} FCFA</h4>
                            <small class="text-muted">
                                @if($ebook->price > 0)
                                    Prix
                                @else
                                    Gratuit
                                @endif
                            </small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-primary">{{ $ebook->downloads }}</h4>
                        <small class="text-muted">Téléchargements</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.ebooks.edit', $ebook) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i>
                        Modifier
                    </a>
                    <button type="button" class="btn btn-outline-danger" onclick="if(confirm('Êtes-vous sûr ?')) { document.getElementById('deleteForm').submit(); }">
                        <i class="fas fa-trash me-1"></i>
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Détails
                </h5>
            </div>
            <div class="card-body">
                <div class="small text-muted">
                    <div><strong>Créé:</strong> {{ $ebook->created_at->format('d/m/Y à H:i') }}</div>
                    <div><strong>Modifié:</strong> {{ $ebook->updated_at->format('d/m/Y à H:i') }}</div>
                    <div><strong>ID:</strong> {{ $ebook->id }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Formulaire de suppression caché -->
<form id="deleteForm" action="{{ route('admin.ebooks.destroy', $ebook) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection
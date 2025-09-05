@extends('admin.layouts.app')

@section('title', 'Ebooks')
@section('page-title', 'Gestion des Ebooks')

@php
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['title' => 'Ebooks', 'url' => '']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-book me-2"></i>
                        Liste des Ebooks
                    </h5>
                    <a href="{{ route('admin.ebooks.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Nouvel Ebook
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($ebooks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Couverture</th>
                                    <th>Titre</th>
                                    <th>Auteur</th>
                                    <th>Catégorie</th>
                                    <th>Prix</th>
                                    <th>Note</th>
                                    <th>Téléchargements</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ebooks as $ebook)
                                    <tr>
                                        <td>
                                            @if($ebook->cover_image_url)
                                                <img src="{{ $ebook->cover_image_url }}" alt="{{ $ebook->title }}" class="img-thumbnail" style="max-width: 60px; height: auto;">
                                            @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 80px;">
                                                    <i class="fas fa-book text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $ebook->title }}</strong>
                                            <div class="small text-muted">{{ Str::limit($ebook->description, 50) }}</div>
                                        </td>
                                        <td>{{ $ebook->author ?? 'Non spécifié' }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $ebook->category ?? 'Non catégorisé' }}</span>
                                        </td>
                                        <td>
                                            @if($ebook->price > 0)
                                                <span class="text-success">{{ number_format($ebook->price, 0, ',', ' ') }} FCFA</span>
                                            @else
                                                <span class="text-primary">Gratuit</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ebook->rating > 0)
                                                <span class="text-warning">
                                                    {{ $ebook->rating }}/5
                                                    <i class="fas fa-star ms-1"></i>
                                                </span>
                                            @else
                                                <span class="text-muted">Non noté</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $ebook->downloads }}</span>
                                        </td>
                                        <td>
                                            @if($ebook->is_active)
                                                <span class="badge bg-success">Actif</span>
                                            @else
                                                <span class="badge bg-danger">Inactif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.ebooks.show', $ebook) }}">
                                                            <i class="fas fa-eye me-2"></i>Voir
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.ebooks.edit', $ebook) }}">
                                                            <i class="fas fa-edit me-2"></i>Modifier
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           onclick="if(confirm('Êtes-vous sûr ?')) { document.getElementById('delete-{{ $ebook->id }}').submit(); }">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </a>
                                                        <form id="delete-{{ $ebook->id }}" 
                                                              action="{{ route('admin.ebooks.destroy', $ebook) }}" 
                                                              method="POST" style="display: none;">
                                                            @csrf
                                                            @method('DELETE')
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    @if($ebooks->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $ebooks->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-book fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucun ebook trouvé</h5>
                        <p class="text-muted">Commencez par créer votre premier ebook.</p>
                        <a href="{{ route('admin.ebooks.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Créer un Ebook
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
@extends('admin.layouts.app')

@section('title', 'Formations')
@section('page-title', 'Gestion des Formations')

@php
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['title' => 'Formations', 'url' => '']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Liste des Formations
                    </h5>
                    <a href="{{ route('admin.formations.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Nouvelle Formation
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Filtres -->
                <form method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="search" class="form-label">Recherche</label>
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Rechercher par titre ou pack">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="pack_id" class="form-label">Pack de formation</label>
                            <select class="form-select" id="pack_id" name="pack_id">
                                <option value="">Tous les packs</option>
                                @foreach($packs as $pack)
                                    <option value="{{ $pack->id }}" {{ request('pack_id') == $pack->id ? 'selected' : '' }}>
                                        {{ $pack->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>
                                Filtrer
                            </button>
                            <a href="{{ route('admin.formations.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                Réinitialiser
                            </a>
                        </div>
                    </div>
                </form>

                @if($formations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Couverture</th>
                                    <th>Titre</th>
                                    <th>Pack</th>
                                    <th>Durée</th>
                                    <th>Modules</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($formations as $formation)
                                    <tr>
                                        <td>
                                            @if($formation->thumbnail_url)
                                                <img src="{{ $formation->thumbnail_url }}" alt="{{ $formation->title }}" class="img-thumbnail" style="max-width: 60px; height: auto;">
                                            @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $formation->title }}</strong>
                                            <div class="small text-muted">{{ Str::limit($formation->description, 50) }}</div>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.formation-packs.show', $formation->pack) }}" class="text-decoration-none">
                                                {{ $formation->pack->name }}
                                            </a>
                                        </td>
                                        <td>{{ $formation->duration_minutes }} min</td>
                                        <td>{{ $formation->modules_count }}</td>
                                        <td>
                                            @if($formation->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.formations.show', $formation) }}">
                                                            <i class="fas fa-eye me-2"></i>Voir
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.formations.edit', $formation) }}">
                                                            <i class="fas fa-edit me-2"></i>Modifier
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           onclick="if(confirm('Êtes-vous sûr ?')) { document.getElementById('delete-{{ $formation->id }}').submit(); }">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </a>
                                                        <form id="delete-{{ $formation->id }}" 
                                                              action="{{ route('admin.formations.destroy', $formation) }}" 
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
                    @if($formations->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $formations->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucune formation trouvée</h5>
                        <p class="text-muted">Commencez par créer votre première formation.</p>
                        <a href="{{ route('admin.formations.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Créer une Formation
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
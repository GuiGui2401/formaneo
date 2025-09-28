@extends('admin.layouts.app')

@section('title', 'Gestion des packs de formation')
@section('page-title', 'Packs de Formation')

@php
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['title' => 'Packs de Formation', 'url' => '']
    ];
@endphp

@section('content')
<!-- Filters and Actions -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.formation-packs.index') }}" class="row g-3">
            <div class="col-lg-3 col-md-6">
                <label for="search" class="form-label">Rechercher</label>
                <input type="text" 
                       class="form-control" 
                       id="search" 
                       name="search" 
                       placeholder="Nom, auteur, slug..."
                       value="{{ request('search') }}">
            </div>
            
            <div class="col-lg-2 col-md-3">
                <label for="status" class="form-label">Statut</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Tous</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
                </select>
            </div>
            
            <div class="col-lg-2 col-md-3">
                <label for="featured" class="form-label">Vedette</label>
                <select class="form-select" id="featured" name="featured">
                    <option value="">Tous</option>
                    <option value="yes" {{ request('featured') === 'yes' ? 'selected' : '' }}>Oui</option>
                    <option value="no" {{ request('featured') === 'no' ? 'selected' : '' }}>Non</option>
                </select>
            </div>
            
            <div class="col-lg-2 col-md-3">
                <label for="promotion" class="form-label">Promo</label>
                <select class="form-select" id="promotion" name="promotion">
                    <option value="">Tous</option>
                    <option value="yes" {{ request('promotion') === 'yes' ? 'selected' : '' }}>Oui</option>
                    <option value="no" {{ request('promotion') === 'no' ? 'selected' : '' }}>Non</option>
                </select>
            </div>
            
            <div class="col-lg-3 col-md-12">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>
                        Filtrer
                    </button>
                    <a href="{{ route('admin.formation-packs.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>
                        Reset
                    </a>
                </div>
            </div>
            
            <div class="col-12 mt-3">
                <a href="{{ route('admin.formation-packs.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>
                    Nouveau Pack
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Packs Grid -->
<div class="row">
    @forelse($packs as $pack)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                @if($pack->thumbnail_url)
                    <img src="{{ $pack->thumbnail_url }}" class="card-img-top" alt="{{ $pack->name }}" style="height: 200px; object-fit: cover;">
                @else
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="fas fa-image fa-3x text-muted"></i>
                    </div>
                @endif
                
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title mb-0">{{ $pack->name }}</h5>
                        <div class="d-flex gap-1">
                            @if($pack->is_featured)
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-star me-1"></i>
                                    Vedette
                                </span>
                            @endif
                            
                            @if($pack->isPromotionActive())
                                <span class="badge bg-danger">
                                    <i class="fas fa-tag me-1"></i>
                                    PROMO
                                </span>
                            @endif
                            
                            <span class="status-badge {{ $pack->is_active ? 'status-active' : 'status-inactive' }}">
                                {{ $pack->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                    </div>
                    
                    <p class="text-muted small mb-2">
                        <i class="fas fa-user me-1"></i>
                        {{ $pack->author }}
                    </p>
                    
                    <p class="card-text flex-grow-1">
                        {{ Str::limit($pack->description, 100) }}
                    </p>
                    
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            @if($pack->isPromotionActive())
                                <div class="fw-bold text-danger">{{ number_format($pack->promotion_price) }}</div>
                                <small class="text-muted text-decoration-line-through">{{ number_format($pack->price) }} FCFA</small>
                            @else
                                <div class="fw-bold text-primary">{{ number_format($pack->price) }}</div>
                                <small class="text-muted">FCFA</small>
                            @endif
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-info">{{ $pack->formations_count }}</div>
                            <small class="text-muted">Formations</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-success">{{ $pack->students_count }}</div>
                            <small class="text-muted">Étudiants</small>
                        </div>
                    </div>
                    
                    @if($pack->rating > 0)
                        <div class="text-center mb-3">
                            <div class="text-warning">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star{{ $i <= $pack->rating ? '' : '-o' }}"></i>
                                @endfor
                            </div>
                            <small class="text-muted">{{ $pack->rating }}/5</small>
                        </div>
                    @endif
                    
                    <div class="d-flex gap-1 mt-auto">
                        <a href="{{ route('admin.formation-packs.show', $pack) }}" 
                           class="btn btn-outline-info btn-sm flex-fill">
                            <i class="fas fa-eye me-1"></i>
                            Voir
                        </a>
                        
                        <a href="{{ route('admin.formation-packs.edit', $pack) }}" 
                           class="btn btn-outline-primary btn-sm flex-fill">
                            <i class="fas fa-edit me-1"></i>
                            Modifier
                        </a>
                        
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                    type="button" 
                                    data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <button class="dropdown-item" 
                                            onclick="togglePackStatus({{ $pack->id }}, '{{ $pack->is_active ? 'désactiver' : 'activer' }}')">
                                        <i class="fas fa-{{ $pack->is_active ? 'ban' : 'check' }} me-2"></i>
                                        {{ $pack->is_active ? 'Désactiver' : 'Activer' }}
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item" 
                                            onclick="togglePackFeatured({{ $pack->id }}, '{{ $pack->is_featured ? 'retirer de la mise en avant' : 'mettre en avant' }}')">
                                        <i class="fas fa-{{ $pack->is_featured ? 'star-o' : 'star' }} me-2"></i>
                                        {{ $pack->is_featured ? 'Retirer vedette' : 'Mettre en vedette' }}
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item text-danger" 
                                            onclick="deletePack('{{ $pack->id }}', '{{ $pack->name }}')">
                                        <i class="fas fa-trash me-2"></i>
                                        Supprimer
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-light">
                    <small class="text-muted">
                        <i class="fas fa-calendar me-1"></i>
                        Créé le {{ $pack->created_at->format('d/m/Y') }}
                    </small>
                    @if($pack->total_duration > 0)
                        <span class="float-end">
                            <i class="fas fa-clock me-1"></i>
                            {{ floor($pack->total_duration / 60) }}h{{ $pack->total_duration % 60 }}min
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-box fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Aucun pack de formation trouvé</h4>
                    <p class="text-muted mb-4">Commencez par créer votre premier pack de formation</p>
                    <a href="{{ route('admin.formation-packs.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Créer le premier pack
                    </a>
                </div>
            </div>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($packs->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $packs->withQueryString()->links() }}
    </div>
@endif

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer le pack <strong id="packName"></strong> ?</p>
                <p class="text-danger small">Cette action est irréversible et supprimera toutes les formations et modules associés.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePackStatus(packId, action) {
    if (confirm(`Êtes-vous sûr de vouloir ${action} ce pack ?`)) {
        fetch(`/admin/formation-packs/${packId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la modification du statut');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la modification du statut');
        });
    }
}

function togglePackFeatured(packId, action) {
    if (confirm(`Êtes-vous sûr de vouloir ${action} ce pack ?`)) {
        fetch(`/admin/formation-packs/${packId}/toggle-featured`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la modification');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la modification');
        });
    }
}

function deletePack(packId, packName) {
    document.getElementById('packName').textContent = packName;
    document.getElementById('deleteForm').action = `/admin/formation-packs/${packId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
@extends('admin.layouts.app')

@section('title', 'Gestion des utilisateurs')
@section('page-title', 'Utilisateurs')

@php
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['title' => 'Utilisateurs', 'url' => '']
    ];
@endphp

@section('content')
<!-- Filters and Actions -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Rechercher</label>
                <input type="text" 
                       class="form-control" 
                       id="search" 
                       name="search" 
                       placeholder="Nom, email, code promo..."
                       value="{{ request('search') }}">
            </div>
            
            <div class="col-md-2">
                <label for="status" class="form-label">Statut</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Tous</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="premium" class="form-label">Premium</label>
                <select class="form-select" id="premium" name="premium">
                    <option value="">Tous</option>
                    <option value="yes" {{ request('premium') === 'yes' ? 'selected' : '' }}>Oui</option>
                    <option value="no" {{ request('premium') === 'no' ? 'selected' : '' }}>Non</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>
                        Filtrer
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>
                        Reset
                    </a>
                </div>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <a href="{{ route('admin.users.create') }}" class="btn btn-success w-100">
                    <i class="fas fa-plus me-1"></i>
                    Nouveau
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-users me-2"></i>
            Liste des utilisateurs ({{ $users->total() }})
        </h5>
        
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm" onclick="exportUsers()">
                <i class="fas fa-download me-1"></i>
                Exporter
            </button>
        </div>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Utilisateur</th>
                        <th>Contact</th>
                        <th>Statut</th>
                        <th>Solde</th>
                        <th>Affiliés</th>
                        <th>Transactions</th>
                        <th>Inscription</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $user->name }}</div>
                                        <small class="text-muted">{{ $user->promo_code }}</small>
                                        @if($user->is_premium)
                                            <span class="badge bg-warning text-dark ms-1">Premium</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            
                            <td>
                                <div>{{ $user->email }}</div>
                                @if($user->phone)
                                    <small class="text-muted">{{ $user->phone }}</small>
                                @endif
                            </td>
                            
                            <td>
                                <span class="status-badge {{ $user->is_active ? 'status-active' : 'status-inactive' }}">
                                    {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                                @if($user->last_login_at)
                                    <br><small class="text-muted">
                                        Dernière connexion: {{ $user->last_login_at->diffForHumans() }}
                                    </small>
                                @endif
                            </td>
                            
                            <td>
                                <span class="fw-bold text-success">
                                    {{ number_format($user->balance, 0, ',', ' ') }} FCFA
                                </span>
                            </td>
                            
                            <td>
                                <div class="text-center">
                                    <span class="badge bg-info">{{ $user->total_affiliates ?? 0 }}</span>
                                    <br><small class="text-muted">Total</small>
                                </div>
                            </td>
                            
                            <td>
                                <div class="text-center">
                                    <span class="badge bg-secondary">{{ $user->transactions_count ?? 0 }}</span>
                                    <br><small class="text-muted">Total</small>
                                </div>
                            </td>
                            
                            <td>
                                <div>{{ $user->created_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                            </td>
                            
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.users.show', $user) }}" 
                                       class="btn btn-outline-info btn-action"
                                       title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                       class="btn btn-outline-primary btn-action"
                                       title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <button type="button" 
                                            class="btn btn-outline-{{ $user->is_active ? 'warning' : 'success' }} btn-action"
                                            onclick="toggleUserStatus({{ $user->id }}, '{{ $user->is_active ? 'désactiver' : 'activer' }}')"
                                            title="{{ $user->is_active ? 'Désactiver' : 'Activer' }}">
                                        <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                    </button>
                                    
                                    <button type="button" 
                                            class="btn btn-outline-danger btn-action"
                                            onclick="deleteUser({ $user->id }}, '{{ $user->name }}')"
                                            title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Aucun utilisateur trouvé</p>
                                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i>
                                        Créer le premier utilisateur
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($users->hasPages())
        <div class="card-footer">
            {{ $users->withQueryString()->links() }}
        </div>
    @endif
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer l'utilisateur <strong id="userName"></strong> ?</p>
                <p class="text-danger small">Cette action est irréversible et supprimera toutes les données associées.</p>
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
function toggleUserStatus(userId, action) {
    if (confirm(`Êtes-vous sûr de vouloir ${action} cet utilisateur ?`)) {
        fetch(`/admin/users/${userId}/toggle-status`, {
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

function deleteUser(userId, userName) {
    document.getElementById('userName').textContent = userName;
    document.getElementById('deleteForm').action = `/admin/users/${userId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function exportUsers() {
    // Construire l'URL avec les filtres actuels
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    
    // Créer et déclencher le téléchargement
    window.location.href = '{{ route("admin.users.index") }}?' + params.toString();
}
</script>
@endpush
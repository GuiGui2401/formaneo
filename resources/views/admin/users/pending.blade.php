@extends('admin.layouts.app')

@section('title', 'Comptes en attente d\'activation')
@section('page-title', 'Comptes à Activer')

@php
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['title' => 'Utilisateurs', 'url' => route('admin.users.index')],
        ['title' => 'Comptes à Activer', 'url' => '']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h4 class="fw-bold text-primary">
            <i class="fas fa-user-clock me-2"></i>
            Comptes en attente d'activation
        </h4>
        <p class="text-muted mb-0">Gérez les comptes web qui nécessitent une activation payante</p>
    </div>
    <div class="col-md-4 text-md-end">
        <span class="badge bg-warning fs-6 px-3 py-2">
            {{ $pendingUsers->total() }} compte(s) en attente
        </span>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card-1">
            <div class="card-body text-center">
                <i class="fas fa-user-slash fa-2x mb-2"></i>
                <h4 class="fw-bold">{{ $totalPending }}</h4>
                <p class="mb-0">Comptes inactifs</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card-2">
            <div class="card-body text-center">
                <i class="fas fa-clock fa-2x mb-2"></i>
                <h4 class="fw-bold">{{ \App\Models\User::where('account_status', 'expired')->count() }}</h4>
                <p class="mb-0">Comptes expirés</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card-3">
            <div class="card-body text-center">
                <i class="fas fa-check-circle fa-2x mb-2"></i>
                <h4 class="fw-bold">{{ \App\Models\User::where('account_status', 'active')->count() }}</h4>
                <p class="mb-0">Comptes actifs</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card-4">
            <div class="card-body text-center">
                <i class="fas fa-coins fa-2x mb-2"></i>
                <h4 class="fw-bold">{{ number_format(\App\Models\Settings::getValue('account_activation_cost', 5000)) }}</h4>
                <p class="mb-0">FCFA / mois</p>
            </div>
        </div>
    </div>
</div>

<!-- Actions en lot -->
@if($pendingUsers->count() > 0)
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1">Actions en lot</h6>
                <small class="text-muted">Sélectionnez des utilisateurs pour effectuer des actions groupées</small>
            </div>
            <div>
                <button type="button" class="btn btn-success btn-sm" onclick="activateSelected()">
                    <i class="fas fa-check me-1"></i>
                    Activer sélectionnés
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm ms-2" onclick="selectAll()">
                    <i class="fas fa-check-square me-1"></i>
                    Tout sélectionner
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Liste des comptes -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-list me-2"></i>
            Liste des comptes en attente
        </h5>
        <div class="d-flex gap-2">
            <div class="input-group" style="width: 250px;">
                <input type="text" class="form-control" placeholder="Rechercher un utilisateur..." id="searchInput">
                <button class="btn btn-outline-secondary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        @if($pendingUsers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleSelectAll()">
                            </th>
                            <th>Utilisateur</th>
                            <th>Statut</th>
                            <th>Date d'inscription</th>
                            <th>Dernière connexion</th>
                            <th>Solde</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingUsers as $user)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input user-checkbox" value="{{ $user->id }}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <span class="text-white fw-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $user->name }}</h6>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->account_status === 'inactive')
                                    <span class="status-badge status-inactive">
                                        <i class="fas fa-times-circle me-1"></i>
                                        Inactif
                                    </span>
                                @elseif($user->account_status === 'expired')
                                    <span class="status-badge status-pending">
                                        <i class="fas fa-clock me-1"></i>
                                        Expiré
                                    </span>
                                @else
                                    <span class="status-badge status-active">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Actif
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span>{{ $user->created_at->format('d/m/Y') }}</span>
                                <br>
                                <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                @if($user->last_login_at)
                                    <span>{{ $user->last_login_at->format('d/m/Y H:i') }}</span>
                                    <br>
                                    <small class="text-muted">{{ $user->last_login_at->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">Jamais connecté</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold">{{ number_format($user->balance, 0, ',', ' ') }} FCFA</span>
                                @if(!$user->welcome_bonus_claimed)
                                    <br>
                                    <small class="text-warning">
                                        <i class="fas fa-gift me-1"></i>
                                        Bonus en attente
                                    </small>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @if($user->account_status !== 'active')
                                        <button type="button" 
                                                class="btn btn-success btn-action" 
                                                onclick="activateUser({{ $user->id }})"
                                                title="Activer le compte">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                    
                                    @if($user->account_status === 'active')
                                        <button type="button" 
                                                class="btn btn-warning btn-action" 
                                                onclick="deactivateUser({{ $user->id }})"
                                                title="Désactiver le compte">
                                            <i class="fas fa-pause"></i>
                                        </button>
                                    @endif
                                    
                                    <a href="{{ route('admin.users.show', $user) }}" 
                                       class="btn btn-primary btn-action"
                                       title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center p-3">
                <div class="text-muted">
                    Affichage de {{ $pendingUsers->firstItem() }} à {{ $pendingUsers->lastItem() }} 
                    sur {{ $pendingUsers->total() }} résultats
                </div>
                {{ $pendingUsers->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5>Aucun compte en attente</h5>
                <p class="text-muted">Tous les comptes web sont activés ou aucun nouveau compte n'a été créé.</p>
                <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
                    <i class="fas fa-users me-2"></i>
                    Voir tous les utilisateurs
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// Gestion des cases à cocher
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function selectAll() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    selectAllCheckbox.checked = true;
}

// Activation d'un utilisateur
function activateUser(userId) {
    if (confirm('Êtes-vous sûr de vouloir activer ce compte ? L\'utilisateur recevra son bonus de bienvenue.')) {
        fetch(`/admin/users/${userId}/activate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur s\'est produite');
        });
    }
}

// Désactivation d'un utilisateur
function deactivateUser(userId) {
    if (confirm('Êtes-vous sûr de vouloir désactiver ce compte ?')) {
        fetch(`/admin/users/${userId}/deactivate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur s\'est produite');
        });
    }
}

// Activation en lot
function activateSelected() {
    const selectedUsers = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
    
    if (selectedUsers.length === 0) {
        alert('Veuillez sélectionner au moins un utilisateur');
        return;
    }
    
    if (confirm(`Êtes-vous sûr de vouloir activer ${selectedUsers.length} compte(s) sélectionné(s) ?`)) {
        Promise.all(selectedUsers.map(userId => 
            fetch(`/admin/users/${userId}/activate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            })
        ))
        .then(() => {
            location.reload();
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur s\'est produite lors de l\'activation en lot');
        });
    }
}

// Recherche
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const userName = row.querySelector('h6').textContent.toLowerCase();
        const userEmail = row.querySelector('small').textContent.toLowerCase();
        
        if (userName.includes(searchTerm) || userEmail.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
@endpush
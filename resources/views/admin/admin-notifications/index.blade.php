@extends('admin.layouts.app')

@section('title', 'Notifications Admin')
@section('page-title', 'Notifications Admin')

@php
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['title' => 'Notifications Admin', 'url' => '']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h4 class="fw-bold text-primary">
            <i class="fas fa-bell me-2"></i>
            Notifications Admin
        </h4>
        <p class="text-muted mb-0">Gérez les notifications système et les alertes importantes</p>
    </div>
    <div class="col-md-4 text-md-end">
        <button type="button" class="btn btn-outline-secondary" onclick="markAllAsRead()">
            <i class="fas fa-check-double me-1"></i>
            Marquer tout comme lu
        </button>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card-1">
            <div class="card-body text-center">
                <i class="fas fa-bell fa-2x mb-2"></i>
                <h4 class="fw-bold">{{ $notifications->total() }}</h4>
                <p class="mb-0">Total notifications</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card-2">
            <div class="card-body text-center">
                <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                <h4 class="fw-bold">{{ $unreadCount }}</h4>
                <p class="mb-0">Non lues</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card-3">
            <div class="card-body text-center">
                <i class="fas fa-user-plus fa-2x mb-2"></i>
                <h4 class="fw-bold">{{ $notifications->where('type', 'new_user_registration')->count() }}</h4>
                <p class="mb-0">Nouveaux comptes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card-4">
            <div class="card-body text-center">
                <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                <h4 class="fw-bold">{{ $notifications->where('type', 'payment_received')->count() }}</h4>
                <p class="mb-0">Paiements reçus</p>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.admin-notifications.index') }}">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label for="type" class="form-label">Type de notification</label>
                    <select name="type" id="type" class="form-select">
                        <option value="">Tous les types</option>
                        <option value="new_user_registration" {{ request('type') === 'new_user_registration' ? 'selected' : '' }}>
                            Nouvel utilisateur
                        </option>
                        <option value="account_activation_needed" {{ request('type') === 'account_activation_needed' ? 'selected' : '' }}>
                            Activation requise
                        </option>
                        <option value="payment_received" {{ request('type') === 'payment_received' ? 'selected' : '' }}>
                            Paiement reçu
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="unread_only" class="form-label">Statut</label>
                    <select name="unread_only" id="unread_only" class="form-select">
                        <option value="">Toutes</option>
                        <option value="1" {{ request('unread_only') === '1' ? 'selected' : '' }}>Non lues seulement</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="per_page" class="form-label">Par page</label>
                    <select name="per_page" id="per_page" class="form-select">
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i>
                            Filtrer
                        </button>
                        <a href="{{ route('admin.admin-notifications.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>
                            Réinitialiser
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Liste des notifications -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-list me-2"></i>
            Liste des notifications
        </h5>
    </div>
    <div class="card-body p-0">
        @if($notifications->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($notifications as $notification)
                <div class="list-group-item {{ !$notification->is_read ? 'bg-light border-start border-primary border-4' : '' }}">
                    <div class="d-flex w-100 justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                @if($notification->type === 'new_user_registration')
                                    <i class="fas fa-user-plus text-success me-2"></i>
                                @elseif($notification->type === 'account_activation_needed')
                                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                @elseif($notification->type === 'payment_received')
                                    <i class="fas fa-money-bill-wave text-primary me-2"></i>
                                @else
                                    <i class="fas fa-bell text-secondary me-2"></i>
                                @endif
                                
                                <h6 class="mb-0 me-2">{{ $notification->title }}</h6>
                                
                                @if(!$notification->is_read)
                                    <span class="badge bg-primary">Nouveau</span>
                                @endif
                            </div>
                            
                            <p class="mb-2 text-muted">{{ $notification->message }}</p>
                            
                            @if($notification->data)
                                <div class="small text-muted">
                                    @if(isset($notification->data['user_email']))
                                        <span class="me-3">
                                            <i class="fas fa-envelope me-1"></i>
                                            {{ $notification->data['user_email'] }}
                                        </span>
                                    @endif
                                    @if(isset($notification->data['amount']))
                                        <span class="me-3">
                                            <i class="fas fa-coins me-1"></i>
                                            {{ number_format($notification->data['amount']) }} FCFA
                                        </span>
                                    @endif
                                    @if(isset($notification->data['platform']))
                                        <span class="me-3">
                                            <i class="fas fa-desktop me-1"></i>
                                            {{ ucfirst($notification->data['platform']) }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        <div class="text-end ms-3">
                            <small class="text-muted d-block">{{ $notification->created_at->format('d/m/Y H:i') }}</small>
                            <small class="text-muted d-block">{{ $notification->created_at->diffForHumans() }}</small>
                            
                            <div class="btn-group btn-group-sm mt-2">
                                @if(!$notification->is_read)
                                    <button type="button" 
                                            class="btn btn-outline-primary btn-sm"
                                            onclick="markAsRead({{ $notification->id }})"
                                            title="Marquer comme lu">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                                
                                @if(isset($notification->data['user_id']))
                                    <a href="{{ route('admin.users.show', $notification->data['user_id']) }}" 
                                       class="btn btn-outline-secondary btn-sm"
                                       title="Voir l'utilisateur">
                                        <i class="fas fa-user"></i>
                                    </a>
                                @endif
                                
                                @if($notification->type === 'new_user_registration' && isset($notification->data['user_id']))
                                    @php
                                        $user = \App\Models\User::find($notification->data['user_id']);
                                    @endphp
                                    @if($user && $user->account_status === 'inactive')
                                        <button type="button" 
                                                class="btn btn-success btn-sm"
                                                onclick="activateUserFromNotification({{ $user->id }})"
                                                title="Activer le compte">
                                            <i class="fas fa-key"></i>
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center p-3">
                <div class="text-muted">
                    Affichage de {{ $notifications->firstItem() }} à {{ $notifications->lastItem() }} 
                    sur {{ $notifications->total() }} résultats
                </div>
                {{ $notifications->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                <h5>Aucune notification</h5>
                <p class="text-muted">Aucune notification ne correspond à vos critères de recherche.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// Marquer une notification comme lue
function markAsRead(notificationId) {
    fetch(`/admin/admin-notifications/${notificationId}/mark-read`, {
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

// Marquer toutes les notifications comme lues
function markAllAsRead() {
    if (confirm('Êtes-vous sûr de vouloir marquer toutes les notifications comme lues ?')) {
        fetch('/admin/admin-notifications/mark-all-read', {
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

// Activer un utilisateur depuis une notification
function activateUserFromNotification(userId) {
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

// Auto-refresh des notifications toutes les 30 secondes
setInterval(() => {
    // Optionnel: recharger uniquement le nombre de notifications non lues
    fetch('/admin/admin-notifications/unread-count')
        .then(response => response.json())
        .then(data => {
            // Mettre à jour le badge dans la sidebar si nécessaire
            const badge = document.querySelector('.sidebar .badge');
            if (badge && data.unread_count > 0) {
                badge.textContent = data.unread_count;
                badge.style.display = 'inline';
            } else if (badge) {
                badge.style.display = 'none';
            }
        })
        .catch(error => console.error('Erreur lors de la mise à jour:', error));
}, 30000);
</script>
@endpush
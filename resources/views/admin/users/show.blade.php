@extends('admin.layouts.app')

@section('title', 'Détails utilisateur')
@section('page-title', $user->name)

@php
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['title' => 'Utilisateurs', 'url' => route('admin.users.index')],
        ['title' => $user->name, 'url' => '']
    ];
@endphp

@section('content')
<div class="row">
    <!-- Informations principales -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                    <span style="font-size: 2rem;">{{ substr($user->name, 0, 1) }}</span>
                </div>
                
                <h4>{{ $user->name }}</h4>
                <p class="text-muted">{{ $user->email }}</p>
                
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <span class="status-badge {{ $user->is_active ? 'status-active' : 'status-inactive' }}">
                        {{ $user->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                    
                    @if($user->is_premium)
                        <span class="badge bg-warning text-dark">Premium</span>
                    @endif
                </div>
                
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm flex-fill">
                        <i class="fas fa-edit me-1"></i>
                        Modifier
                    </a>
                    
                    <button class="btn btn-{{ $user->is_active ? 'warning' : 'success' }} btn-sm flex-fill"
                            onclick="toggleUserStatus({{ $user->id }}, '{{ $user->is_active ? 'désactiver' : 'activer' }}')">
                        <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }} me-1"></i>
                        {{ $user->is_active ? 'Désactiver' : 'Activer' }}
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Statistiques rapides -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Statistiques</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border-end">
                            <h4 class="text-success">{{ number_format($user->balance) }}</h4>
                            <small class="text-muted">Solde (FCFA)</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-info">{{ $stats['total_referrals'] }}</h4>
                        <small class="text-muted">Affiliés</small>
                    </div>
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary">{{ number_format($stats['total_earnings']) }}</h4>
                            <small class="text-muted">Gains (FCFA)</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-warning">{{ number_format($stats['total_spent']) }}</h4>
                        <small class="text-muted">Dépensé (FCFA)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Détails et actions -->
    <div class="col-lg-8">
        <!-- Informations détaillées -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Informations détaillées</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <td><strong>Téléphone:</strong></td>
                                <td>{{ $user->phone ?? 'Non renseigné' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Code promo:</strong></td>
                                <td>
                                    <code>{{ $user->promo_code }}</code>
                                    <button class="btn btn-outline-secondary btn-sm ms-1" onclick="copyToClipboard('{{ $user->promo_code }}')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Lien d'affiliation:</strong></td>
                                <td>
                                    <small>{{ $user->affiliate_link }}</small>
                                    <button class="btn btn-outline-secondary btn-sm ms-1" onclick="copyToClipboard('{{ $user->affiliate_link }}')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Inscription:</strong></td>
                                <td>{{ $user->created_at->format('d/m/Y à H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Dernière connexion:</strong></td>
                                <td>{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Quiz gratuits restants:</strong></td>
                                <td>{{ $user->free_quizzes_left ?? 0 }}</td>
                            </tr>
                            <tr>
                                <td><strong>Parrainé par:</strong></td>
                                <td>
                                    @if($user->referrer)
                                        <a href="{{ route('admin.users.show', $user->referrer) }}">
                                            {{ $user->referrer->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">Aucun</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Ajouter du solde -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Gestion du solde</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.add-balance', $user) }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label for="amount" class="form-label">Montant à ajouter (FCFA)</label>
                        <input type="number" 
                               class="form-control" 
                               id="amount" 
                               name="amount" 
                               min="0" 
                               step="0.01" 
                               required>
                    </div>
                    <div class="col-md-6">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" 
                               class="form-control" 
                               id="description" 
                               name="description" 
                               placeholder="Raison de l'ajout de solde" 
                               required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus-circle me-1"></i>
                            Ajouter au solde
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Transactions récentes -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Transactions récentes</h5>
                <a href="{{ route('admin.transactions.index', ['search' => $user->email]) }}" class="btn btn-outline-primary btn-sm">
                    Voir toutes
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Montant</th>
                                <th>Description</th>
                                <th>Date</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->transactions->take(10) as $transaction)
                                <tr>
                                    <td>
                                        <span class="status-badge 
                                            @if($transaction->type === 'purchase') status-pending
                                            @elseif($transaction->type === 'commission') status-active
                                            @elseif($transaction->type === 'withdrawal') status-inactive
                                            @else status-completed
                                            @endif">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td class="{{ $transaction->amount > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount) }} FCFA
                                    </td>
                                    <td>{{ Str::limit($transaction->description, 50) }}</td>
                                    <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="status-badge {{ $transaction->status === 'completed' ? 'status-active' : 'status-pending' }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted">
                                        Aucune transaction
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Créer une notification temporaire
        const alert = document.createElement('div');
        alert.className = 'alert alert-success position-fixed';
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
        alert.innerHTML = '<i class="fas fa-check me-1"></i> Copié dans le presse-papier !';
        
        document.body.appendChild(alert);
        
        setTimeout(() => {
            document.body.removeChild(alert);
        }, 2000);
    });
}

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
</script>
@endpush
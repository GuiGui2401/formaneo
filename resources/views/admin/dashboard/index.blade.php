@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stat-card stat-card-1">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50 mb-1">Utilisateurs</h6>
                        <h2 class="mb-0">{{ number_format($stats['users']['total']) }}</h2>
                        <small class="text-white-50">
                            <i class="fas fa-arrow-up me-1"></i>
                            +{{ $stats['users']['new_today'] }} aujourd'hui
                        </small>
                    </div>
                    <div class="text-end">
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stat-card stat-card-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50 mb-1">Revenus</h6>
                        <h2 class="mb-0">{{ number_format($stats['revenue']['total']) }} FCFA</h2>
                        <small class="text-white-50">
                            <i class="fas fa-arrow-up me-1"></i>
                            +{{ number_format($stats['revenue']['today']) }} FCFA aujourd'hui
                        </small>
                    </div>
                    <div class="text-end">
                        <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stat-card stat-card-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50 mb-1">Formations</h6>
                        <h2 class="mb-0">{{ $stats['packs']['active'] }}/{{ $stats['packs']['total'] }}</h2>
                        <small class="text-white-50">
                            Packs actifs
                        </small>
                    </div>
                    <div class="text-end">
                        <i class="fas fa-graduation-cap fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stat-card stat-card-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-dark mb-1">Retraits en attente</h6>
                        <h2 class="mb-0 text-dark">{{ $stats['transactions']['pending_withdrawals'] }}</h2>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            À traiter
                        </small>
                    </div>
                    <div class="text-end">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Inscriptions des 30 derniers jours
                </h5>
            </div>
            <div class="card-body">
                <canvas id="userRegistrationsChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Types de transactions
                </h5>
            </div>
            <div class="card-body">
                <canvas id="transactionTypesChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    Revenus des 30 derniers jours
                </h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>
                    Transactions récentes
                </h5>
                <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-outline-primary">
                    Voir tout
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Type</th>
                                <th>Montant</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $transaction)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="fas fa-user text-muted"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $transaction->user->name }}</div>
                                                <small class="text-muted">{{ $transaction->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
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
                                    <td>
                                        <small class="text-muted">
                                            {{ $transaction->created_at->diffForHumans() }}
                                        </small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">
                                        Aucune transaction récente
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-plus me-2"></i>
                    Nouveaux utilisateurs
                </h5>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">
                    Voir tout
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Email</th>
                                <th>Statut</th>
                                <th>Inscription</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentUsers as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="fas fa-user text-muted"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $user->name }}</div>
                                                <small class="text-muted">{{ $user->promo_code }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="status-badge {{ $user->is_active ? 'status-active' : 'status-inactive' }}">
                                            {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $user->created_at->diffForHumans() }}
                                        </small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">
                                        Aucun nouvel utilisateur
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

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Actions rapides
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="{{ route('admin.users.create') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-user-plus fa-2x mb-2"></i>
                            <span>Nouvel utilisateur</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="{{ route('admin.formation-packs.create') }}" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-plus-circle fa-2x mb-2"></i>
                            <span>Nouveau pack</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="{{ route('admin.quizzes.create') }}" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-question-circle fa-2x mb-2"></i>
                            <span>Nouveau quiz</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="{{ route('admin.transactions.withdrawals.pending') }}" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <span>Retraits en attente</span>
                            @if($stats['transactions']['pending_withdrawals'] > 0)
                                <span class="badge bg-warning text-dark">{{ $stats['transactions']['pending_withdrawals'] }}</span>
                            @endif
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Chart configurations
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'top',
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(0,0,0,0.1)'
            }
        },
        x: {
            grid: {
                color: 'rgba(0,0,0,0.1)'
            }
        }
    }
};

// User Registrations Chart
const userCtx = document.getElementById('userRegistrationsChart').getContext('2d');
new Chart(userCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode(collect($charts['user_registrations'])->pluck('date')) !!},
        datasets: [{
            label: 'Inscriptions',
            data: {!! json_encode(collect($charts['user_registrations'])->pluck('count')) !!},
            borderColor: 'rgb(102, 126, 234)',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: chartOptions
});

// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode(collect($charts['daily_revenue'])->pluck('date')) !!},
        datasets: [{
            label: 'Revenus (FCFA)',
            data: {!! json_encode(collect($charts['daily_revenue'])->pluck('revenue')) !!},
            backgroundColor: 'rgba(67, 233, 123, 0.8)',
            borderColor: 'rgb(67, 233, 123)',
            borderWidth: 1
        }]
    },
    options: {
        ...chartOptions,
        scales: {
            ...chartOptions.scales,
            y: {
                ...chartOptions.scales.y,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                    }
                }
            }
        }
    }
});

// Transaction Types Chart
const transactionCtx = document.getElementById('transactionTypesChart').getContext('2d');
new Chart(transactionCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($charts['transaction_types']->keys()) !!},
        datasets: [{
            data: {!! json_encode($charts['transaction_types']->values()) !!},
            backgroundColor: [
                'rgba(102, 126, 234, 0.8)',
                'rgba(67, 233, 123, 0.8)',
                'rgba(250, 112, 154, 0.8)',
                'rgba(168, 237, 234, 0.8)',
                'rgba(255, 159, 64, 0.8)'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});

// Auto-refresh stats every 30 seconds
setInterval(function() {
    // Only refresh if user is still on the page
    if (document.visibilityState === 'visible') {
        location.reload();
    }
}, 30000);
</script>
@endpush
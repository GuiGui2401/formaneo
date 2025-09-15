@extends('admin.layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('content')
<div class="row">
    <!-- Statistiques -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="text-white">Total des paiements</h5>
                        <h2 class="text-white mb-0">{{ number_format($totalPayments, 0, ',', ' ') }} FCFA</h2>
                    </div>
                    <i class="fas fa-money-bill-wave fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="text-white">Total des retraits</h5>
                        <h2 class="text-white mb-0">{{ number_format($totalWithdrawals, 0, ',', ' ') }} FCFA</h2>
                    </div>
                    <i class="fas fa-arrow-circle-down fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="text-white">Paiements en attente</h5>
                        <h2 class="text-white mb-0">{{ number_format($pendingPayments, 0, ',', ' ') }} FCFA</h2>
                    </div>
                    <i class="fas fa-clock fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="text-white">Retraits en attente</h5>
                        <h2 class="text-white mb-0">{{ $pendingWithdrawals }}</h2>
                    </div>
                    <i class="fas fa-hourglass-half fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dernières transactions -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Dernières transactions</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Utilisateur</th>
                                <th>Type</th>
                                <th>Montant</th>
                                <th>Description</th>
                                <th>Statut</th>
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
                                    <td>{{ Str::limit($transaction->description, 50) }}</td>
                                    <td>
                                        <span class="status-badge {{ $transaction->status === 'completed' ? 'status-active' : ($transaction->status === 'pending' ? 'status-pending' : 'status-inactive') }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">Aucune transaction trouvée</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Demandes de retrait en attente -->
@if($pendingWithdrawalRequests->count() > 0)
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Demandes de retrait en attente ({{ $pendingWithdrawalRequests->count() }})</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Utilisateur</th>
                                <th>Montant</th>
                                <th>Méthode</th>
                                <th>Date</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingWithdrawalRequests as $transaction)
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
                                    <td class="text-danger">-{{ number_format(abs($transaction->amount)) }} FCFA</td>
                                    <td>
                                        @php
                                            $meta = json_decode($transaction->meta, true);
                                            $method = $meta['method'] ?? 'N/A';
                                        @endphp
                                        {{ ucfirst(str_replace('_', ' ', $method)) }}
                                    </td>
                                    <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-success" onclick="approveTransaction({{ $transaction->id }})" title="Approuver">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" onclick="rejectTransaction({{ $transaction->id }})" title="Rejeter">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function approveTransaction(id) {
    if (confirm('Approuver cette demande de retrait ?')) {
        fetch(`/admin/transactions/${id}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        }).then(() => location.reload());
    }
}

function rejectTransaction(id) {
    if (confirm('Rejeter cette demande de retrait ? Le montant sera remboursé.')) {
        fetch(`/admin/transactions/${id}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        }).then(() => location.reload());
    }
}
</script>
@endpush
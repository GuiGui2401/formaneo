@extends('admin.layouts.app')

@section('title', 'Gestion des transactions')
@section('page-title', 'Transactions')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="search" placeholder="Rechercher utilisateur..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="type">
                    <option value="">Tous types</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">Tous statuts</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Liste des transactions ({{ $transactions->total() }})</h5>
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
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
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
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.transactions.show', $transaction) }}" class="btn btn-outline-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($transaction->type === 'withdrawal' && $transaction->status === 'pending')
                                        <button type="button" class="btn btn-outline-success" onclick="approveTransaction({{ $transaction->id }})" title="Approuver">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="rejectTransaction({{ $transaction->id }})" title="Rejeter">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">Aucune transaction trouvée</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($transactions->hasPages())
        <div class="card-footer">{{ $transactions->withQueryString()->links() }}</div>
    @endif
</div>
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

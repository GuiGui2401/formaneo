@extends('admin.layouts.app')

@section('title', 'Demandes de retrait en attente')
@section('page-title', 'Demandes de retrait en attente')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Liste des demandes de retrait en attente ({{ $transactions->total() }})</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Utilisateur</th>
                        <th>Montant</th>
                        <th>Méthode</th>
                        <th>Téléphone</th>
                        <th>Date</th>
                        <th width="150">Actions</th>
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
                            <td class="text-danger">-{{ number_format(abs($transaction->amount)) }} FCFA</td>
                            <td>
                                @php
                                    $meta = json_decode($transaction->meta, true);
                                    $method = $meta['method'] ?? 'N/A';
                                @endphp
                                {{ ucfirst(str_replace('_', ' ', $method)) }}
                            </td>
                            <td>
                                @php
                                    $phoneNumber = $meta['phone_number'] ?? 'N/A';
                                @endphp
                                {{ $phoneNumber }}
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
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">Aucune demande de retrait en attente</td>
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
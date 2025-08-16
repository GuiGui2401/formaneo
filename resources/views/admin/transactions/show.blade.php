@extends('admin.layouts.app')

@section('title', 'Détails de la transaction')
@section('page-title', 'Transaction #' . $transaction->id)

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Détails de la transaction</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>ID:</strong></td>
                                <td>#{{ $transaction->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Utilisateur:</strong></td>
                                <td>
                                    <a href="{{ route('admin.users.show', $transaction->user) }}">
                                        {{ $transaction->user->name }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Type:</strong></td>
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
                            </tr>
                            <tr>
                                <td><strong>Montant:</strong></td>
                                <td class="{{ $transaction->amount > 0 ? 'text-success' : 'text-danger' }}">
                                    <h4>{{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount) }} FCFA</h4>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Statut:</strong></td>
                                <td>
                                    <span class="status-badge {{ $transaction->status === 'completed' ? 'status-active' : ($transaction->status === 'pending' ? 'status-pending' : 'status-inactive') }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Date:</strong></td>
                                <td>{{ $transaction->created_at->format('d/m/Y à H:i:s') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Description:</strong></td>
                                <td>{{ $transaction->description }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                @if($transaction->meta)
                    <hr>
                    <h6>Métadonnées:</h6>
                    <pre class="bg-light p-3 rounded">{{ json_encode(json_decode($transaction->meta), JSON_PRETTY_PRINT) }}</pre>
                @endif
                
                @if($transaction->type === 'withdrawal' && $transaction->status === 'pending')
                    <hr>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" onclick="approveTransaction({{ $transaction->id }})">
                            <i class="fas fa-check me-1"></i>
                            Approuver
                        </button>
                        <button type="button" class="btn btn-danger" onclick="rejectTransaction({{ $transaction->id }})">
                            <i class="fas fa-times me-1"></i>
                            Rejeter
                        </button>
                    </div>
                @endif
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Retour à la liste
                </a>
            </div>
        </div>
    </div>
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
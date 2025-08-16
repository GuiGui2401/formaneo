@extends('admin.layouts.app')

@section('title', 'Gestion des formations')
@section('page-title', 'Formations')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="pack_id">
                    <option value="">Tous les packs</option>
                    @foreach($packs as $pack)
                        <option value="{{ $pack->id }}" {{ request('pack_id') == $pack->id ? 'selected' : '' }}>{{ $pack->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    <a href="{{ route('admin.formations.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.formations.create') }}" class="btn btn-success w-100">Nouvelle Formation</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Liste des formations ({{ $formations->total() }})</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Formation</th>
                        <th>Pack</th>
                        <th>Ordre</th>
                        <th>Durée</th>
                        <th>Modules</th>
                        <th>Statut</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($formations as $formation)
                        <tr>
                            <td>
                                <div>
                                    <div class="fw-bold">{{ $formation->title }}</div>
                                    @if($formation->description)
                                        <small class="text-muted">{{ Str::limit($formation->description, 60) }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('admin.formation-packs.show', $formation->pack) }}" class="text-decoration-none">
                                    {{ $formation->pack->name }}
                                </a>
                            </td>
                            <td><span class="badge bg-secondary">{{ $formation->order }}</span></td>
                            <td>
                                @if($formation->duration_minutes > 0)
                                    {{ floor($formation->duration_minutes / 60) }}h{{ $formation->duration_minutes % 60 }}min
                                @else
                                    <span class="text-muted">Non défini</span>
                                @endif
                            </td>
                            <td><span class="badge bg-info">{{ $formation->modules->count() }}</span></td>
                            <td>
                                <span class="status-badge {{ $formation->is_active ? 'status-active' : 'status-inactive' }}">
                                    {{ $formation->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.formations.show', $formation) }}" class="btn btn-outline-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.formations.edit', $formation) }}" class="btn btn-outline-primary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteFormation({{ $formation->id }}, '{{ $formation->title }}')" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-play-circle fa-3x text-muted mb-3"></i>
                                <p>Aucune formation trouvée</p>
                                <a href="{{ route('admin.formations.create') }}" class="btn btn-primary">Créer la première formation</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($formations->hasPages())
        <div class="card-footer">{{ $formations->withQueryString()->links() }}</div>
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
                <p>Êtes-vous sûr de vouloir supprimer la formation <strong id="formationName"></strong> ?</p>
                <p class="text-danger small">Cette action supprimera tous les modules associés.</p>
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
function deleteFormation(formationId, formationTitle) {
    document.getElementById('formationName').textContent = formationTitle;
    document.getElementById('deleteForm').action = `/admin/formations/${formationId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
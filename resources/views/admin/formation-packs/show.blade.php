@extends('admin.layouts.app')

@section('title', 'Pack de formation: ' . $pack->name)
@section('page-title', $pack->name)

@php
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['title' => 'Packs de Formation', 'url' => route('admin.formation-packs.index')],
        ['title' => $pack->name, 'url' => '']
    ];
@endphp

@section('content')
<div class="row">
    <!-- Informations principales -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                @if($pack->thumbnail_url)
                    <img src="{{ $pack->thumbnail_url }}" alt="{{ $pack->name }}" class="img-fluid rounded mb-3" style="max-height: 200px;">
                @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" style="height: 200px;">
                        <i class="fas fa-image fa-3x text-muted"></i>
                    </div>
                @endif
                
                <h4>{{ $pack->name }}</h4>
                <p class="text-muted">par {{ $pack->author }}</p>
                
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <span class="status-badge {{ $pack->is_active ? 'status-active' : 'status-inactive' }}">
                        {{ $pack->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                    
                    @if($pack->is_featured)
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-star me-1"></i>
                            Vedette
                        </span>
                    @endif
                </div>
                
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.formation-packs.edit', $pack) }}" class="btn btn-primary btn-sm flex-fill">
                        <i class="fas fa-edit me-1"></i>
                        Modifier
                    </a>
                    
                    <button class="btn btn-{{ $pack->is_active ? 'warning' : 'success' }} btn-sm flex-fill"
                            onclick="togglePackStatus({{ $pack->id }}, '{{ $pack->is_active ? 'désactiver' : 'activer' }}')">
                        <i class="fas fa-{{ $pack->is_active ? 'ban' : 'check' }} me-1"></i>
                        {{ $pack->is_active ? 'Désactiver' : 'Activer' }}
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Statistiques -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Statistiques</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border-end">
                            <h4 class="text-primary">{{ $stats['formations_count'] }}</h4>
                            <small class="text-muted">Formations</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-info">{{ $stats['modules_count'] }}</h4>
                        <small class="text-muted">Modules</small>
                    </div>
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-success">{{ $stats['students_count'] }}</h4>
                            <small class="text-muted">Étudiants</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-warning">{{ number_format($stats['revenue']) }}</h4>
                        <small class="text-muted">Revenus FCFA</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Détails et formations -->
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
                                <td><strong>Prix:</strong></td>
                                <td>{{ number_format($pack->price) }} FCFA</td>
                            </tr>
                            <tr>
                                <td><strong>Durée totale:</strong></td>
                                <td>{{ floor($pack->total_duration / 60) }}h{{ $pack->total_duration % 60 }}min</td>
                            </tr>
                            <tr>
                                <td><strong>Note:</strong></td>
                                <td>
                                    <div class="text-warning">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star{{ $i <= $pack->rating ? '' : '-o' }}"></i>
                                        @endfor
                                    </div>
                                    {{ $pack->rating }}/5
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Étudiants:</strong></td>
                                <td>{{ number_format($pack->students_count) }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Slug:</strong></td>
                                <td><code>{{ $pack->slug }}</code></td>
                            </tr>
                            <tr>
                                <td><strong>Ordre:</strong></td>
                                <td>{{ $pack->order }}</td>
                            </tr>
                            <tr>
                                <td><strong>Créé le:</strong></td>
                                <td>{{ $pack->created_at->format('d/m/Y à H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Modifié le:</strong></td>
                                <td>{{ $pack->updated_at->format('d/m/Y à H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="mt-3">
                    <label class="form-label"><strong>Description:</strong></label>
                    <p class="text-muted">{{ $pack->description }}</p>
                </div>
            </div>
        </div>
        
        <!-- Formations du pack -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    Formations ({{ $pack->formations->count() }})
                </h5>
                <a href="{{ route('admin.formations.create', ['pack_id' => $pack->id]) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>
                    Ajouter une formation
                </a>
            </div>
            <div class="card-body p-0">
                @if($pack->formations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Ordre</th>
                                    <th>Formation</th>
                                    <th>Modules</th>
                                    <th>Durée</th>
                                    <th>Statut</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pack->formations->sortBy('order') as $formation)
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">{{ $formation->order }}</span>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-medium">{{ $formation->title }}</div>
                                                @if($formation->description)
                                                    <small class="text-muted">{{ Str::limit($formation->description, 60) }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $formation->modules->count() }}</span>
                                        </td>
                                        <td>
                                            @if($formation->duration_minutes > 0)
                                                {{ floor($formation->duration_minutes / 60) }}h{{ $formation->duration_minutes % 60 }}min
                                            @else
                                                <span class="text-muted">Non défini</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="status-badge {{ $formation->is_active ? 'status-active' : 'status-inactive' }}">
                                                {{ $formation->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.formations.show', $formation) }}" 
                                                   class="btn btn-outline-info btn-action"
                                                   title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <a href="{{ route('admin.formations.edit', $formation) }}" 
                                                   class="btn btn-outline-primary btn-action"
                                                   title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <button type="button" 
                                                        class="btn btn-outline-danger btn-action"
                                                        onclick="deleteFormation({{ $formation->id }}, '{{ $formation->title }}')"
                                                        title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-play-circle fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucune formation dans ce pack</h5>
                        <p class="text-muted mb-4">Commencez par ajouter votre première formation</p>
                        <a href="{{ route('admin.formations.create', ['pack_id' => $pack->id]) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Ajouter la première formation
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Formation Modal -->
<div class="modal fade" id="deleteFormationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer la formation <strong id="formationName"></strong> ?</p>
                <p class="text-danger small">Cette action est irréversible et supprimera tous les modules associés.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteFormationForm" method="POST" style="display: inline;">
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
function togglePackStatus(packId, action) {
    if (confirm(`Êtes-vous sûr de vouloir ${action} ce pack ?`)) {
        fetch(`/admin/formation-packs/${packId}/toggle-status`, {
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

function deleteFormation(formationId, formationTitle) {
    document.getElementById('formationName').textContent = formationTitle;
    document.getElementById('deleteFormationForm').action = `/admin/formations/${formationId}`;
    new bootstrap.Modal(document.getElementById('deleteFormationModal')).show();
}
</script>
@endpush
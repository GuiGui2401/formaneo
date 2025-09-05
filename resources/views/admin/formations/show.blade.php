@extends('admin.layouts.app')

@section('title', 'Formation: ' . $formation->title)
@section('page-title', $formation->title)

@php
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['title' => 'Formations', 'url' => route('admin.formations.index')],
        ['title' => $formation->title, 'url' => '']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                @if($formation->thumbnail_url)
                    <img src="{{ $formation->thumbnail_url }}" alt="{{ $formation->title }}" class="img-fluid rounded mb-3" style="max-height: 200px;">
                @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" style="height: 200px;">
                        <i class="fas fa-image fa-3x text-muted"></i>
                    </div>
                @endif
                
                <h4>{{ $formation->title }}</h4>
                <p class="text-muted">Pack: <a href="{{ route('admin.formation-packs.show', $formation->pack) }}">{{ $formation->pack->name }}</a></p>
                
                @if($formation->description)
                    <p>{{ $formation->description }}</p>
                @endif
                
                <div class="d-flex gap-2 mb-3 justify-content-center">
                    <span class="status-badge {{ $formation->is_active ? 'status-active' : 'status-inactive' }}">
                        {{ $formation->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                    <span class="badge bg-secondary">Ordre: {{ $formation->order }}</span>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.formations.edit', $formation) }}" class="btn btn-primary btn-sm flex-fill">
                        <i class="fas fa-edit me-1"></i>
                        Modifier
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Statistiques -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Détails</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td><strong>Durée:</strong></td>
                        <td>
                            @if($formation->duration_minutes > 0)
                                {{ floor($formation->duration_minutes / 60) }}h{{ $formation->duration_minutes % 60 }}min
                            @else
                                Non définie
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Modules:</strong></td>
                        <td>{{ $formation->modules->count() }}</td>
                    </tr>
                    <tr>
                        <td><strong>Vidéo:</strong></td>
                        <td>
                            @if($formation->video_url)
                                <a href="{{ $formation->video_url }}" target="_blank" class="text-decoration-none">
                                    <i class="fas fa-external-link-alt me-1"></i>
                                    Voir
                                </a>
                            @else
                                Non définie
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Créée:</strong></td>
                        <td>{{ $formation->created_at->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <!-- Modules -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Modules ({{ $formation->modules->count() }})</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModuleModal">
                    <i class="fas fa-plus me-1"></i>
                    Ajouter un module
                </button>
            </div>
            <div class="card-body p-0">
                @if($formation->modules->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Ordre</th>
                                    <th>Module</th>
                                    <th>Durée</th>
                                    <th>Statut</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($formation->modules->sortBy('order') as $module)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $module->order }}</span></td>
                                        <td>
                                            <div>
                                                <div class="fw-medium">{{ $module->title }}</div>
                                                @if($module->content)
                                                    <small class="text-muted">{{ Str::limit(strip_tags($module->content), 60) }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($module->duration_minutes > 0)
                                                {{ $module->duration_minutes }}min
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="status-badge {{ $module->is_active ? 'status-active' : 'status-inactive' }}">
                                                {{ $module->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" onclick="editModule({{ $module->id }})" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" onclick="deleteModule({{ $module->id }}, '{{ $module->title }}')" title="Supprimer">
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
                        <i class="fas fa-layer-group fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucun module</h5>
                        <p class="text-muted mb-4">Cette formation ne contient aucun module</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModuleModal">
                            Ajouter le premier module
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Module Modal -->
<div class="modal fade" id="addModuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un module</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.formations.modules.store', $formation) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="module_title" class="form-label">Titre du module</label>
                        <input type="text" class="form-control" id="module_title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="module_content" class="form-label">Contenu</label>
                        <textarea class="form-control" id="module_content" name="content" rows="4"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="module_video_url" class="form-label">URL de la vidéo</label>
                            <input type="url" class="form-control" id="module_video_url" name="video_url">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="module_duration" class="form-label">Durée (min)</label>
                            <input type="number" class="form-control" id="module_duration" name="duration_minutes" min="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="module_order" class="form-label">Ordre</label>
                            <input type="number" class="form-control" id="module_order" name="order" value="{{ $formation->modules->count() + 1 }}" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter le module</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Module Modal -->
<div class="modal fade" id="deleteModuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer le module <strong id="moduleName"></strong> ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteModuleForm" method="POST" style="display: inline;">
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
function editModule(moduleId) {
    // Rediriger vers une page d'édition ou ouvrir un modal d'édition
    window.location.href = `/admin/formations/modules/${moduleId}/edit`;
}

function deleteModule(moduleId, moduleTitle) {
    document.getElementById('moduleName').textContent = moduleTitle;
    document.getElementById('deleteModuleForm').action = `/admin/formations/modules/${moduleId}`;
    new bootstrap.Modal(document.getElementById('deleteModuleModal')).show();
}
</script>
@endpush
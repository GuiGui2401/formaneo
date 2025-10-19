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
                        <td><strong>Vidéos:</strong></td>
                        <td>{{ $formation->videos->count() }}</td>
                    </tr>
                    <tr>
                        <td><strong>Modules:</strong></td>
                        <td>{{ $formation->modules->count() }} <small class="text-muted">(ancien système)</small></td>
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
        <!-- Vidéos -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Vidéos de la formation ({{ $formation->videos->count() }})</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addVideoModal">
                    <i class="fas fa-plus me-1"></i>
                    Ajouter une vidéo
                </button>
            </div>
            <div class="card-body p-0">
                @if($formation->videos->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Ordre</th>
                                    <th>Titre</th>
                                    <th>Durée</th>
                                    <th>Vidéo</th>
                                    <th>Statut</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($formation->videos->sortBy('order') as $video)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $video->order }}</span></td>
                                        <td>
                                            <div>
                                                <div class="fw-medium">{{ $video->title }}</div>
                                                @if($video->description)
                                                    <small class="text-muted">{{ Str::limit($video->description, 60) }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $video->duration_minutes }}min</td>
                                        <td>
                                            <a href="{{ $video->video_url }}" target="_blank" class="text-decoration-none">
                                                <i class="fas fa-external-link-alt me-1"></i>
                                                Voir
                                            </a>
                                        </td>
                                        <td>
                                            <span class="status-badge {{ $video->is_active ? 'status-active' : 'status-inactive' }}">
                                                {{ $video->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" onclick="editVideo({{ $video->id }}, '{{ addslashes($video->title) }}', '{{ addslashes($video->description ?? '') }}', '{{ $video->video_url }}', {{ $video->duration_minutes }}, {{ $video->order }})" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" onclick="deleteVideo({{ $video->id }}, '{{ addslashes($video->title) }}')" title="Supprimer">
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
                        <i class="fas fa-video fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucune vidéo</h5>
                        <p class="text-muted mb-4">Cette formation ne contient aucune vidéo</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVideoModal">
                            Ajouter la première vidéo
                        </button>
                    </div>
                @endif
            </div>
        </div>

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
                                                <button type="button" class="btn btn-outline-primary" onclick="editModule({{ $module->id }}, '{{ addslashes($module->title) }}', '{{ addslashes($module->content ?? '') }}', '{{ $module->video_url ?? '' }}', {{ $module->duration_minutes }}, {{ $module->order }})" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" onclick="deleteModule({{ $module->id }}, '{{ addslashes($module->title) }}')" title="Supprimer">
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

<!-- Edit Module Modal -->
<div class="modal fade" id="editModuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier le module</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editModuleForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_module_title" class="form-label">Titre du module</label>
                        <input type="text" class="form-control" id="edit_module_title" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_module_content" class="form-label">Contenu</label>
                        <textarea class="form-control" id="edit_module_content" name="content" rows="4"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_module_video_url" class="form-label">URL de la vidéo</label>
                            <input type="url" class="form-control" id="edit_module_video_url" name="video_url">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="edit_module_duration" class="form-label">Durée (min)</label>
                            <input type="number" class="form-control" id="edit_module_duration" name="duration_minutes" min="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="edit_module_order" class="form-label">Ordre</label>
                            <input type="number" class="form-control" id="edit_module_order" name="order" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Video Modal -->
<div class="modal fade" id="addVideoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter une vidéo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.formations.videos.store', $formation) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="video_title" class="form-label">Titre de la vidéo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="video_title" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label for="video_description" class="form-label">Description</label>
                        <textarea class="form-control" id="video_description" name="description" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="video_video_url" class="form-label">URL de la vidéo (Mega, YouTube, etc.) <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="video_video_url" name="video_url" required placeholder="https://...">
                        <small class="form-text text-muted">Entrez l'URL complète de la vidéo hébergée sur Mega ou autre plateforme</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="video_duration" class="form-label">Durée (minutes) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="video_duration" name="duration_minutes" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="video_order" class="form-label">Ordre</label>
                            <input type="number" class="form-control" id="video_order" name="order" value="{{ $formation->videos->count() + 1 }}" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter la vidéo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Video Modal -->
<div class="modal fade" id="editVideoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier la vidéo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editVideoForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_video_title" class="form-label">Titre de la vidéo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_video_title" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_video_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_video_description" name="description" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="edit_video_video_url" class="form-label">URL de la vidéo <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="edit_video_video_url" name="video_url" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_video_duration" class="form-label">Durée (minutes) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_video_duration" name="duration_minutes" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_video_order" class="form-label">Ordre</label>
                            <input type="number" class="form-control" id="edit_video_order" name="order" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Video Modal -->
<div class="modal fade" id="deleteVideoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer la vidéo <strong id="videoName"></strong> ?</p>
                <p class="text-danger"><small>Cette action est irréversible.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteVideoForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
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
// Gestion des vidéos
function editVideo(videoId, title, description, videoUrl, duration, order) {
    document.getElementById('edit_video_title').value = title;
    document.getElementById('edit_video_description').value = description;
    document.getElementById('edit_video_video_url').value = videoUrl;
    document.getElementById('edit_video_duration').value = duration;
    document.getElementById('edit_video_order').value = order;
    document.getElementById('editVideoForm').action = `/admin/formations/videos/${videoId}`;
    new bootstrap.Modal(document.getElementById('editVideoModal')).show();
}

function deleteVideo(videoId, videoTitle) {
    document.getElementById('videoName').textContent = videoTitle;
    document.getElementById('deleteVideoForm').action = `/admin/formations/videos/${videoId}`;
    new bootstrap.Modal(document.getElementById('deleteVideoModal')).show();
}

// Gestion des modules (ancien système)
function editModule(moduleId, title, content, videoUrl, duration, order) {
    document.getElementById('edit_module_title').value = title || '';
    document.getElementById('edit_module_content').value = content || '';
    document.getElementById('edit_module_video_url').value = videoUrl || '';
    document.getElementById('edit_module_duration').value = duration || '';
    document.getElementById('edit_module_order').value = order || '';
    document.getElementById('editModuleForm').action = `/admin/formations/modules/${moduleId}`;
    new bootstrap.Modal(document.getElementById('editModuleModal')).show();
}

function deleteModule(moduleId, moduleTitle) {
    document.getElementById('moduleName').textContent = moduleTitle;
    document.getElementById('deleteModuleForm').action = `/admin/formations/modules/${moduleId}`;
    new bootstrap.Modal(document.getElementById('deleteModuleModal')).show();
}
</script>
@endpush
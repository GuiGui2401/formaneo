@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Bannières Promotionnelles</h1>
        <a href="{{ route('admin.banners.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Ajouter une bannière
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Liste des bannières</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Type</th>
                            <th>Aperçu</th>
                            <th>Ordre</th>
                            <th>Actif</th>
                            <th>Créé le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($banners as $banner)
                            <tr>
                                <td>{{ $banner->id }}</td>
                                <td>{{ $banner->title }}</td>
                                <td>
                                    @if ($banner->type === 'image')
                                        <span class="badge badge-info">Image</span>
                                    @elseif ($banner->type === 'video')
                                        <span class="badge badge-success">Vidéo</span>
                                    @else
                                        <span class="badge badge-secondary">Document</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($banner->type === 'image')
                                        <img src="{{ asset('storage/' . $banner->file_path) }}" alt="{{ $banner->title }}" style="max-width: 100px; max-height: 60px;">
                                    @elseif ($banner->type === 'video')
                                        <i class="fas fa-video fa-2x text-primary"></i>
                                    @else
                                        <i class="fas fa-file fa-2x text-secondary"></i>
                                    @endif
                                </td>
                                <td>{{ $banner->order }}</td>
                                <td>
                                    @if ($banner->is_active)
                                        <span class="badge badge-success">Oui</span>
                                    @else
                                        <span class="badge badge-danger">Non</span>
                                    @endif
                                </td>
                                <td>{{ $banner->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.banners.edit', $banner->id) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.banners.toggle-status', $banner->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-{{ $banner->is_active ? 'secondary' : 'success' }} btn-sm">
                                            <i class="fas fa-{{ $banner->is_active ? 'eye-slash' : 'eye' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette bannière ?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <a href="{{ asset('storage/' . $banner->file_path) }}" target="_blank" class="btn btn-info btn-sm">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Aucune bannière trouvée</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Défis & Bonus</h1>
        <a href="{{ route('admin.challenges.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Ajouter un défi
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Liste des défis</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Récompense</th>
                            <th>Cible</th>
                            <th>Expiration</th>
                            <th>Actif</th>
                            <th>Ordre</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($challenges as $challenge)
                            <tr>
                                <td>{{ $challenge->id }}</td>
                                <td>{{ $challenge->title }}</td>
                                <td>{{ number_format($challenge->reward, 0) }} FCFA</td>
                                <td>{{ $challenge->target ?? 'N/A' }}</td>
                                <td>{{ $challenge->expires_at ? $challenge->expires_at->format('d/m/Y') : 'Aucune' }}</td>
                                <td>
                                    @if ($challenge->is_active)
                                        <span class="badge badge-success">Oui</span>
                                    @else
                                        <span class="badge badge-danger">Non</span>
                                    @endif
                                </td>
                                <td>{{ $challenge->order }}</td>
                                <td>
                                    <a href="{{ route('admin.challenges.show', $challenge->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.challenges.edit', $challenge->id) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.challenges.toggle-active', $challenge->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-{{ $challenge->is_active ? 'secondary' : 'success' }} btn-sm">
                                            <i class="fas fa-{{ $challenge->is_active ? 'eye-slash' : 'eye' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.challenges.destroy', $challenge->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce défi ?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Aucun défi trouvé</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $challenges->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Détails du défi</h1>
        <div>
            <a href="{{ route('admin.challenges.edit', $challenge->id) }}" class="btn btn-sm btn-warning shadow-sm mr-2">
                <i class="fas fa-edit fa-sm text-white-50"></i> Modifier
            </a>
            <a href="{{ route('admin.challenges.index') }}" class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations du défi</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID:</strong> {{ $challenge->id }}</p>
                            <p><strong>Titre:</strong> {{ $challenge->title }}</p>
                            <p><strong>Récompense:</strong> {{ number_format($challenge->reward, 0) }} FCFA</p>
                            <p><strong>Cible:</strong> {{ $challenge->target ?? 'Aucune' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Icône:</strong> {{ $challenge->icon_name ?? 'Aucune' }}</p>
                            <p><strong>Ordre:</strong> {{ $challenge->order }}</p>
                            <p><strong>Expiration:</strong> {{ $challenge->expires_at ? $challenge->expires_at->format('d/m/Y H:i') : 'Aucune' }}</p>
                            <p>
                                <strong>Statut:</strong>
                                @if ($challenge->is_active)
                                    <span class="badge badge-success">Actif</span>
                                @else
                                    <span class="badge badge-danger">Inactif</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <hr>
                    <p><strong>Description:</strong></p>
                    <p>{{ $challenge->description }}</p>

                    @if($challenge->image_url)
                        <hr>
                        <p><strong>Image:</strong></p>
                        <img src="{{ asset($challenge->image_url) }}" alt="{{ $challenge->title }}" class="img-fluid" style="max-width: 400px;">
                    @endif

                    <hr>
                    <p><strong>Créé le:</strong> {{ $challenge->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Mis à jour le:</strong> {{ $challenge->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistiques</h6>
                </div>
                <div class="card-body">
                    <p><strong>Utilisateurs participants:</strong> {{ $challenge->users->count() }}</p>
                    <p><strong>Défis complétés:</strong> {{ $challenge->users->where('pivot.is_completed', true)->count() }}</p>
                    <p><strong>Récompenses réclamées:</strong> {{ $challenge->users->where('pivot.reward_claimed', true)->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($challenge->users->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Utilisateurs</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Progression</th>
                            <th>Complété</th>
                            <th>Récompense réclamée</th>
                            <th>Date de complétion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($challenge->users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->pivot->progress }} / {{ $challenge->target ?? '100' }}</td>
                                <td>
                                    @if ($user->pivot->is_completed)
                                        <span class="badge badge-success">Oui</span>
                                    @else
                                        <span class="badge badge-warning">Non</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($user->pivot->reward_claimed)
                                        <span class="badge badge-success">Oui</span>
                                    @else
                                        <span class="badge badge-secondary">Non</span>
                                    @endif
                                </td>
                                <td>{{ $user->pivot->completed_at ? \Carbon\Carbon::parse($user->pivot->completed_at)->format('d/m/Y H:i') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

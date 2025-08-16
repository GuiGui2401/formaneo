@extends('admin.layouts.app')

@section('title', 'Modifier l\'utilisateur')
@section('page-title', 'Modifier: ' . $user->name)

@php
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['title' => 'Utilisateurs', 'url' => route('admin.users.index')],
        ['title' => $user->name, 'url' => route('admin.users.show', $user)],
        ['title' => 'Modifier', 'url' => '']
    ];
@endphp

@section('content')
<form action="{{ route('admin.users.update', $user) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations personnelles</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nom complet</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="balance" class="form-label">Solde (FCFA)</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('balance') is-invalid @enderror" 
                                       id="balance" name="balance" value="{{ old('balance', $user->balance) }}" 
                                       min="0" step="0.01">
                                <span class="input-group-text">FCFA</span>
                            </div>
                            @error('balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Paramètres</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                               value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Compte actif</label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_premium" name="is_premium" 
                               value="1" {{ old('is_premium', $user->is_premium) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_premium">Compte Premium</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">Retour</a>
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </div>
        </div>
    </div>
</form>
@endsection

@extends('admin.layouts.app')

@section('title', 'Créer un utilisateur')
@section('page-title', 'Nouvel Utilisateur')

@php
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['title' => 'Utilisateurs', 'url' => route('admin.users.index')],
        ['title' => 'Nouveau', 'url' => '']
    ];
@endphp

@section('content')
<form action="{{ route('admin.users.store') }}" method="POST">
    @csrf
    
    <div class="row">
        <!-- Informations principales -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>
                        Informations personnelles
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Adresse email <span class="text-danger">*</span></label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="fas fa-eye" id="passwordIcon"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Minimum 6 caractères</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                    <i class="fas fa-eye" id="password_confirmationIcon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Numéro de téléphone</label>
                        <input type="tel" 
                               class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" 
                               name="phone" 
                               value="{{ old('phone') }}" 
                               placeholder="+237 XXX XXX XXX">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Informations financières -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-wallet me-2"></i>
                        Paramètres financiers
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="balance" class="form-label">Solde initial (FCFA)</label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control @error('balance') is-invalid @enderror" 
                                       id="balance" 
                                       name="balance" 
                                       value="{{ old('balance', 0) }}" 
                                       min="0" 
                                       step="0.01">
                                <span class="input-group-text">FCFA</span>
                                @error('balance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Bonus de bienvenue par défaut: 1000 FCFA</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Code promo</label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control" 
                                       id="promo_code_display" 
                                       readonly 
                                       placeholder="Généré automatiquement">
                                <button class="btn btn-outline-secondary" type="button" onclick="generatePromoCode()">
                                    <i class="fas fa-sync"></i>
                                </button>
                            </div>
                            <div class="form-text">Le code sera généré automatiquement si non spécifié</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Options et paramètres -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        Paramètres du compte
                    </h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Compte actif
                        </label>
                        <div class="form-text">L'utilisateur peut se connecter</div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_premium" 
                               name="is_premium" 
                               value="1"
                               {{ old('is_premium') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_premium">
                            Compte Premium
                        </label>
                        <div class="form-text">Accès aux fonctionnalités premium</div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="send_welcome_email" 
                               name="send_welcome_email" 
                               value="1"
                               {{ old('send_welcome_email', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="send_welcome_email">
                            Envoyer email de bienvenue
                        </label>
                        <div class="form-text">Email avec les informations de connexion</div>
                    </div>
                </div>
            </div>
            
            <!-- Informations supplémentaires -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-lightbulb me-2"></i>
                            Informations importantes
                        </h6>
                        <ul class="mb-0">
                            <li>Le code promo sera généré automatiquement</li>
                            <li>Le lien d'affiliation sera créé</li>
                            <li>L'utilisateur recevra 5 quiz gratuits</li>
                            <li>Un bonus de bienvenue peut être attribué</li>
                        </ul>
                    </div>
                    
                    <div class="mt-3">
                        <h6>Valeurs par défaut:</h6>
                        <ul class="list-unstyled small text-muted">
                            <li><strong>Quiz gratuits:</strong> 5</li>
                            <li><strong>Bonus bienvenue:</strong> 1000 FCFA</li>
                            <li><strong>Statut:</strong> Actif</li>
                            <li><strong>Type:</strong> Standard</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Actions -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Retour à la liste
                </a>
                
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" onclick="previewUser()">
                        <i class="fas fa-eye me-1"></i>
                        Aperçu
                    </button>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-user-plus me-1"></i>
                        Créer l'utilisateur
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aperçu de l'utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informations personnelles</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Nom:</strong></td>
                                <td id="preview_name">-</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td id="preview_email">-</td>
                            </tr>
                            <tr>
                                <td><strong>Téléphone:</strong></td>
                                <td id="preview_phone">-</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Paramètres</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Solde initial:</strong></td>
                                <td id="preview_balance">-</td>
                            </tr>
                            <tr>
                                <td><strong>Type:</strong></td>
                                <td id="preview_type">-</td>
                            </tr>
                            <tr>
                                <td><strong>Statut:</strong></td>
                                <td id="preview_status">-</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-success" onclick="document.querySelector('form').submit()">
                    Créer l'utilisateur
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + 'Icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Generate random promo code
function generatePromoCode() {
    const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const numbers = '0123456789';
    
    let code = '';
    code += letters.charAt(Math.floor(Math.random() * letters.length));
    code += letters.charAt(Math.floor(Math.random() * letters.length));
    code += numbers.charAt(Math.floor(Math.random() * numbers.length));
    code += numbers.charAt(Math.floor(Math.random() * numbers.length));
    code += numbers.charAt(Math.floor(Math.random() * numbers.length));
    
    document.getElementById('promo_code_display').value = code;
}

// Preview user data
function previewUser() {
    document.getElementById('preview_name').textContent = document.getElementById('name').value || '-';
    document.getElementById('preview_email').textContent = document.getElementById('email').value || '-';
    document.getElementById('preview_phone').textContent = document.getElementById('phone').value || 'Non renseigné';
    document.getElementById('preview_balance').textContent = (document.getElementById('balance').value || '0') + ' FCFA';
    document.getElementById('preview_type').textContent = document.getElementById('is_premium').checked ? 'Premium' : 'Standard';
    document.getElementById('preview_status').textContent = document.getElementById('is_active').checked ? 'Actif' : 'Inactif';
    
    new bootstrap.Modal(document.getElementById('previewModal')).show();
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const passwordConfirmation = document.getElementById('password_confirmation').value;
    
    if (!name || !email || !password) {
        e.preventDefault();
        alert('Veuillez remplir tous les champs obligatoires.');
        return false;
    }
    
    if (password !== passwordConfirmation) {
        e.preventDefault();
        alert('Les mots de passe ne correspondent pas.');
        return false;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        alert('Le mot de passe doit contenir au moins 6 caractères.');
        return false;
    }
});

// Email validation
document.getElementById('email').addEventListener('blur', function() {
    const email = this.value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (email && !emailRegex.test(email)) {
        this.classList.add('is-invalid');
        if (!this.nextElementSibling || !this.nextElementSibling.classList.contains('invalid-feedback')) {
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = 'Format d\'email invalide';
            this.parentNode.appendChild(feedback);
        }
    } else {
        this.classList.remove('is-invalid');
        const feedback = this.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    }
});

// Generate promo code on page load
document.addEventListener('DOMContentLoaded', function() {
    generatePromoCode();
});
</script>
@endpush
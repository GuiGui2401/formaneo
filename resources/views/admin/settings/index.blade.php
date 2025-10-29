@extends('admin.layouts.app')

@section('title', 'Paramètres du système')
@section('page-title', 'Paramètres')

@php
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['title' => 'Paramètres', 'url' => '']
    ];
@endphp

@section('content')
<form action="{{ route('admin.settings.update') }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="row">
        <!-- Paramètres des Quiz -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-question-circle me-2"></i>
                        Paramètres des Quiz
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="quiz_reward_per_correct" class="form-label">
                            Récompense par réponse correcte (FCFA)
                        </label>
                        <input type="number" 
                               class="form-control @error('quiz_reward_per_correct') is-invalid @enderror" 
                               id="quiz_reward_per_correct" 
                               name="quiz_reward_per_correct" 
                               value="{{ old('quiz_reward_per_correct', $settings['quiz_reward_per_correct']) }}" 
                               min="0" 
                               step="0.01" 
                               required>
                        @error('quiz_reward_per_correct')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="quiz_passing_score" class="form-label">
                            Score minimum pour réussir (%)
                        </label>
                        <input type="number" 
                               class="form-control @error('quiz_passing_score') is-invalid @enderror" 
                               id="quiz_passing_score" 
                               name="quiz_passing_score" 
                               value="{{ old('quiz_passing_score', $settings['quiz_passing_score']) }}" 
                               min="0" 
                               max="100" 
                               required>
                        @error('quiz_passing_score')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="free_quizzes_per_user" class="form-label">
                            Nombre de quiz gratuits par utilisateur
                        </label>
                        <input type="number" 
                               class="form-control @error('free_quizzes_per_user') is-invalid @enderror" 
                               id="free_quizzes_per_user" 
                               name="free_quizzes_per_user" 
                               value="{{ old('free_quizzes_per_user', $settings['free_quizzes_per_user']) }}" 
                               min="0" 
                               required>
                        @error('free_quizzes_per_user')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Paramètres d'Affiliation -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>
                        Paramètres d'Affiliation
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="level1_commission" class="form-label">
                            Commission niveau 1 (FCFA)
                        </label>
                        <input type="number" 
                               class="form-control @error('level1_commission') is-invalid @enderror" 
                               id="level1_commission" 
                               name="level1_commission" 
                               value="{{ old('level1_commission', $settings['level1_commission']) }}" 
                               min="0" 
                               step="0.01" 
                               required>
                        @error('level1_commission')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Commission pour le parrain direct</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="level2_commission" class="form-label">
                            Commission niveau 2 (FCFA)
                        </label>
                        <input type="number" 
                               class="form-control @error('level2_commission') is-invalid @enderror" 
                               id="level2_commission" 
                               name="level2_commission" 
                               value="{{ old('level2_commission', $settings['level2_commission']) }}" 
                               min="0" 
                               step="0.01" 
                               required>
                        @error('level2_commission')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Commission pour le parrain du parrain</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="welcome_bonus" class="form-label">
                            Bonus de bienvenue (FCFA)
                        </label>
                        <input type="number" 
                               class="form-control @error('welcome_bonus') is-invalid @enderror" 
                               id="welcome_bonus" 
                               name="welcome_bonus" 
                               value="{{ old('welcome_bonus', $settings['welcome_bonus']) }}" 
                               min="0" 
                               step="0.01" 
                               required>
                        @error('welcome_bonus')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Paramètres de Portefeuille -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-wallet me-2"></i>
                        Paramètres de Portefeuille
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="min_withdrawal_amount" class="form-label">
                            Montant minimum de retrait (FCFA)
                        </label>
                        <input type="number" 
                               class="form-control @error('min_withdrawal_amount') is-invalid @enderror" 
                               id="min_withdrawal_amount" 
                               name="min_withdrawal_amount" 
                               value="{{ old('min_withdrawal_amount', $settings['min_withdrawal_amount']) }}" 
                               min="0" 
                               step="0.01" 
                               required>
                        @error('min_withdrawal_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="max_withdrawal_amount" class="form-label">
                            Montant maximum de retrait (FCFA)
                        </label>
                        <input type="number" 
                               class="form-control @error('max_withdrawal_amount') is-invalid @enderror" 
                               id="max_withdrawal_amount" 
                               name="max_withdrawal_amount" 
                               value="{{ old('max_withdrawal_amount', $settings['max_withdrawal_amount']) }}" 
                               min="0" 
                               step="0.01" 
                               required>
                        @error('max_withdrawal_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Paramètres d'Activation de Compte -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>
                        Activation de Compte
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="account_activation_cost" class="form-label">
                            Coût d'activation mensuel (FCFA)
                        </label>
                        <input type="number" 
                               class="form-control @error('account_activation_cost') is-invalid @enderror" 
                               id="account_activation_cost" 
                               name="account_activation_cost" 
                               value="{{ old('account_activation_cost', $settings['account_activation_cost']) }}" 
                               min="100" 
                               step="1" 
                               required>
                        @error('account_activation_cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Montant que les utilisateurs web doivent payer pour activer leur compte (valable 1 mois)</div>
                    </div>

                    <div class="alert alert-warning">
                        <h6 class="alert-heading">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Important
                        </h6>
                        <ul class="mb-0 small">
                            <li>Les comptes web sont inactifs par défaut</li>
                            <li>Les utilisateurs mobiles ne sont pas affectés</li>
                            <li>Un bonus de 2000 FCFA est accordé après activation</li>
                            <li>L'activation dure 1 mois puis expire automatiquement</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Paramètres de Formation -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Paramètres de Formation
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="cashback_rate" class="form-label">
                            Taux de cashback (0-1)
                        </label>
                        <input type="number"
                               class="form-control @error('cashback_rate') is-invalid @enderror"
                               id="cashback_rate"
                               name="cashback_rate"
                               value="{{ old('cashback_rate', $settings['cashback_rate']) }}"
                               min="0"
                               max="1"
                               step="0.01"
                               required>
                        @error('cashback_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Pourcentage de cashback à la fin d'une formation (ex: 0.15 = 15%)</div>
                    </div>

                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            Informations importantes
                        </h6>
                        <ul class="mb-0">
                            <li>Les modifications prennent effet immédiatement</li>
                            <li>Les commissions ne sont appliquées qu'aux nouveaux achats</li>
                            <li>Le cashback est calculé sur le prix d'achat du pack</li>
                            <li>Les quiz gratuits s'appliquent aux nouveaux utilisateurs</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Centre d'aide -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-headset me-2"></i>
                        Centre d'aide
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="support_email" class="form-label">
                            Email du support
                        </label>
                        <input type="email"
                               class="form-control @error('support_email') is-invalid @enderror"
                               id="support_email"
                               name="support_email"
                               value="{{ old('support_email', $settings['support_email']) }}"
                               placeholder="support@formaneo.com">
                        @error('support_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Email affiché dans l'application mobile</div>
                    </div>

                    <div class="mb-3">
                        <label for="support_phone" class="form-label">
                            Téléphone du support
                        </label>
                        <input type="text"
                               class="form-control @error('support_phone') is-invalid @enderror"
                               id="support_phone"
                               name="support_phone"
                               value="{{ old('support_phone', $settings['support_phone']) }}"
                               placeholder="+225 XX XX XX XX XX">
                        @error('support_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="support_whatsapp" class="form-label">
                            WhatsApp du support
                        </label>
                        <input type="text"
                               class="form-control @error('support_whatsapp') is-invalid @enderror"
                               id="support_whatsapp"
                               name="support_whatsapp"
                               value="{{ old('support_whatsapp', $settings['support_whatsapp']) }}"
                               placeholder="+225XXXXXXXXXX">
                        @error('support_whatsapp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Numéro WhatsApp pour le support client</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Actions -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    <i class="fas fa-save me-2"></i>
                    Les paramètres sont sauvegardés automatiquement
                </div>
                
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" onclick="resetToDefaults()">
                        <i class="fas fa-undo me-1"></i>
                        Valeurs par défaut
                    </button>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>
                        Sauvegarder les paramètres
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Statistiques système -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Statistiques du système
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-3">
                            <h4 class="text-primary">{{ number_format(\App\Models\User::count()) }}</h4>
                            <small class="text-muted">Utilisateurs totaux</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-3">
                            <h4 class="text-success">{{ number_format(\App\Models\FormationPack::where('is_active', true)->count()) }}</h4>
                            <small class="text-muted">Packs actifs</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-3">
                            <h4 class="text-info">{{ number_format(\App\Models\Quiz::where('is_active', true)->count()) }}</h4>
                            <small class="text-muted">Quiz actifs</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-3">
                            <h4 class="text-warning">{{ number_format(\App\Models\Transaction::where('status', 'pending')->count()) }}</h4>
                            <small class="text-muted">Transactions en attente</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function resetToDefaults() {
    if (confirm('Êtes-vous sûr de vouloir restaurer les valeurs par défaut ? Cette action est irréversible.')) {
        // Valeurs par défaut
        document.getElementById('quiz_reward_per_correct').value = 20;
        document.getElementById('quiz_passing_score').value = 60;
        document.getElementById('level1_commission').value = 1000;
        document.getElementById('level2_commission').value = 500;
        document.getElementById('welcome_bonus').value = 1000;
        document.getElementById('min_withdrawal_amount').value = 1000;
        document.getElementById('max_withdrawal_amount').value = 1000000;
        document.getElementById('cashback_rate').value = 0.15;
        document.getElementById('free_quizzes_per_user').value = 5;
        document.getElementById('support_email').value = 'support@formaneo.com';
        document.getElementById('support_phone').value = '+225 XX XX XX XX XX';
        document.getElementById('support_whatsapp').value = '+225XXXXXXXXXX';
        document.getElementById('account_activation_cost').value = 5000;
    }
}

// Validation en temps réel
document.getElementById('min_withdrawal_amount').addEventListener('input', function() {
    const min = parseFloat(this.value);
    const max = parseFloat(document.getElementById('max_withdrawal_amount').value);
    
    if (min > max) {
        document.getElementById('max_withdrawal_amount').value = min;
    }
});

document.getElementById('max_withdrawal_amount').addEventListener('input', function() {
    const max = parseFloat(this.value);
    const min = parseFloat(document.getElementById('min_withdrawal_amount').value);
    
    if (max < min) {
        document.getElementById('min_withdrawal_amount').value = max;
    }
});
</script>
@endpush
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Connexion Admin - Formaneo</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Figtree', sans-serif;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        
        .login-form {
            padding: 3rem;
        }
        
        .login-illustration {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            min-height: 500px;
        }
        
        .brand-logo {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .brand-subtitle {
            color: #6c757d;
            margin-bottom: 2rem;
        }
        
        .form-control {
            border-radius: 12px;
            padding: 0.75rem 1rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .illustration-icon {
            font-size: 8rem;
            opacity: 0.3;
            margin-bottom: 2rem;
        }
        
        .illustration-text {
            text-align: center;
        }
        
        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }
        
        .form-floating label {
            color: #6c757d;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="login-container">
                    <div class="row g-0">
                        <!-- Formulaire de connexion -->
                        <div class="col-lg-6">
                            <div class="login-form">
                                <div class="text-center mb-4">
                                    <h1 class="brand-logo">Formaneo</h1>
                                    <p class="brand-subtitle">Tableau de bord administrateur</p>
                                </div>

                                @if($errors->any())
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        @foreach($errors->all() as $error)
                                            {{ $error }}
                                        @endforeach
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('admin.login') }}">
                                    @csrf
                                    
                                    <div class="form-floating mb-3">
                                        <input type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               id="email" 
                                               name="email" 
                                               placeholder="admin@formaneo.com"
                                               value="{{ old('email') }}" 
                                               required 
                                               autofocus>
                                        <label for="email">
                                            <i class="fas fa-envelope me-2"></i>
                                            Adresse email
                                        </label>
                                    </div>

                                    <div class="form-floating mb-3">
                                        <input type="password" 
                                               class="form-control @error('password') is-invalid @enderror" 
                                               id="password" 
                                               name="password" 
                                               placeholder="Mot de passe"
                                               required>
                                        <label for="password">
                                            <i class="fas fa-lock me-2"></i>
                                            Mot de passe
                                        </label>
                                    </div>

                                    <div class="form-check mb-4">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="remember" 
                                               name="remember">
                                        <label class="form-check-label" for="remember">
                                            Se souvenir de moi
                                        </label>
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-login">
                                            <i class="fas fa-sign-in-alt me-2"></i>
                                            Se connecter
                                        </button>
                                    </div>
                                </form>

                                <div class="text-center mt-4">
                                    <small class="text-muted">
                                        © {{ date('Y') }} Formaneo. Tous droits réservés.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Illustration -->
                        <div class="col-lg-6">
                            <div class="login-illustration">
                                <div class="illustration-text">
                                    <div class="illustration-icon">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                    <h3 class="mb-3">Tableau de Bord</h3>
                                    <p class="mb-4">
                                        Gérez votre plateforme d'apprentissage en ligne avec des outils puissants et intuitifs.
                                    </p>
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <i class="fas fa-users fa-2x mb-2"></i>
                                            <div>Utilisateurs</div>
                                        </div>
                                        <div class="col-4">
                                            <i class="fas fa-graduation-cap fa-2x mb-2"></i>
                                            <div>Formations</div>
                                        </div>
                                        <div class="col-4">
                                            <i class="fas fa-chart-line fa-2x mb-2"></i>
                                            <div>Analytics</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
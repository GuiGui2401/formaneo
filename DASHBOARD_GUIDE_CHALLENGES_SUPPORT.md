# Guide Dashboard - Challenges et Support

## 📋 Vue d'ensemble

Ce guide explique comment gérer les **Challenges** et les **Informations de Support** depuis le dashboard Laravel admin.

---

## 🎯 Challenges (Défis et Récompenses)

### Table: `challenges`

#### Champs disponibles:
- **title** (string) - Titre du défi
- **description** (text) - Description détaillée
- **reward** (decimal) - Montant de la récompense en FCFA
- **image_url** (string, nullable) - URL de l'image du défi
- **icon_name** (string, nullable) - Nom de l'icône (school, quiz, people, emoji_events, monetization_on, star, menu_book)
- **target** (integer, nullable) - Objectif à atteindre (ex: 5 pour "Réussir 5 quiz")
- **expires_at** (timestamp, nullable) - Date d'expiration du défi
- **is_active** (boolean) - Défi actif ou non (défaut: true)
- **order** (integer) - Ordre d'affichage (défaut: 0)

#### Exemples de défis:
```
Titre: "Quiz Master"
Description: "Réussissez 5 quiz"
Reward: 1000.00
Icon: quiz
Target: 5
Order: 1
```

#### Table de liaison: `user_challenges`
Cette table gère automatiquement la progression des utilisateurs :
- **user_id** - ID de l'utilisateur
- **challenge_id** - ID du défi
- **progress** - Progression actuelle (ex: 3/5)
- **is_completed** - Défi complété ou non
- **completed_at** - Date de complétion
- **reward_claimed** - Récompense réclamée ou non

---

## 💬 Support Info (Aide et Support)

### Table: `support_infos`

#### Champs disponibles:
- **type** (string) - Type de contact (email, phone, whatsapp, faq)
- **label** (string) - Label à afficher (ex: "Email", "Téléphone")
- **value** (string) - Valeur du contact (ex: support@formaneo.com, +237 691 59 28 82)
- **description** (text, nullable) - Description additionnelle
- **icon_name** (string, nullable) - Nom de l'icône (email, phone, chat, help_outline)
- **order** (integer) - Ordre d'affichage (défaut: 0)
- **is_active** (boolean) - Actif ou non (défaut: true)

#### Exemples:
```
Type: email
Label: Email
Value: support@formaneo.com
Icon: email
Order: 1
```

---

## 🔌 API Endpoints

### Challenges

#### GET `/api/v1/challenges`
Obtenir tous les défis actifs (publique)

#### GET `/api/v1/challenges/user` (auth)
Obtenir les défis de l'utilisateur avec leur progression

#### POST `/api/v1/challenges/{id}/complete` (auth)
Marquer un défi comme complété

#### POST `/api/v1/challenges/{id}/claim` (auth)
Réclamer la récompense d'un défi complété

#### POST `/api/v1/challenges/{id}/progress` (auth)
Mettre à jour la progression d'un défi
```json
{
  "progress": 3
}
```

### Support

#### GET `/api/v1/support/info`
Obtenir toutes les informations de support actives

#### POST `/api/v1/support/request`
Envoyer une demande de support
```json
{
  "subject": "Problème de connexion",
  "message": "Je n'arrive pas à me connecter",
  "category": "technique"
}
```

---

## 📊 Ajout au Dashboard Admin

### Pour Laravel Nova / Filament / Backpack:

#### 1. Challenges Resource

```php
// Champs du formulaire
Schema::make([
    Text::make('Titre', 'title')->required(),
    Textarea::make('Description', 'description')->required(),
    Number::make('Récompense (FCFA)', 'reward')->min(0)->step(0.01)->required(),
    Image::make('Image', 'image_url')->disk('public')->path('challenges'),
    Select::make('Icône', 'icon_name')->options([
        'school' => 'Formation',
        'quiz' => 'Quiz',
        'people' => 'Parrainage',
        'emoji_events' => 'Trophée',
        'menu_book' => 'Ebook',
        'star' => 'Étoile',
        'monetization_on' => 'Monnaie',
    ]),
    Number::make('Objectif', 'target')->min(1),
    DateTime::make('Expire le', 'expires_at')->nullable(),
    Boolean::make('Actif', 'is_active')->default(true),
    Number::make('Ordre', 'order')->default(0),
])
```

#### 2. Support Info Resource

```php
// Champs du formulaire
Schema::make([
    Select::make('Type', 'type')->options([
        'email' => 'Email',
        'phone' => 'Téléphone',
        'whatsapp' => 'WhatsApp',
        'faq' => 'FAQ',
    ])->required(),
    Text::make('Label', 'label')->required(),
    Text::make('Valeur', 'value')->required(),
    Textarea::make('Description', 'description'),
    Select::make('Icône', 'icon_name')->options([
        'email' => 'Email',
        'phone' => 'Téléphone',
        'chat' => 'Chat',
        'help_outline' => 'Aide',
    ]),
    Number::make('Ordre', 'order')->default(0),
    Boolean::make('Actif', 'is_active')->default(true),
])
```

---

## 🎨 Upload d'images pour les Challenges

### Configuration du stockage

1. Dans `config/filesystems.php`, assurez-vous que le disk 'public' est configuré :
```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
],
```

2. Créer le lien symbolique :
```bash
php artisan storage:link
```

3. Les images uploadées seront accessibles via :
`http://votre-domaine.com/storage/challenges/image.jpg`

---

## ✅ Données par défaut créées

### Support Info (3 entrées):
1. Email - support@formaneo.com
2. Téléphone - +237 691 59 28 82
3. WhatsApp - Chat instantané

### Challenges (6 défis):
1. Première Formation - 500 FCFA
2. Quiz Master - 1000 FCFA
3. Parrain Actif - 1500 FCFA
4. Lecteur Assidu - 750 FCFA
5. Champion des Formations - 2500 FCFA
6. Connexion Quotidienne - 300 FCFA

---

## 🔄 Progression automatique des challenges

La progression des challenges peut être mise à jour automatiquement dans ton code backend :

```php
// Exemple : Quand un utilisateur complète une formation
$user = Auth::user();
$challenge = Challenge::where('icon_name', 'school')->first();

if ($challenge) {
    $userChallenge = $user->challenges()->where('challenge_id', $challenge->id)->first();
    $currentProgress = $userChallenge ? $userChallenge->pivot->progress : 0;

    // Mettre à jour la progression
    if ($userChallenge) {
        $user->challenges()->updateExistingPivot($challenge->id, [
            'progress' => $currentProgress + 1,
            'is_completed' => ($currentProgress + 1) >= $challenge->target,
            'completed_at' => ($currentProgress + 1) >= $challenge->target ? now() : null,
        ]);
    } else {
        $user->challenges()->attach($challenge->id, [
            'progress' => 1,
            'is_completed' => 1 >= $challenge->target,
            'completed_at' => 1 >= $challenge->target ? now() : null,
        ]);
    }
}
```

---

## 📝 Notes importantes

1. **Images** : Les images doivent être uploadées dans `storage/app/public/challenges/`
2. **Icônes** : Utilise les noms d'icônes Material Design compatibles avec Flutter
3. **Récompenses** : Les récompenses sont automatiquement ajoutées au solde lors du claim
4. **Transactions** : Une transaction est créée automatiquement quand une récompense est réclamée
5. **Support actif** : Seuls les supports avec `is_active = true` sont affichés dans l'app

---

## 🚀 Commandes utiles

```bash
# Voir les routes
php artisan route:list | grep challenge

# Créer un nouveau challenge via tinker
php artisan tinker
Challenge::create([
    'title' => 'Nouveau défi',
    'description' => 'Description',
    'reward' => 500,
    'icon_name' => 'star',
    'is_active' => true
]);

# Voir les challenges avec progression d'un utilisateur
$user = User::find(1);
$user->challenges;
```

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\FormationPack;
use App\Models\Formation;
use App\Models\Module;
use App\Models\Settings;
use App\Models\Transaction;
use App\Models\Commission;
use App\Models\AffiliateLink;
use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\UserPack;
use App\Models\FormationNote;
use App\Models\FormationProgress;
use App\Models\FormationModule;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Créer les paramètres par défaut
        $this->createSettings();
        
        // Créer les administrateurs
        $admins = $this->createAdmins();
        
        // Créer l'utilisateur admin principal
        $admin = $this->createAdminUser();
        
        // Créer un utilisateur de test avec des données réalistes
        $testUser = $this->createTestUser();
        
        // Créer les packs de formations
        $formationPacks = $this->createFormationPacks();
        
        // Créer des utilisateurs affiliés fictifs
        $affiliates = $this->createAffiliates($testUser);
        
        // Créer des achats de packs pour les utilisateurs
        $this->createUserPacks($testUser, $affiliates, $formationPacks);
        
        // Créer les modules de formation (nouvelle table)
        $this->createFormationModules($formationPacks);
        
        // Créer le progrès des formations
        $this->createFormationProgress($testUser, $affiliates, $formationPacks);
        
        // Créer des notes de formation
        $this->createFormationNotes($testUser, $affiliates, $formationPacks);
        
        // Créer des transactions pour les utilisateurs
        $this->createTransactions($testUser, $affiliates);
        
        // Créer des commissions
        $this->createCommissions($testUser, $affiliates);
        
        // Créer les liens d'affiliation
        $this->createAffiliateLinks($testUser, $affiliates);
        
        // Créer des quiz
        $quizzes = $this->createQuizzes();
        
        // Créer des résultats de quiz
        $this->createQuizResults($testUser, $affiliates, $quizzes);
        
        echo "✅ Base de données initialisée avec succès!\n";
        echo "👤 Admin: admin@formaneo.com / Admin@2025\n";
        echo "👤 Test User: david@formaneo.com / Test@2025\n";
        echo "👨‍💼 Super Admin: superadmin@formaneo.com / SuperAdmin@2025\n";
        echo "🔗 Site vitrine: http://cleanestuaire.com/\n";
        echo "📊 Données créées:\n";
        echo "   - " . count($formationPacks) . " packs de formations\n";
        echo "   - 42 formations complètes\n";
        echo "   - 270+ modules\n";
        echo "   - 12 utilisateurs (dont 10 affiliés)\n";
        echo "   - Quiz et résultats\n";
        echo "   - Progrès et notes des formations\n";
    }
    
    private function createSettings(): void
    {
        $settings = [
            // Commissions d'affiliation
            [
                'key' => 'affiliate_commission_basic',
                'value' => '2000',
                'type' => 'float',
                'group' => 'affiliate',
                'description' => 'Commission de base pour 0-100 affiliés/mois (FCFA)',
                'is_public' => false,
            ],
            [
                'key' => 'affiliate_commission_premium',
                'value' => '2500',
                'type' => 'float',
                'group' => 'affiliate',
                'description' => 'Commission premium pour >100 affiliés/mois (FCFA)',
                'is_public' => false,
            ],
            [
                'key' => 'affiliate_threshold',
                'value' => '100',
                'type' => 'integer',
                'group' => 'affiliate',
                'description' => 'Seuil pour passer en commission premium',
                'is_public' => false,
            ],
            
            // Bonus système
            [
                'key' => 'welcome_bonus',
                'value' => '1000',
                'type' => 'float',
                'group' => 'bonus',
                'description' => 'Bonus de bienvenue (FCFA)',
                'is_public' => true,
            ],
            [
                'key' => 'formation_cashback_rate',
                'value' => '0.15',
                'type' => 'float',
                'group' => 'formation',
                'description' => 'Taux de cashback sur les formations (15%)',
                'is_public' => true,
            ],
            
            // Configuration Quiz
            [
                'key' => 'free_quizzes_per_user',
                'value' => '5',
                'type' => 'integer',
                'group' => 'quiz',
                'description' => 'Nombre de quiz gratuits par utilisateur',
                'is_public' => true,
            ],
            [
                'key' => 'quiz_reward_per_correct',
                'value' => '20',
                'type' => 'float',
                'group' => 'quiz',
                'description' => 'Récompense par bonne réponse (FCFA)',
                'is_public' => true,
            ],
            [
                'key' => 'quiz_passing_score',
                'value' => '60',
                'type' => 'integer',
                'group' => 'quiz',
                'description' => 'Score minimum pour réussir un quiz (%)',
                'is_public' => true,
            ],
            
            // Limites de retrait
            [
                'key' => 'min_withdrawal_amount',
                'value' => '2000',
                'type' => 'float',
                'group' => 'withdrawal',
                'description' => 'Montant minimum de retrait (FCFA)',
                'is_public' => true,
            ],
            [
                'key' => 'max_withdrawal_amount',
                'value' => '1000000',
                'type' => 'float',
                'group' => 'withdrawal',
                'description' => 'Montant maximum de retrait (FCFA)',
                'is_public' => true,
            ],
            
            // Configuration stockage
            [
                'key' => 'mega_api_key',
                'value' => 'YOUR_MEGA_API_KEY',
                'type' => 'string',
                'group' => 'storage',
                'description' => 'Clé API Mega pour le stockage',
                'is_public' => false,
            ],
            [
                'key' => 'mega_folder_path',
                'value' => '/Formaneo',
                'type' => 'string',
                'group' => 'storage',
                'description' => 'Dossier Mega pour les formations',
                'is_public' => false,
            ],
            
            // Configuration générale
            [
                'key' => 'site_name',
                'value' => 'Formaneo',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Nom du site',
                'is_public' => true,
            ],
            [
                'key' => 'site_url',
                'value' => 'http://cleanestuaire.com',
                'type' => 'string',
                'group' => 'general',
                'description' => 'URL du site vitrine',
                'is_public' => true,
            ],
            [
                'key' => 'admin_url',
                'value' => 'http://admin.cleanestuaire.com',
                'type' => 'string',
                'group' => 'general',
                'description' => 'URL de l\'administration',
                'is_public' => false,
            ],
            [
                'key' => 'app_currency',
                'value' => 'FCFA',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Devise de l\'application',
                'is_public' => true,
            ],
        ];
        
        foreach ($settings as $setting) {
            Settings::create($setting);
        }
    }
    
    private function createAdmins(): array
    {
        $admins = [];
        
        // Super Admin
        $admins[] = Admin::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@formaneo.com',
            'password' => Hash::make('SuperAdmin@2025'),
            'email_verified_at' => now(),
            'role' => 'super_admin',
            'is_active' => true,
            'last_login_at' => now()->subHours(1),
        ]);
        
        // Admin principal
        $admins[] = Admin::create([
            'name' => 'David Admin',
            'email' => 'david.admin@formaneo.com',
            'password' => Hash::make('Admin@2025'),
            'email_verified_at' => now(),
            'role' => 'admin',
            'is_active' => true,
            'last_login_at' => now()->subHours(3),
        ]);
        
        // Content Manager
        $admins[] = Admin::create([
            'name' => 'Content Manager',
            'email' => 'content@formaneo.com',
            'password' => Hash::make('Content@2025'),
            'email_verified_at' => now(),
            'role' => 'content_manager',
            'is_active' => true,
            'last_login_at' => now()->subDays(1),
        ]);
        
        // Support
        $admins[] = Admin::create([
            'name' => 'Support Team',
            'email' => 'support@formaneo.com',
            'password' => Hash::make('Support@2025'),
            'email_verified_at' => now(),
            'role' => 'support',
            'is_active' => true,
            'last_login_at' => now()->subHours(6),
        ]);
        
        return $admins;
    }
    
    private function createAdminUser(): User
    {
        return User::create([
            'name' => 'Admin Formaneo',
            'email' => 'admin@formaneo.com',
            'password' => Hash::make('Admin@2025'),
            'email_verified_at' => now(),
            'promo_code' => 'ADMIN',
            'affiliate_link' => 'http://cleanestuaire.com/invite/ADMIN',
            'balance' => 0.00,
            'available_for_withdrawal' => 0.00,
            'pending_withdrawals' => 0.00,
            'total_affiliates' => 0,
            'monthly_affiliates' => 0,
            'total_commissions' => 0.00,
            'free_quizzes_left' => 5,
            'total_quizzes_taken' => 0,
            'passed_quizzes' => 0,
            'is_active' => true,
            'is_premium' => true,
            'last_login_at' => now(),
            'metadata' => json_encode([
                'role' => 'admin',
                'permissions' => ['all']
            ]),
            'settings' => json_encode([
                'notifications' => true,
                'email_alerts' => true,
                'theme' => 'dark'
            ]),
        ]);
    }
    
    private function createTestUser(): User
    {
        return User::create([
            'name' => 'David K.',
            'email' => 'david@formaneo.com',
            'password' => Hash::make('Test@2025'),
            'email_verified_at' => now(),
            'promo_code' => 'WB001',
            'affiliate_link' => 'http://cleanestuaire.com/invite/WB001',
            'balance' => 115566.00,
            'available_for_withdrawal' => 100000.00,
            'pending_withdrawals' => 0.00,
            'total_affiliates' => 66,
            'monthly_affiliates' => 20,
            'total_commissions' => 115566.00,
            'free_quizzes_left' => 3,
            'total_quizzes_taken' => 7,
            'passed_quizzes' => 5,
            'is_active' => true,
            'is_premium' => false,
            'last_login_at' => now()->subHours(2),
            'metadata' => json_encode([
                'registration_source' => 'direct',
                'last_quiz_date' => now()->subDays(1)->toDateString(),
                'preferred_language' => 'fr'
            ]),
            'settings' => json_encode([
                'notifications' => true,
                'email_alerts' => false,
                'theme' => 'light'
            ]),
        ]);
    }
    
    private function createFormationPacks(): array
    {
        // Pack 1: Dropskills
        $pack1 = FormationPack::create([
            'name' => 'Dropskills - Pack de Formations',
            'slug' => 'dropskills-pack',
            'author' => 'Cédric D.',
            'description' => 'Pack complet de 27 formations sur le dropshipping, e-commerce et marketing digital.',
            'thumbnail_url' => 'http://cleanestuaire.com/storage/thumbnails/dropskills-pack.jpg',
            'price' => 50000.00,
            'total_duration' => 4005,
            'rating' => 4.80,
            'students_count' => 1250,
            'is_active' => true,
            'is_featured' => true,
            'order' => 1,
            'metadata' => json_encode([
                'level' => 'beginner_to_advanced',
                'language' => 'fr',
                'includes_support' => true,
                'certificate' => true,
                'updates' => 'lifetime'
            ]),
        ]);
        
        // Pack 2: Business Mastery
        $pack2 = FormationPack::create([
            'name' => 'Business Mastery - Pack Complet',
            'slug' => 'business-mastery-pack',
            'author' => 'Jonathan G.',
            'description' => '15 formations complètes pour maîtriser le business en ligne.',
            'thumbnail_url' => 'http://cleanestuaire.com/storage/thumbnails/business-mastery-pack.jpg',
            'price' => 45000.00,
            'total_duration' => 6480,
            'rating' => 4.90,
            'students_count' => 890,
            'is_active' => true,
            'is_featured' => true,
            'order' => 2,
            'metadata' => json_encode([
                'level' => 'intermediate_to_expert',
                'language' => 'fr',
                'includes_support' => true,
                'certificate' => true,
                'updates' => 'lifetime'
            ]),
        ]);
        
        // Créer les formations pour le pack 1
        $dropskillsFormations = [
            'Introduction au Dropshipping', 'Analyse de marché avancée', 'Sélection de produits gagnants',
            'Création de boutique Shopify optimisée', 'Facebook Ads Mastery', 'Google Ads pour E-commerce',
            'TikTok Ads Strategy', 'Email Marketing Automation', 'Copywriting pour convertir',
            'Gestion des fournisseurs', 'Service client excellence', 'Optimisation du taux de conversion',
            'Scaling et automatisation', 'Analyse des métriques', 'Gestion financière e-commerce',
            'Branding et storytelling', 'Influencer Marketing', 'SEO pour e-commerce',
            'Retargeting avancé', 'Upsell et cross-sell strategies', 'Gestion des retours et SAV',
            'International dropshipping', 'Print on demand mastery', 'Subscription box business',
            'Mobile commerce optimization', 'Voice commerce trends', 'Sustainable e-commerce'
        ];
        
        foreach ($dropskillsFormations as $index => $title) {
            Formation::create([
                'pack_id' => $pack1->id,
                'title' => $title,
                'description' => 'Formation complète sur ' . strtolower($title),
                'video_url' => "https://mega.nz/folder/xxxxx#xxxxx/formation_" . ($index + 1),
                'duration_minutes' => 90 + ($index * 5),
                'order' => $index + 1,
                'is_active' => true,
            ]);
        }
        
        // Créer les formations pour le pack 2
        $businessFormations = [
            'Dropshipping 2025', 'Affiliation 2025', 'Closing Mastery', 'Google Ads 2025',
            'Coaching HT', 'Meta Ads', 'Personal Branding', 'Tunnels de Vente 2.0',
            'Créer un site Shopify qui convertit', 'Marketing par e-mail', 'Trouver un produit gagnant',
            'Capcut 2025', 'Canva 2025', 'ChatGPT 2025', 'Growth Hacking'
        ];
        
        $durations = [600, 420, 480, 480, 540, 420, 360, 600, 360, 480, 120, 420, 600, 300, 480];
        
        foreach ($businessFormations as $index => $title) {
            Formation::create([
                'pack_id' => $pack2->id,
                'title' => $title,
                'description' => 'Formation complète sur ' . $title,
                'video_url' => "https://mega.nz/folder/yyyyy#yyyyy/formation_" . ($index + 1),
                'duration_minutes' => $durations[$index],
                'order' => $index + 1,
                'is_active' => true,
            ]);
        }
        
        return [$pack1, $pack2];
    }
    
    private function createAffiliates($referrer): array
    {
        $affiliatesData = [
            ['name' => 'Marie K.', 'email' => 'marie-k@example.com', 'balance' => 25686, 'days_ago' => 25],
            ['name' => 'Paul B.', 'email' => 'paul-b@example.com', 'balance' => 48311, 'days_ago' => 7],
            ['name' => 'Sophie L.', 'email' => 'sophie-l@example.com', 'balance' => 12755, 'days_ago' => 28],
            ['name' => 'Thomas R.', 'email' => 'thomas-r@example.com', 'balance' => 43048, 'days_ago' => 10],
            ['name' => 'Emma C.', 'email' => 'emma-c@example.com', 'balance' => 43335, 'days_ago' => 13],
            ['name' => 'Lucas F.', 'email' => 'lucas-f@example.com', 'balance' => 38332, 'days_ago' => 22],
            ['name' => 'Sarah N.', 'email' => 'sarah-n@example.com', 'balance' => 5999, 'days_ago' => 11],
            ['name' => 'Pierre V.', 'email' => 'pierre-v@example.com', 'balance' => 44480, 'days_ago' => 25],
            ['name' => 'Julie M.', 'email' => 'julie-m@example.com', 'balance' => 35735, 'days_ago' => 35],
            ['name' => 'Alexandre D.', 'email' => 'alexandre-d@example.com', 'balance' => 12349, 'days_ago' => 21],
        ];
        
        $affiliates = [];
        
        foreach ($affiliatesData as $data) {
            $promoCode = strtoupper(Str::random(5));
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'email_verified_at' => now()->subDays($data['days_ago']),
                'promo_code' => $promoCode,
                'affiliate_link' => "http://cleanestuaire.com/invite/$promoCode",
                'referred_by' => $referrer->promo_code,
                'balance' => $data['balance'],
                'available_for_withdrawal' => $data['balance'] * 0.8,
                'pending_withdrawals' => 0,
                'total_affiliates' => rand(0, 15),
                'monthly_affiliates' => rand(0, 8),
                'total_commissions' => $data['balance'],
                'free_quizzes_left' => rand(0, 5),
                'total_quizzes_taken' => rand(0, 10),
                'passed_quizzes' => rand(0, 8),
                'is_active' => true,
                'is_premium' => rand(0, 1) ? true : false,
                'last_login_at' => now()->subDays(rand(0, 7)),
                'created_at' => now()->subDays($data['days_ago']),
                'updated_at' => now()->subDays(rand(0, $data['days_ago'])),
                'metadata' => json_encode([
                    'registration_source' => 'affiliate',
                    'referrer_code' => $referrer->promo_code,
                ]),
                'settings' => json_encode([
                    'notifications' => rand(0, 1) ? true : false,
                    'email_alerts' => rand(0, 1) ? true : false,
                    'theme' => rand(0, 1) ? 'light' : 'dark'
                ]),
            ]);
            
            $affiliates[] = $user;
        }
        
        return $affiliates;
    }
    
    private function createUserPacks($testUser, $affiliates, $formationPacks): void
    {
        // L'utilisateur de test a acheté les deux packs
        UserPack::create([
            'user_id' => $testUser->id,
            'pack_id' => $formationPacks[0]->id,
            'purchased_at' => now()->subDays(25),
        ]);
        
        UserPack::create([
            'user_id' => $testUser->id,
            'pack_id' => $formationPacks[1]->id,
            'purchased_at' => now()->subDays(15),
        ]);
        
        // Certains affiliés ont aussi acheté des packs
        $purchasers = array_slice($affiliates, 0, 6);
        foreach ($purchasers as $index => $user) {
            $packIndex = $index % 2;
            UserPack::create([
                'user_id' => $user->id,
                'pack_id' => $formationPacks[$packIndex]->id,
                'purchased_at' => $user->created_at->addDays(rand(1, 5)),
            ]);
        }
    }
    
    private function createFormationModules($formationPacks): void
    {
        foreach ($formationPacks as $pack) {
            $formations = Formation::where('pack_id', $pack->id)->get();
            
            foreach ($formations as $formation) {
                $moduleCount = rand(4, 8);
                
                for ($i = 1; $i <= $moduleCount; $i++) {
                    FormationModule::create([
                        'formation_id' => $formation->id,
                        'title' => $this->generateModuleTitle($i, $moduleCount),
                        'content' => $this->generateModuleContent($formation->title, $i),
                        'video_url' => "https://mega.nz/folder/" . Str::random(10) . "#" . Str::random(10) . "/module_$i",
                        'duration_minutes' => rand(15, 45),
                        'order' => $i,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
    
    private function createFormationProgress($testUser, $affiliates, $formationPacks): void
    {
        // Progrès pour l'utilisateur de test
        $testUserFormations = Formation::whereIn('pack_id', [$formationPacks[0]->id, $formationPacks[1]->id])->get();
        
        foreach ($testUserFormations as $formation) {
            $progress = rand(20, 100);
            $isCompleted = $progress == 100;
            
            FormationProgress::create([
                'user_id' => $testUser->id,
                'formation_id' => $formation->id,
                'progress' => $progress,
                'completed_at' => $isCompleted ? now()->subDays(rand(1, 20)) : null,
                'cashback_claimed_at' => $isCompleted && rand(0, 1) ? now()->subDays(rand(1, 15)) : null,
            ]);
        }
        
        // Progrès pour les affiliés qui ont acheté des packs
        $userPacks = UserPack::with('user')->get();
        foreach ($userPacks as $userPack) {
            if ($userPack->user_id == $testUser->id) continue;
            
            $formations = Formation::where('pack_id', $userPack->pack_id)->limit(rand(2, 8))->get();
            foreach ($formations as $formation) {
                $progress = rand(0, 100);
                $isCompleted = $progress >= 90;
                
                FormationProgress::create([
                    'user_id' => $userPack->user_id,
                    'formation_id' => $formation->id,
                    'progress' => $progress,
                    'completed_at' => $isCompleted ? $userPack->purchased_at->addDays(rand(1, 30)) : null,
                    'cashback_claimed_at' => $isCompleted && rand(0, 1) ? $userPack->purchased_at->addDays(rand(5, 35)) : null,
                ]);
            }
        }
    }
    
    private function createFormationNotes($testUser, $affiliates, $formationPacks): void
    {
        // Notes pour l'utilisateur de test
        $testUserFormations = Formation::whereIn('pack_id', [$formationPacks[0]->id, $formationPacks[1]->id])->limit(10)->get();
        
        foreach ($testUserFormations as $formation) {
            $noteCount = rand(1, 4);
            for ($i = 0; $i < $noteCount; $i++) {
                FormationNote::create([
                    'user_id' => $testUser->id,
                    'formation_id' => $formation->id,
                    'content' => $this->generateNoteContent($formation->title),
                    'timestamp' => $this->generateVideoTimestamp(),
                ]);
            }
        }
        
        // Notes pour quelques affiliés
        $userPacks = UserPack::with('user')->limit(3)->get();
        foreach ($userPacks as $userPack) {
            if ($userPack->user_id == $testUser->id) continue;
            
            $formations = Formation::where('pack_id', $userPack->pack_id)->limit(rand(2, 5))->get();
            foreach ($formations as $formation) {
                if (rand(0, 1)) {
                    FormationNote::create([
                        'user_id' => $userPack->user_id,
                        'formation_id' => $formation->id,
                        'content' => $this->generateNoteContent($formation->title),
                        'timestamp' => $this->generateVideoTimestamp(),
                    ]);
                }
            }
        }
    }
    
    private function createTransactions($testUser, $affiliates): void
    {
        // Transactions pour l'utilisateur de test
        $testTransactions = [
            ['type' => 'welcome_bonus', 'amount' => 1000, 'days_ago' => 30],
            ['type' => 'affiliate_commission', 'amount' => 2000, 'days_ago' => 28],
            ['type' => 'affiliate_commission', 'amount' => 2500, 'days_ago' => 25],
            ['type' => 'quiz_reward', 'amount' => 100, 'days_ago' => 20],
            ['type' => 'affiliate_commission', 'amount' => 2000, 'days_ago' => 18],
            ['type' => 'formation_cashback', 'amount' => 7500, 'days_ago' => 15],
            ['type' => 'affiliate_commission', 'amount' => 2500, 'days_ago' => 12],
            ['type' => 'quiz_reward', 'amount' => 80, 'days_ago' => 10],
            ['type' => 'affiliate_commission', 'amount' => 2000, 'days_ago' => 8],
            ['type' => 'withdrawal', 'amount' => -15000, 'days_ago' => 5],
            ['type' => 'affiliate_commission', 'amount' => 2500, 'days_ago' => 3],
            ['type' => 'bonus_campaign', 'amount' => 5000, 'days_ago' => 1],
        ];
        
        foreach ($testTransactions as $transactionData) {
            Transaction::create([
                'user_id' => $testUser->id,
                'type' => $transactionData['type'],
                'amount' => $transactionData['amount'],
                'description' => $this->getTransactionDescription($transactionData['type']),
                'status' => 'completed',
                'meta' => json_encode([
                    'reference' => 'TXN_' . strtoupper(Str::random(8)),
                    'processing_time' => $transactionData['type'] === 'withdrawal' ? '24h' : 'instant',
                ]),
                'created_at' => now()->subDays($transactionData['days_ago']),
                'updated_at' => now()->subDays($transactionData['days_ago']),
            ]);
        }
        
        // Transactions pour les affiliés
        foreach ($affiliates as $affiliate) {
            $transactionCount = rand(3, 8);
            for ($i = 0; $i < $transactionCount; $i++) {
                $types = ['welcome_bonus', 'affiliate_commission', 'quiz_reward', 'formation_cashback'];
                $type = $types[array_rand($types)];
                $amount = $this->getTransactionAmount($type);
                
                Transaction::create([
                    'user_id' => $affiliate->id,
                    'type' => $type,
                    'amount' => $amount,
                    'description' => $this->getTransactionDescription($type),
                    'status' => 'completed',
                    'meta' => json_encode([
                        'reference' => 'TXN_' . strtoupper(Str::random(8)),
                    ]),
                    'created_at' => $affiliate->created_at->addDays(rand(1, 10)),
                    'updated_at' => $affiliate->created_at->addDays(rand(1, 10)),
                ]);
            }
        }
    }
    
    private function createCommissions($testUser, $affiliates): void
    {
        // Commissions pour l'utilisateur de test
        $commissionCount = 25;
        for ($i = 0; $i < $commissionCount; $i++) {
            Commission::create([
                'user_id' => $testUser->id,
                'amount' => rand(20, 30) * 100,
                'source_type' => 'User',
                'source_id' => $affiliates[array_rand($affiliates)]->id,
                'paid' => rand(0, 1) ? true : false,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(0, 5)),
            ]);
        }
        
        // Commissions pour quelques affiliés
        foreach (array_slice($affiliates, 0, 5) as $affiliate) {
            $commissionCount = rand(1, 5);
            for ($i = 0; $i < $commissionCount; $i++) {
                Commission::create([
                    'user_id' => $affiliate->id,
                    'amount' => rand(15, 25) * 100,
                    'source_type' => 'User',
                    'source_id' => null,
                    'paid' => rand(0, 1) ? true : false,
                    'created_at' => $affiliate->created_at->addDays(rand(5, 20)),
                    'updated_at' => $affiliate->created_at->addDays(rand(5, 25)),
                ]);
            }
        }
    }
    
    private function createAffiliateLinks($testUser, $affiliates): void
    {
        // Lien d'affiliation pour l'utilisateur de test
        AffiliateLink::create([
            'user_id' => $testUser->id,
            'code' => $testUser->promo_code,
            'url' => $testUser->affiliate_link,
            'clicks' => 1250,
            'conversions' => 66,
            'metadata' => json_encode([
                'conversion_rate' => 5.28,
                'total_earnings' => 115566,
                'last_click' => now()->subHours(3)->toDateTimeString(),
                'top_sources' => ['facebook', 'instagram', 'youtube', 'direct'],
                'monthly_stats' => [
                    'clicks' => 120,
                    'conversions' => 20,
                    'earnings' => 50000,
                ]
            ]),
            'created_at' => $testUser->created_at,
            'updated_at' => now(),
        ]);
        
        // Liens d'affiliation pour les affiliés
        foreach ($affiliates as $affiliate) {
            $clicks = rand(10, 500);
            $conversions = rand(0, (int)($clicks * 0.1));
            $conversionRate = $clicks > 0 ? round(($conversions / $clicks) * 100, 2) : 0;
            
            AffiliateLink::create([
                'user_id' => $affiliate->id,
                'code' => $affiliate->promo_code,
                'url' => $affiliate->affiliate_link,
                'clicks' => $clicks,
                'conversions' => $conversions,
                'metadata' => json_encode([
                    'conversion_rate' => $conversionRate,
                    'total_earnings' => $affiliate->balance,
                    'last_click' => now()->subDays(rand(0, 7))->toDateTimeString(),
                    'top_sources' => array_slice(['facebook', 'instagram', 'tiktok', 'youtube', 'whatsapp', 'direct'], 0, rand(2, 4)),
                    'monthly_stats' => [
                        'clicks' => rand(5, 50),
                        'conversions' => rand(0, 10),
                        'earnings' => rand(1000, 20000),
                    ]
                ]),
                'created_at' => $affiliate->created_at,
                'updated_at' => now()->subDays(rand(0, 3)),
            ]);
        }
    }
    
    private function createQuizzes(): array
    {
        $quizzes = [
            [
                'title' => 'Quiz Dropshipping - Niveau Débutant',
                'description' => 'Testez vos connaissances de base sur le dropshipping et gagnez des récompenses.',
                'questions' => json_encode($this->generateQuizQuestions('dropshipping', 'beginner')),
                'difficulty' => 'beginner',
                'subject' => 'dropshipping',
                'questions_count' => 10,
                'passing_score' => 60,
                'reward_per_correct' => 20.00,
                'is_active' => true,
                'metadata' => json_encode([
                    'category' => 'dropshipping',
                    'estimated_time' => 15,
                    'topics' => ['basics', 'suppliers', 'products'],
                ])
            ],
            [
                'title' => 'Quiz Marketing Digital - Intermédiaire',
                'description' => 'Évaluez vos compétences en marketing digital et publicité en ligne.',
                'questions' => json_encode($this->generateQuizQuestions('marketing', 'intermediate')),
                'difficulty' => 'intermediate',
                'subject' => 'marketing',
                'questions_count' => 15,
                'passing_score' => 70,
                'reward_per_correct' => 25.00,
                'is_active' => true,
                'metadata' => json_encode([
                    'category' => 'marketing',
                    'estimated_time' => 20,
                    'topics' => ['facebook_ads', 'google_ads', 'copywriting'],
                ])
            ],
            [
                'title' => 'Quiz E-commerce - Avancé',
                'description' => 'Défi pour les experts en e-commerce et vente en ligne.',
                'questions' => json_encode($this->generateQuizQuestions('ecommerce', 'advanced')),
                'difficulty' => 'advanced',
                'subject' => 'ecommerce',
                'questions_count' => 20,
                'passing_score' => 80,
                'reward_per_correct' => 30.00,
                'is_active' => true,
                'metadata' => json_encode([
                    'category' => 'ecommerce',
                    'estimated_time' => 30,
                    'topics' => ['analytics', 'conversion_optimization', 'automation'],
                ])
            ],
            [
                'title' => 'Quiz Affiliation Marketing',
                'description' => 'Maîtrisez-vous les bases du marketing d\'affiliation ?',
                'questions' => json_encode($this->generateQuizQuestions('affiliation', 'beginner')),
                'difficulty' => 'beginner',
                'subject' => 'affiliation',
                'questions_count' => 12,
                'passing_score' => 65,
                'reward_per_correct' => 22.00,
                'is_active' => true,
                'metadata' => json_encode([
                    'category' => 'affiliation',
                    'estimated_time' => 18,
                    'topics' => ['commissions', 'tracking', 'promotion'],
                ])
            ],
            [
                'title' => 'Quiz Shopify - Configuration',
                'description' => 'Testez vos connaissances sur la création et configuration de boutiques Shopify.',
                'questions' => json_encode($this->generateQuizQuestions('shopify', 'intermediate')),
                'difficulty' => 'intermediate',
                'subject' => 'shopify',
                'questions_count' => 8,
                'passing_score' => 60,
                'reward_per_correct' => 25.00,
                'is_active' => true,
                'metadata' => json_encode([
                    'category' => 'shopify',
                    'estimated_time' => 12,
                    'topics' => ['setup', 'themes', 'apps'],
                ])
            ]
        ];
        
        $createdQuizzes = [];
        foreach ($quizzes as $quizData) {
            $createdQuizzes[] = Quiz::create($quizData);
        }
        
        return $createdQuizzes;
    }
    
    private function createQuizResults($testUser, $affiliates, $quizzes): void
    {
        $subjects = ['dropshipping', 'marketing', 'ecommerce', 'affiliation', 'shopify'];
        
        // Résultats pour l'utilisateur de test (7 quiz pris, 5 réussis)
        for ($i = 0; $i < 7; $i++) {
            $score = $i < 5 ? rand(60, 98) : rand(40, 59);
            $totalQuestions = rand(8, 20);
            $correctAnswers = round(($score / 100) * $totalQuestions);
            
            QuizResult::create([
                'user_id' => $testUser->id,
                'quiz_id' => 'QUIZ_' . strtoupper(Str::random(8)),
                'score' => $score,
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'time_taken' => rand(300, 1800),
                'subject' => $subjects[array_rand($subjects)],
                'created_at' => now()->subDays(rand(1, 30)),
            ]);
        }
        
        // Résultats pour les affiliés
        foreach ($affiliates as $affiliate) {
            $quizCount = rand(0, 8);
            
            for ($i = 0; $i < $quizCount; $i++) {
                $score = rand(45, 95);
                $totalQuestions = rand(8, 15);
                $correctAnswers = round(($score / 100) * $totalQuestions);
                
                QuizResult::create([
                    'user_id' => $affiliate->id,
                    'quiz_id' => 'QUIZ_' . strtoupper(Str::random(8)),
                    'score' => $score,
                    'total_questions' => $totalQuestions,
                    'correct_answers' => $correctAnswers,
                    'time_taken' => rand(240, 2400),
                    'subject' => $subjects[array_rand($subjects)],
                    'created_at' => $affiliate->created_at->addDays(rand(1, 20)),
                ]);
            }
        }
    }
    
    // Méthodes utilitaires
    private function generateModuleTitle($moduleNumber, $totalModules): string
    {
        $titles = [
            1 => 'Introduction et concepts de base',
            2 => 'Configuration et paramétrage',
            3 => 'Stratégies avancées',
            4 => 'Cas pratiques',
            5 => 'Optimisation et scaling',
            6 => 'Analyse des résultats',
            7 => 'Techniques avancées',
            8 => 'Bonus et ressources',
        ];
        
        if ($moduleNumber == $totalModules && $totalModules > 1) {
            return 'Conclusion et prochaines étapes';
        }
        
        return $titles[$moduleNumber] ?? "Module $moduleNumber - Contenu avancé";
    }
    
    private function generateModuleContent($formationTitle, $moduleNumber): string
    {
        $contents = [
            "Dans ce module, nous abordons les concepts fondamentaux de " . strtolower($formationTitle) . ". Vous apprendrez les bases essentielles pour bien commencer.",
            "Nous passons maintenant à la pratique avec des exercices concrets et des exemples réels d'application des stratégies.",
            "Ce module avancé vous donnera toutes les clés pour maîtriser les techniques professionnelles et optimiser vos résultats.",
            "Étude de cas détaillée avec analyse complète des succès et des échecs pour mieux comprendre les enjeux.",
            "Module pratique avec mise en application directe des concepts appris dans les modules précédents.",
            "Techniques avancées et secrets de pros pour passer au niveau supérieur dans votre business.",
            "Conclusion et synthèse de la formation avec plan d'action personnalisé pour votre réussite."
        ];
        
        return $contents[($moduleNumber - 1) % count($contents)];
    }
    
    private function generateNoteContent($formationTitle): string
    {
        $notes = [
            "Point important à retenir sur les stratégies de " . strtolower($formationTitle),
            "Astuce très utile mentionnée par le formateur - à tester rapidement",
            "Outil recommandé: noter le nom et vérifier les tarifs",
            "Question à poser au support - besoin de clarification sur ce point",
            "Excellente idée d'implémentation pour mon projet personnel",
            "Statistique intéressante à garder en mémoire pour mes présentations",
            "Erreur à éviter absolument - bien noté pour ne pas la reproduire",
            "Ressource complémentaire à consulter après cette formation",
        ];
        
        return $notes[array_rand($notes)];
    }
    
    private function generateVideoTimestamp(): string
    {
        $minutes = rand(0, 45);
        $seconds = rand(0, 59);
        return sprintf("%02d:%02d", $minutes, $seconds);
    }
    
    private function generateQuizQuestions($subject, $difficulty): array
    {
        $questionTemplates = [
            'dropshipping' => [
                'beginner' => [
                    [
                        'question' => 'Qu\'est-ce que le dropshipping ?',
                        'options' => [
                            'Un système de vente sans stock',
                            'Une méthode de livraison rapide',
                            'Un type de publicité en ligne',
                            'Une plateforme e-commerce'
                        ],
                        'correct_answer' => 0,
                        'explanation' => 'Le dropshipping est un modèle commercial où le vendeur ne stocke pas les produits.'
                    ],
                    [
                        'question' => 'Quel est l\'avantage principal du dropshipping ?',
                        'options' => [
                            'Marges plus élevées',
                            'Pas besoin de stock initial',
                            'Livraison plus rapide',
                            'Meilleur service client'
                        ],
                        'correct_answer' => 1,
                        'explanation' => 'Le principal avantage est de pouvoir commencer sans investissement en stock.'
                    ]
                ]
            ],
            'marketing' => [
                'intermediate' => [
                    [
                        'question' => 'Que signifie CPM en publicité ?',
                        'options' => [
                            'Coût Par Mille impressions',
                            'Clics Par Minute',
                            'Conversion Par Mois',
                            'Campagne Publicitaire Mobile'
                        ],
                        'correct_answer' => 0,
                        'explanation' => 'CPM signifie Coût Par Mille impressions.'
                    ]
                ]
            ],
            'ecommerce' => [
                'advanced' => [
                    [
                        'question' => 'Qu\'est-ce que le taux de conversion ?',
                        'options' => [
                            'Visiteurs / Ventes',
                            'Ventes / Visiteurs',
                            'Revenus / Coûts',
                            'Clics / Impressions'
                        ],
                        'correct_answer' => 1,
                        'explanation' => 'Le taux de conversion = (Ventes / Visiteurs) × 100'
                    ]
                ]
            ],
            'affiliation' => [
                'beginner' => [
                    [
                        'question' => 'Qu\'est-ce qu\'une commission d\'affiliation ?',
                        'options' => [
                            'Un salaire fixe',
                            'Une rémunération par vente',
                            'Un bonus annuel',
                            'Une réduction produit'
                        ],
                        'correct_answer' => 1,
                        'explanation' => 'Une commission d\'affiliation est une rémunération basée sur les ventes générées.'
                    ]
                ]
            ],
            'shopify' => [
                'intermediate' => [
                    [
                        'question' => 'Quel est le plan Shopify le moins cher ?',
                        'options' => [
                            'Basic Shopify',
                            'Shopify Lite',
                            'Starter',
                            'Shopify Plus'
                        ],
                        'correct_answer' => 1,
                        'explanation' => 'Shopify Lite est l\'option la plus économique pour débuter.'
                    ]
                ]
            ]
        ];
        
        return $questionTemplates[$subject][$difficulty] ?? [
            [
                'question' => 'Question de base sur ' . $subject,
                'options' => ['Option A', 'Option B', 'Option C', 'Option D'],
                'correct_answer' => 0,
                'explanation' => 'Explication de la réponse correcte.'
            ]
        ];
    }
    
    private function getTransactionDescription($type): string
    {
        $descriptions = [
            'welcome_bonus' => 'Bonus de bienvenue pour votre inscription',
            'affiliate_commission' => 'Commission d\'affiliation pour un nouveau membre',
            'quiz_reward' => 'Récompense pour réussite de quiz',
            'formation_cashback' => 'Cashback sur achat de formation',
            'withdrawal' => 'Retrait sur compte mobile money',
            'bonus_campaign' => 'Bonus campagne promotionnelle',
            'referral_bonus' => 'Bonus de parrainage',
        ];
        
        return $descriptions[$type] ?? 'Transaction système';
    }
    
    private function getTransactionAmount($type): int
    {
        $amounts = [
            'welcome_bonus' => 1000,
            'affiliate_commission' => rand(20, 30) * 100,
            'quiz_reward' => rand(4, 10) * 20,
            'formation_cashback' => rand(30, 100) * 100,
            'referral_bonus' => rand(5, 15) * 100,
        ];
        
        return $amounts[$type] ?? rand(100, 1000);
    }
}
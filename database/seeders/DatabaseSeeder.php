<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\FormationPack;
use App\Models\Formation;
use App\Models\Module;
use App\Models\Settings;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Créer les paramètres par défaut
        $this->createSettings();
        
        // Créer l'utilisateur admin
        $admin = User::create([
            'name' => 'Admin Formaneo',
            'email' => 'admin@formaneo.com',
            'password' => Hash::make('Admin@2025'),
            'email_verified_at' => now(),
            'promo_code' => 'ADMIN',
            'affiliate_link' => 'https://formaneo.app/invite/ADMIN',
            'balance' => 0,
            'is_premium' => true,
        ]);
        
        // Créer un utilisateur de test avec des données fictives
        $testUser = User::create([
            'name' => 'Jean Dupont',
            'email' => 'test@formaneo.com',
            'password' => Hash::make('Test@2025'),
            'email_verified_at' => now(),
            'promo_code' => 'WB001',
            'affiliate_link' => 'https://formaneo.app/invite/WB001',
            'balance' => 115566.00,
            'available_for_withdrawal' => 100000.00,
            'total_affiliates' => 66,
            'monthly_affiliates' => 20,
            'total_commissions' => 115566.00,
            'free_quizzes_left' => 5,
        ]);
        
        // Créer les packs de formations
        $this->createFormationPacks();
        
        // Créer des utilisateurs affiliés fictifs
        $this->createAffiliates($testUser);
    }
    
    private function createSettings(): void
    {
        $settings = [
            // Commissions
            [
                'key' => 'affiliate_commission_basic',
                'value' => '2000',
                'type' => 'float',
                'group' => 'affiliate',
                'description' => 'Commission de base pour 0-100 affiliés/mois (FCFA)',
            ],
            [
                'key' => 'affiliate_commission_premium',
                'value' => '2500',
                'type' => 'float',
                'group' => 'affiliate',
                'description' => 'Commission premium pour >100 affiliés/mois (FCFA)',
            ],
            [
                'key' => 'affiliate_threshold',
                'value' => '100',
                'type' => 'integer',
                'group' => 'affiliate',
                'description' => 'Seuil pour passer en commission premium',
            ],
            
            // Bonus
            [
                'key' => 'welcome_bonus',
                'value' => '1000',
                'type' => 'float',
                'group' => 'bonus',
                'description' => 'Bonus de bienvenue (FCFA)',
            ],
            [
                'key' => 'formation_cashback_rate',
                'value' => '0.15',
                'type' => 'float',
                'group' => 'formation',
                'description' => 'Taux de cashback sur les formations (15%)',
            ],
            
            // Quiz
            [
                'key' => 'free_quizzes_per_user',
                'value' => '5',
                'type' => 'integer',
                'group' => 'quiz',
                'description' => 'Nombre de quiz gratuits par utilisateur',
            ],
            [
                'key' => 'quiz_reward_per_correct',
                'value' => '20',
                'type' => 'float',
                'group' => 'quiz',
                'description' => 'Récompense par bonne réponse (FCFA)',
            ],
            [
                'key' => 'quiz_passing_score',
                'value' => '60',
                'type' => 'integer',
                'group' => 'quiz',
                'description' => 'Score minimum pour réussir un quiz (%)',
            ],
            
            // Retraits
            [
                'key' => 'min_withdrawal_amount',
                'value' => '1000',
                'type' => 'float',
                'group' => 'withdrawal',
                'description' => 'Montant minimum de retrait (FCFA)',
            ],
            [
                'key' => 'max_withdrawal_amount',
                'value' => '1000000',
                'type' => 'float',
                'group' => 'withdrawal',
                'description' => 'Montant maximum de retrait (FCFA)',
            ],
            
            // Mega Storage
            [
                'key' => 'mega_api_key',
                'value' => 'YOUR_MEGA_API_KEY',
                'type' => 'string',
                'group' => 'storage',
                'description' => 'Clé API Mega pour le stockage',
            ],
            [
                'key' => 'mega_folder_path',
                'value' => '/Formaneo',
                'type' => 'string',
                'group' => 'storage',
                'description' => 'Dossier Mega pour les formations',
            ],
        ];
        
        foreach ($settings as $setting) {
            Settings::create($setting);
        }
    }
    
    private function createFormationPacks(): void
    {
        // Pack 1: Dropskills
        $pack1 = FormationPack::create([
            'name' => 'Dropskills - Pack de Formations',
            'slug' => 'dropskills-pack',
            'author' => 'Cédric D.',
            'description' => 'Pack complet de 27 formations sur le dropshipping, e-commerce et marketing digital. Apprenez les stratégies qui fonctionnent vraiment.',
            'price' => 50000.00,
            'total_duration' => 2400,
            'rating' => 4.8,
            'students_count' => 1250,
            'is_featured' => true,
        ]);
        
        // Créer les 27 formations du pack Dropskills
        $dropskillsTitles = [
            'Introduction au Dropshipping',
            'Analyse de marché avancée',
            'Sélection de produits gagnants',
            'Création de boutique Shopify optimisée',
            'Facebook Ads Mastery',
            'Google Ads pour E-commerce',
            'TikTok Ads Strategy',
            'Email Marketing Automation',
            'Copywriting pour convertir',
            'Gestion des fournisseurs',
            'Service client excellence',
            'Optimisation du taux de conversion',
            'Scaling et automatisation',
            'Analyse des métriques',
            'Gestion financière e-commerce',
            'Branding et storytelling',
            'Influencer Marketing',
            'SEO pour e-commerce',
            'Retargeting avancé',
            'Upsell et cross-sell strategies',
            'Gestion des retours et SAV',
            'International dropshipping',
            'Print on demand mastery',
            'Subscription box business',
            'Mobile commerce optimization',
            'Voice commerce trends',
            'Sustainable e-commerce',
        ];
        
        foreach ($dropskillsTitles as $index => $title) {
            $formation = Formation::create([
                'pack_id' => $pack1->id,
                'title' => $title,
                'description' => "Formation complète sur " . strtolower($title),
                'duration' => 90 + ($index * 5),
                'video_url' => "https://mega.nz/folder/xxxxx#xxxxx/formation_" . ($index + 1),
                'order' => $index + 1,
            ]);
            
            // Créer les modules pour chaque formation
            $moduleCount = 5 + ($index % 3);
            for ($m = 1; $m <= $moduleCount; $m++) {
                Module::create([
                    'formation_id' => $formation->id,
                    'title' => "Module $m - " . $this->generateModuleTitle($title, $m),
                    'duration' => 30 + ($m * 5),
                    'video_url' => "https://mega.nz/folder/xxxxx#xxxxx/module_$m",
                    'order' => $m,
                ]);
            }
        }
        
        // Pack 2: Business Mastery
        $pack2 = FormationPack::create([
            'name' => 'Business Mastery - Pack Complet',
            'slug' => 'business-mastery-pack',
            'author' => 'Jonathan G.',
            'description' => '15 formations complètes pour maîtriser le business en ligne : dropshipping, affiliation, closing, publicité, coaching et plus.',
            'price' => 45000.00,
            'total_duration' => 1800,
            'rating' => 4.9,
            'students_count' => 890,
            'is_featured' => true,
        ]);
        
        // Créer les 15 formations du pack Business Mastery
        $businessFormations = [
            ['title' => 'Dropshipping 2025', 'modules' => 10],
            ['title' => 'Affiliation 2025', 'modules' => 7],
            ['title' => 'Closing Mastery', 'modules' => 8],
            ['title' => 'Google Ads 2025', 'modules' => 8],
            ['title' => 'Coaching HT', 'modules' => 9],
            ['title' => 'Meta Ads', 'modules' => 7],
            ['title' => 'Personal Branding', 'modules' => 6],
            ['title' => 'Tunnels de Vente 2.0', 'modules' => 10],
            ['title' => 'Créer un site Shopify qui convertit', 'modules' => 6],
            ['title' => 'Marketing par e-mail', 'modules' => 8],
            ['title' => 'Trouver un produit gagnant', 'modules' => 2],
            ['title' => 'Capcut 2025', 'modules' => 7],
            ['title' => 'Canva 2025', 'modules' => 10],
            ['title' => 'ChatGPT 2025', 'modules' => 5],
            ['title' => 'Growth Hacking', 'modules' => 8],
        ];
        
        foreach ($businessFormations as $index => $formData) {
            $formation = Formation::create([
                'pack_id' => $pack2->id,
                'title' => $formData['title'],
                'description' => "Formation complète sur " . $formData['title'],
                'duration' => $formData['modules'] * 60,
                'video_url' => "https://mega.nz/folder/yyyyy#yyyyy/formation_" . ($index + 1),
                'order' => $index + 1,
            ]);
            
            // Créer les modules
            for ($m = 1; $m <= $formData['modules']; $m++) {
                Module::create([
                    'formation_id' => $formation->id,
                    'title' => "Module $m",
                    'duration' => 30 + ($m * 5),
                    'video_url' => "https://mega.nz/folder/yyyyy#yyyyy/module_$m",
                    'order' => $m,
                ]);
            }
        }
    }
    
    private function createAffiliates($referrer): void
    {
        $names = [
            'Marie K.', 'Paul B.', 'Sophie L.', 'Thomas R.', 'Emma C.',
            'Lucas F.', 'Sarah N.', 'Pierre V.', 'Julie M.', 'Alexandre D.'
        ];
        
        foreach ($names as $index => $name) {
            User::create([
                'name' => $name,
                'email' => Str::slug($name) . '@example.com',
                'password' => Hash::make('password'),
                'promo_code' => strtoupper(Str::random(5)),
                'affiliate_link' => 'https://formaneo.app/invite/' . strtoupper(Str::random(5)),
                'referred_by' => $referrer->promo_code,
                'balance' => rand(1000, 50000),
                'created_at' => now()->subDays(rand(1, 30)),
            ]);
        }
    }
    
    private function generateModuleTitle($formationTitle, $moduleNumber): string
    {
        $modules = [
            1 => 'Introduction et concepts de base',
            2 => 'Configuration et paramétrage',
            3 => 'Stratégies avancées',
            4 => 'Cas pratiques',
            5 => 'Optimisation et scaling',
            6 => 'Analyse des résultats',
            7 => 'Techniques avancées',
            8 => 'Bonus et ressources',
        ];
        
        return $modules[$moduleNumber] ?? "Contenu avancé";
    }
}
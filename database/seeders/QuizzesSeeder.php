<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Quiz;

class QuizzesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dropshipping Quizzes
        $this->createDropshippingQuizzes();
        
        // Marketing Digital Quizzes
        $this->createMarketingQuizzes();
        
        // E-commerce Quizzes
        $this->createEcommerceQuizzes();
        
        // Affiliation Marketing Quizzes
        $this->createAffiliationQuizzes();
        
        // Shopify Quizzes
        $this->createShopifyQuizzes();
        
        // Créativité & Design Quizzes
        $this->createDesignQuizzes();
        
        // Finance & Business Quizzes
        $this->createFinanceQuizzes();
        
        // Réseaux Sociaux Quizzes
        $this->createSocialMediaQuizzes();
        
        // Entrepreneuriat Quizzes
        $this->createEntrepreneurshipQuizzes();
        
        // Publicité Quizzes
        $this->createAdvertisingQuizzes();
    }

    private function createDropshippingQuizzes()
    {
        // Dropshipping Débutant
        Quiz::create([
            'title' => 'Quiz Dropshipping - Niveau Débutant',
            'description' => 'Testez vos connaissances de base sur le dropshipping et gagnez des FCFA !',
            'subject' => 'dropshipping',
            'difficulty' => 'Beginner',
            'questions_count' => 10,
            'is_active' => true,
            'questions' => [
                [
                    'question' => 'Qu\'est-ce que le dropshipping ?',
                    'options' => [
                        'Un modèle où vous stockez les produits avant de les vendre',
                        'Un modèle où le fournisseur expédie directement au client',
                        'Un système de livraison express',
                        'Une méthode de marketing'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Quel est le principal avantage du dropshipping ?',
                    'options' => [
                        'Profits plus élevés',
                        'Contrôle total de la qualité',
                        'Investissement initial faible',
                        'Livraison plus rapide'
                    ],
                    'correctAnswer' => 2
                ],
                [
                    'question' => 'Quelle plateforme est populaire pour le dropshipping ?',
                    'options' => [
                        'Facebook',
                        'Instagram',
                        'Shopify',
                        'LinkedIn'
                    ],
                    'correctAnswer' => 2
                ],
                [
                    'question' => 'Que signifie "POD" dans le dropshipping ?',
                    'options' => [
                        'Product on Demand',
                        'Print on Demand',
                        'Price on Delivery',
                        'Payment on Delivery'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Quel est un défi majeur du dropshipping ?',
                    'options' => [
                        'Trop de profits',
                        'Marges bénéficiaires faibles',
                        'Trop de contrôle',
                        'Investissement élevé'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Combien faut-il généralement pour démarrer en dropshipping ?',
                    'options' => [
                        'Plus de 10,000€',
                        'Entre 5,000€ et 10,000€',
                        'Entre 100€ et 1,000€',
                        'Plus de 50,000€'
                    ],
                    'correctAnswer' => 2
                ],
                [
                    'question' => 'Quel pays est leader dans la fourniture dropshipping ?',
                    'options' => [
                        'États-Unis',
                        'Allemagne',
                        'Chine',
                        'France'
                    ],
                    'correctAnswer' => 2
                ],
                [
                    'question' => 'Qu\'est-ce qu\'AliExpress dans le dropshipping ?',
                    'options' => [
                        'Une plateforme de vente',
                        'Un service de livraison',
                        'Une marketplace de fournisseurs',
                        'Un outil de marketing'
                    ],
                    'correctAnswer' => 2
                ],
                [
                    'question' => 'Quelle est la commission moyenne en dropshipping ?',
                    'options' => [
                        '50-80%',
                        '10-30%',
                        '5-10%',
                        '80-95%'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Comment trouve-t-on des produits gagnants en dropshipping ?',
                    'options' => [
                        'En copiant la concurrence uniquement',
                        'En recherchant les tendances et en testant',
                        'En choisissant au hasard',
                        'En suivant son intuition seulement'
                    ],
                    'correctAnswer' => 1
                ]
            ]
        ]);

        // Je vais continuer avec une version plus courte pour ne pas dépasser la limite
        
        // Dropshipping Intermédiaire
        Quiz::create([
            'title' => 'Quiz Dropshipping - Niveau Intermédiaire',
            'description' => 'Approfondissez vos connaissances en dropshipping avec des questions plus avancées.',
            'subject' => 'dropshipping',
            'difficulty' => 'Intermediate',
            'questions_count' => 8,
            'is_active' => true,
            'questions' => [
                [
                    'question' => 'Qu\'est-ce que le "product research" en dropshipping ?',
                    'options' => [
                        'Rechercher des clients potentiels',
                        'Analyser et identifier des produits rentables',
                        'Étudier la concurrence seulement',
                        'Optimiser les prix'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que le "split testing" en dropshipping ?',
                    'options' => [
                        'Diviser son budget publicitaire',
                        'Tester différentes versions d\'annonces/pages',
                        'Séparer ses fournisseurs',
                        'Partager ses profits'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Quel est le taux de conversion moyen d\'une boutique dropshipping ?',
                    'options' => [
                        '10-15%',
                        '1-3%',
                        '20-30%',
                        '0.5-1%'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce qu\'un "winning product" ?',
                    'options' => [
                        'Un produit cher',
                        'Un produit avec forte demande et bonnes marges',
                        'Un produit populaire uniquement',
                        'Un produit de marque'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que le "retargeting" en dropshipping ?',
                    'options' => [
                        'Cibler de nouveaux clients',
                        'Re-cibler les visiteurs qui n\'ont pas acheté',
                        'Changer de cible démographique',
                        'Cibler la concurrence'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que le "scaling" en dropshipping ?',
                    'options' => [
                        'Réduire ses dépenses',
                        'Augmenter son budget pour maximiser profits',
                        'Changer de niche',
                        'Arrêter la publicité'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que le "chargeback" en dropshipping ?',
                    'options' => [
                        'Remboursement volontaire',
                        'Annulation forcée de paiement par la banque',
                        'Commission du fournisseur',
                        'Frais de livraison'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Quel pourcentage du CA investir en publicité généralement ?',
                    'options' => [
                        '5-10%',
                        '20-40%',
                        '50-70%',
                        '80-90%'
                    ],
                    'correctAnswer' => 1
                ]
            ]
        ]);
    }

    private function createMarketingQuizzes()
    {
        Quiz::create([
            'title' => 'Quiz Marketing Digital - Fondamentaux',
            'description' => 'Maîtrisez les bases du marketing digital et de la publicité en ligne.',
            'subject' => 'marketing',
            'difficulty' => 'Beginner',
            'questions_count' => 8,
            'is_active' => true,
            'questions' => [
                [
                    'question' => 'Que signifie CTR en marketing digital ?',
                    'options' => [
                        'Cost to Revenue',
                        'Click Through Rate',
                        'Customer Traffic Rate',
                        'Conversion Target Rate'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que le SEO ?',
                    'options' => [
                        'Social Engagement Optimization',
                        'Search Engine Optimization',
                        'Sales Enhancement Operation',
                        'Site Efficiency Optimization'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce qu\'un lead en marketing ?',
                    'options' => [
                        'Un client fidèle',
                        'Un prospect intéressé par vos produits',
                        'Un concurrent',
                        'Un partenaire commercial'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Que signifie CPC en publicité ?',
                    'options' => [
                        'Cost Per Click',
                        'Customer Per Campaign',
                        'Conversion Per Click',
                        'Cost Per Customer'
                    ],
                    'correctAnswer' => 0
                ],
                [
                    'question' => 'Qu\'est-ce qu\'un persona en marketing ?',
                    'options' => [
                        'Une vraie personne',
                        'Un profil fictif représentant votre client idéal',
                        'Un employé',
                        'Un influenceur'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Que signifie ROI en marketing ?',
                    'options' => [
                        'Return on Investment',
                        'Rate of Interest',
                        'Revenue Operations Index',
                        'Reach and Impressions'
                    ],
                    'correctAnswer' => 0
                ],
                [
                    'question' => 'Qu\'est-ce qu\'une landing page ?',
                    'options' => [
                        'Page d\'accueil',
                        'Page dédiée à une campagne spécifique',
                        'Page de contact',
                        'Page de produits'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que le retargeting ?',
                    'options' => [
                        'Changer de cible',
                        'Re-cibler les visiteurs qui n\'ont pas converti',
                        'Viser une nouvelle démographie',
                        'Analyser la concurrence'
                    ],
                    'correctAnswer' => 1
                ]
            ]
        ]);

        Quiz::create([
            'title' => 'Quiz Facebook & Instagram Ads',
            'description' => 'Maîtrisez la publicité sur les plateformes Meta (Facebook & Instagram).',
            'subject' => 'marketing',
            'difficulty' => 'Intermediate',
            'questions_count' => 10,
            'is_active' => true,
            'questions' => [
                [
                    'question' => 'Qu\'est-ce que le Facebook Pixel ?',
                    'options' => [
                        'Une image Facebook',
                        'Code de suivi pour mesurer conversions',
                        'Unité de mesure publicitaire',
                        'Format d\'image recommandé'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Quel objectif choisir pour générer des ventes ?',
                    'options' => [
                        'Trafic',
                        'Notoriété',
                        'Conversions',
                        'Engagement'
                    ],
                    'correctAnswer' => 2
                ],
                [
                    'question' => 'Qu\'est-ce qu\'une audience similaire (Lookalike) ?',
                    'options' => [
                        'Audience identique',
                        'Audience avec caractéristiques similaires à vos clients',
                        'Audience concurrent',
                        'Audience géographique'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que le CPM en Facebook Ads ?',
                    'options' => [
                        'Cost Per Million',
                        'Cost Per Mille (1000 impressions)',
                        'Cost Per Message',
                        'Cost Per Month'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Combien de temps laisser pour l\'apprentissage d\'une campagne ?',
                    'options' => [
                        '24 heures',
                        '3-7 jours',
                        '1 heure',
                        '30 jours'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que l\'A/B testing en Facebook Ads ?',
                    'options' => [
                        'Tester deux versions pour voir laquelle performe mieux',
                        'Alterner entre deux campagnes',
                        'Comparer avec Google Ads',
                        'Tester deux budgets'
                    ],
                    'correctAnswer' => 0
                ],
                [
                    'question' => 'Qu\'est-ce que la fatigue publicitaire ?',
                    'options' => [
                        'Épuisement du budget',
                        'Baisse performance car audience a vu trop la pub',
                        'Lassitude du créateur',
                        'Limite de diffusion'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Quel placement privilégier pour commencer ?',
                    'options' => [
                        'Instagram Stories seulement',
                        'Placements automatiques',
                        'Facebook Feed seulement',
                        'Audience Network seulement'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Comment optimiser une campagne qui ne convertit pas ?',
                    'options' => [
                        'Augmenter le budget',
                        'Analyser audience, créatifs et landing page',
                        'Changer d\'objectif',
                        'Arrêter immédiatement'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Quelle est la règle des 20% de texte sur image ?',
                    'options' => [
                        'Mettre 20% de budget sur images',
                        'Maximum 20% de texte sur les visuels publicitaires',
                        '20% de réduction obligatoire',
                        'Publier 20% du temps'
                    ],
                    'correctAnswer' => 1
                ]
            ]
        ]);
    }

    private function createEcommerceQuizzes()
    {
        Quiz::create([
            'title' => 'Quiz E-commerce - Fondamentaux',
            'description' => 'Apprenez les bases de la vente en ligne et de la gestion d\'une boutique e-commerce.',
            'subject' => 'ecommerce',
            'difficulty' => 'Beginner',
            'questions_count' => 8,
            'is_active' => true,
            'questions' => [
                [
                    'question' => 'Qu\'est-ce que le taux de conversion en e-commerce ?',
                    'options' => [
                        'Taux de visiteurs qui effectuent un achat',
                        'Taux de retour produits',
                        'Taux de satisfaction',
                        'Taux de remboursement'
                    ],
                    'correctAnswer' => 0
                ],
                [
                    'question' => 'Qu\'est-ce que l\'abandon de panier ?',
                    'options' => [
                        'Panier volé',
                        'Client ajoute produits mais n\'achète pas',
                        'Panier vide',
                        'Erreur de commande'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Quel pourcentage moyen d\'abandon de panier ?',
                    'options' => [
                        '25-35%',
                        '50-70%',
                        '10-20%',
                        '80-90%'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que l\'UX en e-commerce ?',
                    'options' => [
                        'Unix Extension',
                        'User Experience (expérience utilisateur)',
                        'Ultra eXpress',
                        'Unified eXchange'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que le cross-selling ?',
                    'options' => [
                        'Vendre à l\'étranger',
                        'Proposer produits complémentaires',
                        'Vendre en croix',
                        'Annuler une vente'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que l\'upselling ?',
                    'options' => [
                        'Vendre plus cher',
                        'Proposer une version supérieure du produit',
                        'Vendre en gros',
                        'Augmenter les prix'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Quel est l\'avantage du responsive design ?',
                    'options' => [
                        'Design coloré',
                        'Adaptation à tous les écrans (mobile, tablette, PC)',
                        'Chargement rapide',
                        'Design moderne'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que le remarketing en e-commerce ?',
                    'options' => [
                        'Nouveau marketing',
                        'Re-cibler visiteurs pour les faire revenir',
                        'Marketing de niche',
                        'Marketing direct'
                    ],
                    'correctAnswer' => 1
                ]
            ]
        ]);
    }

    private function createAffiliationQuizzes()
    {
        Quiz::create([
            'title' => 'Quiz Marketing d\'Affiliation - Débutant',
            'description' => 'Maîtrisez-vous les bases du marketing d\'affiliation et de la monétisation ?',
            'subject' => 'affiliation',
            'difficulty' => 'Beginner',
            'questions_count' => 8,
            'is_active' => true,
            'questions' => [
                [
                    'question' => 'Qu\'est-ce que le marketing d\'affiliation ?',
                    'options' => [
                        'Vendre ses propres produits',
                        'Promouvoir les produits d\'autres contre commission',
                        'Créer une entreprise',
                        'Faire de la publicité'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce qu\'un lien d\'affiliation ?',
                    'options' => [
                        'Lien normal',
                        'Lien unique qui track vos ventes pour commission',
                        'Lien cassé',
                        'Lien social'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce qu\'une commission en affiliation ?',
                    'options' => [
                        'Frais à payer',
                        'Pourcentage ou montant gagné par vente générée',
                        'Taxe gouvernementale',
                        'Coût publicitaire'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Quel est le plus grand réseau d\'affiliation au monde ?',
                    'options' => [
                        'Google',
                        'Amazon Associates',
                        'Facebook',
                        'YouTube'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce qu\'un cookie en affiliation ?',
                    'options' => [
                        'Gâteau',
                        'Traceur pour attribuer ventes pendant période donnée',
                        'Type de commission',
                        'Produit alimentaire'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que l\'EPC en affiliation ?',
                    'options' => [
                        'Earnings Per Click (gains par clic)',
                        'European Payment Council',
                        'Electronic Product Code',
                        'Email Per Customer'
                    ],
                    'correctAnswer' => 0
                ],
                [
                    'question' => 'Qu\'est-ce que la divulgation en affiliation ?',
                    'options' => [
                        'Garder secrets ses liens',
                        'Informer audience qu\'on gagne commission',
                        'Cacher ses gains',
                        'Partager mot de passe'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce qu\'un super affilié ?',
                    'options' => [
                        'Héros',
                        'Affilié générant gros volumes ventes',
                        'Débutant en affiliation',
                        'Robot automatique'
                    ],
                    'correctAnswer' => 1
                ]
            ]
        ]);
    }

    private function createShopifyQuizzes()
    {
        Quiz::create([
            'title' => 'Quiz Shopify - Configuration & Basics',
            'description' => 'Testez vos connaissances sur la création et configuration de boutiques Shopify.',
            'subject' => 'shopify',
            'difficulty' => 'Beginner',
            'questions_count' => 8,
            'is_active' => true,
            'questions' => [
                [
                    'question' => 'Qu\'est-ce que Shopify ?',
                    'options' => [
                        'Réseau social',
                        'Plateforme e-commerce en SaaS',
                        'Application mobile',
                        'Fournisseur de produits'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce qu\'un thème Shopify ?',
                    'options' => [
                        'Sujet de discussion',
                        'Template design pour votre boutique',
                        'Produit à vendre',
                        'Mode de paiement'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que l\'App Store Shopify ?',
                    'options' => [
                        'Magasin Apple',
                        'Marketplace d\'applications pour étendre fonctionnalités',
                        'Boutique de thèmes',
                        'Service client'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Comment ajouter des produits sur Shopify ?',
                    'options' => [
                        'Admin Panel > Products > Add product',
                        'Impossible d\'ajouter',
                        'Seulement par CSV',
                        'Contact support'
                    ],
                    'correctAnswer' => 0
                ],
                [
                    'question' => 'Qu\'est-ce qu\'une collection Shopify ?',
                    'options' => [
                        'Groupe de produits organisés',
                        'Collection d\'art',
                        'Factures groupées',
                        'Clients VIP'
                    ],
                    'correctAnswer' => 0
                ],
                [
                    'question' => 'Qu\'est-ce que Shopify Liquid ?',
                    'options' => [
                        'Boisson',
                        'Langage de template pour personnaliser thèmes',
                        'Mode de paiement',
                        'Type de produit'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Comment configurer les frais de livraison ?',
                    'options' => [
                        'Settings > Shipping and delivery',
                        'Impossible de configurer',
                        'Toujours gratuit',
                        'Contact client'
                    ],
                    'correctAnswer' => 0
                ],
                [
                    'question' => 'Qu\'est-ce que le SEO sur Shopify ?',
                    'options' => [
                        'Service à la clientèle',
                        'Optimisation pour moteurs de recherche',
                        'Système de commande',
                        'Support technique'
                    ],
                    'correctAnswer' => 1
                ]
            ]
        ]);
    }

    private function createDesignQuizzes()
    {
        Quiz::create([
            'title' => 'Quiz Design & Créativité Digital',
            'description' => 'Testez vos connaissances en design graphique et création de contenu.',
            'subject' => 'design',
            'difficulty' => 'Intermediate',
            'questions_count' => 6,
            'is_active' => true,
            'questions' => [
                [
                    'question' => 'Qu\'est-ce que la règle des tiers en design ?',
                    'options' => [
                        'Diviser en 3 parties égales',
                        'Grille 3x3 pour placement éléments',
                        '3 couleurs maximum',
                        '3 polices maximum'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que l\'UX Design ?',
                    'options' => [
                        'Ultra eXtreme Design',
                        'User Experience Design',
                        'Unique eXperience Design',
                        'Universal eXport Design'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Quelle est la différence entre RGB et CMYK ?',
                    'options' => [
                        'Aucune différence',
                        'RGB écran, CMYK impression',
                        'RGB impression, CMYK écran',
                        'RGB professionnel, CMYK amateur'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que le responsive design ?',
                    'options' => [
                        'Design qui répond vite',
                        'Adaptation à différentes tailles d\'écran',
                        'Design interactif',
                        'Design moderne'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que la hiérarchie visuelle ?',
                    'options' => [
                        'Ordre d\'importance des éléments visuels',
                        'Classement des designers',
                        'Ordre alphabétique',
                        'Taille des fichiers'
                    ],
                    'correctAnswer' => 0
                ],
                [
                    'question' => 'Qu\'est-ce que le white space en design ?',
                    'options' => [
                        'Espace blanc obligatoire',
                        'Espace vide pour aérer et guider regard',
                        'Erreur de design',
                        'Espace à remplir'
                    ],
                    'correctAnswer' => 1
                ]
            ]
        ]);
    }

    private function createFinanceQuizzes()
    {
        Quiz::create([
            'title' => 'Quiz Finance & Business Management',
            'description' => 'Maîtrisez les bases de la gestion financière et du business en ligne.',
            'subject' => 'finance',
            'difficulty' => 'Intermediate',
            'questions_count' => 6,
            'is_active' => true,
            'questions' => [
                [
                    'question' => 'Qu\'est-ce que le cash flow ?',
                    'options' => [
                        'Flux de visiteurs',
                        'Flux de trésorerie (entrées - sorties argent)',
                        'Vitesse internet',
                        'Processus de vente'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Comment calculer la marge bénéficiaire ?',
                    'options' => [
                        '(Profit / Chiffre d\'affaires) × 100',
                        'Ventes - Coûts',
                        'Revenus totaux',
                        'Prix de vente'
                    ],
                    'correctAnswer' => 0
                ],
                [
                    'question' => 'Qu\'est-ce que le ROI (Return on Investment) ?',
                    'options' => [
                        '(Gain - Coût investissement) / Coût × 100',
                        'Retour à l\'origine',
                        'Revenus totaux',
                        'Bénéfices nets'
                    ],
                    'correctAnswer' => 0
                ],
                [
                    'question' => 'Qu\'est-ce qu\'un business plan ?',
                    'options' => [
                        'Plan de la ville',
                        'Document détaillant stratégie et projections business',
                        'Planning personnel',
                        'Plan marketing seulement'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que le seuil de rentabilité ?',
                    'options' => [
                        'Prix maximum',
                        'Point où revenus égalent coûts totaux',
                        'Profit maximum',
                        'Ventes minimales'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Comment gérer la comptabilité e-commerce ?',
                    'options' => [
                        'Ignorer complètement',
                        'Logiciel comptable + expert-comptable si besoin',
                        'Excel seulement',
                        'Calculatrice'
                    ],
                    'correctAnswer' => 1
                ]
            ]
        ]);
    }

    private function createSocialMediaQuizzes()
    {
        Quiz::create([
            'title' => 'Quiz Réseaux Sociaux & Community Management',
            'description' => 'Maîtrisez les stratégies des réseaux sociaux pour développer votre business.',
            'subject' => 'social_media',
            'difficulty' => 'Intermediate',
            'questions_count' => 8,
            'is_active' => true,
            'questions' => [
                [
                    'question' => 'Qu\'est-ce que l\'engagement rate sur les réseaux sociaux ?',
                    'options' => [
                        'Nombre de followers',
                        '(Likes + commentaires + partages) / Portée × 100',
                        'Temps passé sur page',
                        'Nombre de posts'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Quel est le meilleur moment pour poster sur Instagram ?',
                    'options' => [
                        'Minuit',
                        'Dépend de votre audience et analytics',
                        'Toujours 12h',
                        'Le weekend seulement'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce qu\'un hashtag efficace ?',
                    'options' => [
                        'Le plus populaire',
                        'Mix de populaires, moyens et niches pertinents',
                        'Inventé aléatoirement',
                        'Très long'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que l\'UGC (User Generated Content) ?',
                    'options' => [
                        'Contenu créé par les utilisateurs',
                        'Contenu de l\'entreprise',
                        'Publicité payante',
                        'Contenu copié'
                    ],
                    'correctAnswer' => 0
                ],
                [
                    'question' => 'Comment créer une communauté engagée ?',
                    'options' => [
                        'Acheter des followers',
                        'Contenu de valeur + interaction + constance',
                        'Spammer',
                        'Copier les autres'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que le social listening ?',
                    'options' => [
                        'Écouter de la musique',
                        'Surveiller mentions de marque et conversations',
                        'Écouter podcasts',
                        'Audio des vidéos'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Comment mesurer le ROI des réseaux sociaux ?',
                    'options' => [
                        'Likes seulement',
                        'Conversions + ventes attribuées / Coût investissement',
                        'Followers',
                        'Commentaires'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Comment gérer une crise sur les réseaux sociaux ?',
                    'options' => [
                        'Ignorer complètement',
                        'Réponse rapide, transparente et empathique',
                        'Supprimer tous les commentaires',
                        'Blâmer les clients'
                    ],
                    'correctAnswer' => 1
                ]
            ]
        ]);
    }

    private function createEntrepreneurshipQuizzes()
    {
        Quiz::create([
            'title' => 'Quiz Entrepreneuriat & Mindset Business',
            'description' => 'Développez votre esprit entrepreneurial et vos compétences business.',
            'subject' => 'entrepreneurship',
            'difficulty' => 'Intermediate',
            'questions_count' => 8,
            'is_active' => true,
            'questions' => [
                [
                    'question' => 'Qu\'est-ce qu\'un MVP (Minimum Viable Product) ?',
                    'options' => [
                        'Most Valuable Player',
                        'Version basique produit pour tester marché',
                        'Maximum Viable Product',
                        'Most Visited Page'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que la méthode Lean Startup ?',
                    'options' => [
                        'Construire-Mesurer-Apprendre rapidement',
                        'Démarrage sans argent',
                        'Startup en bonne santé',
                        'Équipe réduite'
                    ],
                    'correctAnswer' => 0
                ],
                [
                    'question' => 'Comment valider une idée business ?',
                    'options' => [
                        'Demander à la famille',
                        'Recherche marché + interviews clients + tests',
                        'Intuition seulement',
                        'Copier la concurrence'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que le networking en entrepreneuriat ?',
                    'options' => [
                        'Réseau internet',
                        'Construire réseau professionnel et relations',
                        'Marketing en ligne',
                        'Vente directe'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Comment gérer l\'échec en entrepreneuriat ?',
                    'options' => [
                        'Abandonner définitivement',
                        'Analyser, apprendre, adapter et persévérer',
                        'Blâmer les autres',
                        'Ignorer l\'échec'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que le bootstrapping ?',
                    'options' => [
                        'Type de chaussures',
                        'Autofinancer son business sans investisseurs',
                        'Technique de programmation',
                        'Marketing gratuit'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que la scalabilité d\'un business ?',
                    'options' => [
                        'Capacité à grandir sans coûts proportionnels',
                        'Mesure de succès',
                        'Flexibilité des prix',
                        'Nombre d\'employés'
                    ],
                    'correctAnswer' => 0
                ],
                [
                    'question' => 'Comment identifier son marché cible ?',
                    'options' => [
                        'Tout le monde',
                        'Segmentation démographique + besoins + comportements',
                        'Plus gros marché',
                        'Marché le plus proche'
                    ],
                    'correctAnswer' => 1
                ]
            ]
        ]);
    }

    private function createAdvertisingQuizzes()
    {
        Quiz::create([
            'title' => 'Quiz Publicité en Ligne & PPC',
            'description' => 'Maîtrisez la publicité payante et les campagnes PPC pour maximiser vos conversions.',
            'subject' => 'advertising',
            'difficulty' => 'Advanced',
            'questions_count' => 8,
            'is_active' => true,
            'questions' => [
                [
                    'question' => 'Qu\'est-ce que le Quality Score en Google Ads ?',
                    'options' => [
                        'Note de 1-10 basée sur pertinence annonce/mots-clés',
                        'Score de qualité du site',
                        'Note client',
                        'Score de performance'
                    ],
                    'correctAnswer' => 0
                ],
                [
                    'question' => 'Comment optimiser un CPC (Cost Per Click) élevé ?',
                    'options' => [
                        'Augmenter budget',
                        'Améliorer Quality Score + mots-clés longue traîne',
                        'Arrêter campagne',
                        'Copier concurrence'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Qu\'est-ce que le remarketing en publicité ?',
                    'options' => [
                        'Nouveau marketing',
                        'Cibler visiteurs ayant interagi avec votre site',
                        'Marketing de masse',
                        'Marketing direct'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Comment calculer le ROAS (Return on Ad Spend) ?',
                    'options' => [
                        'Revenus générés / Coût publicitaire',
                        'Clics / Impressions',
                        'Conversions / Visiteurs',
                        'Budget / Résultats'
                    ],
                    'correctAnswer' => 0
                ],
                [
                    'question' => 'Qu\'est-ce que la negative keywords en Google Ads ?',
                    'options' => [
                        'Mots interdits',
                        'Mots-clés à exclure pour éviter clics non pertinents',
                        'Mots négatifs',
                        'Mots sans résultats'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Comment structurer efficacement un compte Google Ads ?',
                    'options' => [
                        'Une seule campagne',
                        'Campagnes thématiques > groupes annonces > mots-clés',
                        'Tout mélangé',
                        'Par budget seulement'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Comment optimiser une landing page pour PPC ?',
                    'options' => [
                        'Plus de texte',
                        'Cohérence annonce + CTA clair + vitesse + mobile',
                        'Plus d\'images',
                        'Plus de liens'
                    ],
                    'correctAnswer' => 1
                ],
                [
                    'question' => 'Comment utiliser efficacement les extensions d\'annonces ?',
                    'options' => [
                        'Les ignorer',
                        'Liens site + appel + localisation pour maximiser espace',
                        'Une seule extension',
                        'Extensions aléatoires'
                    ],
                    'correctAnswer' => 1
                ]
            ]
        ]);
    }
}

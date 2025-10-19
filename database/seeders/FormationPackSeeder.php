<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FormationPack;
use App\Models\Formation;
use App\Models\FormationModule;
use App\Models\FormationVideo;

class FormationPackSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================
        // PACK 1: Business & Entrepreneuriat
        // ============================================
        $pack1 = FormationPack::create([
            'name' => 'Pack Business & Entrepreneuriat Complet',
            'slug' => 'pack-business-entrepreneuriat',
            'author' => 'Équipe Formaneo',
            'description' => 'Lancez et développez votre business en ligne de 0 à 30k€/mois. Formations complètes sur les offres par abonnement, le coaching, la vente de programmes premium et les stratégies de croissance.',
            'price' => 35000,
            'promotion_price' => 28000,
            'is_on_promotion' => true,
            'promotion_starts_at' => now(),
            'promotion_ends_at' => now()->addDays(30),
            'total_duration' => 720,
            'rating' => 4.9,
            'students_count' => 320,
            'is_active' => true,
            'is_featured' => true,
            'order' => 1
        ]);

        // Formation 1.1
        $formation1_1 = Formation::create([
            'pack_id' => $pack1->id,
            'title' => '10.000€ par mois avec une offre par abonnement',
            'description' => 'Créez et lancez votre offre par abonnement récurrente pour générer 10k€/mois de revenus prévisibles.',
            'duration_minutes' => 90,
            'order' => 1,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_1->id,
            'title' => 'Introduction',
            'description' => 'Découvrez le potentiel des offres par abonnement et comment elles peuvent transformer votre business.',
            'video_url' => 'https://mega.nz/file/mREEjLRb#YmQa4ocAfC1i4dwSRb-mXq5zoJ2JxILeDwp8wHOnGlo',
            'duration_minutes' => 15,
            'order' => 1,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_1->id,
            'title' => 'Lancez votre offre par abonnement',
            'description' => 'Étapes complètes pour créer et lancer votre première offre par abonnement rentable.',
            'video_url' => 'https://mega.nz/file/OIcDAAyR#J-4ljL-m8jafnf_VxHEwI8S6RRx3eXeIkAS3Pfi4LVU',
            'duration_minutes' => 45,
            'order' => 2,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_1->id,
            'title' => 'Automatisez votre business',
            'description' => 'Mettez en place l\'automatisation pour gérer votre business d\'abonnement sans effort.',
            'video_url' => 'https://mega.nz/file/KM8UULYZ#B-3mA0zknQ8e8j6kRre4BgmiGITcR6l34S9YgqfN9N8',
            'duration_minutes' => 30,
            'order' => 3,
            'is_active' => true
        ]);

        // Formation 1.2
        $formation1_2 = Formation::create([
            'pack_id' => $pack1->id,
            'title' => '60k en 4 semaines',
            'description' => 'Stratégie intensive pour générer 60 000€ en seulement 4 semaines avec des techniques éprouvées.',
            'duration_minutes' => 60,
            'order' => 2,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_2->id,
            'title' => '60k en 4 semaines - Stratégie complète',
            'description' => 'Plan d\'action détaillé pour atteindre 60k€ de chiffre d\'affaires en un mois.',
            'video_url' => 'https://mega.nz/file/3c1WVbCZ#AWRfiUjQx4JboY66nlHt-NVBnuZqppSES0uNjfX5VFk',
            'duration_minutes' => 60,
            'order' => 1,
            'is_active' => true
        ]);

        // Formation 1.3
        $formation1_3 = Formation::create([
            'pack_id' => $pack1->id,
            'title' => '2000€ par mois en 1h par semaine',
            'description' => 'Système optimisé pour générer 2000€/mois en ne travaillant qu\'une heure par semaine.',
            'duration_minutes' => 120,
            'order' => 3,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_3->id,
            'title' => 'Introduction',
            'description' => 'Découvrez comment gagner 2000€ mensuels avec un minimum de temps investi.',
            'video_url' => 'https://mega.nz/file/jI9lTRJB#4JiFj9ZH7FnSSeLWy8bXn5I1uchYcmyS7AyJwmbCd2E',
            'duration_minutes' => 20,
            'order' => 1,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_3->id,
            'title' => 'Faites la vente',
            'description' => 'Techniques de vente efficaces pour conclure rapidement vos deals.',
            'video_url' => 'https://mega.nz/file/2JsClA4L#GiGenDPcYzxmJH5VAWLa_OQotO4UQvBHkSgrLULmesQ',
            'duration_minutes' => 50,
            'order' => 2,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_3->id,
            'title' => 'Amenez votre client au succès',
            'description' => 'Comment garantir la satisfaction client et obtenir des recommandations.',
            'video_url' => 'https://mega.nz/file/TV92nLZB#d4adyScmn5-RtQyBVAFGl77FlNTQcAHRLB5PuuUngTo',
            'duration_minutes' => 50,
            'order' => 3,
            'is_active' => true
        ]);

        // Formation 1.4
        $formation1_4 = Formation::create([
            'pack_id' => $pack1->id,
            'title' => 'Systèmes de croissance : 0€ à 30k€/mois',
            'description' => 'Systèmes éprouvés pour scaler votre business de 0 à 30 000€ par mois étape par étape.',
            'duration_minutes' => 150,
            'order' => 4,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_4->id,
            'title' => 'Introduction',
            'description' => 'Vue d\'ensemble des systèmes de croissance pour développer votre business.',
            'video_url' => 'https://mega.nz/file/fJ9wXbyD#5zhID_3x53bnjuyrHvQRw4wVGOd78pj8rzilcKkui5c',
            'duration_minutes' => 20,
            'order' => 1,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_4->id,
            'title' => 'De 0 à 2k€ par mois : Lancez votre offre',
            'description' => 'Premiers pas pour créer et vendre votre première offre rentable.',
            'video_url' => 'https://mega.nz/file/eN1gAKiC#RPMxxuS7PwCeCHb-xa4yU8DNdeqUmfD1pmy8ektoPtU',
            'duration_minutes' => 50,
            'order' => 2,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_4->id,
            'title' => 'De 2k€ à 10k€ par mois',
            'description' => 'Stratégies de scaling pour passer de 2000 à 10 000€ mensuels.',
            'video_url' => 'https://mega.nz/file/uZ1XybBY#okoM7vip7MOi3TThCee8DfYyO07icPP2z9ccNAZk9u4',
            'duration_minutes' => 45,
            'order' => 3,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_4->id,
            'title' => 'De 10k€ à 30k€ par mois',
            'description' => 'Techniques avancées pour atteindre les 30k€ mensuels et au-delà.',
            'video_url' => 'https://mega.nz/file/PENSTShY#v_3bsCOrG2Rj2ASqd428t5lAeCvFgWffeJM1kWO_ldQ',
            'duration_minutes' => 35,
            'order' => 4,
            'is_active' => true
        ]);

        // Formation 1.5
        $formation1_5 = Formation::create([
            'pack_id' => $pack1->id,
            'title' => 'Vendre des programmes à 5000€ et plus',
            'description' => 'Créez et vendez des programmes premium haut de gamme pour maximiser vos revenus.',
            'duration_minutes' => 120,
            'order' => 5,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_5->id,
            'title' => 'Introduction',
            'description' => 'Pourquoi et comment vendre des programmes à prix élevés.',
            'video_url' => 'https://mega.nz/file/CRsmRCaT#NVpoNHVTFuAo4UUI8p9x-GZGEpECvCTl0xKT-GhLIp0',
            'duration_minutes' => 20,
            'order' => 1,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_5->id,
            'title' => 'Comment créer votre programme de haut niveau',
            'description' => 'Structurez une offre premium qui justifie un prix élevé.',
            'video_url' => 'https://mega.nz/file/iNNjSLaA#F3J5KHtNezSUP5Dn4pv_-fAXkvOTD2J8Q8JPDTKyxjM',
            'duration_minutes' => 45,
            'order' => 2,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_5->id,
            'title' => 'Comment vendre votre programme de haut niveau',
            'description' => 'Stratégies de vente pour closer des deals à 5000€+.',
            'video_url' => 'https://mega.nz/file/eYtljYhT#qHn7KHj1D6Em1QEomzzCREgHUMzwp8ToFyzkempZHMk',
            'duration_minutes' => 40,
            'order' => 3,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_5->id,
            'title' => 'Comment délivrer la prestation',
            'description' => 'Assurez un service exceptionnel pour fidéliser vos clients premium.',
            'video_url' => 'https://mega.nz/file/DVkDCbgI#0dTzD6Bebe3TqPF8tNXeQrkJganZhDR7Aw0XUuxqMK4',
            'duration_minutes' => 15,
            'order' => 4,
            'is_active' => true
        ]);

        // Formation 1.6
        $formation1_6 = Formation::create([
            'pack_id' => $pack1->id,
            'title' => 'Closing Mastery',
            'description' => 'Maîtrisez l\'art de la vente et du closing pour convertir vos prospects en clients payants.',
            'duration_minutes' => 180,
            'order' => 6,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_6->id,
            'title' => 'Introduction',
            'description' => 'Les fondations du closing et pourquoi c\'est une compétence essentielle.',
            'video_url' => 'https://mega.nz/file/HFk0CR5Z#emoAKL4zXsnRp1N9ABHEQC0XDFcUoF8jismsEwhXc8A',
            'duration_minutes' => 20,
            'order' => 1,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_6->id,
            'title' => 'Comprendre le closing',
            'description' => 'La psychologie derrière une vente réussie.',
            'video_url' => 'https://mega.nz/file/bYMxQBYZ#xETjmp3qWrUNuynLgyO1-AJcwLEC3Nv_34-aaoX_rJ0',
            'duration_minutes' => 30,
            'order' => 2,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_6->id,
            'title' => 'Les bases du closing',
            'description' => 'Techniques fondamentales pour closer efficacement.',
            'video_url' => 'https://mega.nz/file/TUEESbAC#NhaKZMZsbjMIA_IEsrl5dHHoyXUD8tSJeQiKRnETkuI',
            'duration_minutes' => 35,
            'order' => 3,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_6->id,
            'title' => 'Les techniques de closing',
            'description' => 'Méthodes avancées pour closer n\'importe quel prospect.',
            'video_url' => 'https://mega.nz/file/2VdERYRI#s3UahpQze5YK-0QzEQpeWxJ_q7ZgmyYZQyk1xw3naLg',
            'duration_minutes' => 35,
            'order' => 4,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_6->id,
            'title' => 'Gérer les objections',
            'description' => 'Comment répondre aux objections et les transformer en opportunités.',
            'video_url' => 'https://mega.nz/file/OJUFgBgK#zIpWZcdyQU9gz5mol6XTZJCjxZx0TYsUpq-u32inmIU',
            'duration_minutes' => 30,
            'order' => 5,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_6->id,
            'title' => 'Le script de vente',
            'description' => 'Script éprouvé pour structurer vos appels de vente.',
            'video_url' => 'https://mega.nz/file/yd1CwbTa#AcvEj6d2qPrZrXJQJe7eZ2qMoG5MXppo25hh-DcrZ_4',
            'duration_minutes' => 20,
            'order' => 6,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_6->id,
            'title' => 'Comment trouver ses clients',
            'description' => 'Stratégies de prospection pour remplir votre agenda.',
            'video_url' => 'https://mega.nz/file/iUNgDRhT#El5kEBtLtlXMNm1zV4DJkvb0VcUW35qmTZDANnerBJA',
            'duration_minutes' => 10,
            'order' => 7,
            'is_active' => true
        ]);

        // Formation 1.7
        $formation1_7 = Formation::create([
            'pack_id' => $pack1->id,
            'title' => 'Objectifs Millionnaire',
            'description' => 'Mindset et stratégies pour atteindre le statut de millionnaire.',
            'duration_minutes' => 150,
            'order' => 7,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_7->id,
            'title' => 'Introduction',
            'description' => 'Le chemin vers le premier million : ce qu\'il faut savoir.',
            'video_url' => 'https://mega.nz/file/ONk3kATL#npSFmFRqGEt1XWM3TJs5O-dcKTbEHJ_MxbUobP6cors',
            'duration_minutes' => 25,
            'order' => 1,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_7->id,
            'title' => 'Augmentez votre valeur',
            'description' => 'Comment développer des compétences qui valent des millions.',
            'video_url' => 'https://mega.nz/file/yVMXnSAI#rEK4ONbLYi9kzeG3oVg52JMJfUuIRqTnqQMkBGCBo0Y',
            'duration_minutes' => 45,
            'order' => 2,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_7->id,
            'title' => 'Devenez Entrepreneur',
            'description' => 'Créer et scaler un business rentable.',
            'video_url' => 'https://mega.nz/file/uEVxzCwK#LsS2DQeBet4bmqi0YHjfKWT9pMCeFOF7HuLVPMiA3V8',
            'duration_minutes' => 40,
            'order' => 3,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_7->id,
            'title' => 'Devenez investisseur',
            'description' => 'Faire fructifier votre argent avec l\'investissement.',
            'video_url' => 'https://mega.nz/file/aY9FDDhQ#aXVxJzuBi6ggLZweJ9jQU4X3I2sG11OfmsbYpgPW3pw',
            'duration_minutes' => 30,
            'order' => 4,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_7->id,
            'title' => 'Conclusion',
            'description' => 'Plan d\'action pour votre voyage vers le million.',
            'video_url' => 'https://mega.nz/file/3UMQSSYA#8Kljt2pm8nMMtX9hzCTrnlhaq1teL9fH3I6axd3eJWM',
            'duration_minutes' => 10,
            'order' => 5,
            'is_active' => true
        ]);

        // Formation 1.8
        $formation1_8 = Formation::create([
            'pack_id' => $pack1->id,
            'title' => 'Guide fiscal pour entrepreneurs malins',
            'description' => 'Optimisez votre fiscalité et arrêtez de vous faire arnaquer par les impôts.',
            'duration_minutes' => 120,
            'order' => 8,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_8->id,
            'title' => 'Introduction',
            'description' => 'Les bases de la fiscalité pour entrepreneurs.',
            'video_url' => 'https://mega.nz/file/PMNTRIDB#oSoqf9qDd69a4u-db13ZvlGrn--myfzd_ngAIAcS-wY',
            'duration_minutes' => 20,
            'order' => 1,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_8->id,
            'title' => 'Arrêtez de vous faire tondre',
            'description' => 'Gagnez jusqu\'à 2,5 fois plus avec les bonnes structures.',
            'video_url' => 'https://mega.nz/file/LIUkVSbQ#Ogn4IVxlCsOjxa5xPnsJKFcMS3D_chAZAmyCT5tc1q4',
            'duration_minutes' => 50,
            'order' => 2,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_8->id,
            'title' => 'Mettez en place votre protection sociale',
            'description' => 'Protégez-vous et votre famille tout en optimisant vos charges.',
            'video_url' => 'https://mega.nz/file/CYsznDDJ#TuIslFiuMmFdx3lbC-x-0ll0CDHKiR-YXWjjLyodWvI',
            'duration_minutes' => 30,
            'order' => 3,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation1_8->id,
            'title' => 'Sources de revenus et retraite confortable',
            'description' => 'Préparez votre avenir financier dès maintenant.',
            'video_url' => 'https://mega.nz/file/iM82lCRC#vR4sroWGABXSRdWFobh_RSG59jlGEm7F6kB6uhiHm7s',
            'duration_minutes' => 20,
            'order' => 4,
            'is_active' => true
        ]);

        // ============================================
        // PACK 2: Marketing Digital & E-commerce
        // ============================================
        $pack2 = FormationPack::create([
            'name' => 'Pack Marketing Digital & E-commerce',
            'slug' => 'pack-marketing-ecommerce',
            'author' => 'Équipe Formaneo',
            'description' => 'Maîtrisez le marketing digital, le e-commerce, le SEO, l\'email marketing et les tunnels de vente. Tout ce dont vous avez besoin pour dominer en ligne.',
            'price' => 32000,
            'promotion_price' => 25000,
            'is_on_promotion' => true,
            'promotion_starts_at' => now(),
            'promotion_ends_at' => now()->addDays(30),
            'total_duration' => 840,
            'rating' => 4.8,
            'students_count' => 410,
            'is_active' => true,
            'is_featured' => true,
            'order' => 2
        ]);

        // Formation 2.1
        $formation2_1 = Formation::create([
            'pack_id' => $pack2->id,
            'title' => '100€ par jour avec une liste email',
            'description' => 'Construisez et monétisez votre liste email pour générer 100€ par jour en automatique.',
            'duration_minutes' => 150,
            'order' => 1,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_1->id,
            'title' => 'Comment avoir 100 nouveaux inscrits par jour',
            'description' => 'Stratégies pour faire grossir votre liste rapidement et gratuitement.',
            'video_url' => 'https://mega.nz/file/HccDmaoJ#GR_Dq8xHnlKpnor17m4yRdPjSncr5ZAbtGVAHgwaVy0',
            'duration_minutes' => 55,
            'order' => 1,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_1->id,
            'title' => 'Convertir les inscrits en clients',
            'description' => 'Automatisez vos ventes avec des séquences d\'emails performantes.',
            'video_url' => 'https://mega.nz/file/mdMXyDzQ#5JGMSTKA4evrSfskid6nULojP3xphKnx-hahgZYh6P8',
            'duration_minutes' => 50,
            'order' => 2,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_1->id,
            'title' => 'Faire des ventes toutes les semaines',
            'description' => 'Techniques pour maintenir l\'engagement et vendre régulièrement.',
            'video_url' => 'https://mega.nz/file/3UUijRoD#FJ27YX7OgxHC-l5DgdL9YTKuPkpMslZUsc8P3LDEQ_8',
            'duration_minutes' => 45,
            'order' => 3,
            'is_active' => true
        ]);

        // Formation 2.2
        $formation2_2 = Formation::create([
            'pack_id' => $pack2->id,
            'title' => 'Copywriting & Email Marketing',
            'description' => 'Maîtrisez l\'art de rédiger des emails qui convertissent et génèrent des ventes.',
            'duration_minutes' => 180,
            'order' => 2,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_2->id,
            'title' => 'L\'importance de l\'email marketing',
            'description' => 'Pourquoi l\'email reste le canal le plus rentable.',
            'video_url' => 'https://mega.nz/file/eNtEASCa#Vm8l7UedqYiS3w4LuNBefeGjz0FQpnWGpGUUBCRwgg0',
            'duration_minutes' => 25,
            'order' => 1,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_2->id,
            'title' => 'Les différents cas d\'utilisation',
            'description' => 'Newsletters, promotions, séquences : quand utiliser quoi.',
            'video_url' => 'https://mega.nz/file/TRVjAYgJ#MeBlRpXd2bXFigWqVTbiVhqPIMwVWeLIfVp2ReW2VOM',
            'duration_minutes' => 30,
            'order' => 2,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_2->id,
            'title' => 'Rédiger un mail impactant',
            'description' => 'Techniques de copywriting pour captiver et vendre.',
            'video_url' => 'https://mega.nz/file/7AEDhZZZ#XNDqS8uL32F5raZVLdR9-SlmQirA4vROjpgYVe-VfgE',
            'duration_minutes' => 40,
            'order' => 3,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_2->id,
            'title' => 'Optimisation des emails',
            'description' => 'A/B testing, timing et personnalisation pour maximiser vos résultats.',
            'video_url' => 'https://mega.nz/file/edtwBZyB#_atQf2dG1BJznzBQRm8IcLBKLgxHIdL63nEAV8R2BXg',
            'duration_minutes' => 30,
            'order' => 4,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_2->id,
            'title' => 'Paramétrer les campagnes email',
            'description' => 'Configuration technique de vos outils d\'email marketing.',
            'video_url' => 'https://mega.nz/file/fJkDGAQQ#TUYurzXln7zLn6g66bso2Mwuz70S1HE3fycdSeXv6Vo',
            'duration_minutes' => 25,
            'order' => 5,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_2->id,
            'title' => 'Collecte et segmentation des emails',
            'description' => 'Construisez une liste qualifiée et segmentée pour de meilleurs résultats.',
            'video_url' => 'https://mega.nz/file/7EdBVBQI#ubltrPD35udDIlCYayRigipvMlCFwHYGtwcH0tCnIWo',
            'duration_minutes' => 20,
            'order' => 6,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_2->id,
            'title' => 'Analyse et ajustements des performances',
            'description' => 'Mesurez et améliorez continuellement vos campagnes.',
            'video_url' => 'https://mega.nz/file/qB1UXQrS#6ldlsitXNKbsV1aTiW4GhZgJQervC_cTJrpte-9y_0c',
            'duration_minutes' => 10,
            'order' => 7,
            'is_active' => true
        ]);

        // Formation 2.3
        $formation2_3 = Formation::create([
            'pack_id' => $pack2->id,
            'title' => 'Tunnels de vente 2.0',
            'description' => 'Créez des tunnels de vente ultra-performants avec systeme.io qui convertissent vos visiteurs en clients.',
            'duration_minutes' => 210,
            'order' => 3,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_3->id,
            'title' => 'Introduction',
            'description' => 'Les fondamentaux des tunnels de vente qui convertissent.',
            'video_url' => 'https://mega.nz/file/2VkyiBAQ#79A1foJ29c787P6U_wvTdyi_wcRvziFq4dFvFkVXscg',
            'duration_minutes' => 15,
            'order' => 1,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_3->id,
            'title' => 'Présentation systeme.io',
            'description' => 'Tour complet de l\'outil tout-en-un pour vos tunnels.',
            'video_url' => 'https://mega.nz/file/vIdiGRhC#Dqx2nKSyx6pQO6p5RqnFZ2-kBAdpcB_FYBx_pim_U3Q',
            'duration_minutes' => 30,
            'order' => 2,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_3->id,
            'title' => 'Les différents types de tunnels',
            'description' => 'Webinaire, VSL, tripwire : choisir le bon tunnel pour votre offre.',
            'video_url' => 'https://mega.nz/file/uBECwQ7Y#HKUV6xCv1BNz02on14NQZvb9kRp5x6OWsquLkgTl1Ag',
            'duration_minutes' => 25,
            'order' => 3,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_3->id,
            'title' => 'Créer des tunnels de vente',
            'description' => 'Construction pas à pas de votre premier tunnel performant.',
            'video_url' => 'https://mega.nz/file/WI9WnBgK#T6PZhofuOR_7Gp1OALRFXvnbzBBsaXpMhG8rC3vk5c4',
            'duration_minutes' => 35,
            'order' => 4,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_3->id,
            'title' => 'Faire du HTML grâce à ChatGPT',
            'description' => 'Personnalisez vos pages sans coder grâce à l\'IA.',
            'video_url' => 'https://mega.nz/file/7BV0lZgT#6TFkF2IuhZ85Cw3Q-PCkx9JOGf4_z9lEwTmu7Oteblo',
            'duration_minutes' => 20,
            'order' => 5,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_3->id,
            'title' => 'Gérer le A/B testing',
            'description' => 'Testez et optimisez chaque élément de vos tunnels.',
            'video_url' => 'https://mega.nz/file/GY1GgIgD#8uSxHvvtkUnrmxGPoc9wqGwChfki_hmkrzkjsvzud8U',
            'duration_minutes' => 25,
            'order' => 6,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_3->id,
            'title' => 'Automatiser ses ventes et Upsell',
            'description' => 'Augmentez votre panier moyen avec des upsells automatisés.',
            'video_url' => 'https://mega.nz/file/OFcGmBZB#duaPsU4xJZdsLQkokVYgRYOPhwQWDYAdst_GmwNZjkI',
            'duration_minutes' => 25,
            'order' => 7,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_3->id,
            'title' => 'Créer des campagnes d\'e-mails',
            'description' => 'Intégrez l\'email marketing dans vos tunnels.',
            'video_url' => 'https://mega.nz/file/3YM00A5T#RvRDw7PLFuZUSwiC_ajvrDdVEoiHSGmeK4cAdKph0Mw',
            'duration_minutes' => 20,
            'order' => 8,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_3->id,
            'title' => 'Les règles d\'automatisation',
            'description' => 'Automatisez vos processus pour gagner du temps.',
            'video_url' => 'https://mega.nz/file/jd82la7A#AUJ9PjD1sKihZOKpIQNeSTSjld67EUKN-FsvzGsQNHA',
            'duration_minutes' => 10,
            'order' => 9,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_3->id,
            'title' => 'Optimisation & analyse',
            'description' => 'Analysez vos métriques et optimisez en continu.',
            'video_url' => 'https://mega.nz/file/fVVVQBZK#b6-tcD_GwNvbAdxKZCYAINR3_Iw7eZyEidj4q372kW8',
            'duration_minutes' => 5,
            'order' => 10,
            'is_active' => true
        ]);

        // Formation 2.4
        $formation2_4 = Formation::create([
            'pack_id' => $pack2->id,
            'title' => 'Revenus automatiques : 6000€ par mois',
            'description' => 'Système complet pour générer 6000€ mensuels en automatique avec des tunnels optimisés.',
            'duration_minutes' => 150,
            'order' => 4,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_4->id,
            'title' => 'Introduction',
            'description' => 'Le blueprint des revenus automatiques à 6 chiffres.',
            'video_url' => 'https://mega.nz/file/eZ8m3YBD#5z7W-ARotdi5VcH8PDDWhp4XCi8SUOwAKiJuCL4fowI',
            'duration_minutes' => 20,
            'order' => 1,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_4->id,
            'title' => 'Créez votre système de revenus automatiques',
            'description' => 'Architecture complète d\'un système qui tourne seul.',
            'video_url' => 'https://mega.nz/file/mdkXELxb#WvacWqGp1XtFSw66bK56CQ38wkz7jeK_z1Xbfiw_CyE',
            'duration_minutes' => 50,
            'order' => 2,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_4->id,
            'title' => 'Créez votre tunnel de ventes',
            'description' => 'Construisez votre machine à vendre automatique.',
            'video_url' => 'https://mega.nz/file/nIcnQQqa#UOtUglszoji77P68uJIqbrSxI5aM5CQ1N7cAI6roeG8',
            'duration_minutes' => 40,
            'order' => 3,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_4->id,
            'title' => 'Optimisez votre système',
            'description' => 'Techniques d\'optimisation pour maximiser vos revenus.',
            'video_url' => 'https://mega.nz/file/HAdRmBAQ#Io_ppt94UmYqGO2eyfsnIQ8TXPDmUomI71uPfviO5po',
            'duration_minutes' => 40,
            'order' => 4,
            'is_active' => true
        ]);

        // Formation 2.5
        $formation2_5 = Formation::create([
            'pack_id' => $pack2->id,
            'title' => 'SEO : De 0 à 1000 visiteurs par jour',
            'description' => 'Stratégies SEO complètes pour attirer 1000 visiteurs quotidiens gratuitement.',
            'duration_minutes' => 180,
            'order' => 5,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_5->id,
            'title' => 'Introduction',
            'description' => 'Comprendre le SEO et son potentiel de trafic gratuit.',
            'video_url' => 'https://mega.nz/file/CBk1xBjb#o_BTCwmWUzRq5H613cFTI4Y0OuuEcBtxCeBeqoA-ecE',
            'duration_minutes' => 20,
            'order' => 1,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_5->id,
            'title' => 'Trouver les mots-clés',
            'description' => 'Recherche approfondie des mots-clés rentables et accessibles.',
            'video_url' => 'https://mega.nz/file/6UcBAKYa#ClKxToRt6lesdqqUYXW2FmdQZroD_ONIGs_epghMMok',
            'duration_minutes' => 45,
            'order' => 2,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_5->id,
            'title' => 'Créer le contenu',
            'description' => 'Rédiger du contenu optimisé qui se classe en première page.',
            'video_url' => 'https://mega.nz/file/6Y9EFQAR#HzfSgMbHBusEBQwcF_obabB_S0s3Jk_LMstjwYkb5lM',
            'duration_minutes' => 50,
            'order' => 3,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_5->id,
            'title' => 'Les bases de la création de liens',
            'description' => 'Techniques fondamentales de netlinking et outils essentiels.',
            'video_url' => 'https://mega.nz/file/mMdjEaxT#v8gRyfSxN5MFRFqPlcKMs_gnsl_jkzO5Yh0ooCuTF1c',
            'duration_minutes' => 40,
            'order' => 4,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_5->id,
            'title' => 'Techniques avancées de création de liens',
            'description' => 'Stratégies de backlinking avancées pour dominer Google.',
            'video_url' => 'https://mega.nz/file/PM8FWCCC#T3Padf6be1xPT2d-GP_OvkmnTzR666LoQrr1NpjIEmM',
            'duration_minutes' => 20,
            'order' => 5,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_5->id,
            'title' => 'Bonus : Trouver les mots-clés de vos concurrents',
            'description' => 'Espionnez la stratégie SEO de vos concurrents.',
            'video_url' => 'https://mega.nz/file/3VERlKAD#5IXm3f53BT0YIpCLVKkj6oUwNpQRUU8tzsdAD2puxqk',
            'duration_minutes' => 5,
            'order' => 6,
            'is_active' => true
        ]);

        // Formation 2.6
        $formation2_6 = Formation::create([
            'pack_id' => $pack2->id,
            'title' => 'Business YouTube : 2000€ par mois',
            'description' => 'Construisez un business rentable avec YouTube et transformez vos viewers en clients.',
            'duration_minutes' => 120,
            'order' => 6,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_6->id,
            'title' => 'Introduction : Gagner 50% en plus avec YouTube',
            'description' => 'Comment YouTube peut booster votre business existant.',
            'video_url' => 'https://mega.nz/file/KJsEgSIB#bwOXXS9pC-DgOsp30WqwiwQBfuqTqZB_8IPqFD9cs6I',
            'duration_minutes' => 20,
            'order' => 1,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_6->id,
            'title' => 'Comment grossir votre liste avec YouTube',
            'description' => 'Convertir vos viewers en abonnés email qualifiés.',
            'video_url' => 'https://mega.nz/file/mNNWnT7J#bTLiGI6Il7_t50TzKOBibpFHFppppd0Tmcf27YztOSA',
            'duration_minutes' => 45,
            'order' => 2,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_6->id,
            'title' => 'Le système complet pour 2k€/mois',
            'description' => 'Architecture complète : contenu, monétisation et automatisation.',
            'video_url' => 'https://mega.nz/file/7RUzSIrB#i0hJP8zyGSqi7LFPpOSPaMmkZpYW9vA-MbdNlesHAi8',
            'duration_minutes' => 55,
            'order' => 3,
            'is_active' => true
        ]);

        // Formation 2.7
        $formation2_7 = Formation::create([
            'pack_id' => $pack2->id,
            'title' => 'Dropshipping 2025',
            'description' => 'Formation complète sur le dropshipping moderne : de la recherche de produits à la première vente.',
            'duration_minutes' => 150,
            'order' => 7,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_7->id,
            'title' => 'Introduction au dropshipping',
            'description' => 'Le modèle dropshipping en 2025 : opportunités et réalités.',
            'video_url' => 'https://mega.nz/file/7RU30CpR#_5Rnm610nbHgQ-aILXyfVZkXR9KOkBvDPYZ7s36Hkwc',
            'duration_minutes' => 25,
            'order' => 1,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_7->id,
            'title' => 'Trouver une niche',
            'description' => 'Méthode complète pour identifier une niche profitable.',
            'video_url' => 'https://mega.nz/file/SJ9DCJLR#JS8LuRlr6SU_0Wvg0Yn_0b9tmTpwPp748bK_F2w3ibE',
            'duration_minutes' => 35,
            'order' => 2,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_7->id,
            'title' => 'Création de boutique',
            'description' => 'Setup complet de votre boutique dropshipping professionnelle.',
            'video_url' => 'https://mega.nz/file/rJckgSzB#dJNb4cE4Tr7zpakh5paj2kxqAerZj_-l92UwjGNeVDY',
            'duration_minutes' => 40,
            'order' => 3,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_7->id,
            'title' => 'Stratégies dropshipping',
            'description' => 'Les stratégies qui fonctionnent vraiment en 2025.',
            'video_url' => 'https://mega.nz/file/mcclyBrR#5b16amn7poZRdr15sUQtSFMLO1IxgNf6l9b3s2C1fJg',
            'duration_minutes' => 30,
            'order' => 4,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_7->id,
            'title' => 'Différentes stratégies marketing',
            'description' => 'Facebook Ads, TikTok, influenceurs : quelle stratégie choisir.',
            'video_url' => 'https://mega.nz/file/LEM22BjY#jxZS70IQBnRjIO_EXzkF9dmkOMPiTKqrJmitE0KfVtY',
            'duration_minutes' => 20,
            'order' => 5,
            'is_active' => true
        ]);

        // Formation 2.8
        $formation2_8 = Formation::create([
            'pack_id' => $pack2->id,
            'title' => 'Créer une boutique Shopify qui convertit',
            'description' => 'Guide complet pour créer une boutique Shopify optimisée qui génère des ventes.',
            'duration_minutes' => 180,
            'order' => 8,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_8->id,
            'title' => 'Introduction Shopify',
            'description' => 'Tour d\'horizon de Shopify et ses fonctionnalités.',
            'video_url' => 'https://mega.nz/file/bEMVEaTa#qO3W9OYnRHGbsg9I7AVJC43IgnfZ7emvxkWHnqBFWSw',
            'duration_minutes' => 25,
            'order' => 1,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_8->id,
            'title' => 'Setup boutique Shopify',
            'description' => 'Configuration initiale et paramétrage de votre boutique.',
            'video_url' => 'https://mega.nz/file/fVVBAQSS#_QOrDpuBUp_XUiq9cq3xySuSHU7_YpaJM5IYqTTtaKs',
            'duration_minutes' => 35,
            'order' => 2,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_8->id,
            'title' => 'Construire sa boutique',
            'description' => 'Design et structure pour une boutique professionnelle.',
            'video_url' => 'https://mega.nz/file/2J9VnLwD#X7VnmRNktm2UITgWCSfLvqo8H0CuHPclDXDkA5Ch9X4',
            'duration_minutes' => 45,
            'order' => 3,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_8->id,
            'title' => 'Gérer les produits & collections',
            'description' => 'Organisation optimale de votre catalogue produits.',
            'video_url' => 'https://mega.nz/file/mQ1yyQyQ#Og0J4n5p1B8B0oGkRf5-xzentcVUoivwHvF-3MbO_tE',
            'duration_minutes' => 30,
            'order' => 4,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_8->id,
            'title' => 'Optimiser sa boutique',
            'description' => 'Techniques d\'optimisation pour maximiser vos conversions.',
            'video_url' => 'https://mega.nz/file/fYFWlT5T#nbML21qOAqgqI022W6G8f1-34ovNDHrJq2Qth4q83p0',
            'duration_minutes' => 30,
            'order' => 5,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_8->id,
            'title' => 'Applications utiles',
            'description' => 'Les meilleures apps Shopify pour booster vos ventes.',
            'video_url' => 'https://mega.nz/file/KAUhzIpS#WRMkg-FJFzV_1jcXqXG2_t72J9akqvfVPTkGbvcly-M',
            'duration_minutes' => 15,
            'order' => 6,
            'is_active' => true
        ]);

        // Formation 2.9
        $formation2_9 = Formation::create([
            'pack_id' => $pack2->id,
            'title' => 'Trouver un produit gagnant',
            'description' => 'Méthode éprouvée pour dénicher des produits gagnants et trouver les meilleurs fournisseurs.',
            'duration_minutes' => 60,
            'order' => 9,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_9->id,
            'title' => 'Produit gagnant',
            'description' => 'Critères et techniques pour identifier un produit à fort potentiel.',
            'video_url' => 'https://mega.nz/file/Kd0AGLCa#5SNhWgLl9QlBg0QgFd7PhJd_KdyqraTBWGbMw57CFfM',
            'duration_minutes' => 35,
            'order' => 1,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_9->id,
            'title' => 'Recherche fournisseur',
            'description' => 'Comment trouver et négocier avec des fournisseurs fiables.',
            'video_url' => 'https://mega.nz/file/fIFAjQ6D#a_s3wamZZeFq0tD2jJ4DAb2edvWF5rK4xecZubkQ1TE',
            'duration_minutes' => 25,
            'order' => 2,
            'is_active' => true
        ]);

        // Formation 2.10
        $formation2_10 = Formation::create([
            'pack_id' => $pack2->id,
            'title' => 'Growth Hacking',
            'description' => 'Techniques avancées de growth hacking pour une croissance explosive et automatisée.',
            'duration_minutes' => 210,
            'order' => 10,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_10->id,
            'title' => 'Introduction au Growth Hacking',
            'description' => 'Les principes du growth hacking pour une croissance rapide.',
            'video_url' => 'https://mega.nz/file/3IMDCZCC#WkrQuRuJ3oIlZx_UvnnykZHBS9nCsgnd2t688oiTh08',
            'duration_minutes' => 25,
            'order' => 1,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_10->id,
            'title' => 'Générer des leads avec Google Maps',
            'description' => 'Technique peu connue pour générer des leads B2B qualifiés.',
            'video_url' => 'https://mega.nz/file/bNdmwZaA#9gCUVx8n_kpaxa60gV3z-9asg8KBFovk6I6V4pkfxz0',
            'duration_minutes' => 35,
            'order' => 2,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_10->id,
            'title' => 'Générer des leads en automatique sur LinkedIn',
            'description' => 'Automatisez votre prospection LinkedIn pour générer des leads 24/7.',
            'video_url' => 'https://mega.nz/file/jNkxjKzA#_k537MJWSuEldU6IiSMRS96dJcWg_53hMaVmU22JMJc',
            'duration_minutes' => 40,
            'order' => 3,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_10->id,
            'title' => 'La psychologie dans les tunnels de vente',
            'description' => 'Utilisez les biais psychologiques pour augmenter vos conversions.',
            'video_url' => 'https://mega.nz/file/yVkRmQgR#CPhEHvrIHJOcnQcbD53gBjuTMDkDImls3hari15eT6k',
            'duration_minutes' => 35,
            'order' => 4,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_10->id,
            'title' => 'Stratégie cross platform',
            'description' => 'Dominez plusieurs plateformes simultanément pour maximiser votre reach.',
            'video_url' => 'https://mega.nz/file/XN1CGSpK#gU0XW8XzIttp9WSChJHj4qnssIXliJsH2xYmxZcj6FE',
            'duration_minutes' => 30,
            'order' => 5,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_10->id,
            'title' => 'Automatisation des tâches de croissance',
            'description' => 'Outils et techniques pour automatiser votre croissance.',
            'video_url' => 'https://mega.nz/file/uM8nQLrK#er-elP8wpit5pFqvYE9EEv9a8-EKsldrO168vfbF0XQ',
            'duration_minutes' => 30,
            'order' => 6,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_10->id,
            'title' => 'Suivi et analyse des performances',
            'description' => 'Mesurez et optimisez vos actions de growth hacking.',
            'video_url' => 'https://mega.nz/file/jdEBAIRR#2qobtg0g_-XhxKSqdGp9qGYBpbNq6sH7CA0ISRggWkE',
            'duration_minutes' => 15,
            'order' => 7,
            'is_active' => true
        ]);

        // Formation 2.11
        $formation2_11 = Formation::create([
            'pack_id' => $pack2->id,
            'title' => 'Affiliation 2025',
            'description' => 'Gagnez des commissions récurrentes en promouvant les produits des autres sans créer les vôtres.',
            'duration_minutes' => 180,
            'order' => 11,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_11->id,
            'title' => 'Introduction à l\'affiliation',
            'description' => 'Comprendre le modèle d\'affiliation et son potentiel de revenus.',
            'video_url' => 'https://mega.nz/file/mVtwiSqD#T2unrgswJ8G9TZyvhnbyYFNwOB1I7T_7E3F-36WEEyE',
            'duration_minutes' => 20,
            'order' => 1,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_11->id,
            'title' => 'Trouver un programme d\'affiliation',
            'description' => 'Comment choisir les meilleurs programmes pour votre audience.',
            'video_url' => 'https://mega.nz/file/SEE0iK5J#XgjmuE76kNoZ9SN2NwSgjpH9hDnBc8FGiIwOaRpdVLQ',
            'duration_minutes' => 30,
            'order' => 2,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_11->id,
            'title' => 'Les différents modèles d\'affiliation',
            'description' => 'CPA, CPL, recurring : quel modèle choisir selon vos objectifs.',
            'video_url' => 'https://mega.nz/file/nAVBDLYS#7SCGzresda3-8v-vLH1J9H1BIwXPyLMDCnzX17y71Eg',
            'duration_minutes' => 25,
            'order' => 3,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_11->id,
            'title' => 'Création de contenu pour l\'affiliation',
            'description' => 'Créez du contenu qui convertit sans être trop vendeur.',
            'video_url' => 'https://mega.nz/file/LQclCZCS#nsP8mlb9FNz2p8L_XYI29mzuRtlW__Qb6dxbyXPB_Oc',
            'duration_minutes' => 35,
            'order' => 4,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_11->id,
            'title' => 'Créer de bonnes relations',
            'description' => 'Construisez des relations durables avec vos partenaires d\'affiliation.',
            'video_url' => 'https://mega.nz/file/XMdkGZAY#jdTE63gt1v1Djjl6YDcxuDe1XnLiImwsPtF3EpuZS4M',
            'duration_minutes' => 25,
            'order' => 5,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_11->id,
            'title' => 'Maximiser ses commissions',
            'description' => 'Stratégies avancées pour augmenter vos revenus d\'affiliation.',
            'video_url' => 'https://mega.nz/file/yZdhzQgR#wWAz8JjTVDn7R5NQ7_mwQA-IFKrYShdm0PUoBG9QikE',
            'duration_minutes' => 30,
            'order' => 6,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_11->id,
            'title' => 'Optimisation des performances',
            'description' => 'Analysez et optimisez vos campagnes d\'affiliation.',
            'video_url' => 'https://mega.nz/file/fFUXGJqI#T5zejybzoQ1zbbbaPFvQHpi2HSnI_xE-Ca_1xtVoPa4',
            'duration_minutes' => 15,
            'order' => 7,
            'is_active' => true
        ]);

        // Formation 2.12
        $formation2_12 = Formation::create([
            'pack_id' => $pack2->id,
            'title' => 'Produit Rapide : Vendre en 7 jours',
            'description' => 'Méthode express pour créer et vendre votre premier produit digital en moins d\'une semaine.',
            'duration_minutes' => 120,
            'order' => 12,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_12->id,
            'title' => 'Introduction',
            'description' => 'La méthode rapide pour lancer un produit rentable.',
            'video_url' => 'https://mega.nz/file/iQkR0IJR#LpuUaKQqAi22920zj87x89FGfIyKNRjCi5mBy_oHXzI',
            'duration_minutes' => 15,
            'order' => 1,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_12->id,
            'title' => 'Créez un produit qui cartonne en 48 heures',
            'description' => 'Validation d\'idée et création ultra-rapide de votre produit.',
            'video_url' => 'https://mega.nz/file/eItWXS7a#lO30rY83kwrBtFcgahVw-l9ys28hfYewQWPG5FIQ1Nk',
            'duration_minutes' => 45,
            'order' => 2,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_12->id,
            'title' => 'Faites vos premières ventes',
            'description' => 'Stratégies pour vendre rapidement sans audience.',
            'video_url' => 'https://mega.nz/file/iRkEgKQb#wLpSZpOw-50DwaOEAfcrlFkyScXIfXMyGTSrt6HWJbk',
            'duration_minutes' => 40,
            'order' => 3,
            'is_active' => true
        ]);

        FormationVideo::create([
            'formation_id' => $formation2_12->id,
            'title' => 'Doublez vos profits',
            'description' => 'Techniques pour augmenter rapidement votre chiffre d\'affaires.',
            'video_url' => 'https://mega.nz/file/6MFSVKBA#e5I9pcp0Yqlyq7mzISc-9_nNZS2KC6d7e97Nv803JmE',
            'duration_minutes' => 20,
            'order' => 4,
            'is_active' => true
        ]);

        // ============================================
        // MODULES DE RESSOURCES
        // ============================================
        FormationModule::create([
            'formation_id' => $formation1_1->id,
            'title' => 'Ressources complémentaires',
            'content' => 'Documents et templates pour votre offre par abonnement.',
            'duration_minutes' => 0,
            'order' => 1,
            'is_active' => true
        ]);

        FormationModule::create([
            'formation_id' => $formation1_3->id,
            'title' => 'Modèles d\'email',
            'content' => 'Templates d\'emails prêts à utiliser.',
            'duration_minutes' => 0,
            'order' => 1,
            'is_active' => true
        ]);

        FormationModule::create([
            'formation_id' => $formation1_3->id,
            'title' => 'Modèle de sondage',
            'content' => 'Template de sondage pour identifier les besoins de vos clients.',
            'duration_minutes' => 0,
            'order' => 2,
            'is_active' => true
        ]);

        FormationModule::create([
            'formation_id' => $formation1_3->id,
            'title' => 'Questions à poser',
            'content' => 'Liste des questions essentielles pour vos appels de vente.',
            'duration_minutes' => 0,
            'order' => 3,
            'is_active' => true
        ]);

        FormationModule::create([
            'formation_id' => $formation1_4->id,
            'title' => 'Script de vente coaching',
            'content' => 'Script complet pour vendre vos services de coaching.',
            'duration_minutes' => 0,
            'order' => 1,
            'is_active' => true
        ]);

        FormationModule::create([
            'formation_id' => $formation1_4->id,
            'title' => 'Thématiques rentables',
            'content' => 'Liste des niches et thématiques les plus rentables.',
            'duration_minutes' => 0,
            'order' => 2,
            'is_active' => true
        ]);

        FormationModule::create([
            'formation_id' => $formation1_5->id,
            'title' => 'Dossier de préparation',
            'content' => 'Template pour préparer votre programme premium.',
            'duration_minutes' => 0,
            'order' => 1,
            'is_active' => true
        ]);

        FormationModule::create([
            'formation_id' => $formation1_5->id,
            'title' => 'Email de vente',
            'content' => 'Templates d\'emails pour vendre des programmes à 5000€+.',
            'duration_minutes' => 0,
            'order' => 2,
            'is_active' => true
        ]);

        FormationModule::create([
            'formation_id' => $formation1_5->id,
            'title' => 'Script de vente',
            'content' => 'Script complet pour closer des programmes premium.',
            'duration_minutes' => 0,
            'order' => 3,
            'is_active' => true
        ]);

        FormationModule::create([
            'formation_id' => $formation1_8->id,
            'title' => 'Ressources fiscales',
            'content' => 'Documents et liens utiles pour optimiser votre fiscalité.',
            'duration_minutes' => 0,
            'order' => 1,
            'is_active' => true
        ]);

        FormationModule::create([
            'formation_id' => $formation2_4->id,
            'title' => 'Bonus 1 : Tracking des ventes',
            'content' => 'Comment suivre et analyser vos ventes.',
            'duration_minutes' => 0,
            'order' => 1,
            'is_active' => true
        ]);

        FormationModule::create([
            'formation_id' => $formation2_4->id,
            'title' => 'Fiche pratique',
            'content' => 'Checklist et fiche pratique pour votre système automatique.',
            'duration_minutes' => 0,
            'order' => 2,
            'is_active' => true
        ]);

        FormationModule::create([
            'formation_id' => $formation2_4->id,
            'title' => 'Séquence automatique',
            'content' => 'Template de séquence d\'emails automatique.',
            'duration_minutes' => 0,
            'order' => 3,
            'is_active' => true
        ]);

        FormationModule::create([
            'formation_id' => $formation2_6->id,
            'title' => 'Séquence d\'emails automatique',
            'content' => 'Emails pré-écrits pour convertir vos abonnés YouTube.',
            'duration_minutes' => 0,
            'order' => 1,
            'is_active' => true
        ]);

        FormationModule::create([
            'formation_id' => $formation2_12->id,
            'title' => 'Bonus 1 : Créer un bouton Paypal',
            'content' => 'Guide pour configurer vos paiements PayPal.',
            'duration_minutes' => 0,
            'order' => 1,
            'is_active' => true
        ]);

        FormationModule::create([
            'formation_id' => $formation2_12->id,
            'title' => 'Fiches pratiques',
            'content' => 'Ensemble de fiches pratiques pour votre lancement.',
            'duration_minutes' => 0,
            'order' => 2,
            'is_active' => true
        ]);

        FormationModule::create([
            'formation_id' => $formation2_12->id,
            'title' => 'Modèle de sondage',
            'content' => 'Template de sondage pour valider votre idée.',
            'duration_minutes' => 0,
            'order' => 3,
            'is_active' => true
        ]);

        FormationModule::create([
            'formation_id' => $formation2_12->id,
            'title' => 'Fiche de suivi des ventes',
            'content' => 'Tableau Excel pour suivre vos premières ventes.',
            'duration_minutes' => 0,
            'order' => 4,
            'is_active' => true
        ]);

        FormationModule::create([
            'formation_id' => $formation2_12->id,
            'title' => 'Ressources complémentaires',
            'content' => 'Liens et outils recommandés pour votre produit rapide.',
            'duration_minutes' => 0,
            'order' => 5,
            'is_active' => true
        ]);

        // ============================================
        // MESSAGES DE CONFIRMATION
        // ============================================
        $this->command->info('✅ Seeder exécuté avec succès !');
        $this->command->info('📦 ' . FormationPack::count() . ' packs créés');
        $this->command->info('📚 ' . Formation::count() . ' formations créées');
        $this->command->info('🎥 ' . FormationVideo::count() . ' vidéos ajoutées');
        $this->command->info('📄 ' . FormationModule::count() . ' modules créés');
    }
}
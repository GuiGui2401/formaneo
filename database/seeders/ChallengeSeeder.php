<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Challenge;

class ChallengeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $challenges = [
            [
                'title' => 'Première Formation',
                'description' => 'Complétez votre première formation',
                'reward' => 500.00,
                'icon_name' => 'school',
                'target' => 1,
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Quiz Master',
                'description' => 'Réussissez 5 quiz',
                'reward' => 1000.00,
                'icon_name' => 'quiz',
                'target' => 5,
                'order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Parrain Actif',
                'description' => 'Parrainez 3 amis',
                'reward' => 1500.00,
                'icon_name' => 'people',
                'target' => 3,
                'order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Lecteur Assidu',
                'description' => 'Lisez 3 ebooks',
                'reward' => 750.00,
                'icon_name' => 'menu_book',
                'target' => 3,
                'order' => 4,
                'is_active' => true,
            ],
            [
                'title' => 'Champion des Formations',
                'description' => 'Complétez 5 formations',
                'reward' => 2500.00,
                'icon_name' => 'emoji_events',
                'target' => 5,
                'order' => 5,
                'is_active' => true,
            ],
            [
                'title' => 'Connexion Quotidienne',
                'description' => 'Connectez-vous 7 jours consécutifs',
                'reward' => 300.00,
                'icon_name' => 'star',
                'target' => 7,
                'order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($challenges as $challenge) {
            Challenge::create($challenge);
        }
    }
}

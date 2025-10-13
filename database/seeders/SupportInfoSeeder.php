<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SupportInfo;

class SupportInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supportInfos = [
            [
                'type' => 'email',
                'label' => 'Email',
                'value' => 'support@formaneo.com',
                'description' => 'Envoyez-nous un email pour toute question',
                'icon_name' => 'email',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'type' => 'phone',
                'label' => 'Téléphone',
                'value' => '+237 691 59 28 82',
                'description' => 'Appelez-nous du lundi au vendredi de 8h à 18h',
                'icon_name' => 'phone',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'type' => 'whatsapp',
                'label' => 'WhatsApp',
                'value' => 'Chat instantané',
                'description' => 'Contactez-nous via WhatsApp pour une réponse rapide',
                'icon_name' => 'chat',
                'order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($supportInfos as $info) {
            SupportInfo::create($info);
        }
    }
}

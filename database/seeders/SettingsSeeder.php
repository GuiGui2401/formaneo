<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Settings;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key'=>'quiz_reward_per_correct','value'=>'20','type'=>'float','group'=>'quiz','description'=>'Récompense par bonne réponse'],
            ['key'=>'quiz_passing_score','value'=>'60','type'=>'integer','group'=>'quiz','description'=>'Score pour réussir quiz'],
            ['key'=>'min_withdrawal_amount','value'=>'1000','type'=>'float','group'=>'withdrawal','description'=>'Minimum retrait'],
            ['key'=>'mega_api_key','value'=>env('MEGA_API_KEY',''),'type'=>'string','group'=>'storage','description'=>'Mega API key']
        ];

        foreach ($defaults as $s) {
            Settings::updateOrCreate(['key'=>$s['key']], $s);
        }
    }
}

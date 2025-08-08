<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FormationPack;
use App\Models\Formation;
use App\Models\Module;

class FormationPackSeeder extends Seeder
{
    public function run(): void
    {
        $pack = FormationPack::create([
            'name'=>'Starter Pack',
            'slug'=>'starter-pack',
            'author'=>'Equipe Formaneo',
            'description'=>'Pack d\'introduction pour se lancer.',
            'price'=>15000,
            'total_duration'=>300,
            'rating'=>4.5,
            'students_count'=>120,
            'is_active'=>true,
            'is_featured'=>true
        ]);

        $f = Formation::create([
            'pack_id'=>$pack->id,
            'title'=>'Introduction au e-commerce',
            'description'=>'Bases du e-commerce',
            'duration'=>120,
            'video_url'=>'https://mega.mock/form1',
            'order'=>1
        ]);

        Module::create([
            'formation_id'=>$f->id,
            'title'=>'Module 1 - Présentation',
            'duration'=>30,
            'video_url'=>'https://mega.mock/module1',
            'order'=>1
        ]);
    }
}

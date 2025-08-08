<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Seed User',
            'email' => 'seed@formaneo.com',
            'password' => Hash::make('password'),
            'promo_code' => 'SEED01',
            'affiliate_link' => config('app.url').'/invite/SEED01'
        ]);
    }
}

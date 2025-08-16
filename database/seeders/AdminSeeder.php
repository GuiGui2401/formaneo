<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@formaneo.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        Admin::create([
            'name' => 'Content Manager',
            'email' => 'content@formaneo.com',
            'password' => Hash::make('password123'),
            'role' => 'content_manager',
            'is_active' => true,
        ]);
    }
}
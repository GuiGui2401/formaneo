<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PromotionalBanner;

class PromotionalBannerSeeder extends Seeder
{
    public function run()
    {
        $banners = [
            [
                'name' => 'Bannière Formaneo 300x250',
                'file_path' => 'banners/formaneo-banner-300x250.png',
                'width' => 300,
                'height' => 250,
                'is_active' => true,
                'order' => 1,
            ],
            [
                'name' => 'Bannière Formaneo 728x90',
                'file_path' => 'banners/formaneo-banner-728x90.png',
                'width' => 728,
                'height' => 90,
                'is_active' => true,
                'order' => 2,
            ],
            [
                'name' => 'Bannière Formaneo 320x50',
                'file_path' => 'banners/formaneo-banner-320x50.png',
                'width' => 320,
                'height' => 50,
                'is_active' => true,
                'order' => 3,
            ],
        ];

        foreach ($banners as $banner) {
            PromotionalBanner::updateOrCreate(
                ['name' => $banner['name']],
                $banner
            );
        }
    }
}
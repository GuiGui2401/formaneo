<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\FormationPack;
use App\Models\Ebook;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing products
        Product::truncate();

        // Convert FormationPacks to Products
        $formationPacks = FormationPack::all();
        foreach ($formationPacks as $pack) {
            Product::create([
                'name' => $pack->name,
                'slug' => $this->generateUniqueSlug($pack->name),
                'description' => $pack->description,
                'image_url' => $pack->thumbnail_url,
                'price' => $pack->price,
                'promotion_price' => $pack->promotion_price,
                'is_on_promotion' => $pack->isPromotionActive(),
                'category' => 'formation_pack',
                'is_active' => $pack->is_active,
                'metadata' => ['original_id' => $pack->id, 'type' => 'formation_pack'],
            ]);
        }

        // Convert Ebooks to Products
        $ebooks = Ebook::all();
        foreach ($ebooks as $ebook) {
            Product::create([
                'name' => $ebook->title,
                'slug' => $this->generateUniqueSlug($ebook->title),
                'description' => $ebook->description,
                'image_url' => $ebook->cover_image_url,
                'price' => $ebook->price,
                'promotion_price' => null, // Ebooks don't have promotion price currently
                'is_on_promotion' => false,
                'category' => 'ebook',
                'is_active' => $ebook->is_active,
                'metadata' => ['original_id' => $ebook->id, 'type' => 'ebook'],
            ]);
        }

        // Add some dummy digital products
        Product::create([
            'name' => 'Outil de Productivité Avancé',
            'slug' => $this->generateUniqueSlug('Outil de Productivité Avancé'),
            'description' => 'Un ensemble d\'outils pour booster votre productivité au quotidien.',
            'image_url' => 'https://via.placeholder.com/400x300.png?text=Productivity+Tool',
            'price' => 49.99,
            'promotion_price' => 39.99,
            'is_on_promotion' => true,
            'category' => 'tool',
            'is_active' => true,
            'metadata' => ['features' => ['gestion de tâches', 'suivi de temps', 'automatisation']],
        ]);

        Product::create([
            'name' => 'Pack de Modèles Marketing',
            'slug' => $this->generateUniqueSlug('Pack de Modèles Marketing'),
            'description' => 'Des modèles prêts à l\'emploi pour vos campagnes marketing.',
            'image_url' => 'https://via.placeholder.com/400x300.png?text=Marketing+Templates',
            'price' => 29.99,
            'promotion_price' => null,
            'is_on_promotion' => false,
            'category' => 'template',
            'is_active' => true,
            'metadata' => ['includes' => ['email templates', 'social media templates', 'ad copy templates']],
        ]);
    }

    private function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }
}

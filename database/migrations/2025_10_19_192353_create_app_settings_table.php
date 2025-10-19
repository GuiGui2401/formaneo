<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, number, boolean, json
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insérer les paramètres par défaut du centre d'aide
        DB::table('app_settings')->insert([
            [
                'key' => 'support_email',
                'value' => 'support@formaneo.com',
                'type' => 'string',
                'description' => 'Email du centre d\'aide',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'support_phone',
                'value' => '+33 1 23 45 67 89',
                'type' => 'string',
                'description' => 'Téléphone du centre d\'aide',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'support_whatsapp',
                'value' => '+33123456789',
                'type' => 'string',
                'description' => 'Numéro WhatsApp du centre d\'aide',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};

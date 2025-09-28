<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('formation_packs', function (Blueprint $table) {
            $table->boolean('is_on_promotion')->default(false);
            $table->decimal('promotion_price', 10, 2)->nullable();
            $table->timestamp('promotion_starts_at')->nullable();
            $table->timestamp('promotion_ends_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('formation_packs', function (Blueprint $table) {
            $table->dropColumn(['is_on_promotion', 'promotion_price', 'promotion_starts_at', 'promotion_ends_at']);
        });
    }
};

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
        Schema::create('formation_pack_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('pack_id')->references('id')->on('formation_packs')->onDelete('cascade');
            $table->decimal('progress', 5, 2)->default(0); // 0-100%
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cashback_claimed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'pack_id']);
            $table->index(['user_id']);
            $table->index(['pack_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formation_pack_progress');
    }
};

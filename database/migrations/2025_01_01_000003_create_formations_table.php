<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pack_id')->constrained('formation_packs')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration')->default(0);
            $table->string('video_url')->nullable();
            $table->integer('order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('pack_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formations');
    }
};

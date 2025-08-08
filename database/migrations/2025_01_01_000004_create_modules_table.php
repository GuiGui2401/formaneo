<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_id')->constrained('formations')->onDelete('cascade');
            $table->string('title');
            $table->integer('duration')->default(0);
            $table->string('video_url')->nullable();
            $table->integer('order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('formation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};

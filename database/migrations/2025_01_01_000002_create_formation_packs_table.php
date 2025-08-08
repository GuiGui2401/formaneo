<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formation_packs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('author');
            $table->text('description');
            $table->string('thumbnail_url')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('total_duration')->default(0); // en minutes
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('students_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('slug');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formation_packs');
    }
};
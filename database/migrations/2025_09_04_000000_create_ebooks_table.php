<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ebooks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('cover_image_url')->nullable();
            $table->string('pdf_url')->nullable();
            $table->string('author')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('pages')->nullable();
            $table->string('category')->nullable();
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('downloads')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('category');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ebooks');
    }
};
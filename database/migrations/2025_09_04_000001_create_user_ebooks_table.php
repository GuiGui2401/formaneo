<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_ebooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ebook_id')->constrained()->onDelete('cascade');
            $table->decimal('price_paid', 10, 2)->nullable();
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'ebook_id']);
            $table->index(['user_id']);
            $table->index(['ebook_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_ebooks');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('url');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_links');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('profile_image_url')->nullable();
            
            // Wallet
            $table->decimal('balance', 10, 2)->default(0);
            $table->decimal('available_for_withdrawal', 10, 2)->default(0);
            $table->decimal('pending_withdrawals', 10, 2)->default(0);
            
            // Affiliate
            $table->string('promo_code', 10)->unique();
            $table->string('affiliate_link');
            $table->integer('total_affiliates')->default(0);
            $table->integer('monthly_affiliates')->default(0);
            $table->string('referred_by')->nullable();
            $table->decimal('total_commissions', 10, 2)->default(0);
            
            // Quiz
            $table->integer('free_quizzes_left')->default(5);
            $table->integer('total_quizzes_taken')->default(0);
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_premium')->default(false);
            $table->timestamp('last_login_at')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            $table->json('settings')->nullable();
            
            $table->rememberToken();
            $table->timestamps();
            
            // Indexes
            $table->index('email');
            $table->index('promo_code');
            $table->index('referred_by');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
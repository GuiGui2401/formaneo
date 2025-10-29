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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('account_status', ['inactive', 'active', 'expired'])
                  ->default('active')
                  ->after('email_verified_at');
            $table->timestamp('account_activated_at')->nullable()->after('account_status');
            $table->timestamp('account_expires_at')->nullable()->after('account_activated_at');
            $table->boolean('welcome_bonus_claimed')->default(false)->after('account_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['account_status', 'account_activated_at', 'account_expires_at', 'welcome_bonus_claimed']);
        });
    }
};

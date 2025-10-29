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
            $table->boolean('fake_affiliate_data_enabled')->default(false)->after('welcome_bonus_claimed');
            $table->json('fake_affiliate_data')->nullable()->after('fake_affiliate_data_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['fake_affiliate_data_enabled', 'fake_affiliate_data']);
        });
    }
};

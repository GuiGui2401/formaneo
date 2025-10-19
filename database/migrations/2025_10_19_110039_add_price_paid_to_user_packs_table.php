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
        Schema::table('user_packs', function (Blueprint $table) {
            $table->decimal('price_paid', 10, 2)->after('pack_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_packs', function (Blueprint $table) {
            $table->dropColumn('price_paid');
        });
    }
};

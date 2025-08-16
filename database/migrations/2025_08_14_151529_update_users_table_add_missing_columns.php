<?php
// 2025_08_14_000001_update_users_table_add_missing_columns.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTableAddMissingColumns extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Ajouter colonnes manquantes si elles n'existent pas déjà
            if (!Schema::hasColumn('users', 'passed_quizzes')) {
                $table->integer('passed_quizzes')->default(0)->after('total_quizzes_taken');
            }
            
            // Modifier referred_by pour être une référence à promo_code au lieu de id
            if (Schema::hasColumn('users', 'referred_by')) {
                // Modifier le type de referred_by pour accepter des strings (promo_code)
                $table->string('referred_by', 10)->nullable()->change();
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'passed_quizzes')) {
                $table->dropColumn('passed_quizzes');
            }
        });
    }
}

?>


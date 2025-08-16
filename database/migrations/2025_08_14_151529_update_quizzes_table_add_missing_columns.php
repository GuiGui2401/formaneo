<?php
// 2025_08_14_000004_update_quizzes_table_add_missing_columns.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateQuizzesTableAddMissingColumns extends Migration
{
    public function up()
    {
        Schema::table('quizzes', function (Blueprint $table) {
            // Ajouter colonnes manquantes
            if (!Schema::hasColumn('quizzes', 'questions')) {
                $table->json('questions')->nullable()->after('description');
            }
            if (!Schema::hasColumn('quizzes', 'difficulty')) {
                $table->string('difficulty')->default('medium')->after('questions');
            }
            if (!Schema::hasColumn('quizzes', 'subject')) {
                $table->string('subject')->nullable()->after('difficulty');
            }
            if (!Schema::hasColumn('quizzes', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('reward_per_correct');
            }
            
            // Modifier questions_count pour être après subject
            if (Schema::hasColumn('quizzes', 'questions_count')) {
                // La colonne existe déjà, on peut juste ajouter un index si nécessaire
            }
        });
        
        // Ajouter l'index après avoir ajouté les colonnes
        Schema::table('quizzes', function (Blueprint $table) {
            if (Schema::hasColumn('quizzes', 'is_active') && Schema::hasColumn('quizzes', 'subject')) {
                $table->index(['is_active', 'subject']);
            }
        });
    }

    public function down()
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'subject']);
            
            if (Schema::hasColumn('quizzes', 'questions')) {
                $table->dropColumn('questions');
            }
            if (Schema::hasColumn('quizzes', 'difficulty')) {
                $table->dropColumn('difficulty');
            }
            if (Schema::hasColumn('quizzes', 'subject')) {
                $table->dropColumn('subject');
            }
            if (Schema::hasColumn('quizzes', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
}

?>

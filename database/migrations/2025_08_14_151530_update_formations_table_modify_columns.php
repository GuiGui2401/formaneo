<?php
// 2025_08_14_000005_update_formations_table_modify_columns.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFormationsTableModifyColumns extends Migration
{
    public function up()
    {
        Schema::table('formations', function (Blueprint $table) {
            // Renommer duration en duration_minutes si nécessaire
            if (Schema::hasColumn('formations', 'duration') && !Schema::hasColumn('formations', 'duration_minutes')) {
                $table->renameColumn('duration', 'duration_minutes');
            }
            
            // Ajouter is_active si elle n'existe pas
            if (!Schema::hasColumn('formations', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('metadata');
            }
        });
    }

    public function down()
    {
        Schema::table('formations', function (Blueprint $table) {
            if (Schema::hasColumn('formations', 'duration_minutes') && !Schema::hasColumn('formations', 'duration')) {
                $table->renameColumn('duration_minutes', 'duration');
            }
            
            if (Schema::hasColumn('formations', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
}

?>
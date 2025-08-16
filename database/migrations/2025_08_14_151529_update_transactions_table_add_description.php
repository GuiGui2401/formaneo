<?php
// 2025_08_14_000003_update_transactions_table_add_description.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTransactionsTableAddDescription extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Ajouter la colonne description si elle n'existe pas
            if (!Schema::hasColumn('transactions', 'description')) {
                $table->string('description')->nullable()->after('amount');
            }
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
}

?>
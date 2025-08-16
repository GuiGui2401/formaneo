<?php
// 2025_08_14_000002_update_affiliate_links_table_add_columns.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAffiliateLinksTableAddColumns extends Migration
{
    public function up()
    {
        Schema::table('affiliate_links', function (Blueprint $table) {
            // Ajouter colonnes manquantes
            if (!Schema::hasColumn('affiliate_links', 'clicks')) {
                $table->integer('clicks')->default(0)->after('url');
            }
            if (!Schema::hasColumn('affiliate_links', 'conversions')) {
                $table->integer('conversions')->default(0)->after('clicks');
            }
        });
    }

    public function down()
    {
        Schema::table('affiliate_links', function (Blueprint $table) {
            if (Schema::hasColumn('affiliate_links', 'clicks')) {
                $table->dropColumn('clicks');
            }
            if (Schema::hasColumn('affiliate_links', 'conversions')) {
                $table->dropColumn('conversions');
            }
        });
    }
}

?>
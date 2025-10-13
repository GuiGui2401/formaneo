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
        Schema::create('support_infos', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // email, phone, whatsapp, faq, etc.
            $table->string('label');
            $table->string('value');
            $table->text('description')->nullable();
            $table->string('icon_name')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_infos');
    }
};

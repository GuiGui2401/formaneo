<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPacksTable extends Migration
{
    public function up()
    {
        Schema::create('user_packs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('pack_id')->constrained('formation_packs')->onDelete('cascade');
            $table->timestamp('purchased_at');
            $table->timestamps();
            
            $table->unique(['user_id', 'pack_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_packs');
    }
}
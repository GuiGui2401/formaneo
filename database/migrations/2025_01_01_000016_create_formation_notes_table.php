<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormationNotesTable extends Migration
{
    public function up()
    {
        Schema::create('formation_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('formation_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->string('timestamp'); // timestamp de la vidéo
            $table->timestamps();
            
            $table->index(['user_id', 'formation_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('formation_notes');
    }
}
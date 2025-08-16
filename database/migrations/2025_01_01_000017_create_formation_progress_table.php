<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormationProgressTable extends Migration
{
    public function up()
    {
        Schema::create('formation_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('formation_id')->constrained()->onDelete('cascade');
            $table->decimal('progress', 5, 2)->default(0); // 0-100%
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cashback_claimed_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'formation_id']);
            $table->index(['user_id']);
            $table->index(['formation_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('formation_progress');
    }
}

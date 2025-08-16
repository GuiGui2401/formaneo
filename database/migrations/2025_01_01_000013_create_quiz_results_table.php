<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuizResultsTable extends Migration
{
    public function up()
    {
        Schema::create('quiz_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('quiz_id'); // Peut être généré côté client
            $table->decimal('score', 5, 2); // 0-100%
            $table->integer('total_questions');
            $table->integer('correct_answers');
            $table->integer('time_taken'); // en secondes
            $table->string('subject');
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['subject']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('quiz_results');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Settings;

class QuizController extends Controller
{
    // lister quizzes
    public function index()
    {
        $quizzes = Quiz::paginate(12);
        return response()->json($quizzes);
    }

    // récupérer un quiz
    public function show($id)
    {
        $quiz = Quiz::findOrFail($id);
        return response()->json($quiz);
    }

    // soumettre résultats d'un quiz (simplifié)
    public function submit(Request $request, $id)
    {
        $request->validate([
            'score'=>'required|numeric|min:0|max:100'
        ]);

        $quiz = Quiz::findOrFail($id);
        $user = $request->user();

        $user->total_quizzes_taken += 1;
        // récompense par bonne réponse proportionnelle
        $rewardPerCorrect = (float) Settings::where('key','quiz_reward_per_correct')->value('value') ?? 0;
        $passingScore = (int) Settings::where('key','quiz_passing_score')->value('value') ?? 60;

        // hypothèse: reward = score% * rewardPerCorrect * nombre_de_questions (ici on simplifie)
        $reward = ($request->score / 100) * ($rewardPerCorrect * ($quiz->questions_count ?? 5));

        // si user a encore des free quiz -> ne pas payer
        if ($user->free_quizzes_left > 0) {
            $user->free_quizzes_left -= 1;
        } else {
            $user->balance += $reward;
            $user->total_commissions += $reward;
            // transaction
            $user->transactions()->create([
                'type'=>'quiz_reward',
                'amount'=>$reward,
                'meta'=>json_encode(['quiz_id'=>$quiz->id,'score'=>$request->score])
            ]);
        }

        $user->save();

        return response()->json(['message'=>'Quiz soumis','reward'=>round($reward,2),'balance'=>$user->balance,'passed'=>$request->score >= $passingScore]);
    }
}

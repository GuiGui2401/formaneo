<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\Settings;

class QuizController extends Controller
{
    // Obtenir les quiz disponibles
    public function available(Request $request)
    {
        $quizzes = Quiz::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'quizzes' => $quizzes
        ]);
    }

    // Obtenir le nombre de quiz gratuits restants
    public function getFreeCount(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'free_quizzes_left' => $user->free_quizzes_left ?? 5
        ]);
    }

    // Sauvegarder le résultat d'un quiz
    public function saveResult(Request $request)
    {
        $request->validate([
            'quiz_id' => 'required|string',
            'score' => 'required|numeric|min:0|max:100',
            'total_questions' => 'required|integer|min:1',
            'correct_answers' => 'required|integer|min:0',
            'time_taken' => 'required|integer|min:0',
            'subject' => 'required|string'
        ]);

        $user = $request->user();
        
        // Créer le résultat du quiz
        $quizResult = QuizResult::create([
            'user_id' => $user->id,
            'quiz_id' => $request->quiz_id,
            'score' => $request->score,
            'total_questions' => $request->total_questions,
            'correct_answers' => $request->correct_answers,
            'time_taken' => $request->time_taken,
            'subject' => $request->subject,
        ]);

        // Calculer la récompense
        $rewardPerCorrect = 20; // FCFA par réponse correcte
        $passingScore = 60; // Score minimum pour passer
        
        $totalReward = 0;
        $passed = $request->score >= $passingScore;

        // Si l'utilisateur a encore des quiz gratuits
        if ($user->free_quizzes_left > 0) {
            $user->decrement('free_quizzes_left');
            
            // Récompenser seulement si le quiz est réussi
            if ($passed) {
                $totalReward = $request->correct_answers * $rewardPerCorrect;
                $user->increment('balance', $totalReward);
                
                // Créer une transaction
                $user->transactions()->create([
                    'type' => 'quiz_reward',
                    'amount' => $totalReward,
                    'description' => "Récompense Quiz - {$request->subject}",
                    'meta' => json_encode([
                        'quiz_id' => $request->quiz_id,
                        'score' => $request->score,
                        'correct_answers' => $request->correct_answers
                    ])
                ]);
            }
        }

        // Mettre à jour les statistiques de l'utilisateur
        $user->increment('total_quizzes_taken');
        if ($passed) {
            $user->increment('passed_quizzes', 1);
        }

        return response()->json([
            'success' => true,
            'result_id' => $quizResult->id,
            'passed' => $passed,
            'reward' => $totalReward,
            'new_balance' => $user->balance,
            'free_quizzes_left' => $user->free_quizzes_left
        ]);
    }

    // Obtenir l'historique des quiz
    public function getHistory(Request $request)
    {
        $user = $request->user();
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);

        $results = QuizResult::where('user_id', $user->id)
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'results' => $results->items(),
            'pagination' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
            ]
        ]);
    }

    // Obtenir les statistiques des quiz
    public function getStats(Request $request)
    {
        $user = $request->user();

        $totalQuizzes = $user->total_quizzes_taken ?? 0;
        $passedQuizzes = $user->passed_quizzes ?? 0;
        
        $averageScore = 0;
        if ($totalQuizzes > 0) {
            $averageScore = QuizResult::where('user_id', $user->id)
                ->avg('score');
        }

        $totalRewards = $user->transactions()
            ->where('type', 'quiz_reward')
            ->sum('amount');

        return response()->json([
            'total_quizzes' => $totalQuizzes,
            'passed_quizzes' => $passedQuizzes,
            'average_score' => round($averageScore, 1),
            'total_rewards' => $totalRewards
        ]);
    }
}
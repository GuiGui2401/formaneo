<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChallengeController extends Controller
{
    /**
     * Obtenir tous les défis disponibles
     */
    public function index()
    {
        $challenges = Challenge::active()
            ->notExpired()
            ->orderBy('order', 'asc')
            ->get();

        return response()->json([
            'challenges' => $challenges,
        ]);
    }

    /**
     * Obtenir les défis de l'utilisateur avec leur progression
     */
    public function userChallenges()
    {
        $user = Auth::user();

        // Récupérer tous les défis actifs
        $challenges = Challenge::active()
            ->notExpired()
            ->orderBy('order', 'asc')
            ->get();

        // Enrichir avec la progression de l'utilisateur
        $challengesWithProgress = $challenges->map(function ($challenge) use ($user) {
            $userChallenge = $user->challenges()
                ->where('challenge_id', $challenge->id)
                ->first();

            return [
                'id' => $challenge->id,
                'title' => $challenge->title,
                'description' => $challenge->description,
                'reward' => $challenge->reward,
                'image_url' => $challenge->image_url,
                'icon_name' => $challenge->icon_name,
                'target' => $challenge->target,
                'expires_at' => $challenge->expires_at,
                'progress' => $userChallenge ? $userChallenge->pivot->progress : 0,
                'is_completed' => $userChallenge ? $userChallenge->pivot->is_completed : false,
                'reward_claimed' => $userChallenge ? $userChallenge->pivot->reward_claimed : false,
                'created_at' => $challenge->created_at,
                'updated_at' => $challenge->updated_at,
            ];
        });

        return response()->json([
            'challenges' => $challengesWithProgress,
        ]);
    }

    /**
     * Marquer un défi comme complété
     */
    public function complete(Request $request, $id)
    {
        $user = Auth::user();
        $challenge = Challenge::findOrFail($id);

        // Vérifier si le défi existe déjà pour l'utilisateur
        $userChallenge = $user->challenges()->where('challenge_id', $id)->first();

        if ($userChallenge && $userChallenge->pivot->is_completed) {
            return response()->json([
                'success' => false,
                'message' => 'Défi déjà complété',
            ], 400);
        }

        // Marquer comme complété
        if ($userChallenge) {
            $user->challenges()->updateExistingPivot($id, [
                'progress' => $challenge->target ?? 100,
                'is_completed' => true,
                'completed_at' => now(),
            ]);
        } else {
            $user->challenges()->attach($id, [
                'progress' => $challenge->target ?? 100,
                'is_completed' => true,
                'completed_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Défi complété avec succès',
        ]);
    }

    /**
     * Réclamer la récompense d'un défi
     */
    public function claimReward(Request $request, $id)
    {
        $user = Auth::user();
        $challenge = Challenge::findOrFail($id);

        $userChallenge = $user->challenges()->where('challenge_id', $id)->first();

        if (!$userChallenge || !$userChallenge->pivot->is_completed) {
            return response()->json([
                'success' => false,
                'message' => 'Défi non complété',
            ], 400);
        }

        if ($userChallenge->pivot->reward_claimed) {
            return response()->json([
                'success' => false,
                'message' => 'Récompense déjà réclamée',
            ], 400);
        }

        // Ajouter la récompense au solde de l'utilisateur
        $user->balance += $challenge->reward;
        $user->save();

        // Marquer la récompense comme réclamée
        $user->challenges()->updateExistingPivot($id, [
            'reward_claimed' => true,
        ]);

        // Créer une transaction
        $user->transactions()->create([
            'type' => 'credit',
            'amount' => $challenge->reward,
            'description' => 'Récompense défi: ' . $challenge->title,
            'status' => 'completed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Récompense réclamée avec succès',
            'new_balance' => $user->balance,
        ]);
    }

    /**
     * Mettre à jour la progression d'un défi pour l'utilisateur
     */
    public function updateProgress(Request $request, $id)
    {
        $request->validate([
            'progress' => 'required|integer|min:0',
        ]);

        $user = Auth::user();
        $challenge = Challenge::findOrFail($id);

        $userChallenge = $user->challenges()->where('challenge_id', $id)->first();

        $isCompleted = $challenge->target && $request->progress >= $challenge->target;

        if ($userChallenge) {
            $user->challenges()->updateExistingPivot($id, [
                'progress' => $request->progress,
                'is_completed' => $isCompleted,
                'completed_at' => $isCompleted ? now() : null,
            ]);
        } else {
            $user->challenges()->attach($id, [
                'progress' => $request->progress,
                'is_completed' => $isCompleted,
                'completed_at' => $isCompleted ? now() : null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Progression mise à jour',
            'is_completed' => $isCompleted,
        ]);
    }
}

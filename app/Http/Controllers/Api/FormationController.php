<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Formation;
use App\Models\FormationProgress;
use App\Models\FormationNote;

class FormationController extends Controller
{
    // Obtenir une formation spécifique
    public function show(Request $request, $id)
    {
        $formation = Formation::with(['modules', 'pack'])->findOrFail($id);
        $user = $request->user();

        // Récupérer le progrès de l'utilisateur
        $progress = FormationProgress::where('user_id', $user->id)
            ->where('formation_id', $id)
            ->first();

        $formationData = $formation->toArray();
        $formationData['user_progress'] = $progress ? $progress->progress : 0;
        $formationData['completed_at'] = $progress ? $progress->completed_at : null;

        return response()->json([
            'formation' => $formationData
        ]);
    }

    // Mettre à jour le progrès d'une formation
    public function updateProgress(Request $request, $id)
    {
        $request->validate([
            'progress' => 'required|numeric|min:0|max:100'
        ]);

        $user = $request->user();
        $formation = Formation::findOrFail($id);

        $progress = FormationProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'formation_id' => $id
            ],
            [
                'progress' => $request->progress,
                'completed_at' => $request->progress >= 100 ? now() : null
            ]
        );

        return response()->json([
            'success' => true,
            'progress' => $progress->progress
        ]);
    }

    // Marquer un module comme complété
    public function completeModule(Request $request, $moduleId)
    {
        $user = $request->user();
        
        // Logic pour marquer un module comme complété
        // Vous devrez créer un modèle ModuleProgress ou similaire
        
        return response()->json([
            'success' => true,
            'message' => 'Module complété'
        ]);
    }

    // Réclamer le cashback d'une formation
    public function claimCashback(Request $request, $id)
    {
        $user = $request->user();
        $formation = Formation::findOrFail($id);

        // Vérifier si la formation est complétée
        $progress = FormationProgress::where('user_id', $user->id)
            ->where('formation_id', $id)
            ->where('progress', 100)
            ->first();

        if (!$progress) {
            return response()->json([
                'success' => false,
                'message' => 'Formation non complétée'
            ], 400);
        }

        // Vérifier si le cashback n'a pas déjà été réclamé
        if ($progress->cashback_claimed_at) {
            return response()->json([
                'success' => false,
                'message' => 'Cashback déjà réclamé'
            ], 400);
        }

        // Calculer le cashback (15% du prix de la formation)
        $cashbackAmount = $formation->pack->price * 0.15;

        // Ajouter le cashback au solde de l'utilisateur
        $user->increment('balance', $cashbackAmount);
        
        // Créer une transaction
        $user->transactions()->create([
            'type' => 'cashback',
            'amount' => $cashbackAmount,
            'description' => "Cashback formation - {$formation->title}",
            'meta' => json_encode(['formation_id' => $id])
        ]);

        // Marquer le cashback comme réclamé
        $progress->update(['cashback_claimed_at' => now()]);

        return response()->json([
            'success' => true,
            'cashback_amount' => $cashbackAmount,
            'new_balance' => $user->balance
        ]);
    }

    // Obtenir les statistiques de progression
    public function getProgressStats(Request $request)
    {
        $user = $request->user();

        $totalFormations = FormationProgress::where('user_id', $user->id)->count();
        $completedFormations = FormationProgress::where('user_id', $user->id)
            ->where('progress', 100)
            ->count();

        $totalHours = FormationProgress::join('formations', 'formation_progress.formation_id', '=', 'formations.id')
            ->where('formation_progress.user_id', $user->id)
            ->where('formation_progress.progress', 100)
            ->sum('formations.duration_minutes') / 60;

        $certificatesEarned = $completedFormations; // Supposons 1 certificat par formation complétée

        $totalCashback = $user->transactions()
            ->where('type', 'cashback')
            ->sum('amount');

        return response()->json([
            'total_formations' => $totalFormations,
            'completed_formations' => $completedFormations,
            'total_hours' => round($totalHours, 1),
            'certificates_earned' => $certificatesEarned,
            'total_cashback' => $totalCashback
        ]);
    }

    // Télécharger le certificat d'une formation
    public function downloadCertificate(Request $request, $id)
    {
        $user = $request->user();
        $formation = Formation::findOrFail($id);

        // Vérifier si la formation est complétée
        $progress = FormationProgress::where('user_id', $user->id)
            ->where('formation_id', $id)
            ->where('progress', 100)
            ->first();

        if (!$progress) {
            return response()->json([
                'success' => false,
                'message' => 'Formation non complétée'
            ], 400);
        }

        // Générer l'URL du certificat (à implémenter selon vos besoins)
        $certificateUrl = config('app.url') . "/certificates/{$user->id}/{$id}.pdf";

        return response()->json([
            'certificate_url' => $certificateUrl
        ]);
    }

    // Obtenir les notes d'une formation
    public function getNotes(Request $request, $id)
    {
        $user = $request->user();

        $notes = FormationNote::where('user_id', $user->id)
            ->where('formation_id', $id)
            ->orderBy('timestamp')
            ->get();

        return response()->json([
            'notes' => $notes->map(function ($note) {
                return [
                    'id' => $note->id,
                    'note' => $note->content,
                    'timestamp' => $note->timestamp,
                    'created_at' => $note->created_at->toISOString(),
                ];
            })
        ]);
    }

    // Ajouter une note à une formation
    public function addNote(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|string|max:1000',
            'timestamp' => 'required|string'
        ]);

        $user = $request->user();

        $note = FormationNote::create([
            'user_id' => $user->id,
            'formation_id' => $id,
            'content' => $request->note,
            'timestamp' => $request->timestamp
        ]);

        return response()->json([
            'success' => true,
            'note' => [
                'id' => $note->id,
                'note' => $note->content,
                'timestamp' => $note->timestamp,
                'created_at' => $note->created_at->toISOString(),
            ]
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Formation;
use App\Models\FormationProgress;
use App\Models\FormationNote;
use App\Models\FormationVideo;
use App\Models\FormationVideoProgress;
use App\Models\FormationCertificate;
use App\Models\FormationPackProgress;
use App\Models\FormationPack;

class FormationController extends Controller
{
    // Obtenir toutes les formations de l'utilisateur (via les packs achetés)
    public function getUserFormations(Request $request)
    {
        $user = $request->user();

        // Récupérer tous les packs achetés par l'utilisateur
        $userPacks = $user->ownedPacks()->with('formations.videos')->get();

        // Extraire toutes les formations des packs achetés
        $formations = $userPacks->flatMap(function ($pack) use ($user) {
            return $pack->formations->map(function ($formation) use ($user, $pack) {
                // Récupérer le progrès de l'utilisateur pour cette formation
                $progress = FormationProgress::where('user_id', $user->id)
                    ->where('formation_id', $formation->id)
                    ->first();

                return [
                    'id' => $formation->id,
                    'pack_id' => $pack->id,
                    'pack_name' => $pack->name,
                    'title' => $formation->title,
                    'description' => $formation->description,
                    'duration_minutes' => $formation->duration_minutes,
                    'order' => $formation->order,
                    'video_count' => $formation->videos->count(),
                    'user_progress' => $progress ? $progress->progress : 0,
                    'completed_at' => $progress ? $progress->completed_at : null,
                    'is_active' => $formation->is_active,
                    'created_at' => $formation->created_at->toIso8601String(),
                    'updated_at' => $formation->updated_at->toIso8601String(),
                    'thumbnail_url' => $formation->thumbnail_url,
                    'videos' => $formation->videos,
                ];
            });
        });

        return response()->json([
            'success' => true,
            'formations' => $formations->values()->all(),
        ]);
    }

    // Obtenir une formation spécifique avec ses vidéos
    public function show(Request $request, $id)
    {
        $formation = Formation::with(['videos', 'pack'])->findOrFail($id);
        $user = $request->user();

        // Récupérer le progrès de l'utilisateur pour cette formation
        $progress = FormationProgress::where('user_id', $user->id)
            ->where('formation_id', $id)
            ->first();

        // Récupérer le progrès de chaque vidéo
        $videosData = $formation->videos->map(function ($video) use ($user) {
            $videoProgress = FormationVideoProgress::where('user_id', $user->id)
                ->where('formation_video_id', $video->id)
                ->first();

            $videoData = $video->toArray();
            $videoData['user_progress'] = $videoProgress ? $videoProgress->progress : 0;
            $videoData['completed_at'] = $videoProgress ? $videoProgress->completed_at : null;

            return $videoData;
        });

        $formationData = $formation->toArray();
        $formationData['videos'] = $videosData;
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

    // Mettre à jour la progression d'une vidéo
    public function updateVideoProgress(Request $request, $videoId)
    {
        $request->validate([
            'progress' => 'required|numeric|min:0|max:100'
        ]);

        $user = $request->user();
        $video = FormationVideo::findOrFail($videoId);

        $videoProgress = FormationVideoProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'formation_video_id' => $videoId
            ],
            [
                'progress' => $request->progress,
                'completed_at' => $request->progress >= 100 ? now() : null
            ]
        );

        // Recalculer la progression de la formation
        $this->updateFormationProgress($user->id, $video->formation_id);

        return response()->json([
            'success' => true,
            'video_progress' => $videoProgress->progress,
            'formation_progress' => $this->getFormationProgressPercentage($user->id, $video->formation_id)
        ]);
    }

    // Calculer et mettre à jour la progression d'une formation
    private function updateFormationProgress($userId, $formationId)
    {
        $formation = Formation::with('videos')->findOrFail($formationId);
        $totalVideos = $formation->videos->count();

        if ($totalVideos == 0) {
            return 0;
        }

        // Calculer le pourcentage moyen de progression de toutes les vidéos
        $totalProgress = FormationVideoProgress::whereIn('formation_video_id', $formation->videos->pluck('id'))
            ->where('user_id', $userId)
            ->sum('progress');

        $overallProgress = $totalProgress / $totalVideos;

        // Mettre à jour la progression de la formation
        $formationProgress = FormationProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'formation_id' => $formationId
            ],
            [
                'progress' => $overallProgress,
                'completed_at' => $overallProgress >= 100 ? now() : null
            ]
        );

        // Si la formation est complétée, créer un certificat
        if ($overallProgress >= 100 && !FormationCertificate::where('user_id', $userId)->where('formation_id', $formationId)->exists()) {
            FormationCertificate::create([
                'user_id' => $userId,
                'formation_id' => $formationId,
                'certificate_number' => FormationCertificate::generateCertificateNumber(),
                'issued_at' => now(),
            ]);

            // Mettre à jour la progression du pack
            $this->updatePackProgress($userId, $formation->pack_id);
        }

        return $overallProgress;
    }

    // Calculer et mettre à jour la progression d'un pack
    private function updatePackProgress($userId, $packId)
    {
        $pack = FormationPack::with('formations')->findOrFail($packId);
        $totalFormations = $pack->formations->count();

        if ($totalFormations == 0) {
            return 0;
        }

        // Compter combien de formations sont complétées
        $completedFormations = FormationProgress::whereIn('formation_id', $pack->formations->pluck('id'))
            ->where('user_id', $userId)
            ->where('progress', 100)
            ->count();

        $packProgress = ($completedFormations / $totalFormations) * 100;

        // Mettre à jour la progression du pack
        $packProgressRecord = FormationPackProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'pack_id' => $packId
            ],
            [
                'progress' => $packProgress,
                'completed_at' => $packProgress >= 100 ? now() : null
            ]
        );

        return $packProgress;
    }

    // Obtenir le pourcentage de progression d'une formation
    private function getFormationProgressPercentage($userId, $formationId)
    {
        $progress = FormationProgress::where('user_id', $userId)
            ->where('formation_id', $formationId)
            ->first();

        return $progress ? $progress->progress : 0;
    }

    // Marquer un module comme complété (ancienne méthode, gardée pour compatibilité)
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

    // Réclamer le cashback d'un pack de formations
    public function claimCashback(Request $request, $packId)
    {
        $user = $request->user();
        $pack = FormationPack::findOrFail($packId);

        // Vérifier si le pack est complété
        $packProgress = FormationPackProgress::where('user_id', $user->id)
            ->where('pack_id', $packId)
            ->where('progress', 100)
            ->first();

        if (!$packProgress) {
            return response()->json([
                'success' => false,
                'message' => 'Pack de formation non complété'
            ], 400);
        }

        // Vérifier si le cashback n'a pas déjà été réclamé
        if ($packProgress->cashback_claimed_at) {
            return response()->json([
                'success' => false,
                'message' => 'Cashback déjà réclamé'
            ], 400);
        }

        // Utiliser le montant de cashback défini dans le pack
        $cashbackAmount = $pack->cashback_amount;

        if ($cashbackAmount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun cashback disponible pour ce pack'
            ], 400);
        }

        // Ajouter le cashback au solde de l'utilisateur
        $user->increment('balance', $cashbackAmount);

        // Créer une transaction
        $user->transactions()->create([
            'type' => 'cashback',
            'amount' => $cashbackAmount,
            'description' => "Cashback pack de formation - {$pack->name}",
            'status' => 'completed',
            'meta' => json_encode(['pack_id' => $packId])
        ]);

        // Marquer le cashback comme réclamé
        $packProgress->update(['cashback_claimed_at' => now()]);

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

    // Obtenir tous les certificats de l'utilisateur
    public function getCertificates(Request $request)
    {
        $user = $request->user();

        $certificates = FormationCertificate::with('formation.pack')
            ->where('user_id', $user->id)
            ->orderBy('issued_at', 'desc')
            ->get();

        return response()->json([
            'certificates' => $certificates->map(function ($certificate) {
                return [
                    'id' => $certificate->id,
                    'certificate_number' => $certificate->certificate_number,
                    'formation_title' => $certificate->formation->title,
                    'pack_name' => $certificate->formation->pack->name,
                    'issued_at' => $certificate->issued_at->toISOString(),
                    'certificate_url' => $certificate->certificate_url,
                ];
            })
        ]);
    }

    // Télécharger le certificat d'une formation
    public function downloadCertificate(Request $request, $id)
    {
        $user = $request->user();

        $certificate = FormationCertificate::with('formation')
            ->where('user_id', $user->id)
            ->where('formation_id', $id)
            ->first();

        if (!$certificate) {
            return response()->json([
                'success' => false,
                'message' => 'Certificat non trouvé. Complétez la formation pour obtenir le certificat.'
            ], 404);
        }

        // Générer l'URL du certificat (à implémenter selon vos besoins)
        $certificateUrl = $certificate->certificate_url ?: config('app.url') . "/api/v1/certificates/{$certificate->id}/download";

        return response()->json([
            'success' => true,
            'certificate_url' => $certificateUrl,
            'certificate_number' => $certificate->certificate_number
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
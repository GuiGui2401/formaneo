<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FormationPack;
use App\Models\Formation;
use App\Models\UserPack;

class FormationPackController extends Controller
{
    // Liste des packs de formations
    public function index(Request $request)
    {
        $packs = FormationPack::where('is_active', true)
            ->with('formations')
            ->orderBy('is_featured', 'desc')
            ->orderBy('order')
            ->get();

        // Formater les données pour correspondre à l'attente du frontend
        $formattedPacks = $packs->map(function ($pack) {
            return [
                'id' => $pack->id,
                'name' => $pack->name,
                'slug' => $pack->slug,
                'author' => $pack->author,
                'description' => $pack->description,
                'thumbnail_url' => $pack->thumbnail_url,
                'price' => $pack->price,
                'total_duration' => $pack->total_duration,
                'rating' => $pack->rating,
                'students_count' => $pack->students_count,
                'formations_count' => $pack->formations->count(),
                'is_featured' => $pack->is_featured,
                'created_at' => $pack->created_at,
            ];
        });

        return response()->json([
            'packs' => $formattedPacks
        ]);
    }

    // Afficher un pack spécifique
    public function show($id)
    {
        $pack = FormationPack::with(['formations.modules'])
            ->where('is_active', true)
            ->findOrFail($id);

        return response()->json([
            'pack' => $pack
        ]);
    }

    // Acheter un pack
    public function purchase(Request $request, $id)
    {
        $pack = FormationPack::findOrFail($id);
        $user = $request->user();

        // Vérifier si l'utilisateur possède déjà ce pack
        $userPack = UserPack::where('user_id', $user->id)
            ->where('pack_id', $pack->id)
            ->first();

        if ($userPack) {
            return response()->json([
                'success' => false,
                'message' => 'Vous possédez déjà ce pack'
            ], 400);
        }

        // Vérifier le solde
        if ($user->balance < $pack->price) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant'
            ], 400);
        }

        // Effectuer l'achat
        $user->decrement('balance', $pack->price);

        // Créer la relation utilisateur-pack
        UserPack::create([
            'user_id' => $user->id,
            'pack_id' => $pack->id,
            'purchased_at' => now(),
        ]);

        // Créer la transaction
        $user->transactions()->create([
            'type' => 'purchase',
            'amount' => -$pack->price,
            'description' => "Achat pack {$pack->name}",
            'status' => 'completed',
            'meta' => json_encode([
                'pack_id' => $pack->id,
                'pack_name' => $pack->name
            ])
        ]);

        // Traiter la commission pour le parrain si applicable
        $this->processReferralCommission($user, $pack->price);

        return response()->json([
            'success' => true,
            'message' => 'Pack acheté avec succès',
            'new_balance' => $user->balance
        ]);
    }

    // Obtenir les formations d'un pack
    public function getFormations(Request $request, $id)
    {
        $user = $request->user();
        $pack = FormationPack::findOrFail($id);

        // Vérifier si l'utilisateur possède ce pack
        $userPack = UserPack::where('user_id', $user->id)
            ->where('pack_id', $pack->id)
            ->first();

        if (!$userPack) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne possédez pas ce pack'
            ], 403);
        }

        $formations = Formation::where('pack_id', $pack->id)
            ->with(['modules', 'progress' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->get();

        return response()->json([
            'formations' => $formations
        ]);
    }

    // Traiter la commission de parrainage
    private function processReferralCommission($user, $purchaseAmount)
    {
        if (!$user->referred_by) {
            return;
        }

        $referrer = \App\Models\User::find($user->referred_by);
        if (!$referrer) {
            return;
        }

        // Commission niveau 1 (1000 FCFA pour premier achat)
        $level1Commission = 1000.0;
        
        // Vérifier si c'est le premier achat de ce filleul
        $isFirstPurchase = $user->transactions()
            ->where('type', 'purchase')
            ->count() === 1;

        if ($isFirstPurchase) {
            $referrer->increment('balance', $level1Commission);
            $referrer->increment('total_commissions', $level1Commission);

            $referrer->transactions()->create([
                'type' => 'commission',
                'amount' => $level1Commission,
                'description' => "Commission niveau 1 - Premier achat de {$user->name}",
                'status' => 'completed',
                'meta' => json_encode([
                    'referral_id' => $user->id,
                    'referral_name' => $user->name,
                    'level' => 1,
                    'purchase_amount' => $purchaseAmount
                ])
            ]);

            // Commission niveau 2 si le parrain a lui-même un parrain
            if ($referrer->referred_by) {
                $level2Referrer = \App\Models\User::find($referrer->referred_by);
                if ($level2Referrer) {
                    $level2Commission = 500.0;
                    
                    $level2Referrer->increment('balance', $level2Commission);
                    $level2Referrer->increment('total_commissions', $level2Commission);

                    $level2Referrer->transactions()->create([
                        'type' => 'commission',
                        'amount' => $level2Commission,
                        'description' => "Commission niveau 2 - Achat de sous-filleul {$user->name}",
                        'status' => 'completed',
                        'meta' => json_encode([
                            'referral_id' => $user->id,
                            'referral_name' => $user->name,
                            'level' => 2,
                            'purchase_amount' => $purchaseAmount
                        ])
                    ]);
                }
            }
        }
    }
}
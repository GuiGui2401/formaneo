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
        $user = $request->user();

        $packs = FormationPack::where('is_active', true)
            ->with('formations.modules')
            ->orderBy('is_featured', 'desc')
            ->orderBy('order')
            ->get();

        // Formater les données pour correspondre à l'attente du frontend
        $formattedPacks = $packs->map(function ($pack) use ($user) {
            // Vérifier si l'utilisateur possède ce pack (seulement si authentifié)
            $isPurchased = false;
            if ($user) {
                $isPurchased = UserPack::where('user_id', $user->id)
                    ->where('pack_id', $pack->id)
                    ->exists();
            }

            return [
                'id' => $pack->id,
                'name' => $pack->name,
                'slug' => $pack->slug,
                'author' => $pack->author,
                'description' => $pack->description,
                'thumbnail_url' => $pack->thumbnail_url,
                'price' => $pack->price,
                'current_price' => $pack->getCurrentPrice(),
                'is_on_promotion' => $pack->isPromotionActive(),
                'promotion_price' => $pack->promotion_price,
                'total_duration' => $pack->total_duration,
                'rating' => $pack->rating,
                'students_count' => $pack->students_count,
                'formations_count' => $pack->formations->count(),
                'formations' => $pack->formations,
                'is_featured' => $pack->is_featured,
                'is_purchased' => $isPurchased,
                'created_at' => $pack->created_at,
            ];
        });

        return response()->json([
            'packs' => $formattedPacks
        ]);
    }

    // Afficher un pack spécifique
    public function show(Request $request, $id)
    {
        $pack = FormationPack::with(['formations.modules'])
            ->where('is_active', true)
            ->findOrFail($id);
            
        $user = $request->user();
        
        // Vérifier si l'utilisateur possède ce pack
        $userPack = UserPack::where('user_id', $user->id)
            ->where('pack_id', $pack->id)
            ->first();
            
        // Ajouter l'information d'achat au pack
        $packData = $pack->toArray();
        $packData['is_purchased'] = $userPack ? true : false;
        $packData['current_price'] = $pack->getCurrentPrice();
        $packData['is_on_promotion'] = $pack->isPromotionActive();

        return response()->json([
            'pack' => $packData
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

        // Utiliser le prix actuel (promo ou normal)
        $currentPrice = $pack->getCurrentPrice();
        
        // Vérifier le solde
        if ($user->balance < $currentPrice) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant'
            ], 400);
        }

        // Effectuer l'achat
        $user->decrement('balance', $currentPrice);

        // Créer la relation utilisateur-pack
        UserPack::create([
            'user_id' => $user->id,
            'pack_id' => $pack->id,
            'price_paid' => $currentPrice, // Enregistrer le prix payé (peut être prix promo)
            'purchased_at' => now(),
        ]);

        // Créer la transaction
        $user->transactions()->create([
            'type' => 'pack_purchase',
            'amount' => -$currentPrice,
            'description' => "Achat pack {$pack->name}",
            'status' => 'completed',
            'meta' => json_encode([
                'pack_id' => $pack->id,
                'pack_name' => $pack->name,
                'original_price' => $pack->price,
                'paid_price' => $currentPrice,
                'was_promotion' => $pack->isPromotionActive()
            ])
        ]);

        // Traiter la commission pour le parrain si applicable
        $this->processReferralCommission($user, $currentPrice);

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

    // Traiter la commission de parrainage (à chaque paiement)
    private function processReferralCommission($user, $purchaseAmount)
    {
        if (!$user->referred_by) {
            return;
        }

        $referrer = \App\Models\User::find($user->referred_by);
        if (!$referrer) {
            return;
        }

        // Déterminer le montant de la commission en fonction du nombre d'affiliés
        $commissionAmount = $referrer->total_affiliates >= config('app.affiliate_premium_threshold', 100)
            ? config('app.commission_premium', 2500)
            : config('app.commission_basic', 2000);

        // Incrémenter le solde du parrain
        $referrer->increment('balance', $commissionAmount);
        $referrer->increment('total_commissions', $commissionAmount);

        // Vérifier si c'est le premier achat du filleul pour incrémenter le compteur
        $isFirstPurchase = $user->transactions()
            ->whereIn('type', ['pack_purchase', 'ebook_purchase', 'product_purchase'])
            ->count() === 1;

        if ($isFirstPurchase) {
            $referrer->increment('total_affiliates');
            $referrer->increment('monthly_affiliates');
        }

        // Créer une transaction pour la commission
        $referrer->transactions()->create([
            'type' => 'affiliate_commission',
            'amount' => $commissionAmount,
            'description' => "Commission pour l'achat de {$user->name}",
            'status' => 'completed',
            'meta' => json_encode([
                'referred_user_id' => $user->id,
                'referred_user_name' => $user->name,
                'purchase_amount' => $purchaseAmount,
                'is_first_purchase' => $isFirstPurchase
            ])
        ]);
    }
}
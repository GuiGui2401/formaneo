<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Formation;
use App\Models\FormationPack;
use App\Models\Ebook;
use App\Models\UserPack;
use App\Models\UserEbook;
use App\Models\Transaction;
use App\Services\CommissionService;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function purchaseFormationPack(Request $request)
    {
        $request->validate([
            'pack_id' => 'required|exists:formation_packs,id'
        ]);

        $user = $request->user();
        $pack = FormationPack::findOrFail($request->pack_id);
        
        if (!$pack->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Ce pack de formation n\'est pas disponible'
            ], 400);
        }

        $existingPurchase = UserPack::where('user_id', $user->id)
            ->where('pack_id', $pack->id)
            ->first();
            
        if ($existingPurchase) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà acheté ce pack de formation'
            ], 400);
        }

        // Si le compte est activé ET que la requête vient du web, le prix est 0
        $isWeb = $request->header('X-Platform') === 'web';
        $price = ($user->account_status === 'active' && $isWeb) ? 0 : $pack->getCurrentPrice();
        
        if ($price > 0 && $user->balance < $price) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant. Votre solde actuel est de ' . number_format($user->balance, 0, ',', ' ') . ' FCFA',
                'current_balance' => $user->balance,
                'required_amount' => $price
            ], 400);
        }

        DB::beginTransaction();
        
        try {
            // Déduire du solde seulement si le prix est supérieur à 0
            if ($price > 0) {
                $user->decrement('balance', $price);
            }
            
            UserPack::create([
                'user_id' => $user->id,
                'pack_id' => $pack->id,
                'price_paid' => $price,
                'purchased_at' => now()
            ]);
            
            // Créer la transaction seulement si le prix est supérieur à 0
            $transaction = null;
            if ($price > 0) {
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'purchase',
                    'amount' => -$price,
                    'description' => "Achat du pack de formation: {$pack->name}",
                    'status' => 'completed',
                    'meta' => json_encode([
                        'item_type' => 'formation_pack',
                        'item_id' => $pack->id,
                        'item_name' => $pack->name
                    ])
                ]);
            } else {
                // Transaction gratuite pour compte activé
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'purchase',
                    'amount' => 0,
                    'description' => "Obtention gratuite du pack de formation: {$pack->name} (Compte activé)",
                    'status' => 'completed',
                    'meta' => json_encode([
                        'item_type' => 'formation_pack',
                        'item_id' => $pack->id,
                        'item_name' => $pack->name,
                        'free_with_activation' => true
                    ])
                ]);
            }
            
            if ($pack->cashback_amount > 0) {
                $user->increment('balance', $pack->cashback_amount);
                
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'cashback',
                    'amount' => $pack->cashback_amount,
                    'description' => "Cashback pour l'achat du pack: {$pack->name}",
                    'status' => 'completed',
                    'meta' => json_encode([
                        'related_transaction_id' => $transaction->id,
                        'pack_id' => $pack->id
                    ])
                ]);
            }
            
            // Distribuer les commissions d'affiliation seulement si le prix est supérieur à 0
            if ($price > 0) {
                $commissionService = new CommissionService();
                $commissionService->distributeCommissions($user, $price, 'formation_pack', $pack->id);
            }
            
            DB::commit();
            
            $message = ($price === 0) ? 'Pack de formation obtenu gratuitement (compte activé)' : 'Pack de formation acheté avec succès';
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'new_balance' => $user->fresh()->balance,
                'cashback_received' => $pack->cashback_amount,
                'pack' => [
                    'id' => $pack->id,
                    'name' => $pack->name,
                    'price_paid' => $price
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function purchaseEbook(Request $request)
    {
        $request->validate([
            'ebook_id' => 'required|exists:ebooks,id'
        ]);

        $user = $request->user();
        $ebook = Ebook::findOrFail($request->ebook_id);
        
        if (!$ebook->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cet ebook n\'est pas disponible'
            ], 400);
        }

        $existingPurchase = UserEbook::where('user_id', $user->id)
            ->where('ebook_id', $ebook->id)
            ->first();
            
        if ($existingPurchase) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà acheté cet ebook'
            ], 400);
        }

        // Si l'ebook est gratuit ou (compte activé ET requête web), pas de paiement nécessaire
        $isWeb = $request->header('X-Platform') === 'web';
        if ($ebook->is_free || ($user->account_status === 'active' && $isWeb)) {
            UserEbook::create([
                'user_id' => $user->id,
                'ebook_id' => $ebook->id,
                'price_paid' => 0,
                'purchased_at' => now()
            ]);
            
            $message = $ebook->is_free ? 'Ebook gratuit ajouté à votre bibliothèque' : 
                      ($user->account_status === 'active' ? 'Ebook obtenu gratuitement (compte activé)' : 'Ebook ajouté à votre bibliothèque');
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'ebook' => [
                    'id' => $ebook->id,
                    'title' => $ebook->title,
                    'price_paid' => 0
                ]
            ]);
        }

        // Si le compte est activé ET que la requête vient du web, le prix est 0
        $isWeb = $request->header('X-Platform') === 'web';
        $price = ($user->account_status === 'active' && $isWeb) ? 0 : $ebook->price;
        
        if ($price > 0 && $user->balance < $price) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant. Votre solde actuel est de ' . number_format($user->balance, 0, ',', ' ') . ' FCFA',
                'current_balance' => $user->balance,
                'required_amount' => $price
            ], 400);
        }

        DB::beginTransaction();
        
        try {
            // Déduire du solde seulement si le prix est supérieur à 0
            if ($price > 0) {
                $user->decrement('balance', $price);
            }
            
            UserEbook::create([
                'user_id' => $user->id,
                'ebook_id' => $ebook->id,
                'price_paid' => $price,
                'purchased_at' => now()
            ]);
            
            // Créer la transaction appropriée selon le prix
            $transactionDescription = ($price > 0) ? "Achat de l'ebook: {$ebook->title}" : "Obtention gratuite de l'ebook: {$ebook->title} (Compte activé)";
            
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'purchase',
                'amount' => -$price,
                'description' => $transactionDescription,
                'status' => 'completed',
                'meta' => json_encode([
                    'item_type' => 'ebook',
                    'item_id' => $ebook->id,
                    'item_name' => $ebook->title
                ])
            ]);
            
            // Distribuer les commissions d'affiliation seulement si le prix est supérieur à 0
            if ($price > 0) {
                $commissionService = new CommissionService();
                $commissionService->distributeCommissions($user, $price, 'ebook', $ebook->id);
            }
            
            DB::commit();
            
            $message = ($price === 0) ? 'Ebook obtenu gratuitement (compte activé)' : 'Ebook acheté avec succès';
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'new_balance' => $user->fresh()->balance,
                'ebook' => [
                    'id' => $ebook->id,
                    'title' => $ebook->title,
                    'price_paid' => $price
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getUserPurchases(Request $request)
    {
        $user = $request->user();
        
        $packs = UserPack::where('user_id', $user->id)
            ->with('pack.formations')
            ->orderBy('purchased_at', 'desc')
            ->get();
            
        $ebooks = UserEbook::where('user_id', $user->id)
            ->with('ebook')
            ->orderBy('purchased_at', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'formation_packs' => $packs->map(function($userPack) {
                return [
                    'id' => $userPack->pack->id,
                    'name' => $userPack->pack->name,
                    'description' => $userPack->pack->description,
                    'thumbnail_url' => $userPack->pack->thumbnail_url,
                    'price_paid' => $userPack->price_paid,
                    'purchased_at' => $userPack->purchased_at,
                    'formations_count' => $userPack->pack->formations->count()
                ];
            }),
            'ebooks' => $ebooks->map(function($userEbook) {
                return [
                    'id' => $userEbook->ebook->id,
                    'title' => $userEbook->ebook->title,
                    'description' => $userEbook->ebook->description,
                    'cover_image_url' => $userEbook->ebook->cover_image_url,
                    'pdf_url' => $userEbook->ebook->pdf_url,
                    'author' => $userEbook->ebook->author,
                    'price_paid' => $userEbook->price_paid,
                    'purchased_at' => $userEbook->purchased_at,
                    'downloaded_at' => $userEbook->downloaded_at
                ];
            })
        ]);
    }

    public function checkAccess(Request $request)
    {
        $request->validate([
            'type' => 'required|in:formation_pack,ebook',
            'id' => 'required|integer'
        ]);

        $user = $request->user();
        $hasAccess = false;

        if ($request->type === 'formation_pack') {
            $hasAccess = UserPack::where('user_id', $user->id)
                ->where('pack_id', $request->id)
                ->exists();
        } elseif ($request->type === 'ebook') {
            $hasAccess = UserEbook::where('user_id', $user->id)
                ->where('ebook_id', $request->id)
                ->exists();
        }

        return response()->json([
            'has_access' => $hasAccess
        ]);
    }
}
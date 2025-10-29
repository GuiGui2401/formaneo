<?php

namespace App\Services;

use App\Models\User;
use App\Models\Settings;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class CommissionService
{
    /**
     * Distribue les commissions d'affiliation lors d'un achat
     */
    public function distributeCommissions(User $buyer, float $purchaseAmount, string $sourceType = 'purchase', int $sourceId = null)
    {
        // Vérifier si l'acheteur a été référé
        if (!$buyer->referred_by) {
            Log::info("No referrer found for user {$buyer->id}");
            return;
        }

        // Récupérer les montants de commission depuis les settings
        $level1Commission = Settings::getValue('level1_commission', 1000);
        $level2Commission = Settings::getValue('level2_commission', 500);

        // Niveau 1 : Commission pour le parrain direct
        $directReferrer = User::find($buyer->referred_by);
        if ($directReferrer) {
            $this->giveCommission($directReferrer, $level1Commission, $sourceType, $sourceId, 1, $buyer);
            Log::info("Level 1 commission of {$level1Commission} FCFA given to user {$directReferrer->id} for buyer {$buyer->id}");

            // Niveau 2 : Commission pour le parrain du parrain
            if ($directReferrer->referred_by) {
                $indirectReferrer = User::find($directReferrer->referred_by);
                if ($indirectReferrer) {
                    $this->giveCommission($indirectReferrer, $level2Commission, $sourceType, $sourceId, 2, $buyer);
                    Log::info("Level 2 commission of {$level2Commission} FCFA given to user {$indirectReferrer->id} for buyer {$buyer->id}");
                }
            }
        }
    }

    /**
     * Donne une commission à un utilisateur
     */
    private function giveCommission(User $referrer, float $amount, string $sourceType, int $sourceId = null, int $level, User $buyer)
    {
        // Ajouter la commission au solde du parrain
        $referrer->balance += $amount;
        $referrer->available_for_withdrawal += $amount;
        $referrer->total_commissions += $amount;
        $referrer->save();

        // Créer une transaction de commission
        $referrer->transactions()->create([
            'type' => 'affiliate_commission',
            'amount' => $amount,
            'description' => "Commission niveau {$level} - Achat de {$buyer->name}",
            'status' => 'completed',
            'meta' => json_encode([
                'commission_level' => $level,
                'buyer_id' => $buyer->id,
                'buyer_name' => $buyer->name,
                'source_type' => $sourceType,
                'source_id' => $sourceId
            ])
        ]);
    }

    /**
     * Mettre à jour les statistiques d'affiliation
     */
    public function updateAffiliateStats(User $buyer)
    {
        if (!$buyer->referred_by) {
            return;
        }

        $directReferrer = User::find($buyer->referred_by);
        if ($directReferrer) {
            // Incrémenter le nombre d'affiliés du parrain direct
            $directReferrer->increment('total_affiliates');
            $directReferrer->increment('monthly_affiliates');
        }
    }
}
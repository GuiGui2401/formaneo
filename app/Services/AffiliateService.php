<?php

namespace App\Services;

use App\Models\User;
use App\Models\AffiliateLink;
use App\Models\Commission;

class AffiliateService
{
    public function getUserStats(User $user)
    {
        return [
            'total_affiliates' => $user->total_affiliates,
            'monthly_affiliates' => $user->monthly_affiliates,
            'total_commissions' => $user->total_commissions,
            'affiliate_link' => optional($user->affiliateLink)->url ?? $user->affiliate_link
        ];
    }

    public function recordCommission(User $user, float $amount, $sourceType = null, $sourceId = null)
    {
        $user->total_commissions += $amount;
        $user->available_for_withdrawal += $amount;
        $user->save();

        return Commission::create([
            'user_id'=>$user->id,
            'amount'=>$amount,
            'source_type'=>$sourceType,
            'source_id'=>$sourceId,
            'paid'=>false
        ]);
    }
}

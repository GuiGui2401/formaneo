<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;

class WalletService
{
    public function requestWithdrawal(User $user, float $amount): array
    {
        if ($amount > $user->available_for_withdrawal) {
            return ['success'=>false, 'message'=>'Montant supérieur à la disponibilité pour retrait.'];
        }

        if ($amount < (float) config('payments.min_withdrawal', 1000)) {
            return ['success'=>false, 'message'=>'Montant inférieur au minimum autorisé.'];
        }

        $user->available_for_withdrawal -= $amount;
        $user->pending_withdrawals += $amount;
        $user->save();

        Transaction::create([
            'user_id'=>$user->id,
            'type'=>'withdraw_request',
            'amount'=>$amount,
            'status'=>'pending',
            'meta'=>json_encode([])
        ]);

        return ['success'=>true, 'message'=>'Demande de retrait enregistrée.'];
    }
}

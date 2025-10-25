<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Http\Controllers\Api\CinetPayController;

class WalletController extends Controller
{
    // Obtenir les informations du portefeuille
    public function getInfo(Request $request)
    {
        $user = $request->user();
        
        // Calculer le montant disponible pour retrait
        $availableForWithdrawal = max(0, $user->balance - 1000); // Garder 1000 FCFA minimum
        
        // Calculer les retraits en attente
        $pendingWithdrawals = $user->transactions()
            ->where('type', 'withdrawal')
            ->where('status', 'pending')
            ->sum('amount');

        // Calculer le total des gains
        $totalEarned = $user->transactions()
            ->whereIn('type', ['commission', 'bonus', 'cashback', 'quiz_reward'])
            ->sum('amount');

        // Calculer le total des commissions
        $totalCommissions = $user->transactions()
            ->where('type', 'commission')
            ->sum('amount');

        // Calculer le total des quiz et bonus
        $totalQuizAndBonus = $user->transactions()
            ->whereIn('type', ['quiz_reward', 'bonus'])
            ->sum('amount');

        return response()->json([
            'balance' => $user->balance,
            'available_for_withdrawal' => $availableForWithdrawal,
            'pending_withdrawals' => abs($pendingWithdrawals), // abs car les retraits sont négatifs
            'total_earned' => $totalEarned,
            'total_commissions' => $totalCommissions,
            'total_quiz_and_bonus' => $totalQuizAndBonus,
        ]);
    }

    // Demander un retrait
    public function requestWithdrawal(Request $request)
    {
        // Validation de base. La logique complète est dans CinetPayController.
        $request->validate([
            'amount' => 'required|numeric|min:500',
            'phone_number' => 'required|string',
            'operator' => 'nullable|string|in:WAVECI,WAVESN,MOOV,MTN'
        ]);

        $cinetPayController = new CinetPayController();
        
        // Déléguer la logique de retrait au CinetPayController
        return $cinetPayController->initiateWithdrawal($request);
    }

    // Effectuer un dépôt
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100'
        ]);

        $user = $request->user();
        $amount = $request->amount;

        // Créer la transaction de dépôt
        $transaction = $user->transactions()->create([
            'type' => 'deposit',
            'amount' => $amount,
            'description' => "Dépôt de fonds via CinetPay",
            'status' => 'pending',
            'meta' => json_encode(['method' => 'cinetpay'])
        ]);

        // Retourner l'ID de la transaction pour initiation du paiement CinetPay
        return response()->json([
            'success' => true,
            'transaction_id' => $transaction->id,
            'message' => 'Transaction de dépôt créée, prête pour le paiement CinetPay'
        ]);
    }

    // Transférer vers un autre utilisateur
    public function transfer(Request $request)
    {
        $request->validate([
            'recipient_code' => 'required|string',
            'amount' => 'required|numeric|min:100'
        ]);

        $user = $request->user();
        $amount = $request->amount;
        $recipientCode = $request->recipient_code;

        // Vérifier le solde
        if ($user->balance < $amount) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant'
            ], 400);
        }

        // Trouver le destinataire
        $recipient = \App\Models\User::where('promo_code', $recipientCode)->first();
        
        if (!$recipient) {
            return response()->json([
                'success' => false,
                'message' => 'Code destinataire invalide'
            ], 400);
        }

        if ($recipient->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de transférer vers soi-même'
            ], 400);
        }

        // Effectuer le transfert
        $user->decrement('balance', $amount);
        $recipient->increment('balance', $amount);

        // Créer les transactions
        $user->transactions()->create([
            'type' => 'transfer_out',
            'amount' => -$amount,
            'description' => "Transfert vers {$recipient->name}",
            'status' => 'completed',
            'meta' => json_encode(['recipient_id' => $recipient->id, 'recipient_code' => $recipientCode])
        ]);

        $recipient->transactions()->create([
            'type' => 'transfer_in',
            'amount' => $amount,
            'description' => "Transfert reçu de {$user->name}",
            'status' => 'completed',
            'meta' => json_encode(['sender_id' => $user->id, 'sender_name' => $user->name])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transfert effectué avec succès',
            'new_balance' => $user->balance
        ]);
    }
}
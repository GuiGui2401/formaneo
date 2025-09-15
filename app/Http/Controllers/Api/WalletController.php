<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;

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

        return response()->json([
            'balance' => $user->balance,
            'available_for_withdrawal' => $availableForWithdrawal,
            'pending_withdrawals' => abs($pendingWithdrawals), // abs car les retraits sont négatifs
            'total_earned' => $totalEarned
        ]);
    }

    // Demander un retrait
    public function requestWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000|max:1000000',
            'method' => 'sometimes|string|in:mobile_money,bank_transfer',
            'phone_number' => 'required|string'
        ]);

        $user = $request->user();
        $amount = $request->amount;
        $method = $request->method ?? 'mobile_money';
        $phoneNumber = $request->phone_number;

        // Vérifier que l'utilisateur a assez de fonds
        $availableForWithdrawal = max(0, $user->balance - 1000);
        
        if ($amount > $availableForWithdrawal) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant pour ce retrait'
            ], 400);
        }

        // Créer la transaction de retrait
        $transaction = $user->transactions()->create([
            'type' => 'withdrawal',
            'amount' => -$amount, // Négatif pour les retraits
            'description' => "Retrait par {$method}",
            'status' => 'pending',
            'meta' => json_encode([
                'method' => $method,
                'phone_number' => $phoneNumber
            ])
        ]);

        // Déduire le montant du solde
        $user->decrement('balance', $amount);

        // Envoyer une notification à l'administrateur
        // Récupérer tous les administrateurs
        $admins = \App\Models\Admin::all();
        
        // Envoyer une notification à chaque administrateur
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\WithdrawalRequestNotification($transaction, $user));
        }

        return response()->json([
            'success' => true,
            'message' => 'Demande de retrait soumise avec succès',
            'transaction_id' => $transaction->id,
            'new_balance' => $user->balance
        ]);
    }

    // Effectuer un dépôt
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:500',
            'method' => 'required|string|in:mobile_money,bank_transfer,card'
        ]);

        $user = $request->user();
        $amount = $request->amount;
        $method = $request->method;

        // Créer la transaction de dépôt
        $transaction = $user->transactions()->create([
            'type' => 'deposit',
            'amount' => $amount,
            'description' => "Dépôt par {$method}",
            'status' => 'pending',
            'meta' => json_encode(['method' => $method])
        ]);

        // Dans un vrai système, ici vous intégreriez avec un gateway de paiement
        $paymentUrl = config('app.url') . "/payment/{$transaction->id}";

        return response()->json([
            'success' => true,
            'transaction_id' => $transaction->id,
            'payment_url' => $paymentUrl,
            'message' => 'Transaction créée, redirection vers le paiement'
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
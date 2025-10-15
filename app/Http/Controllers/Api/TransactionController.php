<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    // Liste des transactions avec pagination
    public function index(Request $request)
    {
        $user = $request->user();
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);

        $transactions = $user->transactions()
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        // Formater les transactions pour correspondre au modèle du frontend
        $formattedTransactions = $transactions->getCollection()->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'type' => $this->mapTransactionType($transaction->type),
                'amount' => $transaction->amount,
                'description' => $transaction->description,
                'status' => $this->mapTransactionStatus($transaction->status),
                'created_at' => $transaction->created_at,
                'completed_at' => $transaction->status === 'completed' ? $transaction->updated_at : null,
                'meta' => $transaction->meta ? json_decode($transaction->meta, true) : null,
            ];
        });

        return response()->json([
            'transactions' => $formattedTransactions,
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ]
        ]);
    }

    // Afficher une transaction spécifique
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $transaction = $user->transactions()
            ->findOrFail($id);

        return response()->json([
            'transaction' => [
                'id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'type' => $this->mapTransactionType($transaction->type),
                'amount' => $transaction->amount,
                'description' => $transaction->description,
                'status' => $this->mapTransactionStatus($transaction->status),
                'created_at' => $transaction->created_at,
                'completed_at' => $transaction->status === 'completed' ? $transaction->updated_at : null,
                'meta' => $transaction->meta ? json_decode($transaction->meta, true) : null,
            ]
        ]);
    }

    // Mapper les types de transaction pour correspondre au frontend
    private function mapTransactionType($type)
    {
        $mapping = [
            'commission' => 'commission',
            'bonus' => 'bonus',
            'purchase' => 'purchase',
            'withdrawal' => 'withdrawal',
            'deposit' => 'deposit',
            'cashback' => 'cashback',
            'quiz_reward' => 'quiz_reward',
            'transfer_in' => 'transfer_in',
            'transfer_out' => 'transfer_out',
        ];

        return $mapping[$type] ?? $type;
    }

    // Mapper les statuts de transaction pour correspondre au frontend
    private function mapTransactionStatus($status)
    {
        $mapping = [
            'pending' => 'pending',
            'completed' => 'completed',
            'failed' => 'failed',
            'cancelled' => 'cancelled',
        ];

        return $mapping[$status] ?? $status;
    }

    /**
     * Transfert interne entre utilisateurs
     */
    public function transferToUser(Request $request)
    {
        $request->validate([
            'recipient_email' => 'required|email|exists:users,email',
            'amount' => 'required|numeric|min:100|multiple_of:5',
            'message' => 'nullable|string|max:255'
        ]);

        $sender = $request->user();
        $amount = $request->amount;
        $recipientEmail = $request->recipient_email;

        Log::info('=== DÉBUT TRANSFERT INTERNE ===', [
            'sender_id' => $sender->id,
            'recipient_email' => $recipientEmail,
            'amount' => $amount
        ]);

        // Vérifier que l'utilisateur ne se transfert pas à lui-même
        if ($sender->email === $recipientEmail) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas vous transférer de l\'argent à vous-même'
            ], 400);
        }

        // Trouver le destinataire
        $recipient = User::where('email', $recipientEmail)->first();
        if (!$recipient) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur destinataire non trouvé'
            ], 404);
        }

        // Vérifier le solde disponible (en gardant 1000 FCFA minimum)
        $availableBalance = max(0, $sender->balance - 1000);
        
        if ($amount > $availableBalance) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant pour ce transfert',
                'available_balance' => $availableBalance
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Créer la transaction de sortie pour l'expéditeur
            $senderTransaction = $sender->transactions()->create([
                'type' => 'transfer_out',
                'amount' => -$amount,
                'description' => "Transfert vers {$recipient->first_name} {$recipient->last_name} ({$recipient->email})",
                'status' => 'completed',
                'meta' => json_encode([
                    'recipient_id' => $recipient->id,
                    'recipient_email' => $recipient->email,
                    'recipient_name' => "{$recipient->first_name} {$recipient->last_name}",
                    'message' => $request->message,
                    'transfer_type' => 'internal'
                ])
            ]);

            // Créer la transaction d'entrée pour le destinataire
            $recipientTransaction = $recipient->transactions()->create([
                'type' => 'transfer_in',
                'amount' => $amount,
                'description' => "Transfert reçu de {$sender->first_name} {$sender->last_name} ({$sender->email})",
                'status' => 'completed',
                'meta' => json_encode([
                    'sender_id' => $sender->id,
                    'sender_email' => $sender->email,
                    'sender_name' => "{$sender->first_name} {$sender->last_name}",
                    'message' => $request->message,
                    'transfer_type' => 'internal'
                ])
            ]);

            // Mettre à jour les soldes
            $sender->decrement('balance', $amount);
            $recipient->increment('balance', $amount);

            DB::commit();

            Log::info('Transfert interne réussi', [
                'sender_id' => $sender->id,
                'recipient_id' => $recipient->id,
                'amount' => $amount,
                'sender_new_balance' => $sender->fresh()->balance,
                'recipient_new_balance' => $recipient->fresh()->balance
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transfert effectué avec succès',
                'transaction' => [
                    'id' => $senderTransaction->id,
                    'recipient' => [
                        'name' => "{$recipient->first_name} {$recipient->last_name}",
                        'email' => $recipient->email
                    ],
                    'amount' => $amount,
                    'new_balance' => $sender->fresh()->balance,
                    'created_at' => $senderTransaction->created_at
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur lors du transfert interne', [
                'error' => $e->getMessage(),
                'sender_id' => $sender->id,
                'recipient_email' => $recipientEmail,
                'amount' => $amount
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du transfert'
            ], 500);
        }
    }

    /**
     * Rechercher un utilisateur par email
     */
    public function searchUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $currentUser = $request->user();
        $email = $request->email;

        // Vérifier que ce n'est pas l'utilisateur actuel
        if ($currentUser->email === $email) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas vous transférer de l\'argent à vous-même'
            ], 400);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => "{$user->first_name} {$user->last_name}",
                'email' => $user->email,
                'avatar' => $user->avatar_url ?? null
            ]
        ]);
    }
}
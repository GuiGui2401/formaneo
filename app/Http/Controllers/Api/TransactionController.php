<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;

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
}
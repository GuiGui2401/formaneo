<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction;
use App\Models\UserPack;
use App\Models\UserEbook;

class CinetPayController extends Controller
{
    protected $apiKey;
    protected $siteId;
    protected $notifyUrl;

    public function __construct()
    {
        $this->apiKey = config('cinetpay.api_key');
        $this->siteId = config('cinetpay.site_id');
        $this->notifyUrl = config('cinetpay.notify_url');
    }

    // Initier un paiement pour un dépôt de fonds dans le wallet
    public function initiateDepositPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:500', // Montant minimum pour le dépôt
            'currency' => 'required|string|in:XOF,XAF,USD,EUR',
            'customer_name' => 'required|string',
            'customer_surname' => 'required|string',
            'customer_email' => 'required|email',
            'customer_phone_number' => 'required|string',
            'customer_country' => 'required|string',
            'channels' => 'nullable|string'
        ]);

        $user = $request->user();

        // Générer un ID de transaction unique
        $transactionId = 'DEPOSIT_' . time() . '_' . uniqid();

        // Préparer les données pour CinetPay
        $paymentData = [
            'apikey' => $this->apiKey,
            'site_id' => $this->siteId,
            'transaction_id' => $transactionId,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'description' => "Dépôt de fonds dans le wallet",
            'channels' => $request->channels ?? 'ALL',
            'customer_name' => $request->customer_name,
            'customer_surname' => $request->customer_surname,
            'customer_email' => $request->customer_email,
            'customer_phone_number' => $request->customer_phone_number,
            'customer_country' => $request->customer_country,
            'notify_url' => $this->notifyUrl
        ];

        // Créer une transaction de dépôt en attente
        $transaction = $user->transactions()->create([
            'type' => 'deposit',
            'amount' => $request->amount,
            'description' => "Dépôt de fonds dans le wallet",
            'status' => 'pending',
            'meta' => json_encode([
                'transaction_id' => $transactionId,
                'payment_method' => 'cinetpay'
            ])
        ]);

        return response()->json([
            'success' => true,
            'transaction_id' => $transactionId,
            'payment_data' => $paymentData
        ]);
    }

    // Callback de notification de CinetPay
    public function handleNotification(Request $request)
    {
        // Valider la notification de CinetPay
        $data = $request->all();
        
        // Vérifier que la transaction existe
        $transaction = Transaction::where('meta->transaction_id', $data['transaction_id'])->first();
        
        if (!$transaction) {
            return response()->json(['status' => 'error', 'message' => 'Transaction non trouvée'], 404);
        }

        // Vérifier le statut du paiement
        if ($data['status'] === 'ACCEPTED') {
            // Mettre à jour la transaction
            $transaction->update([
                'status' => 'completed',
                'meta' => json_encode(array_merge(
                    json_decode($transaction->meta, true),
                    ['cinetpay_data' => $data]
                ))
            ]);

            // Traiter le dépôt en fonction du type
            $this->processDeposit($transaction);
            
            return response()->json(['status' => 'success']);
        } elseif ($data['status'] === 'REFUSED') {
            // Mettre à jour la transaction
            $transaction->update([
                'status' => 'failed',
                'meta' => json_encode(array_merge(
                    json_decode($transaction->meta, true),
                    ['cinetpay_data' => $data]
                ))
            ]);
            
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'ignored']);
    }

    // Traiter le dépôt de fonds
    private function processDeposit($transaction)
    {
        // Vérifier que c'est bien une transaction de dépôt
        if ($transaction->type !== 'deposit') {
            return;
        }

        $user = $transaction->user;
        
        // Incrémenter le solde de l'utilisateur
        $user->increment('balance', $transaction->amount);
    }
}
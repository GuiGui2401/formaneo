<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use App\Models\User;
use Exception;

class CinetPayController extends Controller
{
    protected $apiKey;
    protected $siteId;
    protected $notifyUrl;
    protected $returnUrl;

    public function __construct()
    {
        $this->apiKey = config('cinetpay.api_key');
        $this->siteId = config('cinetpay.site_id');
        $this->notifyUrl = config('cinetpay.notify_url');
        $this->returnUrl = config('app.frontend_url
        ', 'https://formaneo.com') . '/wallet';
    }

    /**
     * Initier un paiement pour un dépôt de fonds dans le wallet
     */
    public function initiateDepositPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:500|multiple_of:5', // Montant minimum et multiple de 5
            'phone_number' => 'nullable|string', // Numéro de téléphone optionnel
        ]);

        $user = $request->user();

        // Générer un ID de transaction unique (sans caractères spéciaux)
        $transactionId = 'DEPOSIT' . time() . random_int(1000, 9999);

        // Vérifier que le montant est un multiple de 5
        if ($request->amount % 5 !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Le montant doit être un multiple de 5'
            ], 400);
        }

        // Utiliser le numéro fourni ou celui du profil utilisateur
        $phoneNumber = $request->phone_number ?? $user->phone ?? '+237000000000';

        // Préparer les données pour CinetPay
        $paymentData = [
            'apikey' => $this->apiKey,
            'site_id' => $this->siteId,
            'transaction_id' => $transactionId,
            'amount' => (int) $request->amount,
            'currency' => 'XAF', // Devise par défaut (Cameroun)
            'description' => 'Depot de fonds dans le wallet Formaneo',
            'channels' => 'ALL',
            'notify_url' => $this->notifyUrl,
            'return_url' => $this->returnUrl,
            'customer_id' => (string) $user->id,
            'customer_name' => $user->last_name ?? 'Utilisateur',
            'customer_surname' => $user->first_name ?? 'Formaneo',
            'customer_email' => $user->email,
            'customer_phone_number' => $phoneNumber,
            'customer_address' => 'Douala',
            'customer_city' => 'Douala',
            'customer_country' => 'CM',
            'customer_state' => 'CM',
            'customer_zip_code' => '00225',
            'metadata' => 'user_id:' . $user->id,
            'lang' => 'fr'
        ];

        try {
            // Appel à l'API CinetPay
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'User-Agent' => 'Formaneo-App/1.0'
            ])->post('https://api-checkout.cinetpay.com/v2/payment', $paymentData);

            if ($response->failed()) {
                Log::error('CinetPay API Error', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'initialisation du paiement'
                ], 500);
            }

            $responseData = $response->json();

            // Vérifier la réponse de CinetPay
            if ($responseData['code'] !== '201') {
                Log::error('CinetPay Payment Init Failed', $responseData);
                
                return response()->json([
                    'success' => false,
                    'message' => $responseData['message'] ?? 'Erreur lors de l\'initialisation du paiement'
                ], 400);
            }

            // Créer une transaction de dépôt en attente
            $transaction = $user->transactions()->create([
                'type' => 'deposit',
                'amount' => $request->amount,
                'description' => 'Dépôt de fonds dans le wallet',
                'status' => 'pending',
                'meta' => json_encode([
                    'transaction_id' => $transactionId,
                    'payment_method' => 'cinetpay',
                    'payment_token' => $responseData['data']['payment_token'],
                    'payment_data' => $paymentData
                ])
            ]);

            return response()->json([
                'success' => true,
                'transaction_id' => $transactionId,
                'payment_url' => $responseData['data']['payment_url'],
                'payment_token' => $responseData['data']['payment_token']
            ]);

        } catch (Exception $e) {
            Log::error('CinetPay Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'initialisation du paiement'
            ], 500);
        }
    }

    /**
     * Callback de notification de CinetPay
     */
    public function handleNotification(Request $request)
    {
        Log::info('CinetPay Notification Received', $request->all());

        // Récupérer les données de la notification
        $transactionId = $request->input('cpm_trans_id');
        $siteId = $request->input('cpm_site_id');

        if (!$transactionId || !$siteId) {
            Log::error('CinetPay Notification Missing Data', $request->all());
            return response()->json(['status' => 'error'], 400);
        }

        // Vérifier que la transaction existe
        $transaction = Transaction::whereJsonContains('meta->transaction_id', $transactionId)->first();
        
        if (!$transaction) {
            Log::error('Transaction not found', ['transaction_id' => $transactionId]);
            return response()->json(['status' => 'error'], 404);
        }

        // Vérifier le statut avec l'API CinetPay
        $verificationResult = $this->verifyTransaction($transactionId);

        if ($verificationResult['success']) {
            $status = $verificationResult['data']['status'];
            
            if ($status === 'ACCEPTED' && $transaction->status !== 'completed') {
                // Mettre à jour la transaction
                $transaction->update([
                    'status' => 'completed',
                    'meta' => json_encode(array_merge(
                        json_decode($transaction->meta, true),
                        ['cinetpay_verification' => $verificationResult['data']]
                    ))
                ]);

                // Traiter le dépôt
                $this->processDeposit($transaction);
                
                Log::info('Transaction Completed', ['transaction_id' => $transactionId]);
                
            } elseif ($status === 'REFUSED' && $transaction->status !== 'failed') {
                $transaction->update([
                    'status' => 'failed',
                    'meta' => json_encode(array_merge(
                        json_decode($transaction->meta, true),
                        ['cinetpay_verification' => $verificationResult['data']]
                    ))
                ]);
                
                Log::info('Transaction Failed', ['transaction_id' => $transactionId]);
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Vérifier le statut d'une transaction avec l'API CinetPay
     */
    public function verifyTransaction($transactionId)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'User-Agent' => 'Formaneo-App/1.0'
            ])->post('https://api-checkout.cinetpay.com/v2/payment/check', [
                'apikey' => $this->apiKey,
                'site_id' => $this->siteId,
                'transaction_id' => $transactionId
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                if ($responseData['code'] === '00') {
                    return [
                        'success' => true,
                        'data' => $responseData['data']
                    ];
                }
            }

            Log::error('CinetPay Verification Failed', [
                'transaction_id' => $transactionId,
                'response' => $response->json()
            ]);

            return ['success' => false];

        } catch (Exception $e) {
            Log::error('CinetPay Verification Exception', [
                'transaction_id' => $transactionId,
                'message' => $e->getMessage()
            ]);

            return ['success' => false];
        }
    }

    /**
     * Traiter le dépôt de fonds
     */
    private function processDeposit($transaction)
    {
        if ($transaction->type !== 'deposit') {
            return;
        }

        $user = $transaction->user;
        
        // Incrémenter le solde de l'utilisateur
        $user->increment('balance', $transaction->amount);
        
        Log::info('Deposit Processed', [
            'user_id' => $user->id,
            'amount' => $transaction->amount,
            'new_balance' => $user->fresh()->balance
        ]);
    }

    /**
     * Tester l'API avec un numéro de téléphone spécifique
     */
    public function testPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:500|multiple_of:5',
            'phone' => 'required|string'
        ]);

        $user = $request->user();
        $transactionId = 'TEST' . time() . random_int(1000, 9999);

        $paymentData = [
            'apikey' => $this->apiKey,
            'site_id' => $this->siteId,
            'transaction_id' => $transactionId,
            'amount' => (int) $request->amount,
            'currency' => 'XAF',
            'description' => 'Test paiement Formaneo',
            'channels' => 'MOBILE_MONEY',
            'notify_url' => $this->notifyUrl,
            'return_url' => $this->returnUrl,
            'customer_id' => (string) $user->id,
            'customer_name' => 'Test',
            'customer_surname' => 'User',
            'customer_email' => $user->email,
            'customer_phone_number' => $request->phone,
            'customer_address' => 'Douala',
            'customer_city' => 'Douala',
            'customer_country' => 'CM',
            'customer_state' => 'CM',
            'customer_zip_code' => '00225',
            'metadata' => 'test_user_id:' . $user->id,
            'lang' => 'fr'
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'User-Agent' => 'Formaneo-App/1.0'
            ])->post('https://api-checkout.cinetpay.com/v2/payment', $paymentData);

            return response()->json([
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'response' => $response->json(),
                'payment_data' => $paymentData
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
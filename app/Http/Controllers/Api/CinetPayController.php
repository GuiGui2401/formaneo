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
        // Utiliser une URL publique pour les notifications (à remplacer par ngrok ou serveur public)
        $this->notifyUrl = 'https://webhook.site/unique-id'; // URL temporaire pour tests
        $this->returnUrl = 'http://10.146.233.108:8001/api/v1/cinetpay/return';
    }

    /**
     * Initier un paiement pour un dépôt de fonds dans le wallet
     */
    public function initiateDepositPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100|multiple_of:5',
            'phone_number' => 'nullable|string',
        ]);

        $user = $request->user();
        $transactionId = 'DEPOSIT' . time() . random_int(1000, 9999);
        $phoneNumber = $request->phone_number ?? $user->phone ?? '+237600000000';

        // Préparer les données pour CinetPay - conforme à la documentation
        $paymentData = [
            'apikey' => $this->apiKey,
            'site_id' => $this->siteId,
            'transaction_id' => $transactionId,
            'amount' => (int) $request->amount,
            'currency' => 'XAF',
            'description' => 'Depot de fonds wallet Formaneo', // Sans caractères spéciaux
            'channels' => 'ALL',
            'notify_url' => $this->notifyUrl,
            'return_url' => $this->returnUrl,
            'customer_id' => (string) $user->id,
            'customer_name' => $user->last_name ?? 'User',
            'customer_surname' => $user->first_name ?? 'Formaneo',
            'customer_email' => $user->email,
            'customer_phone_number' => $phoneNumber,
            'customer_address' => 'Douala Centre',
            'customer_city' => 'Douala',
            'customer_country' => 'CM',
            'customer_state' => 'CM',
            'customer_zip_code' => '00237',
            'metadata' => 'user' . $user->id, // Sans caractères spéciaux
            'lang' => 'fr'
        ];

        Log::info('CinetPay Request Data', $paymentData);

        try {
            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
                'User-Agent' => 'Formaneo-App/1.0'
            ])->post('https://api-checkout.cinetpay.com/v2/payment', $paymentData);

            Log::info('CinetPay Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de connexion à CinetPay'
                ], 500);
            }

            $responseData = $response->json();

            if (!isset($responseData['code']) || $responseData['code'] !== '201') {
                return response()->json([
                    'success' => false,
                    'message' => $responseData['message'] ?? 'Erreur lors de l\'initialisation du paiement',
                    'debug' => $responseData
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
                    'payment_token' => $responseData['data']['payment_token'] ?? null,
                ])
            ]);

            return response()->json([
                'success' => true,
                'transaction_id' => $transactionId,
                'payment_url' => $responseData['data']['payment_url'] ?? null,
                'payment_token' => $responseData['data']['payment_token'] ?? null
            ]);

        } catch (Exception $e) {
            Log::error('CinetPay Exception', [
                'message' => $e->getMessage(),
                'amount' => $request->amount,
                'phone' => $phoneNumber
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur technique: ' . $e->getMessage()
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
                
            } elseif (in_array($status, ['CANCELLED', 'CANCELED']) && $transaction->status !== 'cancelled') {
                $transaction->update([
                    'status' => 'cancelled',
                    'meta' => json_encode(array_merge(
                        json_decode($transaction->meta, true),
                        ['cinetpay_verification' => $verificationResult['data']]
                    ))
                ]);
                
                Log::info('Transaction Cancelled', ['transaction_id' => $transactionId]);
                
            } elseif ($status === 'WAITING_FOR_CUSTOMER' && $transaction->status === 'pending') {
                // Ne pas changer le statut, juste logger
                Log::info('Transaction Waiting for Customer', ['transaction_id' => $transactionId]);
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Vérifier le statut d'une transaction avec l'API CinetPay
     */
    public function verifyTransaction($transactionId)
    {
        Log::info('>>> Appel API CinetPay payment/check', [
            'transaction_id' => $transactionId,
            'api_key_set' => !empty($this->apiKey),
            'site_id_set' => !empty($this->siteId)
        ]);

        try {
            $requestData = [
                'apikey' => $this->apiKey,
                'site_id' => $this->siteId,
                'transaction_id' => $transactionId
            ];

            Log::info('Données envoyées à CinetPay', [
                'url' => 'https://api-checkout.cinetpay.com/v2/payment/check',
                'transaction_id' => $transactionId,
                'site_id' => $this->siteId
            ]);

            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
                'User-Agent' => 'Formaneo-App/1.0'
            ])->post('https://api-checkout.cinetpay.com/v2/payment/check', $requestData);

            $statusCode = $response->status();
            $responseBody = $response->body();
            $responseData = $response->json();

            Log::info('Réponse CinetPay reçue', [
                'status_code' => $statusCode,
                'response_code' => $responseData['code'] ?? null,
                'response_message' => $responseData['message'] ?? null,
                'has_data' => isset($responseData['data']),
                'full_response' => $responseData
            ]);

            if ($response->successful()) {
                if (isset($responseData['code']) && $responseData['code'] === '00') {
                    Log::info('✓ Vérification CinetPay réussie', [
                        'transaction_id' => $transactionId,
                        'status' => $responseData['data']['status'] ?? 'UNKNOWN',
                        'amount' => $responseData['data']['amount'] ?? null,
                        'payment_method' => $responseData['data']['payment_method'] ?? null
                    ]);

                    return [
                        'success' => true,
                        'data' => $responseData['data']
                    ];
                } else {
                    Log::warning('CinetPay - Code de retour non valide', [
                        'transaction_id' => $transactionId,
                        'code' => $responseData['code'] ?? 'NO_CODE',
                        'message' => $responseData['message'] ?? 'NO_MESSAGE'
                    ]);
                }
            } else {
                Log::error('CinetPay - Réponse HTTP non réussie', [
                    'transaction_id' => $transactionId,
                    'status_code' => $statusCode,
                    'response_body' => substr($responseBody, 0, 500)
                ]);
            }

            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Vérification échouée',
                'code' => $responseData['code'] ?? null
            ];

        } catch (Exception $e) {
            Log::error('Exception lors de la vérification CinetPay', [
                'transaction_id' => $transactionId,
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
                'trace' => substr($e->getTraceAsString(), 0, 1000)
            ]);

            return [
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ];
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
     * Endpoint de retour pour CinetPay (return_url)
     */
    public function handleReturn(Request $request)
    {
        // Log le retour de CinetPay
        Log::info('CinetPay Return', $request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Retour de CinetPay reçu',
            'data' => $request->all()
        ]);
    }

    /**
     * Endpoint de debug pour vérifier la configuration
     */
    public function debugConfig(Request $request)
    {
        return response()->json([
            'success' => true,
            'config' => [
                'api_key_set' => !empty($this->apiKey),
                'site_id_set' => !empty($this->siteId),
                'password_set' => !empty(env('CINETPAY_PASSWORD')),
                'notify_url' => $this->notifyUrl,
                'return_url' => $this->returnUrl,
            ],
            'endpoints' => [
                'deposit' => 'https://api-checkout.cinetpay.com/v2/payment',
                'transfer_auth' => 'https://client.cinetpay.com/v1/auth/login',
                'transfer' => 'https://client.cinetpay.com/v1/transfer/money/send/contact',
            ]
        ]);
    }

    /**
     * Tester l'API avec un numéro de téléphone spécifique
     */
    public function testPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100|multiple_of:5',
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

    /**
     * API pour vérifier le statut d'une transaction spécifique
     */
    public function checkTransactionStatus(Request $request)
    {
        Log::info('=== DÉBUT VÉRIFICATION STATUT TRANSACTION ===', [
            'request_data' => $request->all(),
            'user_id' => $request->user()->id ?? null
        ]);

        $request->validate([
            'transaction_id' => 'required|string'
        ]);

        $transactionId = $request->transaction_id;
        $user = $request->user();

        Log::info('Recherche de la transaction dans la base de données', [
            'transaction_id' => $transactionId,
            'user_id' => $user->id
        ]);

        // Vérifier que la transaction appartient à l'utilisateur
        $transaction = Transaction::where('user_id', $user->id)
            ->whereJsonContains('meta->transaction_id', $transactionId)
            ->first();

        if (!$transaction) {
            Log::error('Transaction non trouvée dans la base de données', [
                'transaction_id' => $transactionId,
                'user_id' => $user->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Transaction non trouvée'
            ], 404);
        }

        Log::info('Transaction trouvée dans la base de données', [
            'transaction_id' => $transactionId,
            'current_status' => $transaction->status,
            'amount' => $transaction->amount,
            'type' => $transaction->type,
            'created_at' => $transaction->created_at
        ]);

        // Vérifier le statut avec CinetPay
        Log::info('Appel de l\'API CinetPay pour vérification', [
            'transaction_id' => $transactionId
        ]);
        
        $verificationResult = $this->verifyTransaction($transactionId);
        
        Log::info('Résultat de la vérification CinetPay', [
            'success' => $verificationResult['success'] ?? false,
            'data' => $verificationResult['data'] ?? null,
            'full_result' => $verificationResult
        ]);

        if ($verificationResult['success']) {
            $cinetpayStatus = $verificationResult['data']['status'];
            $currentStatus = $transaction->status;
            $paymentMethod = $verificationResult['data']['payment_method'] ?? 'UNKNOWN';
            $operatorId = $verificationResult['data']['operator_id'] ?? null;

            Log::info('Comparaison des statuts', [
                'cinetpay_status' => $cinetpayStatus,
                'current_db_status' => $currentStatus,
                'payment_method' => $paymentMethod,
                'operator_id' => $operatorId
            ]);

            // Mettre à jour le statut si nécessaire
            if ($cinetpayStatus === 'ACCEPTED' && $currentStatus !== 'completed') {
                Log::info('Transaction ACCEPTED - Mise à jour vers completed', [
                    'transaction_id' => $transactionId
                ]);
                
                $transaction->update([
                    'status' => 'completed',
                    'meta' => json_encode(array_merge(
                        json_decode($transaction->meta, true),
                        [
                            'cinetpay_verification' => $verificationResult['data'],
                            'payment_method' => $paymentMethod,
                            'operator_id' => $operatorId,
                            'verified_at' => now()
                        ]
                    ))
                ]);
                
                Log::info('Traitement du dépôt', [
                    'transaction_id' => $transactionId,
                    'amount' => $transaction->amount
                ]);
                
                $this->processDeposit($transaction);
                $currentStatus = 'completed';
                
                Log::info('Transaction marquée comme COMPLÉTÉE avec succès', [
                    'transaction_id' => $transactionId,
                    'amount' => $transaction->amount,
                    'new_balance' => $transaction->user->fresh()->balance
                ]);
                
            } elseif ($cinetpayStatus === 'REFUSED' && $currentStatus !== 'failed') {
                Log::info('Transaction REFUSED - Mise à jour vers failed', [
                    'transaction_id' => $transactionId
                ]);
                
                $transaction->update(['status' => 'failed']);
                $currentStatus = 'failed';
                
            } elseif (in_array($cinetpayStatus, ['CANCELLED', 'CANCELED']) && $currentStatus !== 'cancelled') {
                Log::info('Transaction CANCELLED - Mise à jour vers cancelled', [
                    'transaction_id' => $transactionId
                ]);
                
                $transaction->update(['status' => 'cancelled']);
                $currentStatus = 'cancelled';
                
            } else {
                Log::info('Aucune mise à jour nécessaire', [
                    'transaction_id' => $transactionId,
                    'cinetpay_status' => $cinetpayStatus,
                    'current_status' => $currentStatus
                ]);
            }

            Log::info('=== FIN VÉRIFICATION - SUCCÈS ===', [
                'transaction_id' => $transactionId,
                'final_status' => $currentStatus,
                'cinetpay_status' => $cinetpayStatus
            ]);

            return response()->json([
                'success' => true,
                'status' => $currentStatus,
                'cinetpay_status' => $cinetpayStatus,
                'payment_method' => $paymentMethod,
                'amount' => $transaction->amount,
                'created_at' => $transaction->created_at,
                'debug' => $verificationResult['data']
            ]);
        } else {
            Log::error('=== ÉCHEC VÉRIFICATION CINETPAY ===', [
                'transaction_id' => $transactionId,
                'error' => $verificationResult,
                'current_db_status' => $transaction->status
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Impossible de vérifier le statut avec CinetPay',
                'current_status' => $transaction->status,
                'debug' => $verificationResult
            ], 500);
        }
    }

    /**
     * Initier un retrait via l'API de transfert CinetPay
     */
    public function initiateWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100|multiple_of:5',
            'phone_number' => 'required|string',
            'operator' => 'required|string|in:WAVECI,WAVESN,MOOV,MTN,ORANGE'
        ]);

        $user = $request->user();
        $amount = $request->amount;

        // Vérifier le solde disponible
        $availableForWithdrawal = max(0, $user->balance - 1000);
        
        if ($amount > $availableForWithdrawal) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant pour ce retrait'
            ], 400);
        }

        // Authentification CinetPay pour l'API de transfert
        $authResponse = $this->authenticateCinetPay();
        
        if (!$authResponse['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur d\'authentification avec CinetPay'
            ], 500);
        }

        $token = $authResponse['token'];

        // Créer ou récupérer le contact
        $contactData = $this->createOrGetContact($token, $request->phone_number, $user);
        
        if (!$contactData['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du contact'
            ], 500);
        }

        // Initier le transfert
        $transferResult = $this->initiateTransfer($token, $contactData['contact_id'], $amount, $request->operator);
        
        if ($transferResult['success']) {
            // Créer la transaction de retrait
            $transaction = $user->transactions()->create([
                'type' => 'withdrawal',
                'amount' => -$amount,
                'description' => "Retrait vers {$request->operator} - {$request->phone_number}",
                'status' => 'pending',
                'meta' => json_encode([
                    'operator' => $request->operator,
                    'phone_number' => $request->phone_number,
                    'cinetpay_transfer_id' => $transferResult['transfer_id']
                ])
            ]);

            // Déduire le montant du solde
            $user->decrement('balance', $amount);

            return response()->json([
                'success' => true,
                'message' => 'Retrait initié avec succès',
                'transaction_id' => $transaction->id,
                'transfer_id' => $transferResult['transfer_id'],
                'new_balance' => $user->balance
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $transferResult['message'] ?? 'Erreur lors de l\'initiation du retrait'
            ], 500);
        }
    }

    /**
     * Authentification à l'API de transfert CinetPay
     */
    private function authenticateCinetPay()
    {
        try {
            $response = Http::post('https://client.cinetpay.com/v1/auth/login', [
                'apikey' => $this->apiKey,
                'password' => env('CINETPAY_PASSWORD', '')
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'token' => $data['token']
                ];
            }

            Log::error('CinetPay Auth Failed', $response->json());
            return ['success' => false];

        } catch (Exception $e) {
            Log::error('CinetPay Auth Exception', ['message' => $e->getMessage()]);
            return ['success' => false];
        }
    }

    /**
     * Créer ou récupérer un contact CinetPay
     */
    private function createOrGetContact($token, $phoneNumber, $user)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token
            ])->post('https://client.cinetpay.com/v1/transfer/contact', [
                'phone' => $phoneNumber,
                'name' => $user->last_name ?? 'User',
                'surname' => $user->first_name ?? 'Formaneo',
                'email' => $user->email
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'contact_id' => $data['contact_id'] ?? $data['id']
                ];
            }

            Log::error('CinetPay Contact Creation Failed', $response->json());
            return ['success' => false];

        } catch (Exception $e) {
            Log::error('CinetPay Contact Exception', ['message' => $e->getMessage()]);
            return ['success' => false];
        }
    }

    /**
     * Initier un transfert CinetPay
     */
    private function initiateTransfer($token, $contactId, $amount, $operator)
    {
        try {
            // Vérifier d'abord le solde
            $balanceResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token
            ])->post('https://client.cinetpay.com/v1/transfer/check/balance');

            if ($balanceResponse->successful()) {
                $balanceData = $balanceResponse->json();
                $availableBalance = $balanceData['balance'] ?? 0;

                if ($availableBalance < $amount) {
                    return [
                        'success' => false,
                        'message' => 'Solde CinetPay insuffisant pour effectuer ce retrait'
                    ];
                }
            }

            // Effectuer le transfert
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token
            ])->post('https://client.cinetpay.com/v1/transfer/money/send/contact', [
                'contact_id' => $contactId,
                'amount' => $amount,
                'notify' => 1
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'transfer_id' => $data['transfer_id'] ?? $data['transaction_id']
                ];
            }

            Log::error('CinetPay Transfer Failed', $response->json());
            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Erreur lors du transfert'
            ];

        } catch (Exception $e) {
            Log::error('CinetPay Transfer Exception', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Erreur technique lors du transfert'
            ];
        }
    }

    /**
     * Vérifier le statut d'un transfert
     */
    public function checkWithdrawalStatus(Request $request)
    {
        $request->validate([
            'transfer_id' => 'required|string'
        ]);

        $user = $request->user();
        $transferId = $request->transfer_id;

        // Vérifier que la transaction appartient à l'utilisateur
        $transaction = Transaction::where('user_id', $user->id)
            ->where('type', 'withdrawal')
            ->whereJsonContains('meta->cinetpay_transfer_id', $transferId)
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transfert non trouvé'
            ], 404);
        }

        // Authentification
        $authResponse = $this->authenticateCinetPay();
        
        if (!$authResponse['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur d\'authentification',
                'current_status' => $transaction->status
            ], 500);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $authResponse['token']
            ])->post('https://client.cinetpay.com/v1/transfer/check/money', [
                'transaction_id' => $transferId
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $transferStatus = $data['status'] ?? 'UNKNOWN';

                // Mettre à jour le statut de la transaction
                if ($transferStatus === 'SUCCESS' && $transaction->status !== 'completed') {
                    $transaction->update(['status' => 'completed']);
                } elseif ($transferStatus === 'FAILED' && $transaction->status !== 'failed') {
                    $transaction->update(['status' => 'failed']);
                    // Rembourser l'utilisateur
                    $user->increment('balance', abs($transaction->amount));
                }

                return response()->json([
                    'success' => true,
                    'status' => $transaction->fresh()->status,
                    'transfer_status' => $transferStatus,
                    'amount' => abs($transaction->amount),
                    'created_at' => $transaction->created_at
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Impossible de vérifier le statut',
                'current_status' => $transaction->status
            ], 500);

        } catch (Exception $e) {
            Log::error('Check Transfer Status Exception', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification',
                'current_status' => $transaction->status
            ], 500);
        }
    }
}
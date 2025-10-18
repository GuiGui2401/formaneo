<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use App\Models\User;
use App\Jobs\ProcessCinetPayTransfer;
use Exception;

class CinetPayController extends Controller
{
    protected $apiKey;
    protected $siteId;
    protected $notifyUrl;
    protected $returnUrl;
    protected $transferApiUrl;
    protected $transferPassword;

    public function __construct()
    {
        $this->apiKey = config('cinetpay.api_key');
        $this->siteId = config('cinetpay.site_id');
        // URLs de callback - en développement, utilisez ngrok pour les notifications
        $this->notifyUrl = env('CINETPAY_NOTIFY_URL', 'http://192.168.1.135:8001/api/v1/cinetpay/notify');
        $this->returnUrl = 'http://192.168.1.135:8001/api/v1/cinetpay/return';
        
        // Configuration pour l'API de transfert
        $this->transferApiUrl = 'https://client.cinetpay.com/v1';
        $this->transferPassword = '12345678'; // Mot de passe API CinetPay pour les transferts uniquement
    }

    /**
     * Initier un paiement pour un dépôt de fonds dans le wallet
     */
    public function initiateDepositPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:500|multiple_of:5',
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
            Log::info('=== CRÉATION TRANSACTION EN BASE ===', [
                'user_id' => $user->id,
                'transaction_id' => $transactionId,
                'amount' => $request->amount
            ]);

            $transactionData = [
                'type' => 'deposit',
                'amount' => $request->amount,
                'description' => 'Dépôt de fonds dans le wallet',
                'status' => 'pending',
                'meta' => json_encode([
                    'transaction_id' => $transactionId,
                    'payment_method' => 'cinetpay',
                    'payment_token' => $responseData['data']['payment_token'] ?? null,
                    'cinetpay_response' => $responseData['data'] ?? null,
                    'created_at' => now(),
                ])
            ];

            Log::info('Données de transaction à sauvegarder', $transactionData);

            $transaction = $user->transactions()->create($transactionData);

            Log::info('✓ Transaction créée avec succès', [
                'db_id' => $transaction->id,
                'transaction_id' => $transactionId,
                'status' => $transaction->status,
                'meta' => $transaction->meta
            ]);

            // Vérification immédiate que la transaction peut être retrouvée
            $testFind = Transaction::whereJsonContains('meta->transaction_id', $transactionId)->first();
            Log::info('Test de récupération de transaction', [
                'found' => $testFind ? true : false,
                'found_id' => $testFind ? $testFind->id : null
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
     * Callback de notification de CinetPay avec vérification HMAC
     */
    public function handleNotification(Request $request)
    {
        Log::info('=== NOTIFICATION CINETPAY REÇUE ===', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'method' => $request->method()
        ]);

        // Récupérer le token HMAC depuis l'entête
        $receivedToken = $request->header('x-token');
        
        if (!$receivedToken) {
            Log::error('Token HMAC manquant dans l\'entête');
            return response()->json(['status' => 'error', 'message' => 'Token manquant'], 401);
        }

        // Récupérer les données de la notification selon la documentation
        $cpm_site_id = $request->input('cpm_site_id');
        $cpm_trans_id = $request->input('cpm_trans_id');
        $cpm_trans_date = $request->input('cpm_trans_date');
        $cpm_amount = $request->input('cpm_amount');
        $cpm_currency = $request->input('cpm_currency', '');
        $signature = $request->input('signature', '');
        $payment_method = $request->input('payment_method', '');
        $cel_phone_num = $request->input('cel_phone_num', '');
        $cpm_phone_prefixe = $request->input('cpm_phone_prefixe', '');
        $cpm_language = $request->input('cpm_language', '');
        $cpm_version = $request->input('cpm_version', '');
        $cpm_payment_config = $request->input('cpm_payment_config', '');
        $cpm_page_action = $request->input('cpm_page_action', '');
        $cpm_custom = $request->input('cpm_custom', '');
        $cpm_designation = $request->input('cpm_designation', '');
        $cpm_error_message = $request->input('cpm_error_message', '');

        // Vérifier les données obligatoires
        if (!$cpm_trans_id || !$cpm_site_id) {
            Log::error('Données obligatoires manquantes', [
                'trans_id' => $cpm_trans_id,
                'site_id' => $cpm_site_id
            ]);
            return response()->json(['status' => 'error'], 400);
        }

        // Construire la chaîne pour le token HMAC selon la doc
        $data = $cpm_site_id . $cpm_trans_id . $cpm_trans_date . $cpm_amount . $cpm_currency . 
                $signature . $payment_method . $cel_phone_num . $cpm_phone_prefixe . 
                $cpm_language . $cpm_version . $cpm_payment_config . $cpm_page_action . 
                $cpm_custom . $cpm_designation . $cpm_error_message;

        // Récupérer la clé secrète depuis la config
        $secretKey = env('CINETPAY_SECRET_KEY', '');
        
        if (empty($secretKey)) {
            Log::error('CINETPAY_SECRET_KEY non configurée dans .env');
            // En développement, on peut continuer sans vérifier le token
            // return response()->json(['status' => 'error', 'message' => 'Configuration manquante'], 500);
        } else {
            // Générer le token HMAC avec SHA256
            $generatedToken = hash_hmac('SHA256', $data, $secretKey);
            
            Log::info('Vérification du token HMAC', [
                'received_token' => substr($receivedToken, 0, 20) . '...',
                'generated_token' => substr($generatedToken, 0, 20) . '...',
                'tokens_match' => hash_equals($receivedToken, $generatedToken)
            ]);

            // Vérifier que les tokens correspondent
            if (!hash_equals($receivedToken, $generatedToken)) {
                Log::error('Token HMAC invalide - Notification rejetée');
                return response()->json(['status' => 'error', 'message' => 'Token invalide'], 401);
            }
        }

        Log::info('Token HMAC valide ou non vérifié - Traitement de la notification');

        // Vérifier que la transaction existe dans notre base
        $transaction = Transaction::whereJsonContains('meta->transaction_id', $cpm_trans_id)->first();
        
        if (!$transaction) {
            Log::error('Transaction non trouvée dans la base', ['transaction_id' => $cpm_trans_id]);
            return response()->json(['status' => 'error'], 404);
        }

        Log::info('Transaction trouvée, vérification du statut avec l\'API', [
            'transaction_id' => $cpm_trans_id,
            'current_status' => $transaction->status,
            'error_message' => $cpm_error_message
        ]);

        // Vérifier le statut avec l'API CinetPay
        $verificationResult = $this->verifyTransaction($cpm_trans_id);

        if ($verificationResult['success']) {
            $status = $verificationResult['data']['status'];
            
            Log::info('Statut CinetPay obtenu', [
                'transaction_id' => $cpm_trans_id,
                'cinetpay_status' => $status,
                'current_db_status' => $transaction->status
            ]);
            
            if ($status === 'ACCEPTED' && $transaction->status !== 'completed') {
                $transaction->update([
                    'status' => 'completed',
                    'meta' => json_encode(array_merge(
                        json_decode($transaction->meta, true),
                        [
                            'cinetpay_verification' => $verificationResult['data'],
                            'notification_data' => [
                                'payment_method' => $payment_method,
                                'phone' => $cel_phone_num,
                                'error_message' => $cpm_error_message,
                                'verified_at' => now()
                            ]
                        ]
                    ))
                ]);

                $this->processDeposit($transaction);
                
                Log::info('✓ Transaction marquée comme COMPLÉTÉE', [
                    'transaction_id' => $cpm_trans_id,
                    'amount' => $cpm_amount
                ]);
                
            } elseif ($status === 'REFUSED' && $transaction->status !== 'failed') {
                $transaction->update([
                    'status' => 'failed',
                    'meta' => json_encode(array_merge(
                        json_decode($transaction->meta, true),
                        [
                            'cinetpay_verification' => $verificationResult['data'],
                            'error_message' => $cpm_error_message
                        ]
                    ))
                ]);
                
                Log::info('✗ Transaction marquée comme ÉCHOUÉE', [
                    'transaction_id' => $cpm_trans_id,
                    'error' => $cpm_error_message
                ]);
                
            } elseif (in_array($status, ['CANCELLED', 'CANCELED']) && $transaction->status !== 'cancelled') {
                $transaction->update([
                    'status' => 'cancelled',
                    'meta' => json_encode(array_merge(
                        json_decode($transaction->meta, true),
                        [
                            'cinetpay_verification' => $verificationResult['data'],
                            'error_message' => $cpm_error_message
                        ]
                    ))
                ]);
                
                Log::info('✗ Transaction marquée comme ANNULÉE', ['transaction_id' => $cpm_trans_id]);
            }
        } else {
            Log::error('Échec de la vérification avec l\'API CinetPay', [
                'transaction_id' => $cpm_trans_id,
                'error' => $verificationResult
            ]);
        }

        // Toujours retourner success pour que CinetPay arrête de réessayer
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

    /**
     * Endpoint de debug pour lister les transactions (DEV ONLY)
     */
    public function debugTransactions(Request $request)
    {
        Log::info('=== DEBUG TRANSACTIONS ===');

        $transactions = Transaction::where('type', 'deposit')
            ->latest()
            ->limit(10)
            ->get(['id', 'user_id', 'type', 'amount', 'status', 'meta', 'created_at']);

        $result = [];
        foreach ($transactions as $transaction) {
            $meta = json_decode($transaction->meta, true);
            $result[] = [
                'id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'amount' => $transaction->amount,
                'status' => $transaction->status,
                'transaction_id' => $meta['transaction_id'] ?? 'N/A',
                'payment_method' => $meta['payment_method'] ?? 'N/A',
                'created_at' => $transaction->created_at,
                'meta_raw' => $transaction->meta
            ];
        }

        return response()->json([
            'success' => true,
            'count' => count($result),
            'transactions' => $result
        ]);
    }

    /**
     * Endpoint de test pour vérifier une transaction sans authentification (DEV ONLY)
     */
    public function testCheckStatus(Request $request)
    {
        Log::info('=== TEST CHECK STATUS (NO AUTH) ===', [
            'request_all' => $request->all(),
            'transaction_id' => $request->input('transaction_id')
        ]);

        $transactionId = $request->input('transaction_id');
        
        if (!$transactionId) {
            return response()->json([
                'success' => false,
                'message' => 'transaction_id requis'
            ], 400);
        }

        // Chercher la transaction dans la base - essayer plusieurs méthodes
        Log::info('Recherche de transaction', ['transaction_id' => $transactionId]);
        
        // Méthode 1: whereJsonContains
        $transaction1 = Transaction::whereJsonContains('meta->transaction_id', $transactionId)->first();
        Log::info('Méthode 1 (whereJsonContains)', ['found' => $transaction1 ? $transaction1->id : null]);
        
        // Méthode 2: where avec LIKE
        $transaction2 = Transaction::where('meta', 'LIKE', "%{$transactionId}%")->first();
        Log::info('Méthode 2 (LIKE)', ['found' => $transaction2 ? $transaction2->id : null]);
        
        // Méthode 3: parcourir toutes les transactions
        $allTransactions = Transaction::where('type', 'deposit')->get();
        $transaction3 = null;
        foreach ($allTransactions as $tx) {
            $meta = json_decode($tx->meta, true);
            if (isset($meta['transaction_id']) && $meta['transaction_id'] === $transactionId) {
                $transaction3 = $tx;
                break;
            }
        }
        Log::info('Méthode 3 (foreach)', ['found' => $transaction3 ? $transaction3->id : null]);
        
        $transaction = $transaction1 ?: $transaction2 ?: $transaction3;
        
        if (!$transaction) {
            Log::error('Transaction non trouvée avec aucune méthode', [
                'transaction_id' => $transactionId,
                'total_transactions' => $allTransactions->count()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Transaction non trouvée dans la base de données',
                'debug' => [
                    'searched_id' => $transactionId,
                    'method1_found' => $transaction1 ? true : false,
                    'method2_found' => $transaction2 ? true : false,
                    'method3_found' => $transaction3 ? true : false,
                    'total_deposit_transactions' => $allTransactions->count()
                ]
            ], 404);
        }

        Log::info('Transaction trouvée, appel CinetPay', [
            'transaction_id' => $transactionId,
            'db_status' => $transaction->status
        ]);

        // Vérifier avec CinetPay
        $verificationResult = $this->verifyTransaction($transactionId);
        
        if ($verificationResult['success']) {
            $cinetpayStatus = $verificationResult['data']['status'] ?? 'UNKNOWN';
            
            // Mettre à jour si nécessaire
            if ($cinetpayStatus === 'ACCEPTED' && $transaction->status !== 'completed') {
                $transaction->update(['status' => 'completed']);
                $this->processDeposit($transaction);
                Log::info('Transaction mise à jour vers completed');
            }
            
            return response()->json([
                'success' => true,
                'status' => $transaction->fresh()->status,
                'cinetpay_status' => $cinetpayStatus,
                'amount' => $transaction->amount,
                'data' => $verificationResult['data']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erreur vérification CinetPay',
            'error' => $verificationResult
        ]);
    }

    /**
     * API pour vérifier le statut d'une transaction spécifique
     */
    public function checkTransactionStatus(Request $request)
    {
        Log::info('=== DÉBUT VÉRIFICATION STATUT TRANSACTION ===', [
            'request_data' => $request->all(),
            'has_auth_user' => $request->user() ? true : false
        ]);

        $request->validate([
            'transaction_id' => 'required|string'
        ]);

        $transactionId = $request->transaction_id;
        
        // Essayer d'obtenir l'utilisateur soit par auth, soit par la transaction
        $user = $request->user();
        
        if (!$user) {
            // Si pas d'auth, chercher la transaction pour obtenir l'user_id
            $tempTransaction = Transaction::whereJsonContains('meta->transaction_id', $transactionId)->first();
            if ($tempTransaction) {
                $user = $tempTransaction->user;
                Log::info('Utilisateur trouvé via transaction', ['user_id' => $user->id]);
            }
        }

        Log::info('Recherche de la transaction dans la base de données', [
            'transaction_id' => $transactionId,
            'user_id' => $user ? $user->id : null
        ]);

        // Utiliser la même logique de recherche que testCheckStatus qui fonctionne
        Log::info('Recherche de transaction avec plusieurs méthodes', ['transaction_id' => $transactionId]);
        
        // Méthode 1: whereJsonContains
        $transaction = Transaction::whereJsonContains('meta->transaction_id', $transactionId)->first();
        
        if (!$transaction) {
            // Méthode 2: where avec LIKE
            $transaction = Transaction::where('meta', 'LIKE', "%{$transactionId}%")->first();
        }
        
        if (!$transaction) {
            // Méthode 3: parcourir toutes les transactions
            $allTransactions = Transaction::where('type', 'deposit')->get();
            foreach ($allTransactions as $tx) {
                $meta = json_decode($tx->meta, true);
                if (isset($meta['transaction_id']) && $meta['transaction_id'] === $transactionId) {
                    $transaction = $tx;
                    break;
                }
            }
        }
        
        Log::info('Résultat de la recherche', [
            'found' => $transaction ? true : false,
            'transaction_id' => $transactionId
        ]);

        if (!$transaction) {
            Log::error('Transaction non trouvée dans la base de données', [
                'transaction_id' => $transactionId,
                'user_id' => $user ? $user->id : null
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
     * Initier un retrait via l'API de transfert CinetPay (avec option asynchrone)
     */
    public function initiateWithdrawal(Request $request)
    {
        // Vérifier si on doit utiliser le mode asynchrone (via queue)
        $useQueue = $request->input('async', false) || env('CINETPAY_USE_QUEUE', true);
        
        if ($useQueue) {
            return $this->initiateWithdrawalAsync($request);
        }
        
        $request->validate([
            'amount' => 'required|numeric|min:500|multiple_of:5',
            'phone_number' => 'required|string',
            'operator' => 'nullable|string|in:WAVECI,WAVESN,MOOV,MTN'
        ]);
        
        // Utiliser l'opérateur fourni ou null pour auto-détection
        $operator = $request->input('operator', null);

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
        $transferResult = $this->initiateTransfer($token, $contactData, $request->phone_number, $amount, $operator);
        
        if ($transferResult['success']) {
            // Créer la transaction de retrait
            $transaction = $user->transactions()->create([
                'type' => 'withdrawal',
                'amount' => -$amount,
                'description' => "Retrait vers " . ($operator ?: 'Auto') . " - {$request->phone_number}",
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
            // Gestion spécifique pour les erreurs de timeout
            if (isset($transferResult['is_timeout']) && $transferResult['is_timeout']) {
                Log::warning('Timeout lors du transfert CinetPay', [
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'phone' => $request->phone_number
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Le serveur CinetPay met trop de temps à répondre. Veuillez réessayer dans quelques instants.',
                    'is_timeout' => true
                ], 504); // 504 Gateway Timeout
            }
            
            return response()->json([
                'success' => false,
                'message' => $transferResult['message'] ?? 'Erreur lors de l\'initiation du retrait',
                'details' => $transferResult['error_details'] ?? null
            ], 500);
        }
    }

    /**
     * Authentification à l'API de transfert CinetPay
     */
    private function authenticateCinetPay()
    {
        try {
            Log::info('=== DÉBUT AUTHENTIFICATION CINETPAY ===', [
                'apikey' => $this->apiKey,
                'url' => 'https://client.cinetpay.com/v1/auth/login'
            ]);

            $response = Http::timeout(45)->asForm()->post('https://client.cinetpay.com/v1/auth/login', [
                'apikey' => $this->apiKey,
                'password' => $this->transferPassword
            ]);

            Log::info('CinetPay Auth Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['code']) && $data['code'] == 0 && isset($data['data']['token'])) {
                    Log::info('Authentication réussie', ['token_received' => true]);
                    return [
                        'success' => true,
                        'token' => $data['data']['token']
                    ];
                }
            }

            Log::error('CinetPay Auth Failed', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);
            return ['success' => false, 'error' => $response->json()];

        } catch (Exception $e) {
            Log::error('CinetPay Auth Exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Créer ou récupérer un contact CinetPay
     */
    private function createOrGetContact($token, $phoneNumber, $user)
    {
        try {
            Log::info('=== CRÉATION CONTACT CINETPAY ===', [
                'phone' => $phoneNumber,
                'user_id' => $user->id
            ]);

            // Extraire le préfixe et le numéro selon le format CinetPay
            $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
            
            // Vérifier que le numéro n'est pas vide ou ne contient que le préfixe
            if (empty($cleanPhone) || $cleanPhone === '237') {
                Log::error('Numéro de téléphone invalide ou manquant', [
                    'original_phone' => $phoneNumber,
                    'cleaned_phone' => $cleanPhone
                ]);
                return [
                    'success' => false, 
                    'error' => 'Numéro de téléphone requis et valide'
                ];
            }
            
            // Gérer les formats de téléphone camerounais
            if (strlen($cleanPhone) == 9 && !str_starts_with($cleanPhone, '237')) {
                $prefix = '237';
                $phone = $cleanPhone;
            } elseif (str_starts_with($cleanPhone, '237') && strlen($cleanPhone) > 3) {
                $prefix = '237';
                $phone = substr($cleanPhone, 3);
            } else {
                $prefix = '237'; // Par défaut Cameroun
                $phone = $cleanPhone;
            }

            $contactData = [
                [
                    'prefix' => $prefix,
                    'phone' => $phone,
                    'name' => $user->last_name ?? 'User',
                    'surname' => $user->first_name ?? 'Formaneo',
                    'email' => $user->email
                ]
            ];

            Log::info('Contact data to send', $contactData);

            $response = Http::timeout(45)->asForm()->post("https://client.cinetpay.com/v1/transfer/contact?token={$token}&lang=fr", [
                'data' => json_encode($contactData)
            ]);

            Log::info('CinetPay Contact Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['code']) && $data['code'] == 0 && isset($data['data'])) {
                    // CinetPay peut retourner soit data[0] soit data[0][0] selon les cas
                    $contactInfo = null;
                    
                    if (isset($data['data'][0][0])) {
                        // Cas: data[[{...}]]
                        $contactInfo = $data['data'][0][0];
                        Log::info('Structure double tableau détectée', ['contact_info' => $contactInfo]);
                    } elseif (isset($data['data'][0])) {
                        // Cas: data[{...}]
                        $contactInfo = $data['data'][0];
                        Log::info('Structure simple tableau détectée', ['contact_info' => $contactInfo]);
                    }
                    
                    if ($contactInfo && isset($contactInfo['status'])) {
                        $status = $contactInfo['status'];
                        
                        // Traiter comme succès si le contact est créé ou existe déjà
                        if ($status === 'success' || $status === 'ERROR_PHONE_ALREADY_MY_CONTACT') {
                            Log::info('Contact traité avec succès', [
                                'status' => $status,
                                'message' => $status === 'ERROR_PHONE_ALREADY_MY_CONTACT' ? 'Contact existe déjà' : 'Contact créé'
                            ]);
                            
                            return [
                                'success' => true,
                                'contact_data' => $contactInfo,
                                'lot' => $contactInfo['lot'],
                                'contact_status' => $status
                            ];
                        } else {
                            Log::error('Status de contact non géré', [
                                'status' => $status,
                                'contact_info' => $contactInfo
                            ]);
                        }
                    } else {
                        Log::error('Contact info invalide - pas de status', [
                            'contact_info' => $contactInfo,
                            'full_data' => $data
                        ]);
                    }
                }
            }

            Log::error('CinetPay Contact Creation Failed', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);
            return ['success' => false, 'error' => $response->json()];

        } catch (Exception $e) {
            Log::error('CinetPay Contact Exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Initier un transfert CinetPay avec système de retry
     */
    private function initiateTransfer($token, $contactData, $phoneNumber, $amount, $operator, $retryCount = 0)
    {
        $maxRetries = 2;
        
        try {
            Log::info('=== INITIATION TRANSFERT CINETPAY ===', [
                'amount' => $amount,
                'operator' => $operator,
                'phone' => $phoneNumber,
                'retry_attempt' => $retryCount
            ]);

            // Vérifier d'abord le solde
            $balanceResponse = Http::timeout(30)->get("https://client.cinetpay.com/v1/transfer/check/balance?token={$token}&lang=fr");
            
            Log::info('Balance check response', [
                'status' => $balanceResponse->status(),
                'body' => $balanceResponse->body()
            ]);

            if ($balanceResponse->successful()) {
                $balanceData = $balanceResponse->json();
                if (isset($balanceData['code']) && $balanceData['code'] == 0) {
                    $availableBalance = $balanceData['data']['available'] ?? 0;
                    
                    Log::info('Solde CinetPay', [
                        'available' => $availableBalance,
                        'amount_requested' => $amount
                    ]);

                    if ($availableBalance < $amount) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Solde CinetPay insuffisant pour effectuer ce retrait'
                        ], 400);
                    }
                }
            }

            // Extraire les infos du contact
            $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
            if (strlen($cleanPhone) == 9 && !str_starts_with($cleanPhone, '237')) {
                $prefix = '237';
                $phone = $cleanPhone;
            } elseif (str_starts_with($cleanPhone, '237')) {
                $prefix = '237';
                $phone = substr($cleanPhone, 3);
            } else {
                $prefix = '237';
                $phone = $cleanPhone;
            }

            // Préparer les données de transfert selon la documentation
            $transferData = [
                [
                    'prefix' => $prefix,
                    'phone' => $phone,
                    'amount' => (int) $amount,
                    'client_transaction_id' => 'TRANSFER_' . time() . '_' . random_int(1000, 9999),
                    'notify_url' => $this->notifyUrl . '/transfer'
                ]
            ];
            
            // Ajouter payment_method seulement si c'est un opérateur supporté
            if ($operator && in_array($operator, ['WAVECI', 'WAVESN', 'MOOV', 'MTN'])) {
                $transferData[0]['payment_method'] = $operator;
            }
            // Pour null/vide, ORANGE et autres, laisser CinetPay auto-détecter

            Log::info('Transfer data to send', $transferData);

            // Effectuer le transfert avec timeout progressif selon le retry
            $timeout = 30 + ($retryCount * 15); // 30s, 45s, 60s
            Log::info("Tentative de transfert avec timeout de {$timeout} secondes");
            
            $response = Http::timeout($timeout)->asForm()->post("https://client.cinetpay.com/v1/transfer/money/send/contact?token={$token}&lang=fr", [
                'data' => json_encode($transferData)
            ]);

            Log::info('CinetPay Transfer Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['code']) && $data['code'] == 0 && isset($data['data'])) {
                    // CinetPay peut retourner soit data[0] soit data[0][0] selon les cas
                    $transferInfo = null;
                    
                    if (isset($data['data'][0][0])) {
                        // Cas: data[[{...}]]
                        $transferInfo = $data['data'][0][0];
                        Log::info('Structure double tableau détectée pour transfert', ['transfer_info' => $transferInfo]);
                    } elseif (isset($data['data'][0])) {
                        // Cas: data[{...}]
                        $transferInfo = $data['data'][0];
                        Log::info('Structure simple tableau détectée pour transfert', ['transfer_info' => $transferInfo]);
                    }
                    
                    if ($transferInfo && isset($transferInfo['status']) && $transferInfo['status'] === 'success') {
                        return [
                            'success' => true,
                            'transfer_id' => $transferInfo['transaction_id'],
                            'client_transaction_id' => $transferInfo['client_transaction_id'],
                            'lot' => $transferInfo['lot'],
                            'treatment_status' => $transferInfo['treatment_status']
                        ];
                    } else {
                        Log::error('Transfer info invalide ou status non-success', [
                            'transfer_info' => $transferInfo,
                            'full_data' => $data
                        ]);
                    }
                }
            }

            Log::error('CinetPay Transfer Failed', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);
            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Erreur lors du transfert',
                'error' => $response->json()
            ];

        } catch (Exception $e) {
            Log::error('CinetPay Transfer Exception', [
                'message' => $e->getMessage(),
                'retry_count' => $retryCount
            ]);
            
            // Gestion spécifique des erreurs de timeout avec retry
            if (str_contains($e->getMessage(), 'cURL error 28') || str_contains($e->getMessage(), 'timed out')) {
                if ($retryCount < $maxRetries) {
                    $nextAttempt = $retryCount + 1;
                    Log::warning("Timeout détecté, nouvelle tentative ({$nextAttempt}/{$maxRetries})");
                    
                    // Attendre un peu avant de réessayer
                    sleep(2);
                    
                    // Obtenir un nouveau token si nécessaire (le token expire après 5 minutes)
                    $authResponse = $this->authenticateCinetPay();
                    if (!$authResponse['success']) {
                        return [
                            'success' => false,
                            'message' => 'Impossible de renouveler l\'authentification CinetPay',
                            'is_timeout' => true
                        ];
                    }
                    
                    // Réessayer avec le nouveau token
                    return $this->initiateTransfer($authResponse['token'], $contactData, $phoneNumber, $amount, $operator, $retryCount + 1);
                }
                
                return [
                    'success' => false,
                    'message' => 'Le serveur CinetPay ne répond pas après plusieurs tentatives. Veuillez réessayer plus tard.',
                    'is_timeout' => true,
                    'retry_exhausted' => true
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Erreur technique lors du transfert',
                'error_details' => $e->getMessage()
            ];
        }
    }

    /**
     * Initier un retrait de façon asynchrone (via queue)
     */
    private function initiateWithdrawalAsync(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:500|multiple_of:5',
            'phone_number' => 'required|string',
            'operator' => 'nullable|string|in:WAVECI,WAVESN,MOOV,MTN'
        ]);
        
        // Utiliser l'opérateur fourni ou null pour auto-détection
        $operator = $request->input('operator', null);

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

        try {
            // Créer la transaction en attente
            $transaction = $user->transactions()->create([
                'type' => 'withdrawal',
                'amount' => -$amount,
                'description' => "Retrait vers " . ($operator ?: 'Auto') . " - {$request->phone_number}",
                'status' => 'queued', // Status spécial pour les transferts en queue
                'meta' => json_encode([
                    'operator' => $operator,
                    'phone_number' => $request->phone_number,
                    'queued_at' => now(),
                    'async_mode' => true
                ])
            ]);

            // Déduire le montant du solde immédiatement
            $user->decrement('balance', $amount);

            // Dispatcher le job
            ProcessCinetPayTransfer::dispatch(
                $transaction, 
                $user, 
                $amount, 
                $request->phone_number, 
                $request->operator
            );

            Log::info('Transfert CinetPay ajouté à la queue', [
                'transaction_id' => $transaction->id,
                'user_id' => $user->id,
                'amount' => $amount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Votre retrait est en cours de traitement. Vous recevrez une notification une fois terminé.',
                'transaction_id' => $transaction->id,
                'status' => 'queued',
                'new_balance' => $user->balance,
                'processing_mode' => 'asynchronous'
            ]);

        } catch (Exception $e) {
            Log::error('Erreur lors de la création du job de transfert', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'initiation du retrait. Veuillez réessayer.'
            ], 500);
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
            $response = Http::timeout(30)->get("https://client.cinetpay.com/v1/transfer/check/money?token={$authResponse['token']}&lang=fr&transaction_id={$transferId}");

            Log::info('CinetPay Transfer Check Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['code']) && $data['code'] == 0 && isset($data['data'][0])) {
                    $transferInfo = $data['data'][0];
                    $transferStatus = $transferInfo['treatment_status'] ?? 'UNKNOWN';
                    $sendingStatus = $transferInfo['sending_status'] ?? 'UNKNOWN';

                    Log::info('Transfer status details', [
                        'treatment_status' => $transferStatus,
                        'sending_status' => $sendingStatus,
                        'transfer_valid' => $transferInfo['transfer_valid'] ?? null
                    ]);

                    // Mettre à jour le statut de la transaction selon le treatment_status
                    if ($transferStatus === 'VAL' && $sendingStatus === 'CONFIRM' && $transaction->status !== 'completed') {
                        $transaction->update(['status' => 'completed']);
                        Log::info('Transfer completed successfully');
                    } elseif (in_array($transferStatus, ['REJ', 'FAILED']) && $transaction->status !== 'failed') {
                        $transaction->update(['status' => 'failed']);
                        // Rembourser l'utilisateur
                        $user->increment('balance', abs($transaction->amount));
                        Log::info('Transfer failed, user refunded');
                    }

                    return response()->json([
                        'success' => true,
                        'status' => $transaction->fresh()->status,
                        'transfer_status' => $transferStatus,
                        'sending_status' => $sendingStatus,
                        'amount' => abs($transaction->amount),
                        'created_at' => $transaction->created_at,
                        'transfer_details' => $transferInfo
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Impossible de vérifier le statut',
                'current_status' => $transaction->status,
                'response' => $response->json()
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
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Transaction;
use App\Models\AppSetting;
use App\Models\AdminNotification;
use App\Models\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class AccountActivationController extends Controller
{
    protected $apiKey;
    protected $siteId;
    protected $notifyUrl;
    protected $returnUrl;

    public function __construct()
    {
        $this->apiKey = config('cinetpay.api_key');
        $this->siteId = config('cinetpay.site_id');
        $this->notifyUrl = env('CINETPAY_NOTIFY_URL', 'http://192.168.1.149:8001/api/v1/cinetpay/activation/notify');
        $this->returnUrl = 'http://192.168.1.149:8001/api/v1/cinetpay/return';
    }

    public function getActivationInfo(Request $request)
    {
        $user = $request->user();
        $activationCost = \App\Models\Settings::getValue('account_activation_cost', 5000);

        return response()->json([
            'success' => true,
            'account_status' => $user->account_status ?? 'inactive',
            'account_activated_at' => $user->account_activated_at,
            'account_expires_at' => $user->account_expires_at,
            'welcome_bonus_claimed' => $user->welcome_bonus_claimed ?? false,
            'activation_cost' => (float) $activationCost,
            'welcome_bonus_amount' => 2000
        ]);
    }

    public function initiateActivation(Request $request)
    {
        $user = $request->user();
        
        // Vérifier si le compte est déjà actif
        if ($user->account_status === 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte est déjà actif.'
            ], 400);
        }

        $activationCost = \App\Models\Settings::getValue('account_activation_cost', 5000);
        $transactionId = 'ACTIVATION' . time() . random_int(1000, 9999);
        $phoneNumber = $request->phone_number ?? $user->phone ?? '+237600000000';

        // Préparer les données pour CinetPay selon la documentation
        $paymentData = [
            'apikey' => $this->apiKey,
            'site_id' => $this->siteId,
            'transaction_id' => $transactionId,
            'amount' => (int) $activationCost,
            'currency' => 'XAF',
            'description' => 'Activation compte Formaneo - 1 mois',
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
            'metadata' => 'activation_user' . $user->id,
            'lang' => 'fr'
        ];

        Log::info('CinetPay Activation Request Data', $paymentData);

        try {
            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
                'User-Agent' => 'Formaneo-App/1.0'
            ])->post('https://api-checkout.cinetpay.com/v2/payment', $paymentData);

            Log::info('CinetPay Activation Response', [
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

            // Créer une transaction d'activation en attente
            Log::info('=== CRÉATION TRANSACTION ACTIVATION EN BASE ===', [
                'user_id' => $user->id,
                'transaction_id' => $transactionId,
                'amount' => $activationCost
            ]);

            $transactionData = [
                'type' => 'account_activation',
                'amount' => $activationCost,
                'description' => 'Activation de compte - 1 mois',
                'status' => 'pending',
                'meta' => json_encode([
                    'transaction_id' => $transactionId,
                    'payment_method' => 'cinetpay',
                    'payment_token' => $responseData['data']['payment_token'] ?? null,
                    'cinetpay_response' => $responseData['data'] ?? null,
                    'activation_type' => 'monthly',
                    'duration_months' => 1,
                    'created_at' => now(),
                ])
            ];

            Log::info('Données de transaction activation à sauvegarder', $transactionData);

            $transaction = $user->transactions()->create($transactionData);

            Log::info('✓ Transaction activation créée avec succès', [
                'db_id' => $transaction->id,
                'transaction_id' => $transactionId,
                'status' => $transaction->status,
                'meta' => $transaction->meta
            ]);

            return response()->json([
                'success' => true,
                'transaction_id' => $transactionId,
                'payment_url' => $responseData['data']['payment_url'] ?? null,
                'payment_token' => $responseData['data']['payment_token'] ?? null
            ]);

        } catch (Exception $e) {
            Log::error('CinetPay Activation Exception', [
                'message' => $e->getMessage(),
                'amount' => $activationCost,
                'phone' => $phoneNumber
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur technique: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processActivationPayment(Request $request)
    {
        Log::info('=== NOTIFICATION ACTIVATION CINETPAY REÇUE ===', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'method' => $request->method()
        ]);

        // Récupérer le token HMAC depuis l'entête
        $receivedToken = $request->header('x-token');
        
        if (!$receivedToken) {
            Log::error('Token HMAC manquant dans l\'entête pour activation');
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
            Log::error('Données obligatoires manquantes pour activation', [
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
        
        if (!empty($secretKey)) {
            // Générer le token HMAC avec SHA256
            $generatedToken = hash_hmac('SHA256', $data, $secretKey);
            
            Log::info('Vérification du token HMAC activation', [
                'received_token' => substr($receivedToken, 0, 20) . '...',
                'generated_token' => substr($generatedToken, 0, 20) . '...',
                'tokens_match' => hash_equals($receivedToken, $generatedToken)
            ]);

            // Vérifier que les tokens correspondent
            if (!hash_equals($receivedToken, $generatedToken)) {
                Log::error('Token HMAC invalide - Notification activation rejetée');
                return response()->json(['status' => 'error', 'message' => 'Token invalide'], 401);
            }
        }

        Log::info('Token HMAC valide pour activation - Traitement de la notification');

        // Vérifier que la transaction existe dans notre base
        $transaction = Transaction::whereJsonContains('meta->transaction_id', $cpm_trans_id)
                                 ->where('type', 'account_activation')
                                 ->first();
        
        if (!$transaction) {
            Log::error('Transaction activation non trouvée dans la base', ['transaction_id' => $cpm_trans_id]);
            return response()->json(['status' => 'error'], 404);
        }

        Log::info('Transaction activation trouvée, vérification du statut avec l\'API', [
            'transaction_id' => $cpm_trans_id,
            'current_status' => $transaction->status,
            'error_message' => $cpm_error_message
        ]);

        // Vérifier le statut avec l'API CinetPay
        $verificationResult = $this->verifyTransaction($cpm_trans_id);

        if ($verificationResult['success']) {
            $status = $verificationResult['data']['status'];
            
            Log::info('Statut CinetPay activation obtenu', [
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

                $this->processAccountActivation($transaction);
                
                Log::info('✓ Transaction activation marquée comme COMPLÉTÉE', [
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
                
                Log::info('✗ Transaction activation marquée comme ÉCHOUÉE', [
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
                
                Log::info('✗ Transaction activation marquée comme ANNULÉE', ['transaction_id' => $cpm_trans_id]);
            }
        } else {
            Log::error('Échec de la vérification avec l\'API CinetPay pour activation', [
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
    private function verifyTransaction($transactionId)
    {
        Log::info('>>> Appel API CinetPay payment/check pour activation', [
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

            Log::info('Données envoyées à CinetPay pour activation', [
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

            Log::info('Réponse CinetPay activation reçue', [
                'status_code' => $statusCode,
                'response_code' => $responseData['code'] ?? null,
                'response_message' => $responseData['message'] ?? null,
                'has_data' => isset($responseData['data']),
                'full_response' => $responseData
            ]);

            if ($response->successful()) {
                if (isset($responseData['code']) && $responseData['code'] === '00') {
                    Log::info('✓ Vérification CinetPay activation réussie', [
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
                    Log::warning('CinetPay activation - Code de retour non valide', [
                        'transaction_id' => $transactionId,
                        'code' => $responseData['code'] ?? 'NO_CODE',
                        'message' => $responseData['message'] ?? 'NO_MESSAGE'
                    ]);
                }
            } else {
                Log::error('CinetPay activation - Réponse HTTP non réussie', [
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
            Log::error('Exception lors de la vérification CinetPay activation', [
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
     * Traiter l'activation du compte
     */
    private function processAccountActivation($transaction)
    {
        if ($transaction->type !== 'account_activation') {
            return;
        }

        $user = $transaction->user;
        
        DB::transaction(function () use ($user, $transaction) {
            // Activer le compte
            $user->update([
                'account_status' => 'active',
                'account_activated_at' => now(),
                'account_expires_at' => now()->addMonth()
            ]);
            
            // Donner le bonus de bienvenue si pas encore réclamé
            if (!$user->welcome_bonus_claimed) {
                $user->update([
                    'balance' => ($user->balance ?? 0) + 2000,
                    'welcome_bonus_claimed' => true
                ]);
                
                // Créer la transaction du bonus de bienvenue
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'bonus',
                    'amount' => 2000,
                    'description' => 'Bonus de bienvenue - Activation compte',
                    'status' => 'completed',
                    'meta' => json_encode([
                        'bonus_type' => 'welcome_bonus_activation',
                        'activation_transaction_id' => $transaction->id
                    ])
                ]);
            }
            
            // Distribuer les commissions d'affiliation maintenant que le compte est activé
            $this->distributeAffiliateCommissions($user);
            
            Log::info('Activation de compte traitée', [
                'user_id' => $user->id,
                'account_status' => 'active',
                'expires_at' => $user->account_expires_at,
                'bonus_given' => !$user->welcome_bonus_claimed,
                'new_balance' => $user->fresh()->balance
            ]);
        });
    }

    /**
     * Distribuer les commissions d'affiliation lors de l'activation du compte
     */
    private function distributeAffiliateCommissions($user)
    {
        // Vérifier si l'utilisateur a été référé
        if (!$user->referred_by) {
            Log::info("Pas de parrain trouvé pour l'utilisateur {$user->id} lors de l'activation");
            return;
        }

        // Récupérer les montants de commission depuis les settings
        $level1Commission = Settings::getValue('level1_commission', 1000);
        $level2Commission = Settings::getValue('level2_commission', 500);

        // Niveau 1 : Commission pour le parrain direct
        $directReferrer = User::find($user->referred_by);
        if ($directReferrer) {
            $this->giveCommission($directReferrer, $level1Commission, 1, $user);
            Log::info("Commission niveau 1 de {$level1Commission} FCFA donnée à l'utilisateur {$directReferrer->id} pour l'activation de {$user->id}");

            // Niveau 2 : Commission pour le parrain du parrain
            if ($directReferrer->referred_by) {
                $indirectReferrer = User::find($directReferrer->referred_by);
                if ($indirectReferrer) {
                    $this->giveCommission($indirectReferrer, $level2Commission, 2, $user);
                    Log::info("Commission niveau 2 de {$level2Commission} FCFA donnée à l'utilisateur {$indirectReferrer->id} pour l'activation de {$user->id}");
                }
            }
        }
    }

    /**
     * Donner une commission à un utilisateur
     */
    private function giveCommission(User $referrer, float $amount, int $level, User $activatedUser)
    {
        // Ajouter la commission au solde du parrain
        $referrer->balance += $amount;
        $referrer->total_commissions += $amount;
        $referrer->save();

        // Créer une transaction de commission
        $referrer->transactions()->create([
            'type' => 'affiliate_commission',
            'amount' => $amount,
            'description' => "Commission niveau {$level} - Activation de {$activatedUser->name}",
            'status' => 'completed',
            'meta' => json_encode([
                'commission_level' => $level,
                'activated_user_id' => $activatedUser->id,
                'activated_user_name' => $activatedUser->name,
                'source_type' => 'account_activation',
                'source_id' => $activatedUser->id
            ])
        ]);
    }
}

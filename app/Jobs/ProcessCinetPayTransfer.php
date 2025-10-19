<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessCinetPayTransfer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120; // 2 minutes max pour le job
    public $tries = 3; // Nombre maximum de tentatives
    public $backoff = [10, 30, 60]; // Délai entre les tentatives (10s, 30s, 60s)

    protected $transaction;
    protected $user;
    protected $amount;
    protected $phoneNumber;
    protected $operator;

    /**
     * Create a new job instance.
     */
    public function __construct(Transaction $transaction, User $user, $amount, $phoneNumber, $operator)
    {
        $this->transaction = $transaction;
        $this->user = $user;
        $this->amount = $amount;
        $this->phoneNumber = $phoneNumber;
        $this->operator = $operator;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            Log::info('=== DÉBUT JOB TRANSFERT CINETPAY ===', [
                'transaction_id' => $this->transaction->id,
                'user_id' => $this->user->id,
                'amount' => $this->amount,
                'attempt' => $this->attempts()
            ]);

            // Configuration pour l'API de transfert CinetPay
            $apiKey = '45213166268af015b7d2734.50726534'; // API Key pour les transferts
            $transferPassword = '12345678'; // Mot de passe pour les transferts
            
            // 1. Authentification
            $authResponse = $this->authenticate($apiKey, $transferPassword);
            if (!$authResponse['success']) {
                throw new Exception('Échec de l\'authentification CinetPay');
            }
            
            $token = $authResponse['token'];
            
            // 2. Créer/Vérifier le contact
            $contactResponse = $this->createContact($token, $this->phoneNumber, $this->user);
            if (!$contactResponse['success']) {
                throw new Exception('Échec de la création du contact');
            }
            
            // 3. Initier le transfert
            $transferResponse = $this->executeTransfer($token, $this->phoneNumber, $this->amount, $this->operator);
            
            if ($transferResponse['success']) {
                // Mise à jour de la transaction
                $meta = json_decode($this->transaction->meta, true);
                $meta['cinetpay_transfer_id'] = $transferResponse['transfer_id'];
                $meta['transfer_status'] = 'initiated';
                $meta['processed_at'] = now();
                
                $this->transaction->update([
                    'status' => 'processing',
                    'meta' => json_encode($meta)
                ]);
                
                Log::info('✓ Transfert CinetPay initié avec succès', [
                    'transaction_id' => $this->transaction->id,
                    'transfer_id' => $transferResponse['transfer_id']
                ]);
                
            } else {
                throw new Exception($transferResponse['message'] ?? 'Échec du transfert');
            }
            
        } catch (Exception $e) {
            Log::error('Échec du job de transfert CinetPay', [
                'transaction_id' => $this->transaction->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);
            
            // Si c'est la dernière tentative, marquer comme échoué et rembourser
            if ($this->attempts() >= $this->tries) {
                $this->handleFailure($e);
            } else {
                // Re-throw pour réessayer
                throw $e;
            }
        }
    }

    /**
     * Authentification CinetPay
     */
    private function authenticate($apiKey, $password)
    {
        try {
            $response = Http::timeout(30)->asForm()->post('https://client.cinetpay.com/v1/auth/login', [
                'apikey' => $apiKey,
                'password' => $password
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['code']) && $data['code'] == 0 && isset($data['data']['token'])) {
                    return [
                        'success' => true,
                        'token' => $data['data']['token']
                    ];
                }
            }

            return ['success' => false, 'error' => 'Authentification échouée'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Créer ou récupérer un contact
     */
    private function createContact($token, $phoneNumber, $user)
    {
        try {
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

            $contactData = [
                [
                    'prefix' => $prefix,
                    'phone' => $phone,
                    'name' => $user->last_name ?? 'User',
                    'surname' => $user->first_name ?? 'Formaneo',
                    'email' => $user->email
                ]
            ];

            $response = Http::timeout(30)->asForm()->post("https://client.cinetpay.com/v1/transfer/contact?token={$token}&lang=fr", [
                'data' => json_encode($contactData)
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['code']) && $data['code'] == 0) {
                    // Gérer les différents cas de retour
                    $contactInfo = $data['data'][0][0] ?? $data['data'][0] ?? null;
                    
                    if ($contactInfo && isset($contactInfo['status'])) {
                        if ($contactInfo['status'] === 'success' || $contactInfo['status'] === 'ERROR_PHONE_ALREADY_MY_CONTACT') {
                            return ['success' => true];
                        }
                    }
                }
            }

            return ['success' => false, 'error' => 'Échec création contact'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Exécuter le transfert
     */
    private function executeTransfer($token, $phoneNumber, $amount, $operator)
    {
        try {
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

            $transferData = [
                [
                    'prefix' => $prefix,
                    'phone' => $phone,
                    'amount' => (int) $amount,
                    'client_transaction_id' => 'TRANSFER_' . time() . '_' . random_int(1000, 9999),
                    'notify_url' => env('CINETPAY_NOTIFY_URL', 'http://192.168.1.135:8001/api/v1/cinetpay/notify') . '/transfer'
                ]
            ];
            
            // Ajouter payment_method seulement si c'est un opérateur supporté
            if ($operator && in_array($operator, ['WAVECI', 'WAVESN', 'MOOV', 'MTN'])) {
                $transferData[0]['payment_method'] = $operator;
            }
            // Pour null/vide, ORANGE et autres, laisser CinetPay auto-détecter

            // Timeout progressif selon les tentatives
            $timeout = 30 + ($this->attempts() * 20);
            
            $response = Http::timeout($timeout)->asForm()->post("https://client.cinetpay.com/v1/transfer/money/send/contact?token={$token}&lang=fr", [
                'data' => json_encode($transferData)
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['code']) && $data['code'] == 0) {
                    $transferInfo = $data['data'][0][0] ?? $data['data'][0] ?? null;
                    
                    if ($transferInfo && isset($transferInfo['status']) && $transferInfo['status'] === 'success') {
                        return [
                            'success' => true,
                            'transfer_id' => $transferInfo['transaction_id'],
                            'client_transaction_id' => $transferInfo['client_transaction_id']
                        ];
                    }
                }
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Échec du transfert'
            ];
            
        } catch (Exception $e) {
            // Si c'est un timeout, on peut réessayer
            if (str_contains($e->getMessage(), 'cURL error 28')) {
                Log::warning('Timeout CinetPay détecté', [
                    'attempt' => $this->attempts(),
                    'timeout' => $timeout ?? 30
                ]);
            }
            
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Gérer l'échec définitif
     */
    private function handleFailure($exception)
    {
        // Marquer la transaction comme échouée
        $this->transaction->update(['status' => 'failed']);
        
        // Rembourser l'utilisateur
        $this->user->increment('balance', abs($this->transaction->amount));
        
        Log::error('Échec définitif du transfert CinetPay - Utilisateur remboursé', [
            'transaction_id' => $this->transaction->id,
            'user_id' => $this->user->id,
            'amount' => abs($this->transaction->amount),
            'error' => $exception->getMessage()
        ]);
    }

    /**
     * Gérer l'échec du job
     */
    public function failed(Exception $exception)
    {
        $this->handleFailure($exception);
    }
}
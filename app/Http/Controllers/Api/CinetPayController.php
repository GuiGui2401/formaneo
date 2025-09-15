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

    // Initier un paiement pour un pack de formation
    public function initiatePackPayment(Request $request, $packId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'currency' => 'required|string|in:XOF,XAF,USD,EUR',
            'customer_name' => 'required|string',
            'customer_surname' => 'required|string',
            'customer_email' => 'required|email',
            'customer_phone_number' => 'required|string',
            'customer_country' => 'required|string',
            'channels' => 'nullable|string'
        ]);

        $user = $request->user();
        $pack = \App\Models\FormationPack::findOrFail($packId);

        // Générer un ID de transaction unique
        $transactionId = 'PACK_' . time() . '_' . uniqid();

        // Préparer les données pour CinetPay
        $paymentData = [
            'apikey' => $this->apiKey,
            'site_id' => $this->siteId,
            'transaction_id' => $transactionId,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'description' => "Achat du pack {$pack->name}",
            'channels' => $request->channels ?? 'ALL',
            'customer_name' => $request->customer_name,
            'customer_surname' => $request->customer_surname,
            'customer_email' => $request->customer_email,
            'customer_phone_number' => $request->customer_phone_number,
            'customer_country' => $request->customer_country,
            'notify_url' => $this->notifyUrl
        ];

        // Créer une transaction en attente
        $transaction = $user->transactions()->create([
            'type' => 'pack_purchase',
            'amount' => $request->amount,
            'description' => "Achat du pack {$pack->name}",
            'status' => 'pending',
            'meta' => json_encode([
                'pack_id' => $pack->id,
                'pack_name' => $pack->name,
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

    // Initier un paiement pour un ebook
    public function initiateEbookPayment(Request $request, $ebookId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'currency' => 'required|string|in:XOF,XAF,USD,EUR',
            'customer_name' => 'required|string',
            'customer_surname' => 'required|string',
            'customer_email' => 'required|email',
            'customer_phone_number' => 'required|string',
            'customer_country' => 'required|string',
            'channels' => 'nullable|string'
        ]);

        $user = $request->user();
        $ebook = \App\Models\Ebook::findOrFail($ebookId);

        // Générer un ID de transaction unique
        $transactionId = 'EBOOK_' . time() . '_' . uniqid();

        // Préparer les données pour CinetPay
        $paymentData = [
            'apikey' => $this->apiKey,
            'site_id' => $this->siteId,
            'transaction_id' => $transactionId,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'description' => "Achat de l'ebook {$ebook->title}",
            'channels' => $request->channels ?? 'ALL',
            'customer_name' => $request->customer_name,
            'customer_surname' => $request->customer_surname,
            'customer_email' => $request->customer_email,
            'customer_phone_number' => $request->customer_phone_number,
            'customer_country' => $request->customer_country,
            'notify_url' => $this->notifyUrl
        ];

        // Créer une transaction en attente
        $transaction = $user->transactions()->create([
            'type' => 'ebook_purchase',
            'amount' => $request->amount,
            'description' => "Achat de l'ebook {$ebook->title}",
            'status' => 'pending',
            'meta' => json_encode([
                'ebook_id' => $ebook->id,
                'ebook_title' => $ebook->title,
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

            // Traiter l'achat en fonction du type
            $this->processPurchase($transaction);
            
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

    // Traiter l'achat en fonction du type
    private function processPurchase($transaction)
    {
        $user = $transaction->user;
        $meta = json_decode($transaction->meta, true);

        if ($transaction->type === 'pack_purchase') {
            $packId = $meta['pack_id'];
            $pack = \App\Models\FormationPack::find($packId);

            if ($pack) {
                // Créer la relation utilisateur-pack
                UserPack::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'pack_id' => $pack->id
                    ],
                    [
                        'price_paid' => $transaction->amount,
                        'purchased_at' => now(),
                    ]
                );

                // Traiter la commission pour le parrain si applicable
                $this->processReferralCommission($user, $transaction->amount);
            }
        } elseif ($transaction->type === 'ebook_purchase') {
            $ebookId = $meta['ebook_id'];
            $ebook = \App\Models\Ebook::find($ebookId);

            if ($ebook) {
                // Créer l'entrée d'achat
                \App\Models\UserEbook::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'ebook_id' => $ebook->id
                    ],
                    [
                        'price_paid' => $transaction->amount,
                        'purchased_at' => now(),
                    ]
                );
            }
        }

        // Ne pas incrémenter le solde de l'utilisateur pour les achats, 
        // car l'argent a déjà été débité lors du paiement initial
        // $user->increment('balance', $transaction->amount);
    }

    // Traiter la commission de parrainage
    private function processReferralCommission($user, $purchaseAmount)
    {
        if (!$user->referred_by) {
            return;
        }

        $referrer = \App\Models\User::find($user->referred_by);
        if (!$referrer) {
            return;
        }

        // Commission niveau 1 (1000 FCFA pour premier achat)
        $level1Commission = 1000.0;
        
        // Vérifier si c'est le premier achat de ce filleul
        $isFirstPurchase = $user->transactions()
            ->where('type', 'pack_purchase')
            ->count() === 1;

        if ($isFirstPurchase) {
            $referrer->increment('balance', $level1Commission);
            $referrer->increment('total_commissions', $level1Commission);

            $referrer->transactions()->create([
                'type' => 'commission',
                'amount' => $level1Commission,
                'description' => "Commission niveau 1 - Premier achat de {$user->name}",
                'status' => 'completed',
                'meta' => json_encode([
                    'referral_id' => $user->id,
                    'referral_name' => $user->name,
                    'level' => 1,
                    'purchase_amount' => $purchaseAmount
                ])
            ]);

            // Commission niveau 2 si le parrain a lui-même un parrain
            if ($referrer->referred_by) {
                $level2Referrer = \App\Models\User::find($referrer->referred_by);
                if ($level2Referrer) {
                    $level2Commission = 500.0;
                    
                    $level2Referrer->increment('balance', $level2Commission);
                    $level2Referrer->increment('total_commissions', $level2Commission);

                    $level2Referrer->transactions()->create([
                        'type' => 'commission',
                        'amount' => $level2Commission,
                        'description' => "Commission niveau 2 - Achat de sous-filleul {$user->name}",
                        'status' => 'completed',
                        'meta' => json_encode([
                            'referral_id' => $user->id,
                            'referral_name' => $user->name,
                            'level' => 2,
                            'purchase_amount' => $purchaseAmount
                        ])
                    ]);
                }
            }
        }
    }
}
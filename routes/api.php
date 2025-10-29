<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FormationPackController;
use App\Http\Controllers\Api\FormationController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\AffiliateController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\EbookController;
use App\Http\Controllers\Api\CinetPayController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ChallengeController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\AccountActivationController;
use App\Http\Controllers\Api\PaymentController;

// Routes publiques
Route::prefix('v1')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    });

    // Produits (pour la boutique)
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{id}', [ProductController::class, 'show']);
    });

    // Quiz publics
    Route::prefix('quiz')->group(function () {
        Route::get('available', [QuizController::class, 'available']);
    });



    // Support (publique)
    Route::prefix('support')->group(function () {
        Route::get('info', [SupportController::class, 'index']);
        Route::post('request', [SupportController::class, 'submitRequest']);
    });

    // Routes authentifiées
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
            Route::put('profile', [AuthController::class, 'updateProfile']);
        });

        // Packs de formations
        Route::prefix('packs')->group(function () {
            Route::get('/', [FormationPackController::class, 'index']);
            Route::get('categories', [FormationPackController::class, 'getCategories']);
            Route::get('{id}', [FormationPackController::class, 'show']);
            Route::post('{id}/purchase', [FormationPackController::class, 'purchase']);
            Route::get('{id}/formations', [FormationPackController::class, 'getFormations']);
        });

        // Formations
        Route::prefix('formations')->group(function () {
            Route::get('my-formations', [FormationController::class, 'getUserFormations']); // Mes formations
            Route::get('progress-stats', [FormationController::class, 'getProgressStats']);
            Route::get('certificates', [FormationController::class, 'getCertificates']);
            Route::get('{id}', [FormationController::class, 'show']);
            Route::put('{id}/progress', [FormationController::class, 'updateProgress']);
            Route::put('videos/{videoId}/progress', [FormationController::class, 'updateVideoProgress']);
            Route::post('modules/{id}/complete', [FormationController::class, 'completeModule']);
            Route::get('{id}/certificate', [FormationController::class, 'downloadCertificate']);
            Route::get('{id}/notes', [FormationController::class, 'getNotes']);
            Route::post('{id}/notes', [FormationController::class, 'addNote']);
        });

        // Packs de formations - Cashback
        Route::post('packs/{packId}/cashback', [FormationController::class, 'claimCashback']);

        // Quiz
        Route::prefix('quiz')->group(function () {
            Route::get('free-count', [QuizController::class, 'getFreeCount']);
            Route::post('results', [QuizController::class, 'saveResult']);
            Route::get('history', [QuizController::class, 'getHistory']);
            Route::get('stats', [QuizController::class, 'getStats']);
        });

        // Affiliation
        Route::prefix('affiliate')->group(function () {
            Route::get('dashboard', [AffiliateController::class, 'dashboard']);
            Route::get('list', [AffiliateController::class, 'getAffiliates']);
            Route::get('detailed-stats', [AffiliateController::class, 'getDetailedStats']);
            Route::post('generate-link', [AffiliateController::class, 'generateLink']);
            Route::get('banners', [AffiliateController::class, 'getBanners'])->name('api.affiliate.banners');
            Route::get('banner/{id}/download', [AffiliateController::class, 'downloadBanner'])->name('api.affiliate.banner.download');
            Route::get('commissions', [AffiliateController::class, 'getCommissions']);
        });

        // Portefeuille
        Route::prefix('wallet')->group(function () {
            Route::get('info', [WalletController::class, 'getInfo']);
            Route::post('withdraw', [WalletController::class, 'requestWithdrawal']);
            Route::post('deposit', [WalletController::class, 'deposit']);
            Route::post('transfer', [WalletController::class, 'transfer']);
        });

        // Paiements par wallet
        Route::prefix('payment')->group(function () {
            Route::post('formation-pack', [PaymentController::class, 'purchaseFormationPack']);
            Route::post('ebook', [PaymentController::class, 'purchaseEbook']);
            Route::get('purchases', [PaymentController::class, 'getUserPurchases']);
            Route::post('check-access', [PaymentController::class, 'checkAccess']);
        });

        // Ebooks
        Route::prefix('ebooks')->group(function () {
            Route::get('/', [EbookController::class, 'index']);
            Route::get('/search', [EbookController::class, 'search']);
            Route::get('/categories', [EbookController::class, 'categories']);
            Route::get('{id}', [EbookController::class, 'show']);
            Route::post('{id}/purchase', [EbookController::class, 'purchase']);
            Route::get('{id}/download', [EbookController::class, 'download']);
            Route::get('{id}/view', [EbookController::class, 'view']); // Nouvelle route pour consultation en ligne
        });

        // Challenges
        Route::prefix('challenges')->group(function () {
            Route::get('/', [ChallengeController::class, 'index']);
            Route::get('user', [ChallengeController::class, 'userChallenges']);
            Route::post('{id}/complete', [ChallengeController::class, 'complete']);
            Route::post('{id}/claim', [ChallengeController::class, 'claimReward']);
            Route::post('{id}/progress', [ChallengeController::class, 'updateProgress']);
        });

        // Paramètres de l'application
        Route::get('settings', function() {
            return response()->json([
                'support_email' => \App\Models\AppSetting::get('support_email', 'support@formaneo.com'),
                'support_phone' => \App\Models\AppSetting::get('support_phone', '+33 1 23 45 67 89'),
                'support_whatsapp' => \App\Models\AppSetting::get('support_whatsapp', '+33123456789'),
            ]);
        });

        // CinetPay (dépôts et retraits)
        Route::prefix('cinetpay')->group(function () {
            Route::post('deposit/initiate', [CinetPayController::class, 'initiateDepositPayment']);
            Route::post('withdrawal/initiate', [CinetPayController::class, 'initiateWithdrawal']);
            Route::post('withdrawal/validate', [CinetPayController::class, 'validateWithdrawal']); // Pour l'admin
            Route::post('test', [CinetPayController::class, 'testPayment']);
            Route::get('debug', [CinetPayController::class, 'debugConfig']);
            Route::post('check-withdrawal', [CinetPayController::class, 'checkWithdrawalStatus']);
            Route::get('ping', function () {
                return response()->json(['success' => true, 'message' => 'CinetPay API accessible']);
            });
        });

        // Transferts
        Route::prefix('transfer')->group(function () {
            Route::post('internal', [TransactionController::class, 'transferToUser']);
            Route::post('external', [CinetPayController::class, 'initiateWithdrawal']);
            Route::post('search-user', [TransactionController::class, 'searchUser']);
            Route::get('operators', function() {
                return response()->json([
                    'success' => true,
                    'operators' => [
                        ['code' => 'AUTO', 'name' => 'Détection automatique', 'country' => 'CM'],
                        ['code' => 'MTN', 'name' => 'MTN Mobile Money', 'country' => 'CM'],
                        ['code' => 'MOOV', 'name' => 'Moov Money', 'country' => 'CM'],
                        ['code' => 'WAVECI', 'name' => 'Wave Côte d\'Ivoire', 'country' => 'CI'],
                        ['code' => 'WAVESN', 'name' => 'Wave Sénégal', 'country' => 'SN'],
                    ]
                ]);
            });
        });
        
        // Endpoint pour simuler un paiement réussi (test uniquement)
        Route::post('test-payment-success', function (Request $request) {
            $request->validate([
                'transaction_id' => 'required|string'
            ]);
            
            $transactionId = $request->transaction_id;
            $transaction = \App\Models\Transaction::where('meta', 'LIKE', "%{$transactionId}%")->first();
            
            if (!$transaction) {
                return response()->json(['success' => false, 'message' => 'Transaction non trouvée']);
            }
            
            // Simuler une transaction réussie
            $transaction->update(['status' => 'completed']);
            
            // Traiter selon le type de transaction
            if ($transaction->type === 'deposit') {
                $user = $transaction->user;
                $user->increment('balance', $transaction->amount);
                Log::info('Test Payment Success - Deposit', [
                    'transaction_id' => $transactionId,
                    'user_id' => $user->id,
                    'amount' => $transaction->amount,
                    'new_balance' => $user->fresh()->balance
                ]);
            } elseif ($transaction->type === 'account_activation') {
                $user = $transaction->user;
                
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
                    \App\Models\Transaction::create([
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
                
                Log::info('Test Payment Success - Account Activation', [
                    'transaction_id' => $transactionId,
                    'user_id' => $user->id,
                    'account_status' => 'active',
                    'bonus_given' => true,
                    'new_balance' => $user->fresh()->balance
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Paiement marqué comme réussi',
                'transaction' => $transaction->fresh(),
                'user_status' => $transaction->user->fresh()
            ]);
        });

        // Transactions
        Route::get('transactions', [TransactionController::class, 'index']);
        Route::get('transactions/{id}', [TransactionController::class, 'show']);

        // Panier
        Route::prefix('cart')->group(function () {
            Route::post('add', [CartController::class, 'add']);
            Route::get('/', [CartController::class, 'index']);
            Route::post('update-quantity', [CartController::class, 'updateQuantity']);
            Route::post('remove', [CartController::class, 'remove']);
            Route::post('checkout', [CartController::class, 'checkout']);
        });

        // Mes Achats
        Route::prefix('purchases')->group(function () {
            Route::get('/', [PurchaseController::class, 'index'])->name('purchases.index');
        });

        // Device Token
        Route::post('/device-token', [DeviceController::class, 'storeToken']);

        // Account Activation
        Route::prefix('account')->group(function () {
            Route::get('activation/info', [AccountActivationController::class, 'getActivationInfo']);
            Route::post('activation/initiate', [AccountActivationController::class, 'initiateActivation']);
        });
    });

    // Public Purchases Download Route (with custom token handling)
    Route::get('purchases/{productId}/download', [PurchaseController::class, 'download'])->middleware(\App\Http\Middleware\AuthenticateFromQueryParam::class)->name('purchases.download');

    // Routes publiques CinetPay (notifications)
    Route::prefix('cinetpay')->group(function () {
        Route::post('notify', [CinetPayController::class, 'handleNotification']);
        Route::get('notify', [CinetPayController::class, 'handleNotification']); // Pour test GET
        Route::get('return', [CinetPayController::class, 'handleReturn']); // return_url
        Route::post('return', [CinetPayController::class, 'handleReturn']); // return_url POST
        Route::post('activation/notify', [AccountActivationController::class, 'processActivationPayment']); // Activation notifications
        Route::post('test-check-status', [CinetPayController::class, 'testCheckStatus']); // Test sans auth
        Route::get('debug-transactions', [CinetPayController::class, 'debugTransactions']); // Debug transactions
        Route::post('check-status', [CinetPayController::class, 'checkTransactionStatus']); // Temporairement public
        Route::get('ping', function () {
            return response()->json(['success' => true, 'message' => 'CinetPay API accessible', 'timestamp' => now()]);
        });
        Route::get('debug', [CinetPayController::class, 'debugConfig']);
        Route::post('test-simple', function () {
            $transactionId = 'TEST' . time() . random_int(1000, 9999);
            $data = [
                'apikey' => '45213166268af015b7d2734.50726534',
                'site_id' => '105905750',
                'transaction_id' => $transactionId,
                'amount' => 100,
                'currency' => 'XAF',
                'description' => 'Test paiement Formaneo',
                'channels' => 'ALL',
                'notify_url' => 'http://10.146.233.108:8001/api/v1/cinetpay/notify',
                'return_url' => 'http://10.146.233.108:8001/api/v1/cinetpay/return',
                'customer_name' => 'Test',
                'customer_surname' => 'User',
                'customer_email' => 'test@formaneo.com',
                'customer_phone_number' => '+237658895572',
                'customer_address' => 'Douala Centre',
                'customer_city' => 'Douala',
                'customer_country' => 'CM',
                'customer_state' => 'CM',
                'customer_zip_code' => '00237',
                'lang' => 'fr'
            ];
            
            try {
                $response = Http::timeout(30)->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'Formaneo-App/1.0'
                ])->post('https://api-checkout.cinetpay.com/v2/payment', $data);
                
                return response()->json([
                    'request' => $data,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'request' => $data
                ]);
            }
        });
    });
});
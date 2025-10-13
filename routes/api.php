<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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

    // Packs de formations publics
    Route::get('packs', [FormationPackController::class, 'index']);
    Route::get('packs/{id}', [FormationPackController::class, 'show']);

    // Quiz publics
    Route::prefix('quiz')->group(function () {
        Route::get('available', [QuizController::class, 'available']);
    });

    // Ebooks publics
    Route::prefix('ebooks')->group(function () {
        Route::get('/', [EbookController::class, 'index']);
        Route::get('/{id}', [EbookController::class, 'show']);
        Route::get('/search', [EbookController::class, 'search']);
        Route::get('/categories', [EbookController::class, 'categories']);
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
            Route::get('{id}', [FormationPackController::class, 'show']);
            Route::post('{id}/purchase', [FormationPackController::class, 'purchase']);
            Route::get('{id}/formations', [FormationPackController::class, 'getFormations']);
        });

        // Formations
        Route::prefix('formations')->group(function () {
            Route::get('{id}', [FormationController::class, 'show']);
            Route::put('{id}/progress', [FormationController::class, 'updateProgress']);
            Route::post('modules/{id}/complete', [FormationController::class, 'completeModule']);
            Route::post('{id}/cashback', [FormationController::class, 'claimCashback']);
            Route::get('stats', [FormationController::class, 'getProgressStats']);
            Route::get('{id}/certificate', [FormationController::class, 'downloadCertificate']);
            Route::get('{id}/notes', [FormationController::class, 'getNotes']);
            Route::post('{id}/notes', [FormationController::class, 'addNote']);
        });

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
            Route::get('stats', [AffiliateController::class, 'getDetailedStats']);
            Route::post('generate-link', [AffiliateController::class, 'generateLink']);
            Route::get('banners', [AffiliateController::class, 'getBanners'])->name('api.affiliate.banners');
            Route::get('banners/{id}/download', [AffiliateController::class, 'downloadBanner'])->name('api.affiliate.banner.download');
            Route::get('commissions', [AffiliateController::class, 'getCommissions']);
        });

        // Portefeuille
        Route::prefix('wallet')->group(function () {
            Route::get('info', [WalletController::class, 'getInfo']);
            Route::post('withdraw', [WalletController::class, 'requestWithdrawal']);
            Route::post('deposit', [WalletController::class, 'deposit']);
            Route::post('transfer', [WalletController::class, 'transfer']);
        });

        // Ebooks
        Route::prefix('ebooks')->group(function () {
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

        // CinetPay (uniquement pour les dépôts de fonds)
        Route::prefix('cinetpay')->group(function () {
            Route::post('deposit/initiate', [CinetPayController::class, 'initiateDepositPayment']);
            Route::post('test', [CinetPayController::class, 'testPayment']);
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
    });

    // Routes publiques CinetPay (notifications)
    Route::prefix('cinetpay')->group(function () {
        Route::post('notify', [CinetPayController::class, 'handleNotification']);
        Route::get('notify', [CinetPayController::class, 'handleNotification']); // Pour test GET
    });
});
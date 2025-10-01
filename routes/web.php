<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\FormationPackController;
use App\Http\Controllers\Admin\FormationController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\EbookController;
use App\Http\Controllers\Admin\ProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/admin/login');
});

// Routes d'authentification admin
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AdminAuthController::class, 'login']);
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
        
        // Dashboard principal
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        
        // Gestion des utilisateurs
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}', [UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
            Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{user}/add-balance', [UserController::class, 'addBalance'])->name('add-balance');
        });

        // Gestion des packs de formations
        Route::prefix('formation-packs')->name('formation-packs.')->group(function () {
            Route::get('/', [FormationPackController::class, 'index'])->name('index');
            Route::get('/create', [FormationPackController::class, 'create'])->name('create');
            Route::post('/', [FormationPackController::class, 'store'])->name('store');
            Route::get('/{pack}', [FormationPackController::class, 'show'])->name('show');
            Route::get('/{pack}/edit', [FormationPackController::class, 'edit'])->name('edit');
            Route::put('/{pack}', [FormationPackController::class, 'update'])->name('update');
            Route::delete('/{pack}', [FormationPackController::class, 'destroy'])->name('destroy');
            Route::post('/{pack}/toggle-status', [FormationPackController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{pack}/toggle-featured', [FormationPackController::class, 'toggleFeatured'])->name('toggle-featured');
            Route::post('/{pack}/toggle-promotion', [FormationPackController::class, 'togglePromotion'])->name('toggle-promotion');
        });

        // Gestion des formations
        Route::prefix('formations')->name('formations.')->group(function () {
            Route::get('/', [FormationController::class, 'index'])->name('index');
            Route::get('/create', [FormationController::class, 'create'])->name('create');
            Route::post('/', [FormationController::class, 'store'])->name('store');
            Route::get('/{formation}', [FormationController::class, 'show'])->name('show');
            Route::get('/{formation}/edit', [FormationController::class, 'edit'])->name('edit');
            Route::put('/{formation}', [FormationController::class, 'update'])->name('update');
            Route::delete('/{formation}', [FormationController::class, 'destroy'])->name('destroy');
            Route::post('/{formation}/modules', [FormationController::class, 'storeModule'])->name('modules.store');
            Route::put('/modules/{module}', [FormationController::class, 'updateModule'])->name('modules.update');
            Route::delete('/modules/{module}', [FormationController::class, 'destroyModule'])->name('modules.destroy');
        });

        // Gestion des ebooks
        Route::prefix('ebooks')->name('ebooks.')->group(function () {
            Route::get('/', [EbookController::class, 'index'])->name('index');
            Route::get('/create', [EbookController::class, 'create'])->name('create');
            Route::post('/', [EbookController::class, 'store'])->name('store');
            Route::get('/{ebook}', [EbookController::class, 'show'])->name('show');
            Route::get('/{ebook}/edit', [EbookController::class, 'edit'])->name('edit');
            Route::put('/{ebook}', [EbookController::class, 'update'])->name('update');
            Route::delete('/{ebook}', [EbookController::class, 'destroy'])->name('destroy');
        });

        // Gestion des quiz
        Route::prefix('quizzes')->name('quizzes.')->group(function () {
            Route::get('/', [QuizController::class, 'index'])->name('index');
            Route::get('/create', [QuizController::class, 'create'])->name('create');
            Route::post('/', [QuizController::class, 'store'])->name('store');
            Route::get('/{quiz}', [QuizController::class, 'show'])->name('show');
            Route::get('/{quiz}/edit', [QuizController::class, 'edit'])->name('edit');
            Route::put('/{quiz}', [QuizController::class, 'update'])->name('update');
            Route::delete('/{quiz}', [QuizController::class, 'destroy'])->name('destroy');
            Route::get('/{quiz}/results', [QuizController::class, 'results'])->name('results');
        });

        // Gestion des transactions
        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::get('/', [TransactionController::class, 'index'])->name('index');
            Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');
            Route::post('/{transaction}/approve', [TransactionController::class, 'approve'])->name('approve');
            Route::post('/{transaction}/reject', [TransactionController::class, 'reject'])->name('reject');
            Route::get('/withdrawals/pending', [TransactionController::class, 'pendingWithdrawals'])->name('withdrawals.pending');
        });

        // Paramètres
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::put('/', [SettingsController::class, 'update'])->name('update');
        });

        // Gestion des produits (pour la boutique)
        Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
    });
});
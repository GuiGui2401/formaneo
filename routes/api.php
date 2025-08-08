<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FormationPackController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\AffiliateController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\TransactionController;

// public
Route::post('register',[AuthController::class,'register']);
Route::post('login',[AuthController::class,'login']);
Route::get('formation-packs',[FormationPackController::class,'index']);
Route::get('formation-packs/{slug}',[FormationPackController::class,'show']);
Route::get('quizzes',[QuizController::class,'index']);
Route::get('quizzes/{id}',[QuizController::class,'show']);

// authenticated
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout',[AuthController::class,'logout']);
    Route::get('me',[AuthController::class,'profile']);

    Route::post('packs/{id}/purchase',[FormationPackController::class,'purchase']);

    Route::post('quizzes/{id}/submit',[QuizController::class,'submit']);

    Route::get('affiliate/generate',[AffiliateController::class,'generate']);
    Route::get('affiliate/stats',[AffiliateController::class,'stats']);

    Route::get('wallet/balance',[WalletController::class,'balance']);
    Route::post('wallet/withdraw',[WalletController::class,'requestWithdrawal']);

    Route::get('transactions',[TransactionController::class,'index']);
});

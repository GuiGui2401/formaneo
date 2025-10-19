<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

// Route de test pour bypasser temporairement les timeouts CinetPay
Route::post('/test-direct-transfer', function (Request $request) {
    
    // Configuration directe
    $apiKey = '45213166268af015b7d2734.50726534';
    $password = '12345678';
    
    try {
        // 1. Auth rapide
        $authResponse = Http::timeout(15)->asForm()->post('https://client.cinetpay.com/v1/auth/login', [
            'apikey' => $apiKey,
            'password' => $password
        ]);
        
        if (!$authResponse->successful()) {
            return response()->json(['success' => false, 'message' => 'Auth failed']);
        }
        
        $token = $authResponse->json()['data']['token'];
        
        // 2. Test de transfert immédiat (sans vérifications)
        $transferData = [
            [
                'prefix' => '237',
                'phone' => '658895572', // Numéro de test
                'amount' => 500,
                'client_transaction_id' => 'QUICK_TEST_' . time(),
                'notify_url' => 'http://192.168.1.136:8001/api/v1/cinetpay/notify/transfer'
                // Pas de payment_method = auto-détection
            ]
        ];
        
        // Timeout court pour test rapide
        $transferResponse = Http::timeout(30)->asForm()->post(
            "https://client.cinetpay.com/v1/transfer/money/send/contact?token={$token}&lang=fr",
            ['data' => json_encode($transferData)]
        );
        
        return response()->json([
            'success' => $transferResponse->successful(),
            'status_code' => $transferResponse->status(),
            'response' => $transferResponse->json(),
            'elapsed_time' => '< 30s'
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'is_timeout' => str_contains($e->getMessage(), 'timed out')
        ]);
    }
});
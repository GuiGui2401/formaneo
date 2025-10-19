<?php

echo "=== TEST AUTO-DÉTECTION OPÉRATEUR ===\n";
echo "Test avec opérateur = null (auto-détection)\n";
echo "Montant: 500 FCFA\n";
echo "Numéro: 658895572\n\n";

// Configuration
$apiKey = '45213166268af015b7d2734.50726534';
$transferPassword = '12345678';
$transferApiUrl = 'https://client.cinetpay.com/v1';

try {
    // 1. Authentification
    echo "1. Authentification...\n";
    $ch = curl_init($transferApiUrl . '/auth/login');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'apikey' => $apiKey,
        'password' => $transferPassword
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    $authResponse = curl_exec($ch);
    curl_close($ch);
    
    $authData = json_decode($authResponse, true);
    $token = $authData['data']['token'];
    echo "   ✅ Token obtenu\n\n";
    
    // 2. Test transfert SANS payment_method (auto-détection)
    echo "2. Test transfert avec auto-détection...\n";
    
    $transferData = json_encode([[
        'prefix' => '237',
        'phone' => '658895572',
        'amount' => 500,
        'client_transaction_id' => 'AUTO_TEST_' . time(),
        'notify_url' => 'http://192.168.1.136:8001/api/v1/cinetpay/notify/transfer'
        // PAS de payment_method = auto-détection
    ]]);
    
    echo "   Données envoyées: " . $transferData . "\n";
    
    $ch = curl_init($transferApiUrl . '/transfer/money/send/contact?token=' . $token . '&lang=fr');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['data' => $transferData]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    $transferResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   Code HTTP: $httpCode\n";
    
    $transferData = json_decode($transferResponse, true);
    
    if (isset($transferData['code']) && $transferData['code'] == 0) {
        echo "   ✅ TRANSFERT RÉUSSI avec auto-détection!\n";
        if (isset($transferData['data'][0]['transaction_id'])) {
            echo "   Transaction ID: " . $transferData['data'][0]['transaction_id'] . "\n";
        }
    } else {
        echo "   ❌ Échec: " . $transferResponse . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== FIN TEST AUTO-DÉTECTION ===\n";
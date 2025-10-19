<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

echo "=== TEST TRANSFERT CINETPAY ===\n";
echo "Montant: 500 FCFA (nouveau minimum)\n";
echo "Numéro: 658895572\n";
echo "Opérateur: Auto-détection\n\n";

// Configuration
$apiKey = '45213166268af015b7d2734.50726534';
$transferPassword = '12345678';
$transferApiUrl = 'https://client.cinetpay.com/v1';

try {
    // 1. Authentification
    echo "1. Authentification CinetPay...\n";
    $authUrl = $transferApiUrl . '/auth/login';
    
    $ch = curl_init($authUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'apikey' => $apiKey,
        'password' => $transferPassword
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $authResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   Response HTTP: $httpCode\n";
    
    $authData = json_decode($authResponse, true);
    
    if (!isset($authData['data']['token'])) {
        echo "   ❌ Échec de l'authentification\n";
        echo "   Response: " . $authResponse . "\n";
        exit(1);
    }
    
    $token = $authData['data']['token'];
    echo "   ✅ Token obtenu: " . substr($token, 0, 50) . "...\n\n";
    
    // 2. Vérifier le solde
    echo "2. Vérification du solde...\n";
    $balanceUrl = $transferApiUrl . '/transfer/check/balance?token=' . $token . '&lang=fr';
    
    $ch = curl_init($balanceUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $balanceResponse = curl_exec($ch);
    curl_close($ch);
    
    $balanceData = json_decode($balanceResponse, true);
    
    if (isset($balanceData['data']['available'])) {
        $available = $balanceData['data']['available'];
        echo "   ✅ Solde disponible: $available FCFA\n";
        
        if ($available < 300) {
            echo "   ⚠️  Solde insuffisant pour effectuer le transfert\n";
        }
    }
    echo "\n";
    
    // 3. Créer le contact
    echo "3. Création/vérification du contact...\n";
    $contactUrl = $transferApiUrl . '/transfer/contact?token=' . $token . '&lang=fr';
    
    $contactData = json_encode([[
        'prefix' => '237',
        'phone' => '658895572',
        'name' => 'Test',
        'surname' => 'User',
        'email' => 'test@example.com'
    ]]);
    
    $ch = curl_init($contactUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['data' => $contactData]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $contactResponse = curl_exec($ch);
    curl_close($ch);
    
    $contactResponseData = json_decode($contactResponse, true);
    
    if (isset($contactResponseData['code']) && $contactResponseData['code'] == 0) {
        echo "   ✅ Contact créé/vérifié\n";
    } else {
        echo "   ⚠️  Response: " . $contactResponse . "\n";
    }
    echo "\n";
    
    // 4. Effectuer le transfert
    echo "4. Initiation du transfert...\n";
    echo "   ⏳ Cela peut prendre jusqu'à 60 secondes...\n";
    
    $transferUrl = $transferApiUrl . '/transfer/money/send/contact?token=' . $token . '&lang=fr';
    
    $transferData = json_encode([[
        'prefix' => '237',
        'phone' => '658895572',
        'amount' => 500,
        'client_transaction_id' => 'TEST_' . time(),
        'notify_url' => 'http://192.168.1.136:8001/api/v1/cinetpay/notify/transfer'
        // On retire payment_method pour laisser CinetPay détecter automatiquement
    ]]);
    
    $ch = curl_init($transferUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['data' => $transferData]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 90); // 90 secondes de timeout
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $startTime = time();
    $transferResponse = curl_exec($ch);
    $elapsedTime = time() - $startTime;
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    echo "   Temps écoulé: {$elapsedTime} secondes\n";
    echo "   Code HTTP: $httpCode\n";
    
    if ($curlError) {
        echo "   ❌ Erreur cURL: $curlError\n";
        
        if (strpos($curlError, 'Operation timed out') !== false) {
            echo "\n⚠️  TIMEOUT DÉTECTÉ - L'API CinetPay met trop de temps à répondre\n";
            echo "Le problème vient de l'API CinetPay, pas de votre code.\n";
            echo "Solutions:\n";
            echo "1. Réessayer plus tard\n";
            echo "2. Contacter le support CinetPay\n";
            echo "3. Utiliser le mode asynchrone (queue)\n";
        }
    } else {
        $transferResponseData = json_decode($transferResponse, true);
        
        if (isset($transferResponseData['code']) && $transferResponseData['code'] == 0) {
            echo "   ✅ TRANSFERT RÉUSSI!\n";
            
            if (isset($transferResponseData['data'][0])) {
                $transfer = $transferResponseData['data'][0];
                if (isset($transfer['transaction_id'])) {
                    echo "   Transaction ID: " . $transfer['transaction_id'] . "\n";
                }
                if (isset($transfer['status'])) {
                    echo "   Status: " . $transfer['status'] . "\n";
                }
            }
        } else {
            echo "   ❌ Échec du transfert\n";
            echo "   Response: " . $transferResponse . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "\n❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DU TEST ===\n";
<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔧 Script de correction des soldes wallet\n";
echo "=========================================\n\n";

// Récupérer tous les utilisateurs avec des transactions
$users = User::whereHas('transactions')->get();

echo "👥 Utilisateurs trouvés: " . $users->count() . "\n\n";

foreach ($users as $user) {
    echo "📧 Utilisateur: {$user->email} (ID: {$user->id})\n";
    echo "   Solde actuel: {$user->balance} FCFA\n";
    
    // Calculer le solde correct basé sur les transactions
    $transactions = $user->transactions()->where('status', 'completed')->get();
    
    $calculatedBalance = 0;
    $details = [];
    
    foreach ($transactions as $transaction) {
        $amount = $transaction->amount;
        $calculatedBalance += $amount;
        
        $details[] = [
            'type' => $transaction->type,
            'amount' => $amount,
            'description' => $transaction->description,
            'date' => $transaction->created_at->format('d/m/Y H:i')
        ];
    }
    
    echo "   Transactions analysées:\n";
    foreach ($details as $detail) {
        $sign = $detail['amount'] >= 0 ? '+' : '';
        echo "     - {$detail['type']}: {$sign}{$detail['amount']} FCFA ({$detail['description']}) - {$detail['date']}\n";
    }
    
    echo "   Solde calculé: {$calculatedBalance} FCFA\n";
    
    if ($user->balance != $calculatedBalance) {
        echo "   ⚠️  INCOHÉRENCE DÉTECTÉE!\n";
        echo "   🔄 Correction en cours...\n";
        
        // Mettre à jour le solde
        $user->update(['balance' => $calculatedBalance]);
        
        echo "   ✅ Solde corrigé: {$user->balance} → {$calculatedBalance} FCFA\n";
    } else {
        echo "   ✅ Solde correct\n";
    }
    
    echo "\n" . str_repeat('-', 50) . "\n\n";
}

echo "🎉 Script terminé!\n";
echo "Tous les soldes ont été vérifiés et corrigés si nécessaire.\n";
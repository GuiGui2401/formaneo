<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Transaction;

class FixWalletBalances extends Command
{
    protected $signature = 'wallet:fix-balances {--user-id= : Fix specific user ID}';
    protected $description = 'Fix wallet balances based on transaction history';

    public function handle()
    {
        $this->info('🔧 Script de correction des soldes wallet');
        $this->info('=========================================');
        $this->newLine();

        $userId = $this->option('user-id');
        
        if ($userId) {
            $users = User::where('id', $userId)->get();
            if ($users->isEmpty()) {
                $this->error("Utilisateur avec ID {$userId} non trouvé");
                return 1;
            }
        } else {
            $users = User::whereHas('transactions')->get();
        }

        $this->info("👥 Utilisateurs trouvés: " . $users->count());
        $this->newLine();

        $correctedCount = 0;

        foreach ($users as $user) {
            $this->info("📧 Utilisateur: {$user->email} (ID: {$user->id})");
            $this->info("   Solde actuel: {$user->balance} FCFA");
            
            // Calculer le solde correct basé sur les transactions
            $transactions = $user->transactions()->where('status', 'completed')->get();
            
            $calculatedBalance = 0;
            
            $this->info("   Transactions analysées:");
            foreach ($transactions as $transaction) {
                $amount = $transaction->amount;
                $calculatedBalance += $amount;
                
                $sign = $amount >= 0 ? '+' : '';
                $this->info("     - {$transaction->type}: {$sign}{$amount} FCFA ({$transaction->description}) - {$transaction->created_at->format('d/m/Y H:i')}");
            }
            
            $this->info("   Solde calculé: {$calculatedBalance} FCFA");
            
            if ($user->balance != $calculatedBalance) {
                $this->warn("   ⚠️  INCOHÉRENCE DÉTECTÉE!");
                $this->info("   🔄 Correction en cours...");
                
                // Mettre à jour le solde
                $user->update(['balance' => $calculatedBalance]);
                
                $this->info("   ✅ Solde corrigé: {$user->balance} → {$calculatedBalance} FCFA");
                $correctedCount++;
            } else {
                $this->info("   ✅ Solde correct");
            }
            
            $this->info(str_repeat('-', 50));
            $this->newLine();
        }

        $this->info("🎉 Script terminé!");
        $this->info("Comptes corrigés: {$correctedCount}/{$users->count()}");
        
        return 0;
    }
}
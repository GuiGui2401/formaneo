<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ExpireAccountsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Find all active accounts that have expired
        $expiredUsers = User::where('account_status', 'active')
                           ->where('account_expires_at', '<=', now())
                           ->get();

        $expiredCount = 0;

        foreach ($expiredUsers as $user) {
            // Skip mobile users - they don't have expiration
            if ($user->account_activated_at === null) {
                continue; // This is likely a mobile user with default active status
            }

            $user->expireAccount();
            $expiredCount++;

            Log::info('Account expired', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'expired_at' => $user->account_expires_at,
                'expired_by_job' => true
            ]);
        }

        Log::info('ExpireAccountsJob completed', [
            'expired_count' => $expiredCount,
            'total_checked' => $expiredUsers->count()
        ]);
    }
}

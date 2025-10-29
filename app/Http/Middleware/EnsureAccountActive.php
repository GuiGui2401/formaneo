<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Skip middleware for mobile users (keep existing functionality)
        if ($request->header('X-Platform') === 'mobile') {
            return $next($request);
        }

        // Check if account has expired and update status if needed
        if ($user->hasAccountExpired()) {
            $user->expireAccount();
        }

        // Check if account is active
        if (!$user->isAccountActive()) {
            return response()->json([
                'error' => 'Account not active',
                'message' => 'Votre compte doit être activé pour accéder à cette fonctionnalité.',
                'account_status' => $user->account_status,
                'activation_required' => true
            ], 403);
        }

        return $next($request);
    }
}

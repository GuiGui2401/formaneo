<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthenticateFromQueryParam
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('token')) {
            $token = PersonalAccessToken::findToken($request->query('token'));

            if ($token && $token->tokenable instanceof User) {
                Auth::login($token->tokenable);
            }
        }

        // If not authenticated by query param, try default Sanctum authentication
        // This is important if the route is also protected by 'auth:sanctum'
        // or if other authentication methods are expected.
        if (!Auth::check()) {
            // If still not authenticated, return unauthorized response
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            // Si l'utilisateur essaie d'accéder à l'admin, rediriger vers admin.login
            if ($request->is('admin/*') || $request->is('admin')) {
                return route('admin.login');
            }
            
            // Pour les autres routes, rediriger vers la route login générale
            return route('admin.login');
        }
        
        return null;
    }
}
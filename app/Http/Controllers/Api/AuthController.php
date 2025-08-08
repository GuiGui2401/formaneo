<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    // Register
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'promo_code' => 'nullable|string|max:10'
        ]);

        $promo = strtoupper(Str::random(5));
        while (User::where('promo_code', $promo)->exists()) {
            $promo = strtoupper(Str::random(5));
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'promo_code' => $promo,
            'affiliate_link' => config('app.url') . "/invite/{$promo}",
            'balance' => 1000.0, // Bonus de bienvenue
            'free_quizzes_left' => 5, // Quiz gratuits
        ]);

        // Si code promo fourni, traiter le parrainage
        if ($request->promo_code) {
            $referrer = User::where('promo_code', $request->promo_code)->first();
            if ($referrer) {
                $user->update(['referred_by' => $referrer->id]);
                $referrer->increment('total_affiliates');
                $referrer->increment('monthly_affiliates');
            }
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => $user->makeHidden(['password']),
            'token' => $token
        ], 201);
    }

    // Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiants invalides'
            ], 401);
        }

        $user->update(['last_login_at' => now()]);
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => $user->makeHidden(['password']),
            'token' => $token
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Déconnecté avec succès'
        ]);
    }

    // Get current user
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user()->makeHidden(['password'])
        ]);
    }

    // Update profile
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user = $request->user();
        $user->update($request->only(['name', 'email', 'phone']));

        return response()->json([
            'success' => true,
            'user' => $user->makeHidden(['password']),
            'message' => 'Profil mis à jour avec succès'
        ]);
    }

    // Forgot password
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return response()->json([
            'success' => $status === Password::RESET_LINK_SENT,
            'message' => $status === Password::RESET_LINK_SENT 
                ? 'Email de réinitialisation envoyé' 
                : 'Impossible d\'envoyer l\'email'
        ]);
    }
}
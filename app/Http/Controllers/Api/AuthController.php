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
use App\Models\AdminNotification;
use App\Models\Settings;

class AuthController extends Controller
{
    // Register
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'promo_code' => 'nullable|string|exists:users,promo_code'
        ]);

        $promo = strtoupper(Str::random(5));
        while (User::where('promo_code', $promo)->exists()) {
            $promo = strtoupper(Str::random(5));
        }

        // Determine account status based on platform
        $accountStatus = 'inactive'; // Default for web
        $balance = 0; // No welcome bonus until activation
        
        // Check if request is from mobile
        if ($request->header('X-Platform') === 'mobile') {
            $accountStatus = 'active';
            $balance = 2000.0; // Mobile users get immediate welcome bonus
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'promo_code' => $promo,
            'affiliate_link' => config('app.url') . "/invite/{$promo}",
            'balance' => $balance,
            'free_quizzes_left' => 5, // Quiz gratuits
            'account_status' => $accountStatus,
            'welcome_bonus_claimed' => $accountStatus === 'active',
        ]);

        // Create welcome bonus transaction only for mobile or active accounts
        if ($accountStatus === 'active') {
            $user->transactions()->create([
                'type' => 'bonus',
                'amount' => 2000.0,
                'description' => 'Bonus de bienvenue',
                'status' => 'completed',
                'meta' => json_encode([
                    'bonus_type' => 'welcome_bonus',
                    'user_id' => $user->id
                ])
            ]);
        }

        // Si code promo fourni, lier l'utilisateur au parrain
        if ($request->promo_code) {
            $referrer = User::where('promo_code', $request->promo_code)->first();
            if ($referrer) {
                // Lier le nouvel utilisateur au parrain
                $user->update(['referred_by' => $referrer->id]);
                
                // Les commissions seront données lors de l'activation du compte, pas à l'inscription
                // Mettre à jour seulement les statistiques du parrain
                $referrer->increment('total_affiliates');
                $referrer->increment('monthly_affiliates');
            }
        }

        $token = $user->createToken('api-token')->plainTextToken;

        // Create admin notification for web registrations only
        if ($accountStatus === 'inactive') {
            AdminNotification::createNewUserNotification($user);
        }

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
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Register
    public function register(Request $request)
    {
        $request->validate([
            'name'=>'required|string|max:255',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|string|min:6|confirmed'
        ]);

        $promo = strtoupper(Str::random(6));

        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'promo_code'=>$promo,
            'affiliate_link'=>config('app.url')."/invite/{$promo}",
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'=>$user,
            'token'=>$token
        ], 201);
    }

    // Login
    public function login(Request $request)
    {
        $request->validate(['email'=>'required|email','password'=>'required|string']);
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['email'=>['Identifiants invalides']]);
        }

        $user->last_login_at = now();
        $user->save();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['user'=>$user,'token'=>$token]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message'=>'Déconnecté']);
    }

    // Profile
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }
}

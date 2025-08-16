<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('promo_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('premium')) {
            $query->where('is_premium', $request->premium === 'yes');
        }

        $users = $query->withCount(['transactions', 'referrals'])
            ->latest()
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'balance' => 'nullable|numeric|min:0',
            'is_premium' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $promo = $this->generateUniquePromoCode();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'balance' => $request->balance ?? 0,
            'promo_code' => $promo,
            'affiliate_link' => config('app.url') . "/invite/{$promo}",
            'is_premium' => $request->boolean('is_premium'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    public function show(User $user)
    {
        $user->load(['transactions' => function ($query) {
            $query->latest()->limit(20);
        }, 'referrals']);

        $stats = [
            'total_earnings' => $user->transactions()
                ->whereIn('type', ['commission', 'bonus', 'cashback', 'quiz_reward'])
                ->sum('amount'),
            'total_spent' => abs($user->transactions()
                ->where('type', 'purchase')
                ->sum('amount')),
            'total_referrals' => $user->referrals()->count(),
            'active_referrals' => $user->referrals()
                ->where('last_login_at', '>', now()->subDays(30))
                ->count(),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user)],
            'phone' => 'nullable|string|max:20',
            'balance' => 'nullable|numeric|min:0',
            'is_premium' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'balance' => $request->balance ?? $user->balance,
            'is_premium' => $request->boolean('is_premium'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Utilisateur {$status} avec succès.");
    }

    public function addBalance(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
        ]);

        $user->increment('balance', $request->amount);

        // Créer une transaction
        $user->transactions()->create([
            'type' => 'admin_credit',
            'amount' => $request->amount,
            'description' => $request->description,
            'status' => 'completed',
            'meta' => json_encode(['admin_id' => auth('admin')->id()]),
        ]);

        return back()->with('success', 'Solde ajouté avec succès.');
    }

    private function generateUniquePromoCode()
    {
        do {
            $promo = strtoupper(\Illuminate\Support\Str::random(5));
        } while (User::where('promo_code', $promo)->exists());

        return $promo;
    }
}
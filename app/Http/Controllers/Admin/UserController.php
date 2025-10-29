<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

    public function updateFakeAffiliateData(Request $request, User $user)
    {
        $request->validate([
            'fake_affiliate_data_enabled' => 'required|boolean',
            'fake_affiliate_data' => 'nullable|array',
            'fake_affiliate_data.top_performers' => 'nullable|array',
            'fake_affiliate_data.chart_data' => 'nullable|array',
        ]);

        $fakeData = null;
        if ($request->fake_affiliate_data_enabled && $request->fake_affiliate_data) {
            $fakeData = $request->fake_affiliate_data;
        }

        $user->update([
            'fake_affiliate_data_enabled' => $request->fake_affiliate_data_enabled,
            'fake_affiliate_data' => $fakeData,
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Configuration des données d\'affiliation mise à jour avec succès.'
            ]);
        }
        
        return back()->with('success', 'Configuration des données d\'affiliation mise à jour avec succès.');
    }

    public function generateDemoData(User $user)
    {
        // Générer des données de démonstration réalistes
        $demoData = [
            'top_performers' => [
                [
                    'name' => 'Sarah Johnson',
                    'total_commission' => 15800
                ],
                [
                    'name' => 'Michael Chen',
                    'total_commission' => 12400
                ],
                [
                    'name' => 'Emma Martinez',
                    'total_commission' => 9650
                ]
            ],
            'chart_data' => [
                'labels' => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                'datasets' => [
                    [
                        'label' => 'Commissions',
                        'data' => [2400, 1800, 3200, 2900, 4100, 3500, 2800]
                    ],
                    [
                        'label' => 'Inscriptions',
                        'data' => [12, 8, 16, 14, 20, 18, 13]
                    ]
                ]
            ]
        ];

        $user->update([
            'fake_affiliate_data_enabled' => true,
            'fake_affiliate_data' => $demoData,
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Données de démonstration générées avec succès.'
            ]);
        }
        
        return back()->with('success', 'Données de démonstration générées avec succès.');
    }

    public function pending(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        
        // Récupérer tous les utilisateurs avec statut inactif ou expiré
        $pendingUsers = User::whereIn('account_status', ['inactive', 'expired'])
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage);
        
        $totalPending = User::where('account_status', 'inactive')->count();

        // Retourner la vue même s'il n'y a pas d'utilisateurs en attente
        return view('admin.users.pending', compact('pendingUsers', 'totalPending'));
    }

    public function activate(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        if ($user->isAccountActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Le compte est déjà actif'
            ], 400);
        }

        // Manually activate account
        $user->activateAccount();
        
        // Give welcome bonus if not already claimed
        if (!$user->welcome_bonus_claimed) {
            $user->update([
                'balance' => $user->balance + 2000,
                'welcome_bonus_claimed' => true
            ]);
            
            // Create welcome bonus transaction
            $user->transactions()->create([
                'type' => 'bonus',
                'amount' => 2000,
                'description' => 'Bonus de bienvenue - Activation manuelle par admin',
                'status' => 'completed',
                'meta' => json_encode([
                    'bonus_type' => 'welcome_bonus_manual',
                    'activated_by' => 'admin'
                ])
            ]);
        }
        
        // Distribuer les commissions d'affiliation
        $this->distributeAffiliateCommissions($user);

        return response()->json([
            'success' => true,
            'message' => 'Compte activé avec succès',
            'user' => $user->fresh()
        ]);
    }

    public function deactivate(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        $user->deactivateAccount();

        return response()->json([
            'success' => true,
            'message' => 'Compte désactivé avec succès',
            'user' => $user->fresh()
        ]);
    }

    private function generateUniquePromoCode()
    {
        do {
            $promo = strtoupper(\Illuminate\Support\Str::random(5));
        } while (User::where('promo_code', $promo)->exists());

        return $promo;
    }

    /**
     * Distribuer les commissions d'affiliation lors de l'activation du compte
     */
    private function distributeAffiliateCommissions($user)
    {
        // Vérifier si l'utilisateur a été référé
        if (!$user->referred_by) {
            Log::info("Pas de parrain trouvé pour l'utilisateur {$user->id} lors de l'activation admin");
            return;
        }

        // Récupérer les montants de commission depuis les settings
        $level1Commission = Settings::getValue('level1_commission', 1000);
        $level2Commission = Settings::getValue('level2_commission', 500);

        // Niveau 1 : Commission pour le parrain direct
        $directReferrer = User::find($user->referred_by);
        if ($directReferrer) {
            $this->giveCommission($directReferrer, $level1Commission, 1, $user);
            Log::info("Commission niveau 1 de {$level1Commission} FCFA donnée à l'utilisateur {$directReferrer->id} pour l'activation admin de {$user->id}");

            // Niveau 2 : Commission pour le parrain du parrain
            if ($directReferrer->referred_by) {
                $indirectReferrer = User::find($directReferrer->referred_by);
                if ($indirectReferrer) {
                    $this->giveCommission($indirectReferrer, $level2Commission, 2, $user);
                    Log::info("Commission niveau 2 de {$level2Commission} FCFA donnée à l'utilisateur {$indirectReferrer->id} pour l'activation admin de {$user->id}");
                }
            }
        }
    }

    /**
     * Donner une commission à un utilisateur
     */
    private function giveCommission(User $referrer, float $amount, int $level, User $activatedUser)
    {
        // Ajouter la commission au solde du parrain
        $referrer->balance += $amount;
        $referrer->total_commissions += $amount;
        $referrer->save();

        // Créer une transaction de commission
        $referrer->transactions()->create([
            'type' => 'affiliate_commission',
            'amount' => $amount,
            'description' => "Commission niveau {$level} - Activation manuelle admin de {$activatedUser->name}",
            'status' => 'completed',
            'meta' => json_encode([
                'commission_level' => $level,
                'activated_user_id' => $activatedUser->id,
                'activated_user_name' => $activatedUser->name,
                'source_type' => 'account_activation_admin',
                'source_id' => $activatedUser->id,
                'activated_by' => 'admin'
            ])
        ]);
    }
}
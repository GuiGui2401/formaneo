<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AffiliateController extends Controller
{
    // Dashboard principal d'affiliation
    public function dashboard(Request $request)
    {
        $user = $request->user();
        
        // Calcul des gains
        $todayEarnings = $user->transactions()
            ->where('type', 'affiliate_commission')
            ->whereDate('created_at', today())
            ->sum('amount');
            
        $yesterdayEarnings = $user->transactions()
            ->where('type', 'affiliate_commission')
            ->whereDate('created_at', today()->subDay())
            ->sum('amount');
            
        $currentMonthEarnings = $user->transactions()
            ->where('type', 'affiliate_commission')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
            
        $lastMonthEarnings = $user->transactions()
            ->where('type', 'affiliate_commission')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('amount');
            
        $totalEarnings = $user->transactions()
            ->where('type', 'affiliate_commission')
            ->sum('amount');

        // Stats des affiliés
        $activeAffiliates = User::where('referred_by', $user->id)
            ->where('last_login_at', '>', now()->subDays(30))
            ->count();

        // Liste récente des affiliés
        $recentAffiliates = User::where('referred_by', $user->id)
            ->latest()
            ->take(10)
            ->get(['name', 'created_at'])
            ->map(function ($affiliate) {
                return [
                    'name' => $affiliate->name,
                    'joined_date' => $affiliate->created_at->format('Y-m-d'),
                    'status' => 'active',
                    'commission_earned' => rand(1000, 5000), // À calculer réellement
                ];
            });

        return response()->json([
            'earnings' => [
                'today' => $todayEarnings,
                'yesterday' => $yesterdayEarnings,
                'current_month' => $currentMonthEarnings,
                'last_month' => $lastMonthEarnings,
                'total' => $totalEarnings,
            ],
            'stats' => [
                'total_affiliates' => $user->total_affiliates ?? 0,
                'monthly_affiliates' => $user->monthly_affiliates ?? 0,
                'active_affiliates' => $activeAffiliates,
            ],
            'affiliate_link' => $user->affiliate_link,
            'promo_code' => $user->promo_code,
            'affiliates_list' => $recentAffiliates,
        ]);
    }

    // Liste des affiliés avec pagination
    public function getAffiliates(Request $request)
    {
        $user = $request->user();
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);

        $affiliates = User::where('referred_by', $user->id)
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        $affiliatesList = $affiliates->items();
        $formattedAffiliates = collect($affiliatesList)->map(function ($affiliate) {
            return [
                'id' => $affiliate->id,
                'name' => $affiliate->name,
                'email' => $affiliate->email,
                'joined_date' => $affiliate->created_at->format('Y-m-d'),
                'status' => $affiliate->last_login_at > now()->subDays(30) ? 'active' : 'inactive',
                'commission_earned' => $affiliate->transactions()
                    ->where('type', 'commission')
                    ->sum('amount'),
            ];
        });

        return response()->json([
            'affiliates' => $formattedAffiliates,
            'pagination' => [
                'current_page' => $affiliates->currentPage(),
                'last_page' => $affiliates->lastPage(),
                'per_page' => $affiliates->perPage(),
                'total' => $affiliates->total(),
            ]
        ]);
    }

    // Statistiques détaillées
    public function getDetailedStats(Request $request)
    {
        $user = $request->user();
        $period = $request->get('period', 'weekly');

        // --- Top Performers (Top 3 Affiliates by Commission Generated for the Current User) ---
        $topAffiliates = DB::table('users as affiliates')
            ->join('transactions', function($join) {
                $join->on('affiliates.id', '=', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(transactions.meta, '$.referred_user_id'))"));
            })
            ->where('transactions.user_id', $user->id)
            ->where('transactions.type', 'affiliate_commission')
            ->whereBetween('transactions.created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->select('affiliates.name', DB::raw('SUM(transactions.amount) as total_commission'))
            ->groupBy('affiliates.id', 'affiliates.name')
            ->orderByDesc('total_commission')
            ->take(3)
            ->get();

        // --- Chart Data (for the last 7 days) ---
        $labels = [];
        $commissionsData = [];
        $signupsData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('D'); // e.g., "Mon"

            $commissionsData[] = Transaction::where('user_id', $user->id)
                ->where('type', 'affiliate_commission')
                ->whereDate('created_at', $date)
                ->sum('amount');

            $signupsData[] = User::where('referred_by', $user->id)
                ->whereDate('created_at', $date)
                ->count();
        }

        return response()->json([
            'top_performers' => $topAffiliates,
            'chart_data' => [
                'labels' => $labels, // ['Mon', 'Tue', ...]
                'datasets' => [
                    ['label' => 'Commissions', 'data' => $commissionsData],
                    ['label' => 'Inscriptions', 'data' => $signupsData],
                ]
            ]
        ]);
    }

    // Générer un nouveau lien d'affiliation
    public function generateLink(Request $request)
    {
        $user = $request->user();
        $campaign = $request->get('campaign');
        
        $link = $user->affiliate_link;
        if ($campaign) {
            $link .= "?campaign=" . urlencode($campaign);
        }

        return response()->json([
            'link' => $link
        ]);
    }

    // Bannières promotionnelles
    public function getBanners(Request $request)
    {
        return response()->json([
            'banners' => [
                [
                    'id' => '1',
                    'name' => 'Bannière Instagram',
                    'size' => '1080x1080',
                    'format' => 'jpg',
                    'url' => config('app.url') . '/banners/instagram.jpg',
                ],
                [
                    'id' => '2',
                    'name' => 'Bannière Facebook',
                    'size' => '1200x630',
                    'format' => 'jpg',
                    'url' => config('app.url') . '/banners/facebook.jpg',
                ],
                [
                    'id' => '3',
                    'name' => 'Bannière Twitter',
                    'size' => '1024x512',
                    'format' => 'jpg',
                    'url' => config('app.url') . '/banners/twitter.jpg',
                ],
                [
                    'id' => '4',
                    'name' => 'Story Instagram/Facebook',
                    'size' => '1080x1920',
                    'format' => 'jpg',
                    'url' => config('app.url') . '/banners/story.jpg',
                ],
            ]
        ]);
    }

    // Télécharger une bannière
    public function downloadBanner(Request $request, $id)
    {
        $banners = [
            '1' => config('app.url') . '/banners/instagram.jpg',
            '2' => config('app.url') . '/banners/facebook.jpg',
            '3' => config('app.url') . '/banners/twitter.jpg',
            '4' => config('app.url') . '/banners/story.jpg',
        ];

        $downloadUrl = $banners[$id] ?? null;

        return response()->json([
            'download_url' => $downloadUrl
        ]);
    }

    // Historique des commissions
    public function getCommissions(Request $request)
    {
        $user = $request->user();
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);

        $commissions = $user->transactions()
            ->where('type', 'commission')
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        $commissionsList = $commissions->items();
        $formattedCommissions = collect($commissionsList)->map(function ($commission) {
            $meta = json_decode($commission->meta, true);
            
            return [
                'id' => $commission->id,
                'amount' => $commission->amount,
                'description' => $commission->description ?? 'Commission d\'affiliation',
                'date' => $commission->created_at->format('Y-m-d H:i:s'),
                'status' => 'completed',
                'type' => $meta['type'] ?? 'level1',
            ];
        });

        return response()->json([
            'commissions' => $formattedCommissions,
            'pagination' => [
                'current_page' => $commissions->currentPage(),
                'last_page' => $commissions->lastPage(),
                'per_page' => $commissions->perPage(),
                'total' => $commissions->total(),
            ]
        ]);
    }
}
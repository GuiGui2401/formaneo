<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class AffiliateController extends Controller
{
    // Dashboard principal d'affiliation
    public function dashboard(Request $request)
    {
        $user = $request->user();
        
        $todayEarnings = $user->transactions()->where('type', 'affiliate_commission')->whereDate('created_at', today())->sum('amount');
        $yesterdayEarnings = $user->transactions()->where('type', 'affiliate_commission')->whereDate('created_at', today()->subDay())->sum('amount');
        $currentMonthEarnings = $user->transactions()->where('type', 'affiliate_commission')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount');
        $lastMonthEarnings = $user->transactions()->where('type', 'affiliate_commission')->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->sum('amount');
        $totalEarnings = $user->transactions()->where('type', 'affiliate_commission')->sum('amount');

        $activeAffiliates = User::where('referred_by', $user->id)->where('last_login_at', '>', now()->subDays(30))->count();

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
        ]);
    }

    // Statistiques détaillées pour les graphiques et top affiliés
    public function getDetailedStats(Request $request)
    {
        $user = $request->user();
        
        // --- Top Performers (Compatible with SQLite) ---
        $topAffiliatesData = DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('type', 'affiliate_commission')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->select(
                DB::raw("json_extract(meta, '$.referred_user_id') as referred_id"),
                DB::raw('SUM(amount) as total_commission')
            )
            ->groupBy('referred_id')
            ->orderByDesc('total_commission')
            ->take(3)
            ->get();

        $topAffiliateIds = $topAffiliatesData->pluck('referred_id')->filter();
        $topAffiliateUsers = User::whereIn('id', $topAffiliateIds)->pluck('name', 'id');

        $topPerformers = $topAffiliatesData->map(function ($affiliate) use ($topAffiliateUsers) {
            return [
                'name' => $topAffiliateUsers[$affiliate->referred_id] ?? 'Utilisateur Inconnu',
                'total_commission' => $affiliate->total_commission,
            ];
        });

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
            'top_performers' => $topPerformers,
            'chart_data' => [
                'labels' => $labels,
                'datasets' => [
                    ['label' => 'Commissions', 'data' => $commissionsData],
                    ['label' => 'Inscriptions', 'data' => $signupsData],
                ]
            ]
        ]);
    }
}

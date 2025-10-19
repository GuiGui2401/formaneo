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

        // --- IDs des affiliés ---
        $referredUserIds = User::where('referred_by', $user->id)->pluck('id');

        // --- Calcul des revenus (commissions) ---
        $todayCommissions = $user->transactions()->where('type', 'affiliate_commission')->whereDate('created_at', today())->sum('amount');
        $yesterdayCommissions = $user->transactions()->where('type', 'affiliate_commission')->whereDate('created_at', today()->subDay())->sum('amount');
        $currentMonthCommissions = $user->transactions()->where('type', 'affiliate_commission')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount');
        $lastMonthCommissions = $user->transactions()->where('type', 'affiliate_commission')->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->sum('amount');
        $totalCommissions = $user->transactions()->where('type', 'affiliate_commission')->sum('amount');

        // --- Statistiques d'inscriptions par période ---
        $dailyRegistrations = User::where('referred_by', $user->id)->whereDate('created_at', today())->count();
        $weeklyRegistrations = User::where('referred_by', $user->id)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $monthlyRegistrations = User::where('referred_by', $user->id)->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count();
        $yearlyRegistrations = User::where('referred_by', $user->id)->whereYear('created_at', now()->year)->count();
        $totalRegistrations = User::where('referred_by', $user->id)->count();

        // --- Statistiques d'inscriptions avec achat (Conversions) ---
        $dailyConversions = DB::table('users')
            ->where('referred_by', $user->id)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('transactions')
                    ->whereColumn('transactions.user_id', 'users.id')
                    ->whereIn('transactions.type', ['pack_purchase', 'ebook_purchase', 'product_purchase'])
                    ->whereDate('transactions.created_at', today());
            })
            ->count();

        $weeklyConversions = DB::table('users')
            ->where('referred_by', $user->id)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('transactions')
                    ->whereColumn('transactions.user_id', 'users.id')
                    ->whereIn('transactions.type', ['pack_purchase', 'ebook_purchase', 'product_purchase'])
                    ->whereBetween('transactions.created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            })
            ->count();

        $monthlyConversions = DB::table('users')
            ->where('referred_by', $user->id)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('transactions')
                    ->whereColumn('transactions.user_id', 'users.id')
                    ->whereIn('transactions.type', ['pack_purchase', 'ebook_purchase', 'product_purchase'])
                    ->whereMonth('transactions.created_at', now()->month)
                    ->whereYear('transactions.created_at', now()->year);
            })
            ->count();

        $yearlyConversions = DB::table('users')
            ->where('referred_by', $user->id)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('transactions')
                    ->whereColumn('transactions.user_id', 'users.id')
                    ->whereIn('transactions.type', ['pack_purchase', 'ebook_purchase', 'product_purchase'])
                    ->whereYear('transactions.created_at', now()->year);
            })
            ->count();

        $totalConversions = DB::table('users')
            ->where('referred_by', $user->id)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('transactions')
                    ->whereColumn('transactions.user_id', 'users.id')
                    ->whereIn('transactions.type', ['pack_purchase', 'ebook_purchase', 'product_purchase']);
            })
            ->count();

        $activeAffiliates = User::where('referred_by', $user->id)->where('last_login_at', '>', now()->subDays(30))->count();

        return response()->json([
            'earnings' => [
                'today' => $todayCommissions,
                'yesterday' => $yesterdayCommissions,
                'current_month' => $currentMonthCommissions,
                'last_month' => $lastMonthCommissions,
            ],
            'stats' => [
                'registrations' => [
                    'total' => $totalRegistrations,
                    'monthly' => $monthlyRegistrations,
                ],
                'conversions' => [
                    'daily' => $dailyConversions,
                    'weekly' => $weeklyConversions,
                    'monthly' => $monthlyConversions,
                    'yearly' => $yearlyConversions,
                    'total' => $totalConversions,
                ],
                'active_affiliates' => $activeAffiliates,
                'total_commissions' => $totalCommissions,
            ],
            'affiliate_link' => $user->affiliate_link,
            'promo_code' => $user->promo_code,
        ]);
    }

    // Statistiques détaillées pour les graphiques et top affiliés
    public function getDetailedStats(Request $request)
    {
        $user = $request->user();

        // --- Top Performers (utilisateurs référés qui ont généré le plus de commissions cette semaine) ---
        $topPerformers = User::where('referred_by', $user->id)
            ->whereHas('transactions', function($query) {
                $query->where('type', 'pack_purchase')
                    ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            })
            ->withSum(['transactions' => function($query) {
                $query->where('type', 'pack_purchase')
                    ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            }], 'amount')
            ->orderByDesc('transactions_sum_amount')
            ->take(3)
            ->get()
            ->map(function($affiliate) use ($user) {
                // Calculer la commission générée (20% du montant total d'achat)
                $totalPurchase = abs($affiliate->transactions_sum_amount ?? 0);
                $commission = $totalPurchase * 0.20;

                return [
                    'name' => $affiliate->name,
                    'total_commission' => $commission,
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

    // Fournir les bannières promotionnelles
    public function getBanners(Request $request)
    {
        $banners = \App\Models\PromotionalBanner::where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(function($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'type' => $banner->type,
                    'file_url' => $banner->file_url ? url('storage/' . $banner->file_path) : null,
                    'description' => $banner->description,
                    'download_url' => route('api.affiliate.banner.download', ['id' => $banner->id]),
                ];
            });

        return response()->json(['banners' => $banners]);
    }

    // Télécharger une bannière spécifique
    public function downloadBanner(Request $request, $id)
    {
        $banner = \App\Models\PromotionalBanner::findOrFail($id);
        $filePath = storage_path('app/public/' . $banner->file_path);

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'Fichier de bannière non trouvé.'], 404);
        }

        $filename = $banner->title . '.' . pathinfo($banner->file_path, PATHINFO_EXTENSION);
        return response()->download($filePath, $filename);
    }

    private function createGenericBanner($path, $filename)
    {
        preg_match('/(\d+)x(\d+)/', $filename, $matches);
        $width = $matches[1] ?? 300;
        $height = $matches[2] ?? 250;

        $image = imagecreatetruecolor($width, $height);

        $bgColor = imagecolorallocate($image, 230, 230, 230);
        imagefill($image, 0, 0, $bgColor);

        $textColor = imagecolorallocate($image, 50, 50, 50);
        $text = "Formaneo Banner {$width}x{$height}";

        $fontSize = 4;
        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $textHeight = imagefontheight($fontSize);
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2;

        imagestring($image, $fontSize, $x, $y, $text, $textColor);

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        imagepng($image, $path);
        imagedestroy($image);
    }
}

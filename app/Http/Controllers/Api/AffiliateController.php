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
        $dailyCommissions = $user->transactions()->where('type', 'affiliate_commission')->whereDate('created_at', today())->sum('amount');
        $weeklyCommissions = $user->transactions()->where('type', 'affiliate_commission')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('amount');
        $monthlyCommissions = $user->transactions()->where('type', 'affiliate_commission')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount');
        $yearlyCommissions = $user->transactions()->where('type', 'affiliate_commission')->whereYear('created_at', now()->year)->sum('amount');
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
                'daily' => $dailyCommissions,
                'weekly' => $weeklyCommissions,
                'monthly' => $monthlyCommissions,
                'yearly' => $yearlyCommissions,
                'total' => $totalCommissions,
            ],
            'stats' => [
                'daily' => [
                    'registrations' => $dailyRegistrations,
                    'conversions' => $dailyConversions,
                    'commissions' => $dailyCommissions,
                ],
                'weekly' => [
                    'registrations' => $weeklyRegistrations,
                    'conversions' => $weeklyConversions,
                    'commissions' => $weeklyCommissions,
                ],
                'monthly' => [
                    'registrations' => $monthlyRegistrations,
                    'conversions' => $monthlyConversions,
                    'commissions' => $monthlyCommissions,
                ],
                'yearly' => [
                    'registrations' => $yearlyRegistrations,
                    'conversions' => $yearlyConversions,
                    'commissions' => $yearlyCommissions,
                ],
                'total' => [
                    'registrations' => $totalRegistrations,
                    'conversions' => $totalConversions,
                    'commissions' => $totalCommissions,
                    'active_affiliates' => $activeAffiliates,
                ],
            ],
            'affiliate_link' => $user->affiliate_link,
            'promo_code' => $user->promo_code,
        ]);
    }

    // Statistiques détaillées pour les graphiques et top affiliés
    public function getDetailedStats(Request $request)
    {
        $user = $request->user();

        // --- Top Performers (for MySQL) ---
        $topPerformers = DB::table('users as affiliates')
            ->join('transactions', 'affiliates.id', '=', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(transactions.meta, '$.referred_user_id'))"))
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
        $bannerFiles = [
            'banner_728x90.png',
            'banner_300x250.png',
            'banner_160x600.png',
            'banner_468x60.png',
        ];

        $banners = array_map(function ($file, $index) {
            return [
                'id' => $index + 1,
                'name' => $file,
                'url' => asset('storage/banners/' . $file),
                'download_url' => route('api.affiliate.banner.download', ['id' => $index + 1]),
            ];
        }, $bannerFiles, array_keys($bannerFiles));

        return response()->json($banners);
    }

    // Télécharger une bannière spécifique
    public function downloadBanner(Request $request, $id)
    {
        $bannerFiles = [
            'banner_728x90.png',
            'banner_300x250.png',
            'banner_160x600.png',
            'banner_468x60.png',
        ];

        $bannerId = $id - 1;

        if (!isset($bannerFiles[$bannerId])) {
            return response()->json(['error' => 'Bannière non trouvée.'], 404);
        }

        $filePath = storage_path('app/public/banners/' . $bannerFiles[$bannerId]);

        if (!file_exists($filePath)) {
            $this->createGenericBanner($filePath, $bannerFiles[$bannerId]);
        }

        return response()->download($filePath);
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

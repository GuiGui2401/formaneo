<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\FormationPack;
use App\Models\Transaction;
use App\Models\Quiz;
use App\Models\QuizResult;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = $this->getStats();
        $charts = $this->getChartData();
        $recentTransactions = $this->getRecentTransactions();
        $recentUsers = $this->getRecentUsers();
        
        return view('admin.dashboard.index', compact('stats', 'charts', 'recentTransactions', 'recentUsers'));
    }

    private function getStats()
    {
        $totalUsers = User::count();
        $newUsersToday = User::whereDate('created_at', today())->count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)->count();
        
        $totalPacks = FormationPack::count();
        $activePacks = FormationPack::where('is_active', true)->count();
        
        $totalRevenue = Transaction::where('type', 'purchase')->sum('amount');
        $todayRevenue = Transaction::where('type', 'purchase')
            ->whereDate('created_at', today())->sum('amount');
        $monthRevenue = Transaction::where('type', 'purchase')
            ->whereMonth('created_at', now()->month)->sum('amount');
            
        $pendingWithdrawals = Transaction::where('type', 'withdrawal')
            ->where('status', 'pending')->count();
            
        $totalQuizzes = Quiz::count();
        $quizResultsToday = QuizResult::whereDate('created_at', today())->count();
        
        $activeUsers = User::where('last_login_at', '>', now()->subDays(30))->count();
        
        return [
            'users' => [
                'total' => $totalUsers,
                'new_today' => $newUsersToday,
                'new_month' => $newUsersThisMonth,
                'active' => $activeUsers,
            ],
            'packs' => [
                'total' => $totalPacks,
                'active' => $activePacks,
            ],
            'revenue' => [
                'total' => abs($totalRevenue),
                'today' => abs($todayRevenue),
                'month' => abs($monthRevenue),
            ],
            'transactions' => [
                'pending_withdrawals' => $pendingWithdrawals,
            ],
            'quizzes' => [
                'total' => $totalQuizzes,
                'results_today' => $quizResultsToday,
            ],
        ];
    }

    private function getChartData()
    {
        // Données pour le graphique des inscriptions (30 derniers jours)
        $userRegistrations = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = User::whereDate('created_at', $date)->count();
            $userRegistrations[] = [
                'date' => $date->format('Y-m-d'),
                'count' => $count,
            ];
        }

        // Données pour le graphique des revenus (30 derniers jours)
        $dailyRevenue = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $revenue = abs(Transaction::where('type', 'purchase')
                ->whereDate('created_at', $date)->sum('amount'));
            $dailyRevenue[] = [
                'date' => $date->format('Y-m-d'),
                'revenue' => $revenue,
            ];
        }

        // Répartition des types de transactions
        $transactionTypes = Transaction::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type');

        return [
            'user_registrations' => $userRegistrations,
            'daily_revenue' => $dailyRevenue,
            'transaction_types' => $transactionTypes,
        ];
    }

    private function getRecentTransactions()
    {
        return Transaction::with('user')
            ->latest()
            ->limit(10)
            ->get();
    }

    private function getRecentUsers()
    {
        return User::latest()
            ->limit(10)
            ->get();
    }
}
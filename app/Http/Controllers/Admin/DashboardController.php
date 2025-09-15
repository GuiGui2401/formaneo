<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Statistiques des paiements
        $totalPayments = Transaction::whereIn('type', ['pack_purchase', 'ebook_purchase'])
            ->where('status', 'completed')
            ->sum('amount');
            
        // Statistiques des retraits
        $totalWithdrawals = Transaction::where('type', 'withdrawal')
            ->where('status', 'completed')
            ->sum('amount');
            
        // Paiements en attente
        $pendingPayments = Transaction::whereIn('type', ['pack_purchase', 'ebook_purchase'])
            ->where('status', 'pending')
            ->sum('amount');
            
        // Retraits en attente
        $pendingWithdrawals = Transaction::where('type', 'withdrawal')
            ->where('status', 'pending')
            ->count();
            
        // Dernières transactions
        $recentTransactions = Transaction::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Demandes de retrait en attente
        $pendingWithdrawalRequests = Transaction::with('user')
            ->where('type', 'withdrawal')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.dashboard', compact(
            'totalPayments',
            'totalWithdrawals',
            'pendingPayments',
            'pendingWithdrawals',
            'recentTransactions',
            'pendingWithdrawalRequests'
        ));
    }
}
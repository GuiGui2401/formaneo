<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->latest()->paginate(20);

        $types = Transaction::distinct()->pluck('type');
        $statuses = ['pending', 'completed', 'failed', 'cancelled'];

        return view('admin.transactions.index', compact('transactions', 'types', 'statuses'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load('user');
        return view('admin.transactions.show', compact('transaction'));
    }

    public function approve(Transaction $transaction)
    {
        if ($transaction->type === 'withdrawal' && $transaction->status === 'pending') {
            $transaction->update(['status' => 'completed']);
            
            return back()->with('success', 'Retrait approuvé avec succès.');
        }

        return back()->with('error', 'Impossible d\'approuver cette transaction.');
    }

    public function reject(Transaction $transaction)
    {
        if ($transaction->type === 'withdrawal' && $transaction->status === 'pending') {
            // Rembourser le montant à l'utilisateur
            $transaction->user->increment('balance', abs($transaction->amount));
            $transaction->update(['status' => 'cancelled']);
            
            return back()->with('success', 'Retrait rejeté et montant remboursé.');
        }

        return back()->with('error', 'Impossible de rejeter cette transaction.');
    }

    public function pendingWithdrawals()
    {
        $transactions = Transaction::where('type', 'withdrawal')
            ->where('status', 'pending')
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('admin.transactions.pending-withdrawals', compact('transactions'));
    }
}
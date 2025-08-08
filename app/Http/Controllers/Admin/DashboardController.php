<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\FormationPack;
use App\Models\Transaction;

class DashboardController extends Controller
{
    public function stats()
    {
        $users = User::count();
        $packs = FormationPack::count();
        $balanceSum = Transaction::where('type','quiz_reward')->sum('amount');

        return response()->json([
            'users' => $users,
            'packs' => $packs,
            'total_rewards' => $balanceSum
        ]);
    }
}

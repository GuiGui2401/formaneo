<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\WalletService;
use App\Models\Transaction;

class WalletController extends Controller
{
    protected $wallet;

    public function __construct(WalletService $wallet)
    {
        $this->wallet = $wallet;
    }

    public function balance(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'balance' => $user->balance,
            'available_for_withdrawal' => $user->available_for_withdrawal,
            'pending_withdrawals' => $user->pending_withdrawals
        ]);
    }

    public function requestWithdrawal(Request $request)
    {
        $request->validate(['amount'=>'required|numeric|min:1000']);

        $user = $request->user();
        $amount = (float)$request->amount;

        $result = $this->wallet->requestWithdrawal($user, $amount);

        if (!$result['success']) {
            return response()->json(['error'=>$result['message']], 400);
        }

        return response()->json(['message'=>$result['message']]);
    }
}

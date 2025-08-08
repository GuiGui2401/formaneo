<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $transactions = $user->transactions()->orderBy('created_at','desc')->paginate(20);
        return response()->json($transactions);
    }
}

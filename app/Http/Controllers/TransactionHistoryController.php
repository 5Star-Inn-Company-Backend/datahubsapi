<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionHistoryController extends Controller
{
    public function all()
    {
        $airtimes = Transaction::where('user_id',Auth::id())->latest()->limit(100)->get();
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $airtimes,
        ], 200);
    }

    public function data()
    {
        $airtimes = Transaction::where('user_id',Auth::id())->where('title', 'LIKE', '%data')->latest()->get();
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $airtimes,
        ], 200);
    }

    public function totalSpent()
    {
        $airtimes = Transaction::where('user_id',Auth::id())->where('type', 'debit')->sum('amount');
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $airtimes,
        ], 200);
    }

    public function totalFund()
    {
        $airtimes = Transaction::where('user_id',Auth::id())->where('type', 'credit')->sum('amount');
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $airtimes,
        ], 200);
    }

}

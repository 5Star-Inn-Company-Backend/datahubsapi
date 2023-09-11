<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionHistoryController extends Controller
{
    public function all()
    {
        $airtimes = Transaction::latest()->get();
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $airtimes,
        ], 200);
    }

    public function data()
    {
        $airtimes = Transaction::where('title', 'LIKE', '%data')->latest()->get();
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $airtimes,
        ], 200);
    }

    public function totalSpent()
    {
        $airtimes = Transaction::where('type', 'debit')->sum('amount');
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $airtimes,
        ], 200);
    }

    public function totalFund()
    {
        $airtimes = Transaction::where('type', 'credit')->sum('amount');
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $airtimes,
        ], 200);
    }

}

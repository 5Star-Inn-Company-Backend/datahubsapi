<?php

namespace App\Http\Controllers;

use App\Models\virtual_acct;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function listAll()
    {
        $airtimes = Wallet::where([['status', 1], ['user_id', Auth::id()]])->get();
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $airtimes,
        ], 200);
    }


    public function listVAccts()
    {
        $airtimes = virtual_acct::where([['status', 'active'], ['user_id', Auth::id()]])->get();
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $airtimes,
        ], 200);
    }

}

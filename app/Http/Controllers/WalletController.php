<?php

namespace App\Http\Controllers;

use App\Jobs\CreateVirtualAccount;
use App\Jobs\MCDCreateVirtualAccount;
use App\Jobs\MonnifyCreateVirtualAccount;
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

    public function wBalance()
    {
        $airtimes = Wallet::where([['status', 1], ['user_id', Auth::id()], ['name','wallet']])->first();

        if(!$airtimes){
            return response()->json([
                'status' => false,
                'message' => 'Wallet not found',
            ], 200);
        }
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $airtimes->balance,
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

    public function generateVAccts()
    {
        $user=Auth::user();
        $vaccts = virtual_acct::where([['status', 'active'], ['user_id', Auth::id()]])->count();

        if($vaccts > 0){
            return response()->json([
                'status' => false,
                'message' => 'You have an active virtual account already. Kindly logout and login again.',
            ], 200);
        }

        if(env('VIRTUAL_ACCOUNT_GENERATION_DOMAIN','test') == 'test'){
            CreateVirtualAccount::dispatch($user);
        }elseif(env('VIRTUAL_ACCOUNT_GENERATION_DOMAIN','test') == 'monnify'){
            MonnifyCreateVirtualAccount::dispatch($user);
        }else{
            MCDCreateVirtualAccount::dispatch($user);
        }

        return response()->json([
            'status' => true,
            'message' => 'Account generation in progress, it might take 5 minutes or more.',
        ], 200);
    }

}

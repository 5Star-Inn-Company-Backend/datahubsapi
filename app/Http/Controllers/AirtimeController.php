<?php

namespace App\Http\Controllers;

use App\Jobs\MCDPurchaseAirtimeJob;
use App\Models\tbl_serverconfig_airtime;
use App\Models\tbl_serverconfig_data;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AirtimeController extends Controller
{
    public function listAll()
    {
        $airtimes = tbl_serverconfig_airtime::where('status', 1)->get();
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $airtimes,
        ], 200);
    }

    public function purchaseairtime(Request $request)
    {
        $input = $request->all();
        $rules = array(
            "networkID" => "required",
            "amount" => "required",
            "phone" => "required|min:11",
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()]);
        }

        $airtimes = tbl_serverconfig_airtime::where([['id', $input['networkID']], ['status',1]])->first();

        if(!$airtimes){
            return response()->json([
                'status' => false,
                'message' => "Network ID not valid or available",
            ], 200);
        }

        $wallet=Wallet::where([['user_id',Auth::id()], ['status',1]])->first();

        if(!$wallet){
            return response()->json([
                'status' => false,
                'message' => "No valid wallet",
            ], 200);
        }


        if($wallet->balance < 1){
            return response()->json([
                'status' => false,
                'message' => "Insufficient balance. Kindly topup your wallet",
            ], 200);
        }

        $oBal=$wallet->balance;
        $amount=$input['amount'];

        if($amount > $wallet->balance){
            return response()->json([
                'status' => false,
                'message' => "Insufficient balance to handle request. Kindly topup your wallet",
            ], 200);
        }

        $wallet->balance -=$amount;
        $wallet->save();

        $ref=env('BUSINESS_SHORT_NAME',"dt").time().rand();


        $t=Transaction::create([
            "user_id" => Auth::id(),
            "title" => $airtimes->network." Airtime",
            "amount" => $amount,
            "commission" => 0,
            "reference" => $ref,
            "recipient" => $input['phone'],
            "transaction_type" => "airtime",
            "remark" => "Pending",
            "server" => "0",
            "server_response" => "",
            "prev_balance" => $oBal,
            "new_balance" => $wallet->balance,
        ]);

        MCDPurchaseAirtimeJob::dispatch($airtimes,$t);

        return response()->json([
            'status' => true,
            'message' => "Transaction successful",
        ], 200);
    }



}

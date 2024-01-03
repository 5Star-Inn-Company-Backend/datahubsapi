<?php

namespace App\Http\Controllers;

use App\Jobs\MCDPurchaseEducationJob;
use App\Models\tbl_serverconfig_cabletv;
use App\Models\tbl_serverconfig_education;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EducationController extends Controller
{
    public function listAll()
    {
        $datas = tbl_serverconfig_education::get()->makeHidden(['amount','plan_id','server']);
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $datas,
        ]);
    }


    public function purchase(Request $request)
    {
        $input = $request->all();
        $rules = array(
            "networkID" => "required",
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()]);
        }

        $airtimes = tbl_serverconfig_education::where([['id', $input['networkID']], ['status',1]])->first();

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
        $amount=$airtimes->amount;

        if($amount > $wallet->balance){
            return response()->json([
                'status' => false,
                'message' => "Insufficient balance to handle request. Kindly topup your wallet",
            ], 200);
        }

        $wallet->balance -=$amount;
        $wallet->save();


        $t=Transaction::create([
            "user_id" => Auth::id(),
            "title" => $airtimes->name." Education",
            "amount" => $amount,
            "commission" => 0,
            "reference" => rand(),
            "recipient" => '0',
            "remark" => "Pending",
            "server" => "0",
            "server_response" => "",
            "prev_balance" => $oBal,
            "new_balance" => $wallet->balance,
        ]);

        MCDPurchaseEducationJob::dispatch($airtimes,$t);

        return response()->json([
            'status' => true,
            'message' => "Transaction successful",
        ], 200);
    }

}

<?php

namespace App\Http\Controllers;

use App\Jobs\MCDPurchaseDataJob;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\tbl_serverconfig_data;
use App\Models\tbl_serverconfig_airtime;
use App\Models\tbl_serverconfig_cabletv;
use App\Models\tbl_serverconfig_education;
use App\Models\tbl_serverconfig_electricity;
use Illuminate\Support\Facades\Validator;

class DataController extends Controller
{

    public function listAll($network, $category)
    {
        $datas = tbl_serverconfig_data::where([['network', $network],['category', $category],['status', 1]])->get();
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $datas,
        ], 200);
    }

    public function purchasedata(Request $request)
    {
        $input = $request->all();
        $rules = array(
            "networkID" => "required",
            "phone" => "required|min:11",
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()]);
        }

        $airtimes = tbl_serverconfig_data::where([['id', $input['networkID']], ['status',1]])->first();

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
            "title" => $airtimes->name,
            "amount" => $airtimes->amount,
            "commission" => 0,
            "reference" => rand(),
            "recipient" => $input['phone'],
            "transaction_type" => "data",
            "remark" => "Pending",
            "server" => "0",
            "server_response" => "",
            "prev_balance" => $oBal,
            "new_balance" => $wallet->balance,
        ]);

        MCDPurchaseDataJob::dispatch($airtimes,$t);

        return response()->json([
            'status' => true,
            'message' => "Transaction successful",
        ], 200);
    }

    public function datatypes($network)
    {
        $datatypes = tbl_serverconfig_data::select('category')->where([['network', $network], ['status', 1]])->distinct()->get();

        return response()->json([
            'status' => true,
            'data' => $datatypes
        ]);
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\tbl_serverconfig_airtime;
use App\Models\tbl_serverconfig_data;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

        Transaction::create([
            "title" => $airtimes->network." Airtime",
            "amount" => $input['amount'],
            "commission" => 3,
            "reference" => rand(),
            "recipient" => $input['phone'],
            "remark" => "Successful",
            "server" => "0",
            "server_response" => "{'status':'success'}",
        ]);

        return response()->json([
            'status' => true,
            'message' => "Transaction successful",
        ], 200);
    }



}

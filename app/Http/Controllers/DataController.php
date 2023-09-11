<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
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


        Transaction::create([
            "title" => $airtimes->network." Data",
            "amount" => $airtimes->amount,
            "commission" => 4,
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

    public function datatypes($network)
    {
        $datatypes = tbl_serverconfig_data::select('category')->where([['network', $network], ['status', 1]])->distinct()->get();

        return response()->json([
            'status' => true,
            'data' => $datatypes
        ]);
    }

}

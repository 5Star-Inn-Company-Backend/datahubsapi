<?php

namespace App\Http\Controllers;

use App\Models\tbl_airtime2cash;
use App\Models\tbl_serverconfig_airtime2cash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Airtime2CashController extends Controller
{
    public function listAll()
    {
        $airtimes = tbl_serverconfig_airtime2cash::where('status', 1)->get();
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $airtimes,
        ], 200);
    }

    public function purchase(Request $request)
    {
        $input = $request->all();
        $rules = array(
            "networkID" => "required",
            "amount" => "required",
            "phone" => "required|min:11",
            "type" => "required|in:bank,wallet",
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()]);
        }

        $airtimes = tbl_serverconfig_airtime2cash::where([['id', $input['networkID']], ['status',1]])->first();

        if(!$airtimes){
            return response()->json([
                'status' => false,
                'message' => "Network ID not valid or available",
            ], 200);
        }

        tbl_airtime2cash::create([
            "network" => $airtimes->network,
            "amount" => $input['amount'],
            "phoneno" => $input['phone'],
            "receiver" => $input['type'],
            "user_id" => Auth::id(),
            "reference" => rand(),
            "ip" => $request->ip()
        ]);

        return response()->json([
            'status' => true,
            'message' => "Transfer the airtime to this number",
            'data' => $airtimes->number
        ]);
    }
}

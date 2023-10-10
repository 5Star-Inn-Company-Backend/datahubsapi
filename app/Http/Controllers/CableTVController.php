<?php

namespace App\Http\Controllers;

use App\Models\tbl_serverconfig_cabletv;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CableTVController extends Controller
{

    public function tvlist($network)
    {

        $cabletvs = tbl_serverconfig_cabletv::where([['type', $network],['status', 1]])->get();
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $cabletvs
        ]);
    }

    public function tvvalidate(Request $request)
    {
        $input = $request->all();
        $rules = array(
            "networkID" => "required|in:dstv,gotv,startimes",
            "phone" => "required|min:10",
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()]);
        }

//        $cabletvtypes = tbl_serverconfig_cabletv::where([['id',$input['networkID']],['status', 1]])->first();
//
//        if(!$cabletvtypes){
//            return response()->json(['status' => false, 'message' => "Network ID not valid or available"]);
//        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => env('SERVER6') . "merchant-verify",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('billersCode' => $input['phone'],'serviceID' => $input['networkID']),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic ' .env('SERVER6_AUTH'),
            ),
        ));

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);

        curl_close($curl);

        $rep=json_decode($response, true);

        try{
            return response()->json(['status' => true, 'message' => 'Validated successfully', 'data' => $rep['content']['Customer_Name'], 'details' => $rep['content']]);
        }catch (\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Unable to validate'
            ]);
        }

    }

    public function tvpurchase(Request $request)
    {
        $input = $request->all();
        $rules = array(
            "networkID" => "required",
            "phone" => "required|min:10",
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()]);
        }

        $cabletvtypes = tbl_serverconfig_cabletv::where([['id',$input['networkID']],['status', 1]])->first();

        if(!$cabletvtypes){
            return response()->json(['status' => false, 'message' => "Network ID not valid or available"]);
        }

        Transaction::create([
            "title" => $cabletvtypes->name,
            "amount" => $cabletvtypes->price,
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
        ]);

    }


}
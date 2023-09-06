<?php

namespace App\Http\Controllers;

use App\Models\tbl_serverconfig_electricity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ElectricityController extends Controller
{
    public function listAll()
    {
        $datas = tbl_serverconfig_electricity::get()->makeHidden(['server', 'code10']);
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $datas,
        ]);
    }


    public function elecvalidate(Request $request)
    {
        $input = $request->all();
        $rules = array(
            "networkID" => "required",
            "type" => "required|in:prepaid,postpaid",
            "phone" => "required|min:10",
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()]);
        }

        $types = tbl_serverconfig_electricity::where([['id',$input['networkID']],['status', 1]])->first();

        if(!$types){
            return response()->json(['status' => false, 'message' => "Network ID not valid or available"]);
        }

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
            CURLOPT_POSTFIELDS => array('billersCode' => $input['phone'],'serviceID' => $types->code,'type' => $input['type']),
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


    public function purchase(Request $request)
    {
        $input = $request->all();
        $rules = array(
            "networkID" => "required",
            "type" => "required|in:prepaid,postpaid",
            "phone" => "required|min:11",
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()]);
        }

        $airtimes = tbl_serverconfig_electricity::where([['id', $input['networkID']], ['status',1]])->first();

        if(!$airtimes){
            return response()->json([
                'status' => false,
                'message' => "Network ID not valid or available",
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => "Transaction successful",
        ], 200);
    }


}

<?php

namespace App\Http\Controllers;

use App\Models\tbl_serverconfig_rechargecard;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RechargeCardController extends Controller
{
    public function listAll()
    {
        $datas = tbl_serverconfig_rechargecard::where('status', 1)->get();
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $datas,
        ], 200);
    }

    public function purchase(Request $request)
    {
        $input = $request->all();
        $rules = array(
            "networkID" => "required",
            "quantity" => "required|min:1",
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()]);
        }

        $airtimes = tbl_serverconfig_rechargecard::where([['id', $input['networkID']], ['status',1]])->first();

        if(!$airtimes){
            return response()->json([
                'status' => false,
                'message' => "Network ID not valid or available",
            ], 200);
        }


        Transaction::create([
            "title" => $airtimes->network."_" .$airtimes->amount." RC",
            "amount" => $airtimes->amount,
            "commission" => 6,
            "reference" => rand(),
            "remark" => $input['quantity'],
            "server" => "0",
            "server_response" => "{'status':'success'}",
        ]);

//        $payload='{
//    "network": "AIRTEL",
//    "amount" : "200",
//    "quantity" : "3",
//    "order" : "instant"
//}';
//
//        echo $payload;
//
//        $curl = curl_init();
//
//        curl_setopt_array($curl, array(
//            CURLOPT_URL => 'https://rechargecardportal.5starcompany.com.ng/api/generate-epins-test',
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => '',
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => 'POST',
//            CURLOPT_POSTFIELDS =>$payload,
//            CURLOPT_HTTPHEADER => array(
//                'Content-Type: application/json',
//                'Authorization: Bearer 5|FR3u2zbT4VdoIVzKzq57U2xZhSQwSBCB6NtLF46i'
//            ),
//        ));
//
//        $response = curl_exec($curl);
//
//        curl_close($curl);
//
//        $rep=json_decode($response, true);
//
//        if($rep['success']){
            return response()->json([
                'status' => true,
                'message' => "Transaction successful",
                'data' => json_decode('[
            {
                "pin": "4484588718345",
                "serial": "7804243029765",
                "amount": "200",
                "expiry": "end_date=28/04/2025",
                "id": 100159,
                "network": "AIRTEL"
            },
            {
                "pin": "4484588718345",
                "serial": "7804243029765",
                "amount": "200",
                "expiry": "end_date=28/04/2025",
                "id": 100159,
                "network": "AIRTEL"
            },
            {
                "pin": "4484588718345",
                "serial": "7804243029765",
                "amount": "200",
                "expiry": "end_date=28/04/2025",
                "id": 100159,
                "network": "AIRTEL"
            }
        ]'),
            ], 200);
//        }else{
//            return response()->json([
//                'status' => false,
//                'message' => "Transaction fail",
//            ], 200);
//        }


    }

}

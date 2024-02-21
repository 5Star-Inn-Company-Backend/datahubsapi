<?php

namespace App\Http\Controllers;

use App\Jobs\MCDPurchaseTVJob;
use App\Models\tbl_serverconfig_cabletv;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $amount=$cabletvtypes->price;

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
            "title" => $cabletvtypes->name,
            "amount" => $amount,
            "commission" => 4,
            "reference" => rand(),
            "recipient" => $input['phone'],
            "transaction_type" => "cabletv",
            "remark" => "Pending",
            "server" => "0",
            "server_response" => "",
            "prev_balance" => $oBal,
            "new_balance" => $wallet->balance,
        ]);

        MCDPurchaseTVJob::dispatch($cabletvtypes,$t);

        return response()->json([
            'status' => true,
            'message' => "Transaction successful",
        ]);

    }


}

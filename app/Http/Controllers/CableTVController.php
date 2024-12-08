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

        $cabletvs = tbl_serverconfig_cabletv::where([['type', strtolower($network)],['status', 1]])->get();
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


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => env('MCD_URL').'/validate',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
    "service": "tv",
    "provider": "'.$input['networkID'].'",
    "number": "'.$input['phone'].'"
}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer '.env('MCD_TOKEN')
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $rep=json_decode($response, true);

        try{
            return response()->json(['status' => true, 'message' => 'Validated successfully', 'data' => $rep['data']]);
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

        $ref=env('BUSINESS_SHORT_NAME',"dt").time().rand();

        $discount=(explode("%",$cabletvtypes->discount)[0]/100) * $amount;

        $walletB=Wallet::where([['user_id',Auth::id()], ['status',1], ['name', 'bonus']])->first();

        if($walletB){
            $walletB->balance +=$discount;
            $walletB->save();
        }


        $t=Transaction::create([
            "user_id" => Auth::id(),
            "title" => $cabletvtypes->name,
            "amount" => $amount,
            "commission" => $discount,
            "reference" => $ref,
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

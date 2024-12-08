<?php

namespace App\Http\Controllers;

use App\Jobs\PayReferralBonusJob;
use App\Models\FundingConfig;
use App\Models\Transaction;
use App\Models\User;
use App\Models\virtual_acct;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MonnifyPaymentWebhookController extends Controller
{
    public function index(Request $request){
        $input = $request->all();

        Log::info("Monnify Webhook: ".json_encode($input));

        $rules = array(
            "eventType" => "required",
            "eventData" => "required"
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()]);
        }

        if($input['eventType'] != "SUCCESSFUL_TRANSACTION"){
            return response()->json(['status' => false, 'message' => "I am not expecting you"]);
        }

        $acct_no=$input['eventData']['destinationAccountInformation']['accountNumber'];
        $amount=$input['eventData']['amountPaid'];
        $ref=$input['eventData']['transactionReference'];
        $paymentType=$input['eventData']['product']['type'];

        if($paymentType == "RESERVED_ACCOUNT") {
            $va = virtual_acct::where([['account_number', $acct_no], ['domain', 'monnify']])->first();

            if (!$va) {
                return response()->json(['status' => false, 'message' => 'Thanks. Account number not found']);
            }

            $fc=FundingConfig::where('name','Bank Transfer')->first();
        }else{

            $fc=FundingConfig::where('name','Monnify')->first();
        }


        $wallet=Wallet::where([['user_id',$va->user_id], ['name','wallet']])->first();


        if(!$wallet){
            return response()->json(['status' => false, 'message' => 'Thanks. User did not have a wallet yet']);
        }


        $t=Transaction::where('reference',$ref)->first();


        if($t){
            return response()->json(['status' => false, 'message' => 'Thanks. Transaction has been credited already']);
        }

        $charges=0;

        if($fc){
            if($fc->charges_type == 1){
                $charges=(($fc->charges/100) * $amount);
            }else{
                $charges=$fc->charges;
            }

        }

        $oBal=$wallet->balance;

        $wallet->balance+=$amount;
        $wallet->balance-=$charges;
        $wallet->save();


        Transaction::create([
            "user_id" => $wallet->user_id,
            "title" => "Wallet Funding",
            "amount" => $amount,
            "charges" => $charges,
            "commission" => 0,
            "reference" => $ref,
            "recipient" => $acct_no,
            "transaction_type" => "wallet_funding",
            "remark" => "Credited",
            "type" => "credit",
            "server" => "0",
            "status" => 1,
            "server_response" => "Monnify",
            "prev_balance" => $oBal,
            "new_balance" => $wallet->balance,
        ]);

        $user=User::find($wallet->user_id);

        PayReferralBonusJob::dispatch($user->id,$user->referer_id,1);

        return response()->json(['status' => true, 'message' => 'User credited']);

    }

}


//{
//    "eventType": "SUCCESSFUL_TRANSACTION",
//    "eventData": {
//    "product": {
//        "reference": "1636106097661",
//        "type": "RESERVED_ACCOUNT"
//      },
//      "transactionReference": "MNFY|04|20211117112842|000170",
//      "paymentReference": "MNFY|04|20211117112842|000170",
//      "paidOn": "2021-11-17 11:28:42.615",
//      "paymentDescription": "Adm",
//      "metaData": {},
//      "paymentSourceInformation": [
//        {
//            "bankCode": "",
//          "amountPaid": 3000,
//          "accountName": "Monnify Limited",
//          "sessionId": "e6cV1smlpkwG38Cg6d5F9B2PRnIq5FqA",
//          "accountNumber": "0065432190"
//        }
//      ],
//      "destinationAccountInformation": {
//        "bankCode": "232",
//        "bankName": "Sterling bank",
//        "accountNumber": "6000140770"
//      },
//      "amountPaid": 3000,
//      "totalPayable": 3000,
//      "cardDetails": {},
//      "paymentMethod": "ACCOUNT_TRANSFER",
//      "currency": "NGN",
//      "settlementAmount": "2990.00",
//      "paymentStatus": "PAID",
//      "customer": {
//        "name": "John Doe",
//        "email": "test@tester.com"
//      }
//    }
//  }

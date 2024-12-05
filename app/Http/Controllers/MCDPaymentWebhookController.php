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
use Illuminate\Support\Facades\Validator;

class MCDPaymentWebhookController extends Controller
{
    public function index(Request $request){
        $input = $request->all();
        $rules = array(
            "account_number" => "required",
            "account_reference" => "required",
            "ref" => "required",
            "amount" => "required",
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()]);
        }

        $va=virtual_acct::where('account_number',$input['account_number'])->first();

        if(!$va){
            return response()->json(['status' => false, 'message' => 'Thanks. Account number not found']);
        }


        $wallet=Wallet::where([['user_id',$va->user_id], ['name','wallet']])->first();


        if(!$wallet){
            return response()->json(['status' => false, 'message' => 'Thanks. User did not have a wallet yet']);
        }


        $t=Transaction::where('reference',$input['ref'])->first();


        if($t){
            return response()->json(['status' => false, 'message' => 'Thanks. Transaction has been credited already']);
        }

        $fc=FundingConfig::where('name','Bank Transfer')->first();

        $charges=0;
        $amount=$input['amount'];

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
            "reference" => $input['ref'],
            "recipient" => $input['account_number'],
            "transaction_type" => "wallet_funding",
            "remark" => "Credited",
            "type" => "credit",
            "server" => "0",
            "status" => 1,
            "server_response" => "",
            "prev_balance" => $oBal,
            "new_balance" => $wallet->balance,
        ]);

        $user=User::find($wallet->user_id);

        PayReferralBonusJob::dispatch($user->id,$user->referer_id,1);

        return response()->json(['status' => true, 'message' => 'User credited']);

    }

}


//{
//    "account_number": "8218059045",
//    "account_reference": "Gladysokiemute234202",
//    "amount": 500,
//    "fees": 5,
//    "narration": "07068676694\/8218059045\/STARCOMPANY\/REN",
//    "ref": "100004240105060155110254799207",
//    "from_account_name": "GLADYS OKIEMUTE",
//    "from_account_number": "XXXXXX6694",
//    "paid_at": "2024-01-05T06:03:15.000Z"
//}

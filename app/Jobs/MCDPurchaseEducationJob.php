<?php

namespace App\Jobs;

use App\Models\tbl_serverconfig_education;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MCDPurchaseEducationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
//    public $tries = 1;

    public $config, $transaction;
    /**
     * Create a new job instance.
     */
    public function __construct(tbl_serverconfig_education $config, Transaction $transaction)
    {
        $this->config=$config;
        $this->transaction=$transaction;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $payload='{
    "coded": "'.$this->config->code.'",
    "quantity": "1",
    "ref": "'.$this->transaction->reference.'",
    "number": "0",
    "payment": "wallet",
    "reseller_price":"'.$this->transaction->amount.'",
    "promo":"0"
}';

        Log::info("=====MCDPurchaseEducationJob====${payload}====User(".$this->transaction->user_id.")");


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => env('MCD_URL').'/resultchecker',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer '.env('MCD_TOKEN')
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;

        Log::info($response);

        $rep=json_decode($response,true);
        if($rep['success'] == 1){
            $this->transaction->status=1;
            $this->transaction->remark="Successful";
            $this->transaction->server_response=$response;
            $this->transaction->token=$rep['token'];
            $this->transaction->save();
        }else{
            $this->transaction->server_response=$response;
            $this->transaction->save();
        }

    }
}

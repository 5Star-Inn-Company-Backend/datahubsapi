<?php

namespace App\Jobs;

use App\Models\tbl_serverconfig_airtime;
use App\Models\tbl_serverconfig_data;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MCDPurchaseDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
//    public $tries = 1;

    public $config, $transaction;

    public function __construct(tbl_serverconfig_data $config, Transaction $transaction)
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
    "coded": "'.$this->config->coded.'",
    "number": "'.$this->transaction->recipient.'",
    "payment" : "wallet",
    "promo" : "0",
    "ref":"'.$this->transaction->reference.'",
    "reseller_price":"'.$this->transaction->amount.'",
    "country": "NG"
}';

        Log::info("=====MCDPurchaseDataJob====${payload}====User(".$this->transaction->user_id.")");



        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => env('MCD_URL').'/data',
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
            $this->transaction->save();
        }else{
            $this->transaction->server_response=$response;
            $this->transaction->save();
        }

    }
}

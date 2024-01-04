<?php

namespace App\Jobs;

use App\Models\tbl_serverconfig_rechargecard;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MCDPurchaseRechargeCardJob implements ShouldQueue
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
    public function __construct(tbl_serverconfig_rechargecard $config, Transaction $transaction)
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
    "provider": "'.$this->config->network.'",
    "amount": "'.$this->config->amount.'",
    "number": "08166939205",
    "quantity" : "'.$this->transaction->recipient.'",
    "payment" : "wallet",
    "promo" : "0",
    "ref":"'.$this->transaction->reference.'"
}';


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => env('MCD_URL').'/airtimepin',
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

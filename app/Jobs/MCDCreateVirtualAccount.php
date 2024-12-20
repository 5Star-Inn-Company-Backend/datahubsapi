<?php

namespace App\Jobs;

use App\Models\FundingConfig;
use App\Models\User;
use App\Models\virtual_acct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MCDCreateVirtualAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $user;

    public function __construct(user $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $fc=FundingConfig::where('name','Bank Transfer')->first();

        if($fc->provider == "MONNIFY"){
            MonnifyCreateVirtualAccount::dispatch($this->user);
            return;
        }

        $payload='{
                 "uniqueid":" '.env('BUSINESS_SHORT_NAME'). $this->user->id .'",
                 "account_name":"'. $this->user->firstname .' '. $this->user->lastname .'",
                 "business_short_name":" '.env('BUSINESS_SHORT_NAME') .'",
                 "address":"'.$this->user->address .'",
                 "gender":"'.$this->user->gender .'",
                 "email":"'.$this->user->email .'",
                 "phone":"'.$this->user->phone .'",
                 "dob":"'.$this->user->dob .'",
                 "webhook_url":"'.env('APP_URL').'/api/hook/mcdpayment",
                "bvn":"'.$this->user->bvn .'",
                "provider":"'.env('MCD_BANK').'"
            }';


        Log::info("=====MCDCreateVirtualAccount====${payload}");


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => env('MCD_URL').'/virtual-account3',
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

        $responseData = json_decode($response, true);

        if ($responseData['success'] == 1) {
            virtual_acct::create([
                'user_id' => $this->user->id,
                'account_name' => $responseData['data']['customer_name'],
                'account_number' => $responseData['data']['account_number'],
                'provider' => $responseData['data']['bank_name'],
                'domain' => 'live',
                'reference' => $responseData['data']['account_reference'],
                'assignment' => 'reserved',
                'status' => 'active',

            ]);

//            echo $response;
        } else {
//            echo "Failed to create virtual account.";
        }
    }
}

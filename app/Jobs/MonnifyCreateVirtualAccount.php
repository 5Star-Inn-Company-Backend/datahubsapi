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

class MonnifyCreateVirtualAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user=$this->user;

        try {

            $fc=FundingConfig::where('name','Monnify')->first();

            $fcv=explode("|",$fc->ppkey);

            $apiKey=$fcv[0];
            $secretKey=$fcv[1];
            $contractCode=$fcv[2];

            Log::info("Create Monnify Account for " . $user->email);

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => env("MONNIFY_URL") . "/v1/auth/login",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Basic " . base64_encode($apiKey.":".$secretKey)
                ),
            ));
            $response = curl_exec($curl);
            $respons = $response;

            curl_close($curl);

            Log::info("Create Monnify Account Login response " . $respons);

//        $response='{"requestSuccessful":true,"responseMessage":"success","responseCode":"0","responseBody":{"accessToken":"eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJhdWQiOlsibW9ubmlmeS1wYXltZW50LWVuZ2luZSJdLCJzY29wZSI6WyJwcm9maWxlIl0sImV4cCI6MTU5MTQ5Nzc5OSwiYXV0aG9yaXRpZXMiOlsiTVBFX01BTkFHRV9MSU1JVF9QUk9GSUxFIiwiTVBFX1VQREFURV9SRVNFUlZFRF9BQ0NPVU5UIiwiTVBFX0lOSVRJQUxJWkVfUEFZTUVOVCIsIk1QRV9SRVNFUlZFX0FDQ09VTlQiLCJNUEVfQ0FOX1JFVFJJRVZFX1RSQU5TQUNUSU9OIiwiTVBFX1JFVFJJRVZFX1JFU0VSVkVEX0FDQ09VTlQiLCJNUEVfREVMRVRFX1JFU0VSVkVEX0FDQ09VTlQiLCJNUEVfUkVUUklFVkVfUkVTRVJWRURfQUNDT1VOVF9UUkFOU0FDVElPTlMiXSwianRpIjoiOTYyNTA5NzctMmZkOS00ZDM4LTliYzEtNTMyMTMwYmFiODc0IiwiY2xpZW50X2lkIjoiTUtfVEVTVF9LUFoyQjJUQ1hLIn0.iTOX9RWwA0zcLh3OsTtuFD-ehAbW1FrUcAZLM73V66_oTuV2jJ5wBjWNvyQToZKl2Rf5TH2UgiJyaapAZR6yU9Y4Di_oz97kq0CwpoFoe_rLmfgWgh-jrYEsrkj751jiQQm_vZ6BEw9OJhYtMBb1wEXtY4rFMC7I2CLmCnwpJaMWgrWnTRcoLZlPTcWGMBLeggaY9oLfIIorV9OTVkB2kihA9QHX-8oUGkYpvKyC9ERNYMURcK01LnPgSBWI7lXrjf8Ct2BjHi6RKdlFRPNpp3OAbN9Oautvwy09WS3XOhA8eycA0CNBh8o7jekVLCLjXgz6YrcMH0j9ahb3mPBr7Q","expiresIn":368}}';

            $response = json_decode($respons, true);
            $token = $response['responseBody']['accessToken'];

            $fn=$user->lastname . " ". $user->firstname;

            $payload='{
	"bvn": "' . $user->bvn . '",
	"accountReference": "' . $user->id.$user->bvn . '",
	"accountName": "' . $fn . '",
	"currencyCode": "NGN",
	"contractCode": "' . $contractCode . '",
	"customerEmail": "' . $user->email . '",
	"customerName": "' . $fn . '",
	"getAllAvailableBanks": true
}';

            Log::info($payload);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => env("MONNIFY_URL") . "/v2/bank-transfer/reserved-accounts",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Authorization: Bearer " . $token
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            Log::info("Response");
            Log::info($response);

            $response = json_decode($response, true);

            if($response['requestSuccessful']){
                for ($i=0;$i<count($response['responseBody']['accounts']);$i++){
                    virtual_acct::create([
                        'user_id' => $this->user->id,
                        'account_name' => $response['responseBody']['accounts'][$i]['accountName'],
                        'account_number' => $response['responseBody']['accounts'][$i]['accountNumber'],
                        'provider' => $response['responseBody']['accounts'][$i]['bankName'],
                        'domain' => 'monnify',
                        'reference' => $response['responseBody']['accountReference'],
                        'assignment' => 'reserved',
                        'status' => 'active',
                    ]);
                }
            }

        } catch (\Exception $e) {
            echo "Error encountered ";
            Log::info("Monnify Error");
            Log::info($e);
        }
    }
}

<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\virtual_acct;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Log;

class CreateVirtualAccount implements ShouldQueue
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

        $payload='{
                 "firstname":" '. $this->user->firstname .' ",
                 "lastname":" '. $this->user->lastname .' ",
                  "address":" '. $this->user->address .' ",
                 "gender":" '. $this->user->gender .' ",
                 "email":" '. $this->user->email .' ",
                 "phone":" '. $this->user->phone .' ",
                 "dob":" '. $this->user->dob .' ",
                "bvn":"",
                "provider":"safehaven"
            }';

        Log::info("=====PaylonyCreateVirtualAccountPayload====${$payload}");

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.paylony.com/api/v1/create_account',
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
                'Authorization: Bearer sk_test_pqvard3tkffusqzvlsten58f4rwduzedzevowik'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        Log::info("=====PaylonyCreateVirtualAccountResponse====${$response}");

          echo $response;

         $responseData = json_decode($response, true);

         if ($responseData['success'] && $responseData['status'] === '00') {
            virtual_acct::create([
                'user_id' => $this->user->id,
                'account_name' => $responseData['data']['account_name'],
                'account_number' => $responseData['data']['account_number'],
                'provider' => $responseData['data']['provider'],
                'domain' => $responseData['data']['domain'],
                'reference' => $responseData['data']['reference'],
                'assignment' => $responseData['data']['assignment'],
                'status' => $responseData['data']['status'],

            ]);

//            echo $response;
        } else {
//            echo "Failed to create virtual account.";
        }
    }
}

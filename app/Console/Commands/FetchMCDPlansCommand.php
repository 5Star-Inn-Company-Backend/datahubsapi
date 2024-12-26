<?php

namespace App\Console\Commands;

use App\Models\tbl_serverconfig_data;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchMCDPlansCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mcdplans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch MCD plans';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->dataPlans();
    }

    private function dataPlans()
    {
        $this->info("Fetching data plans");

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => env('MCD_URL') . "/data/ALL",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . env('MCD_TOKEN'),
                'Content-Type: application/json'
            ),
        ));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);

        echo $response;

        curl_close($curl);

        $reps = json_decode($response, true);

        if($reps['success'] == 1){
            foreach ($reps['data'] as $rep){
                if($rep['server'] == 10 || $rep['server'] == 6){
                    $fp=tbl_serverconfig_data::where('coded',$rep['coded'])->first();

                    if($fp){
                        $this->updateNewPlan($fp->id,$rep['price'],$rep['name']);
                    }else{
                        $this->createNewPlan($rep);
                    }
                }
            }
        }

    }

    private function createNewPlan($rep){
        $this->info("Creating plan " . $rep['name']." ".$rep['network']);
        Log::info("Creating plan " . $rep['name']." ".$rep['network']);
        tbl_serverconfig_data::create([
            'name' => $rep['name'],
            'dataplan' => $rep['dataplan'],
            'category' => $rep['category'],
            'network' => $rep['network'],
            'coded' => $rep['coded'],
            'plan_id' => $rep['coded'],
            'amount' => $rep['price']+30,
            'price' => $rep['price'],
            'server' => $rep['server'],
            'status' => 1,
        ]);
    }

    private function updateNewPlan($id,$price,$name){
        $this->info("Updating plan $id - $name - $price");
        Log::info("Updating plan $id - $name - $price");
        tbl_serverconfig_data::where('id',$id)->update([
            'name' => $name,
            'amount' => $price+30,
            'price' => $price
        ]);
    }

}

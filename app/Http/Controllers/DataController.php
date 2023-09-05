<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\tbl_serverconfig_data;
use App\Models\tbl_serverconfig_airtime;
use App\Models\tbl_serverconfig_cabletv;
use App\Models\tbl_serverconfig_education;
use App\Models\tbl_serverconfig_electricity;

class DataController extends Controller
{

    public function datatypes()
    {
        $datatypes = tbl_serverconfig_data::all();

        if ($datatypes) {
            $types = [];
            foreach ($datatypes as $datatype) {
                $types[] = $datatype->category;
            }
            return response()->json([
                'status' => true,
                'message' => $types
            ]);
        }
    }

    public function scratchcards()
    {
        $scratchs = tbl_serverconfig_education::where('status', 1)->get();

        if ($scratchs) {
            $scratchcard = [];
            foreach ($scratchs as $scratch) {
                $scratchcard[] = $scratch->name;
            }

            return response()->json([
                'status' => true,
                'message' => $scratchcard
            ]); //
        }
    }

    public function electricity()
    {
        $electricitys = tbl_serverconfig_electricity::where('status', 1)->get();
        if ($electricitys) {
            $electric = [];
            foreach ($electricitys as $electricity) {
                $electric[] = $electricity->name;
            }

            return response()->json([
                'status' => true,
                'message' => $electric
            ]); //
        }
    }

    public function purchasedata(Request $request)
    {
        // Get the current time in the Africa/Lagos time zone
        $now = Carbon::now('Africa/Lagos');

        // Format the current time in the required format
        $request_id = $now->format('YmdHis') . uniqid();
        $curl = curl_init();
        $requestdata = [
            "request_id" => $request_id,
            "serviceID" => $request->serviceID,
            "billersCode" => $request->billersCode,
            'variation_code' => $request->variation_code,
            'amount' => $request->amount,
            "phone" => $request->phone,
        ];

        $headers = [
            'api-key: ab8085f10d5322b9bcd08a6adb975401',
            'secret-key: SK_70529d32acb17b3d3641a27325a9dd16b15a0a41d45',
            'Content-Type: application/json',
            'Cookie: laravel_session=eyJpdiI6IjFUekFFbEFaM2xuckRsdEF1RzVZaVE9PSIsInZhbHVlIjoiUUtQdjRNcTBkNUNMMDdhcmdlbzNpczI4dm1hVHU0S2NMYTFmUTQ2ZjVlZ3hvR1Q2VlwvVTc0R0JlbGxPdEcwWDBSU3lGOUZFRElUK2ZSOGplSkR0c3p3PT0iLCJtYWMiOiIzMjYwNTg2ZTAyODU1ZTFmMGFhNDU3NGI4ZGI1NzcwNjIzMTdhNDM1YTJiNTJmYmVhMDUyNmYxZTdkYzRjNzdiIn0%3D'

        ];



        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://sandbox.vtpass.com/api/pay',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($requestdata),
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        // echo $response;

        $jsondecoderesponse = json_decode($response);

        return response()->json([
            "status" => 200,
            "message" => $jsondecoderesponse
        ]);
    }

    public function cablesubscribe(Request $request)
    {
        // Get the current time in the Africa/Lagos time zone
        $now = Carbon::now('Africa/Lagos');

        // Format the current time in the required format
        $request_id = $now->format('YmdHis') . uniqid();

        $requestbody = [
            "request_id" => $request_id,
            "serviceID" => $request->serviceID,
            "billersCode" =>  $request->billersCode,
            "variation_code" => $request->variation_code,
            "amount" => $request->amount,
            "phone" => $request->phone,
            "subscription_type" => $request->subscription_type,
            "quantity" => $request->quantity,
        ];

        $headers = [
            'api-key: ab8085f10d5322b9bcd08a6adb975401',
            'secret-key: SK_70529d32acb17b3d3641a27325a9dd16b15a0a41d45',
            'Content-Type: application/json',
            'Cookie: laravel_session=eyJpdiI6IjBSSEpiMkpZY1c5a1wvWXBFdENGV1FRPT0iLCJ2YWx1ZSI6InNHR3ZzVTFNcUNNNnR4bXRxR0htN3A2YXZZTlVUbWFuOTJqRGNxcEIzQkl1Sm03bXVLTkxNcklOY3RIdmZJT1ZFdDVKN1Q2OWRxNGk5Yjg5OXIzT1wvZz09IiwibWFjIjoiOGFmNjczNDg2OWVmYzRiYzk1YjQ4ZTQ3MDA2MTY0MjMwYmQ3NGY2ZGJjM2FmNGM2YmYwYjY5MThlOTUzMDc0NiJ9'
        ];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://sandbox.vtpass.com/api/pay',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($requestbody),
            CURLOPT_HTTPHEADER => $headers
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return response()->json([
            "status" => true,
            "message" => json_decode($response, true),
        ]);
        // echo $response;

    }

    public function electricitypay(Request $request)
    {

        // Get the current time in the Africa/Lagos time zone
        $now = Carbon::now('Africa/Lagos');

        // Format the current time in the required format
        $request_id = $now->format('YmdHis') . uniqid();

        $requestbody = [
            "request_id" => $request_id,
            "serviceID" => $request->serviceID,
            "billersCode" =>  $request->billersCode,
            "variation_code" => $request->variation_code,
            "amount" => $request->amount,
            "phone" => $request->phone,
            "subscription_type" => $request->subscription_type,
            "quantity" => $request->quantity,
        ];

        $headers = [
            'api-key: ab8085f10d5322b9bcd08a6adb975401',
            'secret-key: SK_70529d32acb17b3d3641a27325a9dd16b15a0a41d45',
            'Content-Type: application/json',
            'Cookie: laravel_session=eyJpdiI6IjBSSEpiMkpZY1c5a1wvWXBFdENGV1FRPT0iLCJ2YWx1ZSI6InNHR3ZzVTFNcUNNNnR4bXRxR0htN3A2YXZZTlVUbWFuOTJqRGNxcEIzQkl1Sm03bXVLTkxNcklOY3RIdmZJT1ZFdDVKN1Q2OWRxNGk5Yjg5OXIzT1wvZz09IiwibWFjIjoiOGFmNjczNDg2OWVmYzRiYzk1YjQ4ZTQ3MDA2MTY0MjMwYmQ3NGY2ZGJjM2FmNGM2YmYwYjY5MThlOTUzMDc0NiJ9'
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://sandbox.vtpass.com/api/pay',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($requestbody),
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return response()->json([
            "status" => true,
            "message" => json_decode($response, true),
        ]);
        // echo $response;

    }

    public function scratchcard(Request $request)
    {

        // Get the current time in the Africa/Lagos time zone
        $now = Carbon::now('Africa/Lagos');

        // Format the current time in the required format
        $request_id = $now->format('YmdHis') . uniqid();

        $requestbody = [
            "request_id" => $request_id,
            "serviceID" => $request->serviceID,
            "billersCode" =>  $request->billersCode,
            "variation_code" => $request->variation_code,
            "amount" => $request->amount,
            "phone" => $request->phone,
            "subscription_type" => $request->subscription_type,
            "quantity" => $request->quantity,
        ];

        $headers = [
            'api-key: ab8085f10d5322b9bcd08a6adb975401',
            'secret-key: SK_70529d32acb17b3d3641a27325a9dd16b15a0a41d45',
            'Content-Type: application/json',
            'Cookie: laravel_session=eyJpdiI6IjBSSEpiMkpZY1c5a1wvWXBFdENGV1FRPT0iLCJ2YWx1ZSI6InNHR3ZzVTFNcUNNNnR4bXRxR0htN3A2YXZZTlVUbWFuOTJqRGNxcEIzQkl1Sm03bXVLTkxNcklOY3RIdmZJT1ZFdDVKN1Q2OWRxNGk5Yjg5OXIzT1wvZz09IiwibWFjIjoiOGFmNjczNDg2OWVmYzRiYzk1YjQ4ZTQ3MDA2MTY0MjMwYmQ3NGY2ZGJjM2FmNGM2YmYwYjY5MThlOTUzMDc0NiJ9'
        ];


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://sandbox.vtpass.com/api/pay',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($requestbody),
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return response()->json([
            "status" => true,
            "message" => json_decode($response, true),
        ]);
        // echo $response;

    }

}

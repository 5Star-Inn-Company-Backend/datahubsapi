<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    public function profile()
    {
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => Auth::user(),
        ], 200);
    }

    public function kyc(Request $request)
    {
        $input = $request->all();
        $rules = array(
            "bvn" => "required",
            "account_number" => "required",
            "bank_code" => "required",
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()]);
        }

        User::where('id',Auth::id())->update([
            'bvn' => $input['bvn'],
            'account_number' => $input['account_number'],
            'bank_code' => $input['bank_code'],
            'account_name' => 'YOUR ACCOUNT NAME'
        ]);

        return response()->json([
            'status' => true,
            'message' => "Noted successfully",
        ], 200);
    }

    function banklist()
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.paystack.co/bank',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . env("PAYSTACK_SECRET_KEY"),
                "Cache-Control: no-cache",
            ),
        ));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);

        curl_close($curl);

        $rep = json_decode($response, true);


        return response()->json(['success' => 1, 'message' => 'Fetch successfully', 'data' => $rep['data']]);
    }


    public function changePassword(Request $request)
    {
        $input = $request->all();
        $rules = array(
            "current" => "required",
            "new" => "required",
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()]);
        }

        if(!Hash::check($input['current'], Auth::user()->password)){
            return response()->json([
                'status' => false,
                'message' => "Current password is incorrect",
            ], 200);
        }

        User::where('id',Auth::id())->update([
            'password' => Hash::make($input['new'])
        ]);

        return response()->json([
            'status' => true,
            'message' => "Password changed successfully",
        ], 200);
    }

    public function changePin(Request $request)
    {
        $input = $request->all();
        $rules = array(
            "current" => "required|int|min:5",
            "new" => "required|int|min:5",
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()]);
        }

        if($input['current'] != Auth::user()->pin){
            return response()->json([
                'status' => false,
                'message' => "Current pin is incorrect",
            ], 200);
        }

        User::where('id',Auth::id())->update([
            'pin' => $input['new']
        ]);

        return response()->json([
            'status' => true,
            'message' => "Pin changed successfully",
        ], 200);
    }
}

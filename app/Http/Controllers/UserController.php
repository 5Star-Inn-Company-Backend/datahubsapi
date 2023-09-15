<?php

namespace App\Http\Controllers;

use App\Jobs\CreateWallets;
use App\Models\virtual_acct;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Jobs\CreateVirtualAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $input = $request->all();
        $rules = array(
            "firstname" => "required|string",
            "lastname" => "required|string",
            "address" => "required",
            "gender" => "required",
            "email" => "required|email|unique:users",
            "dob" => "required",
            "phone" => "required",
            "password" => "required|min:6|string",
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()]);
        }

        $user = new User();
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->address = $request->address;
        $user->phone = $request->phone;
        $user->gender = $request->gender;
        $user->dob = $request->dob;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        if($user->save()){

            CreateVirtualAccount::dispatch($user);
            CreateWallets::dispatch($user->id);

            return response()->json([
                'status' => true,
                "message" => "Registration successful",
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                "message" => "Unable to Register User",
            ],422);
        }


    }

    public function login(Request $request)
    {
        $input = $request->all();
        $rules = array(
            "email" => "required",
            "password" => "required",
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()]);
        }


        $password = $request->password;
        $email = $request->email;

        if(Auth::attempt(['email' => $email, 'password' => $password])){
            $user = $request->user();
            $tokenresult = $user->createToken('Personal Access Token');
            $token = $tokenresult->plainTextToken;
            $expires_at = Carbon::now()->addweeks(1);
            $vaccounts=virtual_acct::where([['user_id', Auth::id()], ['status', 'active']])->get();
            return response()->json([
                'status' => true,
                "data" => [
                    "user" => Auth::user(),
                    "vaccounts" => $vaccounts,
                    "access_token" => $token,
                    "token_type" => "Bearer",
                    "expires_at" => $expires_at,
                ]
            ],200);
        }else{
            return response()->json([
                "status" => false,
                "message" => "Wrong password or email address",
            ],401);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Jobs\CreateWallets;
use App\Jobs\MCDCreateVirtualAccount;
use App\Models\FundingConfig;
use App\Models\virtual_acct;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use App\Jobs\CreateVirtualAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
            "phone" => "required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|starts_with:0",
            "password" => "required|min:6|string",
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()],422);
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
        //        $user->save();

        if($user->save()){

            if(env('VIRTUAL_ACCOUNT_GENERATION_DOMAIN','test') == 'test'){
                CreateVirtualAccount::dispatch($user);
            }else{
                MCDCreateVirtualAccount::dispatch($user);
            }

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
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()],422);
        }


        $password = $request->password;
        $email = $request->email;

        if(Auth::attempt(['email' => $email, 'password' => $password])){
            $user = $request->user();
            $tokenresult = $user->createToken('Personal Access Token');
            $token = $tokenresult->plainTextToken;
            $expires_at = Carbon::now()->addweeks(1);
            $vaccounts=virtual_acct::where([['user_id', Auth::id()], ['status', 'active']])->get();
            $fc=FundingConfig::where('name','Bank Transfer')->first();

            return response()->json([
                'status' => true,
                "data" => [
                    "user" => Auth::user(),
                    "vaccounts" => $vaccounts,
                    "funding_config" => $fc,
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


    public function forgotPassword(Request $request)
    {

        $input = $request->all();
        $rules = array(
            "email" => "required|email"
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()]);
        }

        $user=User::where('email',$request->only('email'))->first();

        $token = Password::createToken(
            $user
        );

        Mail::to($request->only('email'))->send(new \App\Mail\PasswordReset($token));

        return response()->json(['status' => true, 'message' => 'Reset password link sent on your email id.']);

//      $status = Password::sendResetLink(
//            $request->only('email')
//        );
//
//        return $status === Password::RESET_LINK_SENT
//            ? response()->json(['status' => true, 'message' => 'Reset password link sent on your email id.'])
//            : response()->json(['status' => false, 'message' => 'Unable to send reset password link']);
    }


    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $input = $request->all();
        $rules = array(
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()]);
        }


        $credentials = $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );

        $response = $this->broker()->reset(
            $credentials, function ($user, $password) {
            $this->resetPassword($user, $password);
        }
        );

        return $response == Password::PASSWORD_RESET
            ? response()->json(['status' => true, 'message' => 'Password reset successful'])
            : response()->json(['status' => false, 'message' => 'Unable to reset password']);
    }


    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->forceFill([
            'password' => bcrypt($password),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));
    }



    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }


}

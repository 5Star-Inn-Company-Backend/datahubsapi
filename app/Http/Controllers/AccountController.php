<?php

namespace App\Http\Controllers;

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

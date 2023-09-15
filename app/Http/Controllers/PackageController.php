<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
{

    public function packages()
    {
        $list=Package::where('status', 1)->get();
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $list,
        ], 200);
    }

    public function currentPackage()
    {
        $list=Package::where('id', Auth::user()->package)->first();
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $list,
        ], 200);
    }


    public function changePackage(Request $request)
    {
        $input = $request->all();
        $rules = array(
            "new" => "required|int",
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()]);
        }

        $p=Package::where([['status', 1], ['id', $input['new']]])->first();

        if(!$p){
            return response()->json([
                'status' => false,
                'message' => "New package id is invalid",
            ], 200);
        }

        User::where('id',Auth::id())->update([
            'package' => $p->id
        ]);

        return response()->json([
            'status' => true,
            'message' => "Package changed successfully",
        ], 200);
    }

}

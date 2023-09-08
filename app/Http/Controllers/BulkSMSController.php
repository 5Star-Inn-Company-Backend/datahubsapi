<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BulkSMSController extends Controller
{
    public function send(Request $request)
    {
        $input = $request->all();
        $rules = array(
            "sender_name" => "required",
            "recipients" => "required|min:11",
            "message" => "required|min:3",
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()]);
        }

        return response()->json([
            'status' => true,
            'message' => "Message sending in progress",
        ], 200);
    }

}

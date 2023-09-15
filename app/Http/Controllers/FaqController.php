<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{
    function listAll(){
        $list=Faq::where('status', 1)->get();
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $list,
        ]);
    }

    public function likeFaq(Request $request)
    {
        $input = $request->all();
        $rules = array(
            "id" => "required|int",
            "type" => "required|in:like,dislike",
        );

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => implode(",", $validator->errors()->all()), 'error' => $validator->errors()->all()]);
        }

        $p=Faq::find($input['id']);

        if(!$p){
            return response()->json([
                'status' => false,
                'message' => "FAQ id is invalid",
            ], 200);
        }

        if ($input['type'] == 'like'){
            $p->like+=1;
        }

        if ($input['type'] == 'dislike'){
            $p->dislike+=1;
        }

        $p->save();

        return response()->json([
            'status' => true,
            'message' => "Noted successfully",
        ], 200);
    }

}

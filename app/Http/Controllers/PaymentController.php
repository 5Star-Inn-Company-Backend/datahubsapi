<?php

namespace App\Http\Controllers;

use App\Models\FundingConfig;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function atm(){

        $fetch=FundingConfig::where('status',1)->get();
        return response()->json([
            'status' => true,
            'message' => 'Fetched successfully',
            'data' => $fetch,
        ], 200);
    }
}


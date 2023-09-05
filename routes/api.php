<?php

use App\Http\Controllers\AirtimeController;
use App\Http\Controllers\CableTVController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('register', [UserController::class,'register']);
Route::post('login', [UserController::class,'login']);

Route::group(['middleware' => 'auth:sanctum'], function (){
    Route::get('list-airtime', [AirtimeController::class, 'listAll']);
    Route::post('purchase-airtime', [AirtimeController::class, 'purchaseairtime']);

    Route::get('list-tv', [CableTVController::class, 'tvlist']);
    Route::post('validate-tv', [CableTVController::class, 'tvvalidate']);
    Route::get('purchase-tv', [CableTVController::class, 'tvlist']);

    Route::get('allnetworks', [DataController::class, 'networks']);
    Route::get('datatypes', [DataController::class, 'datatypes']);
    Route::get('scratchcards', [DataController::class, 'scratchcards']);
    Route::get('electricity', [DataController::class, 'electricity']);

    Route::post('purchasedata', [DataController::class, 'purchasedata']);
    Route::post('cablesubscribe', [DataController::class, 'cablesubscribe']);
    Route::post('electricitypay', [DataController::class, 'electricitypay']);
    Route::post('scratchcard' , [DataController::class, 'scratchcard']);
});

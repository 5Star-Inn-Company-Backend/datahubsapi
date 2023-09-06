<?php

use App\Http\Controllers\AirtimeController;
use App\Http\Controllers\CableTVController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\ElectricityController;
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

    Route::get('list-data/{network}/{category}', [DataController::class, 'listAll']);
    Route::post('purchase-data', [DataController::class, 'purchasedata']);
    Route::get('types-data/{network}', [DataController::class, 'datatypes']);

    Route::get('list-tv/{network}', [CableTVController::class, 'tvlist']);
    Route::post('validate-tv', [CableTVController::class, 'tvvalidate']);
    Route::post('purchase-tv', [CableTVController::class, 'tvpurchase']);

    Route::get('list-education', [EducationController::class, 'listAll']);
    Route::post('purchase-education', [EducationController::class, 'purchase']);

    Route::get('list-electricity', [ElectricityController::class, 'listAll']);
    Route::post('validate-electricity', [ElectricityController::class, 'elecvalidate']);
    Route::post('purchase-electricity', [ElectricityController::class, 'purchase']);

    Route::post('purchase-electricity', [ElectricityController::class, 'purchase']);

});

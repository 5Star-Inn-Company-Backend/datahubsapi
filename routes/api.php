<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Airtime2CashController;
use App\Http\Controllers\AirtimeController;
use App\Http\Controllers\CableTVController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\ElectricityController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RechargeCardController;
use App\Http\Controllers\TransactionHistoryController;
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
    Route::get('list-airtime2cash', [Airtime2CashController::class, 'listAll']);
    Route::post('purchase-airtime2cash', [Airtime2CashController::class, 'purchase']);

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

    Route::get('list-rechargecard', [RechargeCardController::class, 'listAll']);
    Route::post('purchase-rechargecard', [RechargeCardController::class, 'purchase']);

    Route::post('bulk-sms-send', [\App\Http\Controllers\BulkSMSController::class, 'send']);

    Route::get('fund-wallet-atm', [PaymentController::class, 'atm']);

    Route::get('profile', [AccountController::class, 'profile']);
    Route::post('change-password', [AccountController::class, 'changePassword']);
    Route::post('change-pin', [AccountController::class, 'changePin']);

    Route::get('transaction-history', [TransactionHistoryController::class, 'all']);
    Route::get('data-history', [TransactionHistoryController::class, 'data']);
    Route::get('total-spent', [TransactionHistoryController::class, 'totalSpent']);
    Route::get('total-fund', [TransactionHistoryController::class, 'totalFund']);

});

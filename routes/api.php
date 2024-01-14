<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Airtime2CashController;
use App\Http\Controllers\AirtimeController;
use App\Http\Controllers\CableTVController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\ElectricityController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\MCDPaymentWebhookController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RechargeCardController;
use App\Http\Controllers\TransactionHistoryController;
use App\Http\Controllers\WalletController;
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
Route::post('reset-password-request', [UserController::class,'forgotPassword']);
Route::post('reset-password', [UserController::class,'reset']);

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
    Route::post('kyc', [AccountController::class, 'kyc']);
    Route::get('bank-list', [AccountController::class, 'banklist']);

    Route::get('transaction-history', [TransactionHistoryController::class, 'all']);
    Route::get('data-history', [TransactionHistoryController::class, 'data']);
    Route::get('total-spent', [TransactionHistoryController::class, 'totalSpent']);
    Route::get('total-fund', [TransactionHistoryController::class, 'totalFund']);

    Route::get('wallets', [WalletController::class, 'listAll']);
    Route::get('wallet-balance', [WalletController::class, 'wBalance']);
    Route::get('vaccts', [WalletController::class, 'listVAccts']);

    Route::get('packages', [PackageController::class, 'packages']);
    Route::get('current-package', [PackageController::class, 'currentPackage']);
    Route::post('change-package', [PackageController::class, 'changePackage']);

    Route::get('list-faqs', [FaqController::class, 'listAll']);
    Route::post('like-faq', [FaqController::class, 'likeFaq']);


    Route::post('hook/mcdpayment', [MCDPaymentWebhookController::class, 'index']);


});

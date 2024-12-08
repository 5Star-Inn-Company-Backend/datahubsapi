<?php

namespace App\Jobs;

use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Bus\Queueable;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class PayReferralBonusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    public int $user;
    public string $referral;
    public int $location;

    public function __construct($user,$referral,$location)
    {
        $this->user=$user;
        $this->referral=$referral;
        $this->location=$location;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user=User::find($this->user);
        $referral=$this->referral;

        if($referral!=null) {

            //0 = register; 1 = funding; 2 = transaction; 9 = any
            $location = $this->location;

            if ($user->referer_bonus_paid == 1) {
                return;
            }

            $settings = Setting::where("name", "referral_action")->first();
            $refAmount = Setting::where("name", "referral_bonus")->first();
            $amount = $refAmount->value;

            if ($settings->value == $location || $settings->value == 9) {

                $wallet = Wallet::where([['user_id', $referral], ['status', 1]])->first();

                $oBal = $wallet->balance;
                $wallet->balance += $amount;
                $wallet->save();

                $ref = env('BUSINESS_SHORT_NAME', "dt") . "rbonus" . time();


                Transaction::create([
                    "user_id" => $referral,
                    "title" => "Referral Bonus",
                    "amount" => $amount,
                    "commission" => 0,
                    "reference" => $ref,
                    "recipient" => $user->email,
                    "transaction_type" => "bonus",
                    "remark" => "Successful",
                    "type" => "credit",
                    "server" => "0",
                    "server_response" => "",
                    "prev_balance" => $oBal,
                    "new_balance" => $wallet->balance,
                ]);

                $user->referer_bonus_paid = 1;
                $user->save();
            }
        }
    }
}

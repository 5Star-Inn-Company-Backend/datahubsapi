<?php

namespace App\Jobs;

use App\Models\Wallet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateWallets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    public $user_id;
    public function __construct($user_id)
    {
        $this->user_id=$user_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Wallet::create([
            'user_id' => $this->user_id,
            'name' => 'wallet',
        ]);

        Wallet::create([
            'user_id' => $this->user_id,
            'name' => 'MTN SME DATA',
        ]);

        Wallet::create([
            'user_id' => $this->user_id,
            'name' => 'MTN CG DATA',
        ]);

        Wallet::create([
            'user_id' => $this->user_id,
            'name' => 'GLO CG DATA',
        ]);

        Wallet::create([
            'user_id' => $this->user_id,
            'name' => 'AIRTEL CG DATA',
        ]);

        Wallet::create([
            'user_id' => $this->user_id,
            'name' => '9MOBILE CG DATA',
        ]);
    }
}

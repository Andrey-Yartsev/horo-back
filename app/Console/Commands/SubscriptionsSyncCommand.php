<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SubscriptionsSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        User::whereNotNull('stripe_customer_id')->chunkById(10, function ($users) {
            foreach ($users as $user) {
                $user->syncSubscriptions();
                $user->cancelDuplicateSubscriptions();
            }
        });

        echo "Subscriptions synced.\n";

        return 0;
    }
}

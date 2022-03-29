<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use App\Notifications\UserWantsToUnsubscribeNotification;
use Illuminate\Support\Facades\Notification;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

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
        $user = User::first();
        $notification = new UserWantsToUnsubscribeNotification($user);
        Notification::route('mail', 'a3om77@gmail.com')->notify($notification);
        echo "Sent.\n";
        return 0;
    }
}

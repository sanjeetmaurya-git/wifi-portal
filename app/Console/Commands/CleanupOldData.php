<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WifiSession;
use App\Models\OtpRequest;
use App\Models\UsageLog;

class CleanupOldData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup old hotspot logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        // delete otp older than 2 days
        OtpRequest::where('created_at','<',now()->subDays(2))->delete();

        // delete session logs older than 90 days
        WifiSession::where('created_at','<',now()->subDays(90))->delete();

        // delete usage logs older than 30 days
        UsageLog::where('created_at','<',now()->subDays(30))->delete();

        $this->info('Old data cleaned successfully');

    }
}



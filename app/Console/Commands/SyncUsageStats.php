<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MikrotikService;
use App\Models\UsageLog;
use App\Models\WifiUser;


class SyncUsageStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-usage-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mikrotik = new MikrotikService();
        $activeUsers = $mikrotik->getActiveUsers();
        foreach($activeUsers as $user){
            $wifiUser = WifiUser::where('mobile',$user['user'])->first();
            if($wifiUser){
                UsageLog::create([
                    'user_id'=>$wifiUser->id,
                    'download_bytes'=>$user['bytes-in'],
                    'upload_bytes'=>$user['bytes-out'],
                    'recorded_at'=>now()
                ]);
            }
        }
    }
}

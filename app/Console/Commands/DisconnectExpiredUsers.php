<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WifiSession;
use App\Services\MikrotikService;

class DisconnectExpiredUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'disconnect:expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disconnect expired WiFi users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredSessions = WifiSession::whereNull('logout_at')
        ->whereRaw('DATE_ADD(login_at, INTERVAL duration_minutes MINUTE) < NOW()')
        ->get();

        $mikrotik = new MikrotikService();
        foreach($expiredSessions as $session){
            try{
                $mikrotik->removeHotspotUser(
                    $session->user->mobile
                );
            }catch(\Exception $e){
            
            }
            $session->logout_at = now();
            $session->save();
        }
    }
}

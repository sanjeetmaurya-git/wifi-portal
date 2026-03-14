<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WifiSession;
use App\Services\MikrotikService;
use Carbon\Carbon;

class ExpireWifiSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wifi:expire-sessions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disconnect expired WiFi sessions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Find sessions that haven't logged out but are past their expiry time
        $sessions = WifiSession::whereNull('logout_at')
            ->where('expires_at', '<', Carbon::now())
            ->get();

        if ($sessions->isEmpty()) {
            $this->info("No expired sessions found.");
            return;
        }

        $mikrotik = new MikrotikService();
        $count = 0;

        foreach ($sessions as $session) {
            try {
                // If the session has a user with a mobile number, remove them from Mikrotik
                if ($session->user && $session->user->mobile) {
                    $mikrotik->removeHotspotUser($session->user->mobile);
                }
            } catch (\Exception $e) {
                // Router might not be connected yet
                $this->error("Failed to disconnect from Mikrotik: " . $e->getMessage());
            }

            // Mark session as logged out
            $session->update([
                'logout_at' => Carbon::now()
            ]);
            
            $count++;
        }

        $this->info("Successfully disconnected {$count} expired session(s).");
    }
}

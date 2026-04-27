<?php

namespace App\Services;

use App\Models\RadCheck;
use App\Models\RadReply;
use App\Models\RadAcct;
use App\Services\MikrotikService;
use Illuminate\Support\Facades\Log;

class RadiusService
{
    /**
     * Sync a user to FreeRADIUS tables (Mobile based)
     */
    public function syncUser($username, $password, $plan)
    {
        $this->removeUser($username); // Clear old

        try {
            // Add Authentication
            RadCheck::create(['username' => $username, 'attribute' => 'Cleartext-Password', 'op' => ':=', 'value' => $password]);

            $this->addLimits($username, $plan);

            Log::info("[Radius] User $username synced.");
            return true;
        } catch (\Exception $e) {
            Log::error("[Radius] Sync failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sync MAC Address to RADIUS (For automatic login)
     */
    public function syncMac($mac, $plan)
    {
        if (empty($mac))
            return false;

        $this->removeUser($mac); // Clear old

        try {
            // MAC based login: Username = MAC, Password = 'password' (matches Winbox)
            RadCheck::create(['username' => $mac, 'attribute' => 'Cleartext-Password', 'op' => ':=', 'value' => 'password']);

            $this->addLimits($mac, $plan);

            Log::info("[Radius] MAC $mac synced.");

            // ⚡ THE "AUTO-KICK" MAGIC (SaaS Compatible)
            // We enqueue the command so the router can pull it even without port forwarding
            try {
                $mk = new MikrotikService();
                
                // Commands to clear everything for this MAC
                $mk->enqueueCommand("/ip hotspot active remove [find mac-address=\"$mac\"]");
                $mk->enqueueCommand("/ip hotspot host remove [find mac-address=\"$mac\"]");
                $mk->enqueueCommand("/ip hotspot cookie remove [find mac-address=\"$mac\"]");
                
                Log::info("[Radius] Host $mac removal enqueued for instant re-auth.");
            } catch (\Exception $e) {
                Log::warning("[Radius] Auto-kick enqueue failed: " . $e->getMessage());
            }

            return true;
        } catch (\Exception $e) {
            Log::error("[Radius] MAC sync failed: " . $e->getMessage());
            return false;
        }
    }

    private function addLimits($username, $plan)
    {
        $up = $plan->upload_limit ?: '2M';
        $down = $plan->download_limit ?: '5M';

        RadReply::create(['username' => $username, 'attribute' => 'Mikrotik-Rate-Limit', 'op' => ':=', 'value' => "{$up}/{$down}"]);

        if ($plan->duration_minutes > 0) {
            RadReply::create(['username' => $username, 'attribute' => 'Session-Timeout', 'op' => ':=', 'value' => $plan->duration_minutes * 60]);
        }
    }

    /**
     * Get total data usage for a username (Mobile or MAC)
     */
    public function getUsage($username)
    {
        $input  = RadAcct::where('username', $username)->sum('acctinputoctets');
        $output = RadAcct::where('username', $username)->sum('acctoutputoctets');
        
        return round(($input + $output) / (1024 * 1024), 2); // Convert to MB
    }

    /**
     * Remove user from RADIUS (e.g. on logout or manual disconnect)
     */
    public function removeUser($username)
    {
        RadCheck::where('username', $username)->delete();
        RadReply::where('username', $username)->delete();
        Log::info("[RadiusService] User $username removed from RADIUS.");
    }
}

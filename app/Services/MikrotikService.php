<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;
use RouterOS\Config;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\Router; //Step 17 
use App\Models\MikrotikCommand;
use Illuminate\Support\Facades\DB;

class MikrotikService
{
    /**
     * Helper to add a command to the queue for the MikroTik to pull.
     * This is the "SaaS Mode" which works WITHOUT Port Forwarding.
     */
    public function enqueueCommand(string $command, string $routerId = null): bool
    {
        try {
            MikrotikCommand::create([
                'router_id' => $routerId,
                'command'   => $command,
                'status'    => 'pending'
            ]);
            Log::info("[MikroTik Queue] Command enqueued: $command");
            return true;
        } catch (Exception $e) {
            Log::error("[MikroTik Queue] Failed to enqueue command: " . $e->getMessage());
            return false;
        }
    }
    private function isDevelopmentMode(): bool
    {
        return !env('MIKROTIK_CONNECTED', false);
    }

    private function getClient(): Client
    {
        $router = Router::where('active', true)->first();

        if (!$router && !$this->isDevelopmentMode()) {
            throw new Exception("No active router found in database.");
        }

        $config = new Config([
            'host' => $router ? $router->ip_address : env('MIKROTIK_HOST', '192.168.88.1'),
            'user' => $router ? $router->username : env('MIKROTIK_USER', 'apiuser'),
            'pass' => $router ? $router->password : env('MIKROTIK_PASS', 'password'),
            'port' => $router ? (int) $router->port : (int) env('MIKROTIK_PORT', 8728),
            'timeout' => 2, // ⚡ Fail fast in 2 seconds instead of 5
        ]);

        return new Client($config);
    }

    /**
     * Add or Update Hotspot User on MikroTik
     */
    public function addHotspotUser($mobile, $password, $profile = 'default', $uptimeLimit = null, $bytesLimit = null, $rateLimit = null, $mac = null): bool
    {
        if ($this->isDevelopmentMode()) {
            Log::info("[MikroTik MOCK] User Added/Updated → $mobile");
            return true;
        }

        try {
            $client = $this->getClient();

            // Check if profile exists first if it's not default
            if ($profile !== 'default') {
                $pQ = new Query('/ip/hotspot/user/profile/print');
                $pQ->where('name', $profile);
                if (empty($client->query($pQ)->read())) {
                    $addP = new Query('/ip/hotspot/user/profile/add');
                    $addP->equal('name', $profile);
                    if ($rateLimit) $addP->equal('rate-limit', $rateLimit);
                    $client->query($addP)->read();
                }
            }

            // 📡 Method 1: Real-time API Update
            $q = new Query('/ip/hotspot/user/print');
            $q->where('name', $mobile);
            $users = $client->query($q)->read();

            if (!empty($users)) {
                $update = new Query('/ip/hotspot/user/set');
                $update->equal('.id', $users[0]['.id']);
                $update->equal('password', $password);
                $update->equal('profile', $profile);
                if ($uptimeLimit) $update->equal('limit-uptime', $uptimeLimit);
                if ($bytesLimit)  $update->equal('limit-bytes-total', $bytesLimit);
                if ($rateLimit)   $update->equal('rate-limit', $rateLimit);
                if ($mac)         $update->equal('mac-address', $mac);
                $client->query($update)->read();
            } else {
                $add = new Query('/ip/hotspot/user/add');
                $add->equal('name', $mobile);
                $add->equal('password', $password);
                $add->equal('profile', $profile);
                if ($uptimeLimit) $add->equal('limit-uptime', $uptimeLimit);
                if ($bytesLimit)  $add->equal('limit-bytes-total', $bytesLimit);
                if ($rateLimit)   $add->equal('rate-limit', $rateLimit);
                if ($mac)         $add->equal('mac-address', $mac);
                $client->query($add)->read();
            }

            // kick active session to apply new limits
            $this->removeActiveSession($mobile);
            Log::info("[MikroTik API] User $mobile activated in real-time.");
            return true;

        } catch (\Exception $e) {
            // 📡 Method 2: API blocked -> Enqueue for Polling/Scheduler execution
            Log::warning("[MikroTik SaaS] API blocked. Enqueuing activation command for $mobile.");
            
            $cli = ":if ([:len [/ip hotspot user find name=\"$mobile\"]] > 0) do={ " .
                   "/ip hotspot user set [find name=\"$mobile\"] password=\"$password\" profile=\"$profile\"" .
                   ($uptimeLimit ? " limit-uptime=$uptimeLimit" : "") .
                   ($bytesLimit ? " limit-bytes-total=$bytesLimit" : "") .
                   " } else={ " .
                   "/ip hotspot user add name=\"$mobile\" password=\"$password\" profile=\"$profile\"" .
                   ($uptimeLimit ? " limit-uptime=$uptimeLimit" : "") .
                   ($bytesLimit ? " limit-bytes-total=$bytesLimit" : "") .
                   " }; " .
                   "/ip hotspot active remove [find user=\"$mobile\"];";
            
            $this->enqueueCommand($cli);
            return true;
        }
    }

    /**
     * ⚡ DEFINITIVE FIX: Authorize a device directly on the router.
     * Queues RouterOS commands that run ON THE ROUTER via the scheduler.
     * NO browser interaction. Works on ALL phones. No "Not Secure" warning ever.
     *
     * Cmd 1: /ip hotspot host set ... authorized=yes  (RouterOS 7.x — instant access)
     * Cmd 2: /ip hotspot ip-binding add type=bypassed  (RouterOS 6.x+7.x fallback)
     */
    public function authorizeHost(string $mac, string $mobile): bool
    {
        if ($this->isDevelopmentMode()) {
            Log::info("[MikroTik MOCK] authorizeHost → MAC: $mac, User: $mobile");
            return true;
        }

        // Try direct API first
        try {
            $client = $this->getClient();
            // RouterOS 7.x: set host as authorized
            $q = new Query('/ip/hotspot/host/print');
            $q->where('mac-address', $mac);
            $hosts = $client->query($q)->read();
            if (!empty($hosts)) {
                $set = new Query('/ip/hotspot/host/set');
                $set->equal('.id', $hosts[0]['.id']);
                $set->equal('authorized', 'yes');
                $client->query($set)->read();
                Log::info("[MikroTik API] Host $mac authorized via API.");
                return true;
            }
        } catch (\Exception $e) {
            Log::warning("[MikroTik SaaS] API blocked. Queuing host authorization for $mac.");
        }

        // ── Queue commands for the scheduler (runs every 5s) ─────────────────
        if (empty($mac)) {
            Log::error("[MikroTik] Cannot authorize host: MAC is empty!");
            return false;
        }

        // Cmd 0: Heartbeat Log (so user can see it in Winbox > Log)
        $cmd0 = "/log info \"PMWANI: Processing activation for $mobile (MAC: $mac)\";";

        // Cmd 1: Force-authorize the host in the local host table (Instant Access)
        // This is THE magic command for silent login in RouterOS 6/7.
        $cmd1 = ":if ([:len [/ip hotspot host find mac-address=\"$mac\"]] > 0) do={ " .
                "/ip hotspot host set [find mac-address=\"$mac\"] authorized=yes " .
                "} else={ /log warning \"PMWANI: Host $mac not found in table yet - waiting for connection\" };";

        // Cmd 2: IP Binding (Regular type with MAC)
        // We use type=regular so data tracking WORKS. 
        // type=bypassed gives internet but kills tracking.
        $cmd2 = ":if ([:len [/ip hotspot ip-binding find mac-address=\"$mac\"]] > 0) do={ " .
                "/ip hotspot ip-binding set [find mac-address=\"$mac\"] type=regular comment=\"$mobile\" " .
                "} else={ /ip hotspot ip-binding add mac-address=\"$mac\" type=regular comment=\"$mobile\" };";

        $this->enqueueCommand($cmd0);
        $this->enqueueCommand($cmd1);
        $this->enqueueCommand($cmd2);

        Log::info("[MikroTik Queue] authorizeHost commands sent for MAC: $mac (User: $mobile)");
        return true;
    }

    /**
     * Silent Activation: Add MAC to IP Binding (Bypassed)
     * This skips the login form entirely!
     */
    public function addIpBinding(string $mac, string $comment = ''): bool
    {
        if ($this->isDevelopmentMode()) {
            Log::info("[MikroTik MOCK] IP Binding Added → MAC: $mac");
            return true;
        }

        try {
            $client = $this->getClient();

            // 1. Check if already exists
            $q = new Query('/ip/hotspot/ip-binding/print');
            $q->where('mac-address', $mac);
            $existing = $client->query($q)->read();

            if (!empty($existing)) {
                $set = new Query('/ip/hotspot/ip-binding/set');
                $set->equal('.id', $existing[0]['.id']);
                $set->equal('type', 'bypassed');
                $set->equal('comment', $comment);
                $client->query($set)->read();
            } else {
                $add = new Query('/ip/hotspot/ip-binding/add');
                $add->equal('mac-address', $mac);
                $add->equal('type', 'bypassed');
                $add->equal('comment', $comment);
                $client->query($add)->read();
            }

            Log::info("[MikroTik API] MAC $mac bypassed globally.");
            return true;
        } catch (Exception $e) {
            Log::warning("[MikroTik SaaS] API blocked. Enqueuing IP Binding for $mac.");
            $cli = ":if ([:len [/ip hotspot ip-binding find mac-address=\"$mac\"]] > 0) do={ " .
                   "/ip hotspot ip-binding set [find mac-address=\"$mac\"] type=bypassed comment=\"$comment\" " .
                   " } else={ /ip hotspot ip-binding add mac-address=\"$mac\" type=bypassed comment=\"$comment\" };";
            return $this->enqueueCommand($cli);
        }
    }

    /**
     * Remove MAC from IP Binding
     */
    public function removeIpBinding(string $mac): bool
    {
        if ($this->isDevelopmentMode()) return true;

        try {
            $client = $this->getClient();
            $q = new Query('/ip/hotspot/ip-binding/print');
            $q->where('mac-address', $mac);
            $existing = $client->query($q)->read();

            foreach ($existing as $item) {
                $rem = new Query('/ip/hotspot/ip-binding/remove');
                $rem->equal('.id', $item['.id']);
                $client->query($rem)->read();
            }
            return true;
        } catch (Exception $e) {
             $cli = "/ip hotspot ip-binding remove [find mac-address=\"$mac\"];";
             return $this->enqueueCommand($cli);
        }
    }

    public function removeHotspotUser(string $mobile): bool
    {
        // ✅ Development Mode
        if ($this->isDevelopmentMode()) {
            Log::info("[MikroTik MOCK] User removed → Mobile: $mobile");
            return true;
        }

        try {
            $client = $this->getClient();

            // First find the user ID
            $query = new Query('/ip/hotspot/user/print');
            $query->where('name', $mobile);
            $response = $client->query($query)->read();

            if (empty($response)) {
                Log::warning("[MikroTik] User not found → $mobile");
                return false;
            }

            $userId = $response[0]['.id'];

            // Then remove by ID
            $removeQuery = new Query('/ip/hotspot/user/remove');
            $removeQuery->equal('.id', $userId);
            $client->query($removeQuery)->read();

            Log::info("[MikroTik] User removed successfully → $mobile");
            return true;

        } catch (Exception $e) {
            Log::error("[MikroTik ERROR] Failed to remove user $mobile → " . $e->getMessage());
            return false;
        }
    }

    /**
     * 🔥 REMOVE ACTIVE SESSION (VERY IMPORTANT)
     * This kicks the user out of the 'Active' tab so they can log in fresh.
     */
    public function removeActiveSession(string $mobile): bool
    {
        if ($this->isDevelopmentMode()) return true;

        try {
            $client = $this->getClient();
            $query = new Query('/ip/hotspot/active/print');
            $query->where('user', $mobile);
            $active = $client->query($query)->read();

            foreach ($active as $session) {
                $remove = new Query('/ip/hotspot/active/remove');
                $remove->equal('.id', $session['.id']);
                $client->query($remove)->read();
            }
            Log::info("[MikroTik] Kicked active sessions for $mobile");
            return true;
        } catch (Exception $e) {
            Log::error("[MikroTik] Error removing active session: " . $e->getMessage());
            return false;
        }
    }

    public function userExists(string $mobile): bool
    {
        if ($this->isDevelopmentMode()) {
            Log::info("[MikroTik MOCK] Checking user → $mobile");
            return false;
        }

        try {
            $client = $this->getClient();
            $query = new Query('/ip/hotspot/user/print');
            $query->where('name', $mobile);
            $response = $client->query($query)->read();

            return !empty($response);

        } catch (Exception $e) {
            Log::error("[MikroTik ERROR] userExists check failed → " . $e->getMessage());
            return false;
        }
    }

    public function getActiveUsers()
    {
        if ($this->isDevelopmentMode()) {
            return [];
        }

        try {
            $client = $this->getClient();
            $query = new Query('/ip/hotspot/active/print');
            return $client->query($query)->read();
        } catch (Exception $e) {
            Log::error("[MikroTik ERROR] getActiveUsers failed → " . $e->getMessage());
            return [];
        }
    }

    public function isUserActive(string $mobile): bool
    {
        if ($this->isDevelopmentMode()) {
            return true;
        }

        try {
            $client = $this->getClient();
            $query = new Query('/ip/hotspot/active/print');
            $query->where('user', $mobile);
            $response = $client->query($query)->read();

            return !empty($response);
        } catch (Exception $e) {
            Log::error("[MikroTik ERROR] isUserActive check failed → " . $e->getMessage());
            return false;
        }
    }

    // Connect to router for status check
    public function connect()
    {
        if ($this->isDevelopmentMode()) {
            // Return a dummy client or just null if we handle it in controller
            return true;
        }

        return $this->getClient();
    }

    /**
     * 🔄 RESET byte counters for a single user (used by midnight daily-data reset cron)
     */
    public function resetUserCounters(string $mobile): bool
    {
        if ($this->isDevelopmentMode()) {
            Log::info("[MikroTik MOCK] Reset counters for $mobile");
            return true;
        }

        try {
            $client = $this->getClient();

            // Find user ID
            $q = new Query('/ip/hotspot/user/print');
            $q->where('name', $mobile);
            $users = $client->query($q)->read();

            if (empty($users)) {
                Log::warning("[MikroTik] resetUserCounters: user not found → $mobile");
                return false;
            }

            $userId = $users[0]['.id'];
            $reset  = new Query('/ip/hotspot/user/reset-counters');
            $reset->equal('.id', $userId);
            $client->query($reset)->read();

            Log::info("[MikroTik] Counters reset for $mobile");
            return true;
        } catch (Exception $e) {
            Log::error("[MikroTik ERROR] resetUserCounters failed for $mobile → " . $e->getMessage());
            return false;
        }
    }

    /**
     * 🔥 WIPE ALL ACTIVE SESSIONS
     * Forcefully kicks EVERYONE out of the hotspot.
     */
    public function killAllSessions(): bool
    {
        if ($this->isDevelopmentMode()) return true;

        try {
            $client = $this->getClient();
            $query = new Query('/ip/hotspot/active/print');
            $active = $client->query($query)->read();

            foreach ($active as $session) {
                $remove = new Query('/ip/hotspot/active/remove');
                $remove->equal('.id', $session['.id']);
                $client->query($remove)->read();
                Log::info("[MikroTik] Force closed session for: " . ($session['user'] ?? 'unknown'));
            }
            
            // Also clear hosts to be sure
            $hostQuery = new Query('/ip/hotspot/host/print');
            $hosts = $client->query($hostQuery)->read();
            foreach ($hosts as $h) {
                if (($h['authorized'] ?? 'false') === 'true' || ($h['bypassed'] ?? 'false') === 'true') {
                    $removeHost = new Query('/ip/hotspot/host/remove');
                    $removeHost->equal('.id', $h['.id']);
                    $client->query($removeHost)->read();
                }
            }

            return true;
        } catch (Exception $e) {
            Log::error("[MikroTik] Error wiping all sessions: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Force-kick a host from the MikroTik (Triggers immediate RADIUS re-auth)
     */
    public function removeHost($mac): bool
    {
        if ($this->isDevelopmentMode()) {
            Log::info("[MikroTik MOCK] Host Removed → $mac");
            return true;
        }

        try {
            $client = $this->getClient();

            // 1. Remove from Active Users (Force logout)
            $qActive = new Query('/ip/hotspot/active/print');
            $qActive->where('mac-address', $mac);
            $active = $client->query($qActive)->read();
            foreach ($active as $a) {
                $remActive = new Query('/ip/hotspot/active/remove');
                $remActive->equal('.id', $a['.id']);
                $client->query($remActive)->read();
            }

            // 2. Remove from Hosts table (Clear the 'Unauthorized' cache)
            $qHost = new Query('/ip/hotspot/host/print');
            $qHost->where('mac-address', $mac);
            $hosts = $client->query($qHost)->read();
            foreach ($hosts as $h) {
                $remHost = new Query('/ip/hotspot/host/remove');
                $remHost->equal('.id', $h['.id']);
                $client->query($remHost)->read();
            }

            // 3. Clear Cookies for this user
            $qCookie = new Query('/ip/hotspot/cookie/print');
            $qCookie->where('mac-address', $mac);
            $cookies = $client->query($qCookie)->read();
            foreach ($cookies as $c) {
                $remCookie = new Query('/ip/hotspot/cookie/remove');
                $remCookie->equal('.id', $c['.id']);
                $client->query($remCookie)->read();
            }

            return true;
        } catch (Exception $e) {
            Log::warning("[MikroTik] Failed to remove host $mac: " . $e->getMessage());
            return false;
        }
    }
}

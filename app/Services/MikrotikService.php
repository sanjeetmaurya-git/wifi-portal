<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;
use RouterOS\Config;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\Router; //Step 17 

class MikrotikService
{
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

    public function addHotspotUser(
        string $mobile,
        string $password,
        string $profile = 'default',
        string $uptimeLimit = null,
        string $bytesLimit = null,
        string $rateLimit = null,
        string $mac = null
    ): bool {
        // ✅ Development Mode — No Router Needed
        if ($this->isDevelopmentMode()) {
            Log::info("[MikroTik MOCK] User added/updated → Mobile: $mobile | Profile: $profile | Limits: $uptimeLimit / $bytesLimit");
            return true;
        }

        try {
            $client = $this->getClient();

            // 🛠️ DYNAMIC PROFILE ENFORCEMENT
            // We create a specific profile for this exact data limit if it's not 'default'
            if ($profile !== 'default') {
                $profQuery = new Query('/ip/hotspot/user/profile/print');
                $profQuery->where('name', $profile);
                $existingProf = $client->query($profQuery)->read();

                $profParams = [
                    'name' => $profile,
                    // 'transparent-proxy' => 'yes'
                    'status-autorefresh' => '1m'
                ];
                // ❌ limit-bytes-total is NOT valid on profiles — only on users (line 110 below handles this)
                if ($uptimeLimit)
                    $profParams['session-timeout'] = $uptimeLimit;
                if ($rateLimit)
                    $profParams['rate-limit'] = $rateLimit;

                if (empty($existingProf)) {
                    $addProf = new Query('/ip/hotspot/user/profile/add');
                    foreach ($profParams as $k => $v)
                        $addProf->equal($k, (string) $v);
                    $response = $client->query($addProf)->read();
                    Log::info("[MikroTik] Add Profile Response: " . json_encode($response));
                    if (isset($response['after']['message'])) {
                        Log::error("MikroTik Profile Add Error: " . $response['after']['message']);
                    }
                } else {
                    $setProf = new Query('/ip/hotspot/user/profile/set');
                    $setProf->equal('.id', $existingProf[0]['.id']);
                    foreach ($profParams as $k => $v)
                        $setProf->equal($k, (string) $v);
                    $response = $client->query($setProf)->read();
                    Log::info("[MikroTik] Set Profile Response: " . json_encode($response));
                    if (isset($response['after']['message'])) {
                        Log::error("MikroTik Profile Set Error: " . $response['after']['message']);
                    }
                }
            }

            // 🔍 Check if user already exists
            $existsQuery = new Query('/ip/hotspot/user/print');
            $existsQuery->where('name', $mobile);
            $existing = $client->query($existsQuery)->read();

            // Prepare common parameters for the USER
            $params = [
                'password' => $password,
                'profile'  => $profile
            ];
 
            // Individual user-level limits
            if ($uptimeLimit) $params['limit-uptime'] = (string)$uptimeLimit;
            if ($bytesLimit)  $params['limit-bytes-total'] = (string)$bytesLimit;
            
            // ❌ Removed rate-limit from here because MikroTik rejects it on /ip/hotspot/user/add
            // Speed limits MUST be handled via the Profile.

            if (!empty($existing)) {
                // 🔄 Update existing user
                $userId = $existing[0]['.id'];
                $updateQuery = new Query('/ip/hotspot/user/set');
                $updateQuery->equal('.id', $userId);

                foreach ($params as $key => $value) {
                    $updateQuery->equal($key, (string) $value);
                }

                $response = $client->query($updateQuery)->read();
                if (isset($response['after']['message'])) {
                    throw new Exception("MikroTik User Update Failed: " . $response['after']['message']);
                }
                Log::info("[MikroTik] User updated successfully → $mobile");

                // ✨ CRITICAL: Reset user counters
                $client->query((new Query('/ip/hotspot/user/reset-counters'))->equal('.id', $userId))->read();

            } else {
                // ✨ Add new user
                $addQuery = new Query('/ip/hotspot/user/add');
                $addQuery->equal('name', $mobile);

                foreach ($params as $key => $value) {
                    $addQuery->equal($key, (string) $value);
                }

                $response = $client->query($addQuery)->read();
                if (isset($response['after']['message'])) {
                    throw new Exception("MikroTik User Creation Failed: " . $response['after']['message']);
                }
                Log::info("[MikroTik] User created successfully → $mobile");
            }

            // ✨ CRITICAL: Kick any existing active session so the new limits apply immediately on the fresh login.
            $activeQuery = new Query('/ip/hotspot/active/print');
            $activeQuery->where('user', $mobile);
            $activeSessions = $client->query($activeQuery)->read();

            foreach ($activeSessions as $active) {
                $removeActive = new Query('/ip/hotspot/active/remove');
                $removeActive->equal('.id', $active['.id']);
                $client->query($removeActive)->read();
            }

            // 🧹 CLEANUP: Remove Host entry to force a fresh authorization
            if ($mac && $mac !== 'unknown') {
                $hostQuery = new Query('/ip/hotspot/host/print');
                $hostQuery->where('mac-address', $mac);
                $hosts = $client->query($hostQuery)->read();
                foreach ($hosts as $host) {
                    $removeHost = new Query('/ip/hotspot/host/remove');
                    $removeHost->equal('.id', $host['.id']);
                    $client->query($removeHost)->read();
                }
            }

            return true;

        } catch (Exception $e) {
            Log::error("[MikroTik ERROR] Transaction failed for $mobile → " . $e->getMessage());
            return false;
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
}
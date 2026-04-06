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
        ]);

        return new Client($config);
    }

    public function addHotspotUser(string $mobile, string $password, string $profile = 'default', string $rateLimit = null): bool
    {
        // ✅ Development Mode — No Router Needed
        if ($this->isDevelopmentMode()) {
            Log::info("[MikroTik MOCK] User added/updated → Mobile: $mobile | Profile: $profile");
            return true;
        }

        try {
            $client = $this->getClient();

            // 🔍 Check if user already exists
            $existsQuery = new Query('/ip/hotspot/user/print');
            $existsQuery->where('name', $mobile);
            $existing = $client->query($existsQuery)->read();

            if (!empty($existing)) {
                // 🔄 Update existing user
                $userId = $existing[0]['.id'];
                $updateQuery = new Query('/ip/hotspot/user/set');
                $updateQuery->equal('.id', $userId);
                $updateQuery->equal('password', $password);
                $updateQuery->equal('profile', $profile);
                if ($rateLimit) {
                    $updateQuery->equal('rate-limit', $rateLimit);
                }
                $client->query($updateQuery)->read();
                Log::info("[MikroTik] User updated successfully → $mobile");
            } else {
                // ✨ Add new user
                $addQuery = new Query('/ip/hotspot/user/add');
                $addQuery->equal('name', $mobile);
                $addQuery->equal('password', $password);
                $addQuery->equal('profile', $profile);
                if ($rateLimit) {
                    $addQuery->equal('rate-limit', $rateLimit);
                }
                $client->query($addQuery)->read();
                Log::info("[MikroTik] User created successfully → $mobile");
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

    public function userExists(string $mobile): bool
    {
        if ($this->isDevelopmentMode()) {
            Log::info("[MikroTik MOCK] Checking user → $mobile");
            return false;
        }

        try {
            $client  = $this->getClient();
            $query   = new Query('/ip/hotspot/user/print');
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
}
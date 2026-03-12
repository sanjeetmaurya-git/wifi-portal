<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;
use RouterOS\Config;
use Exception;
use Illuminate\Support\Facades\Log;

class MikrotikService
{
    private function isDevelopmentMode(): bool
    {
        return !env('MIKROTIK_CONNECTED', false);
    }

    private function getClient(): Client
    {
        $config = new Config([
            'host' => env('MIKROTIK_HOST', '192.168.88.1'),
            'user' => env('MIKROTIK_USER', 'apiuser'),
            'pass' => env('MIKROTIK_PASS', 'password'),
            'port' => (int) env('MIKROTIK_PORT', 8728),
        ]);

        return new Client($config);
    }

    public function addHotspotUser(string $mobile, string $password, string $profile = 'default'): bool
    {
        // ✅ Development Mode — No Router Needed
        if ($this->isDevelopmentMode()) {
            Log::info("[MikroTik MOCK] User added → Mobile: $mobile | Profile: $profile");
            return true;
        }

        try {
            $client = $this->getClient();

            $query = new Query('/ip/hotspot/user/add');
            $query->equal('name',    $mobile);
            $query->equal('password', $password);
            $query->equal('profile',  $profile);

            $client->query($query)->read();

            Log::info("[MikroTik] User created successfully → $mobile");
            return true;

        } catch (Exception $e) {
            Log::error("[MikroTik ERROR] Failed to add user $mobile → " . $e->getMessage());
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

        $client = $this->getClient();
        $query = new Query('/ip/hotspot/active/print');
        return $client->query($query)->read();
    }
}
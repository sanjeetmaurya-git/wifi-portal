<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RadiusService
{
    /**
     * Sync user credentials and limits to FreeRADIUS tables.
     * 
     * @param string $username Usually the Mobile Number
     * @param string $password Usually the Mobile Number
     * @param string|null $speedLimit e.g., "1M/5M"
     * @param int|null $dataLimitMB e.g., 1024 for 1GB
     * @param int|null $sessionTimeout Seconds
     */
    public function syncUser($username, $password, $speedLimit = null, $dataLimitMB = null, $sessionTimeout = null)
    {
        try {
            // 1. Authentication (radcheck)
            DB::table('radcheck')->updateOrInsert(
                ['username' => $username, 'attribute' => 'Cleartext-Password'],
                ['op' => ':=', 'value' => $password]
            );

            // 2. Speed Limit (radreply)
            if ($speedLimit) {
                DB::table('radreply')->updateOrInsert(
                    ['username' => $username, 'attribute' => 'Mikrotik-Rate-Limit'],
                    ['op' => '=', 'value' => $speedLimit]
                );
            }

            // 3. Data Limit (radreply) - Custom attribute for SQL counters
            if (!is_null($dataLimitMB)) {
                DB::table('radreply')->updateOrInsert(
                    ['username' => $username, 'attribute' => 'Max-All-MB'],
                    ['op' => ':=', 'value' => $dataLimitMB]
                );
            }

            // 4. Time Limit (radreply)
            if ($sessionTimeout) {
                DB::table('radreply')->updateOrInsert(
                    ['username' => $username, 'attribute' => 'Session-Timeout'],
                    ['op' => ':=', 'value' => $sessionTimeout]
                );
            }

            Log::info("[RADIUS] Synced user $username with limits.");
            return true;
        } catch (\Exception $e) {
            Log::error("[RADIUS] Sync failed for $username: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Summary of data usage for a user.
     */
    public function getUsage($username)
    {
        try {
            $daily = $this->calculateUsage($username, Carbon::today());
            $monthly = $this->calculateUsage($username, Carbon::now()->startOfMonth());
            $total = $this->calculateUsage($username);

            return [
                'daily' => $daily,
                'monthly' => $monthly,
                'total' => $total,
            ];
        } catch (\Exception $e) {
            Log::error("[RADIUS] Usage calc failed for $username: " . $e->getMessage());
            return null;
        }
    }

    private function calculateUsage($username, $since = null)
    {
        $query = DB::table('radacct')->where('username', $username);
        
        if ($since) {
            $query->where('acctstarttime', '>=', $since);
        }

        $input = $query->sum('acctinputoctets') ?? 0;
        $output = $query->sum('acctoutputoctets') ?? 0;
        $totalBytes = $input + $output;

        return [
            'bytes' => $totalBytes,
            'mb' => round($totalBytes / (1024 * 1024), 2),
            'gb' => round($totalBytes / (1024 * 1024 * 1024), 2),
        ];
    }

    /**
     * Remove user from RADIUS
     */
    public function removeUser($username)
    {
        DB::table('radcheck')->where('username', $username)->delete();
        DB::table('radreply')->where('username', $username)->delete();
        Log::info("[RADIUS] Removed user $username.");
    }
}

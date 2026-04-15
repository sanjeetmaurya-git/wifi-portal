<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MikrotikService;
use App\Models\WifiSession;
use App\Models\WifiUser;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncUsageStats extends Command
{
    protected $signature   = 'wifi:sync-usage';
    protected $description = 'Sync live bytes-in + bytes-out from MikroTik into wifi_sessions.used_mb (runs every 5 min)';

    public function handle(): void
    {
        $mikrotik    = new MikrotikService();
        $activeUsers = $mikrotik->getActiveUsers(); // returns [] in dev mode

        if (empty($activeUsers)) {
            $this->info('[SyncUsage] No active users from MikroTik (dev mode or empty hotspot).');
            return;
        }

        $synced  = 0;
        $skipped = 0;

        foreach ($activeUsers as $row) {
            $mobile = $row['user'] ?? null;
            if (!$mobile) { $skipped++; continue; }

            $bytesTotal = ((int) ($row['bytes-in']  ?? 0))
                        + ((int) ($row['bytes-out'] ?? 0));
            $mb = round($bytesTotal / 1024 / 1024, 2);

            // Find the active session for this user
            $user = WifiUser::where('mobile', $mobile)->first();
            if (!$user) { $skipped++; continue; }

            $session = WifiSession::where('user_id', $user->id)
                ->where('expires_at', '>', Carbon::now())
                ->whereNull('logout_at')
                ->latest()
                ->first();

            if (!$session) { $skipped++; continue; }

            $session->update(['used_mb' => $mb]);
            Log::debug("[SyncUsage] $mobile → {$mb} MB used");
            $synced++;
        }

        $this->info("[SyncUsage] Done — Synced: $synced  Skipped: $skipped");
        Log::info("[SyncUsage] Sync complete — Synced: $synced, Skipped: $skipped");
    }
}

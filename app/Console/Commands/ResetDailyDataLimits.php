<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WifiSession;
use App\Services\MikrotikService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ResetDailyDataLimits extends Command
{
    protected $signature   = 'wifi:reset-daily-data';
    protected $description = 'Reset MikroTik byte counters for all active daily-plan users (runs at midnight)';

    public function handle(): void
    {
        $this->info('[' . now() . '] Starting daily data reset...');

        // Find all active sessions that belong to a daily plan
        $sessions = WifiSession::with('user', 'plan')
            ->where('expires_at', '>', Carbon::now())
            ->whereNull('logout_at')
            ->whereHas('plan', fn ($q) => $q->where('plan_type', 'daily'))
            ->get();

        if ($sessions->isEmpty()) {
            $this->info('No active daily-plan sessions found. Nothing to reset.');
            return;
        }

        $mikrotik = new MikrotikService();
        $reset    = 0;
        $failed   = 0;

        foreach ($sessions as $session) {
            $mobile = $session->user->mobile ?? null;
            if (!$mobile) continue;

            try {
                // Reset MikroTik byte counters for this user so the daily allowance refreshes
                $mikrotik->resetUserCounters($mobile);

                // Also reset the local used_mb tracker for today
                $session->update(['used_mb' => 0]);

                Log::info("[DailyReset] Reset counters for user: $mobile (session #{$session->id})");
                $reset++;
            } catch (\Exception $e) {
                Log::error("[DailyReset] Failed to reset for user: $mobile — " . $e->getMessage());
                $failed++;
            }
        }

        $this->info("Daily reset complete. ✅ Reset: $reset  ❌ Failed: $failed");
        Log::info("[DailyReset] Complete — Reset: $reset, Failed: $failed");
    }
}

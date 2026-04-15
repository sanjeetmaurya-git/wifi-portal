<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\MikrotikService;
use App\Models\WifiSession;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ✅ Expire + disconnect sessions every minute
Schedule::command('wifi:expire-sessions')->everyMinute();

// ✅ Daily cleanup of old logs
Schedule::command('cleanup:data')->daily();

// ✅ Step 29 — Reset MikroTik byte counters for daily-plan users at midnight
Schedule::command('wifi:reset-daily-data')->dailyAt('00:00');

// ✅ Step 34 — Sync live usage stats from MikroTik every 5 minutes
Schedule::command('wifi:sync-usage')->everyFiveMinutes();
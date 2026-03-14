<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\MikrotikService;
use App\Models\WifiSession;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('wifi:expire-sessions')->everyMinute();



// ✅ NEW — daily cleanup of old logs
Schedule::command('cleanup:data')->daily();
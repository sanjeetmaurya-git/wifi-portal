<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\MikrotikService;
use App\Models\WifiSession;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {

$expiredSessions = WifiSession::whereNull('logout_at')
->whereRaw('DATE_ADD(login_at, INTERVAL duration_minutes MINUTE) < NOW()')
->get();

$mikrotik = new MikrotikService();

foreach($expiredSessions as $session){

    try{
        $mikrotik->removeHotspotUser(
            $session->user->mobile
        );
    }catch(\Exception $e){

    }

    $session->logout_at = now();
    $session->save();
}

})->everyMinute();

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production' || env('FORCE_HTTPS', false)) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
            
            // If you have a specific domain, force it here to prevent 'wifi.login' leaks
            if (env('APP_URL')) {
                \Illuminate\Support\Facades\URL::forceRootUrl(env('APP_URL'));
            }
        }
    }
}

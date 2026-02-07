<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // Named API rate limiter - uses 'api:' prefix so it doesn't share
        // the counter with web route throttles (which use bare user ID).
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by('api:'.$request->user()?->id ?: $request->ip());
        });
    }
}

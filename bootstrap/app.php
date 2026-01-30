<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Subdomain proxy detection for local development only
        // php artisan serve doesn't support Route::domain(), so we need this workaround
        // In production, nginx/Apache handle subdomain routing properly
        if (env('APP_ENV') === 'local') {
            $middleware->prepend(\App\Http\Middleware\SubdomainProxy::class);
        }

        $middleware->alias([
            'proxy' => \App\Http\Middleware\ProxyMiddleware::class,
            'proxy.security' => \App\Http\Middleware\ProxySecurityHeaders::class,
            'subscription' => \App\Http\Middleware\CheckSubscription::class,
        ]);

        // Rate limiting for API routes
        $middleware->throttleApi('60,1'); // 60 requests per minute
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

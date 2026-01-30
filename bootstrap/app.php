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
        // Subdomain proxy detection - runs on all requests to catch subdomains
        // In production with nginx, Route::domain() handles this instead
        $middleware->prepend(\App\Http\Middleware\SubdomainProxy::class);

        $middleware->alias([
            'proxy.security' => \App\Http\Middleware\ProxySecurityHeaders::class,
            'subscription' => \App\Http\Middleware\CheckSubscription::class,
        ]);

        // Rate limiting for API routes
        $middleware->throttleApi('60,1'); // 60 requests per minute
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;

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

        // Exclude ingress_session from encryption - we need to forward it to HA as-is
        $middleware->encryptCookies(except: ['ingress_session']);

        // Rate limiting for API routes
        $middleware->throttleApi('60,1'); // 60 requests per minute
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response) {
            if ($response->getStatusCode() === 419) {
                return back()->withErrors([
                    'error' => 'The page expired, please try again.',
                ]);
            }

            return $response;
        });
    })->create();

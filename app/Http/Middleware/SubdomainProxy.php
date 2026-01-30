<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ProxyController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to detect subdomain requests and route them to the proxy controller.
 *
 * This is used in local development where php artisan serve doesn't support
 * Route::domain() properly. In production, nginx/Apache handle subdomain routing.
 */
class SubdomainProxy
{
    public function __construct(
        private ProxyController $proxyController
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // In production, nginx routes subdomains directly via Route::domain()
        // This middleware is mainly for local development with php artisan serve
        // which doesn't support Route::domain() properly

        $host = $request->getHost(); // getHost() returns host without port
        $proxyDomain = config('app.proxy_domain', 'harelay.com');

        // Skip if this is the main domain (no subdomain)
        if ($host === $proxyDomain || $host === 'www.'.$proxyDomain) {
            return $next($request);
        }

        // Check if this is a subdomain request (e.g., abc123.harelay.test)
        if (! preg_match('/^([a-z0-9]+)\.'.preg_quote($proxyDomain, '/').'$/i', $host, $matches)) {
            return $next($request);
        }

        $subdomain = strtolower($matches[1]);

        // Initialize session manually (needed because we're intercepting before normal middleware)
        if (! $request->hasSession()) {
            $this->initializeSession($request);
        }

        return $this->proxyController->handle($request, $subdomain);
    }

    /**
     * Initialize the session from the encrypted cookie.
     */
    private function initializeSession(Request $request): void
    {
        $sessionName = config('session.cookie');
        $encryptedSessionId = $request->cookies->get($sessionName);

        $sessionId = null;
        if ($encryptedSessionId) {
            try {
                $decrypted = app('encrypter')->decrypt($encryptedSessionId, false);

                // Database sessions use format: hash|session_id
                $sessionId = str_contains($decrypted, '|')
                    ? explode('|', $decrypted)[1]
                    : $decrypted;
            } catch (\Exception) {
                $sessionId = null;
            }
        }

        $session = app('session')->driver();
        if ($sessionId) {
            $session->setId($sessionId);
        }
        $session->start();
        $request->setLaravelSession($session);

        // Set user resolver so $request->user() works
        $request->setUserResolver(fn () => Auth::user());
    }
}

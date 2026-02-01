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
 * This middleware is prepended to the global stack and handles ALL subdomain
 * requests (both development and production). It intercepts requests before
 * normal routing, detects subdomains, and proxies them to Home Assistant.
 */
class SubdomainProxy
{
    public function __construct(
        private ProxyController $proxyController,
        private ProxySecurityHeaders $securityHeaders
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
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

        // Apply security headers middleware to the proxy response
        // This ensures cache headers and security headers are set correctly
        $response = $this->securityHeaders->handle($request, fn () => $this->proxyController->handle($request, $subdomain));

        // Save session and set cookie (since we bypass StartSession middleware)
        $this->saveSession($request, $response);

        return $response;
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

    /**
     * Save the session and set the session cookie on the response.
     */
    private function saveSession(Request $request, Response $response): void
    {
        $session = $request->session();

        // Save session data to storage (database)
        $session->save();

        // Set session cookie on response if not already set
        $sessionName = config('session.cookie');
        $sessionId = $session->getId();

        // Encrypt the session ID (Laravel expects encrypted session cookies)
        $cookieValue = app('encrypter')->encrypt($sessionId, false);

        // Get session cookie config
        $config = config('session');

        $response->headers->setCookie(
            cookie(
                name: $sessionName,
                value: $cookieValue,
                minutes: $config['lifetime'],
                path: $config['path'],
                domain: $config['domain'],
                secure: $config['secure'] ?? false,
                httpOnly: $config['http_only'] ?? true,
                sameSite: $config['same_site'] ?? 'lax'
            )
        );
    }
}

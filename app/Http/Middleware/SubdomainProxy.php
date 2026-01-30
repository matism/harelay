<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ProxyController;
use Closure;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SubdomainProxy
{
    public function __construct(
        private ProxyController $proxyController
    ) {}

    /**
     * Detect subdomain requests and handle them via ProxyController.
     * Manually starts session to ensure authentication works.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $proxyDomain = config('app.proxy_domain', 'harelay.com');
        $escapedDomain = preg_quote($proxyDomain, '/');

        // Check if this is a subdomain request (e.g., abc123.harelay.test)
        if (preg_match('/^([a-z0-9]+)\.' . $escapedDomain . '$/i', $host, $matches)) {
            $subdomain = strtolower($matches[1]);

            // Start session manually so auth works
            if (!$request->hasSession()) {
                $sessionName = config('session.cookie');
                $encryptedSessionId = $request->cookies->get($sessionName);

                \Log::debug('SubdomainProxy cookie debug', [
                    'session_name' => $sessionName,
                    'encrypted_value' => $encryptedSessionId ? substr($encryptedSessionId, 0, 50) . '...' : null,
                ]);

                // Decrypt the session cookie (Laravel encrypts it by default)
                $sessionId = null;
                if ($encryptedSessionId) {
                    try {
                        // Laravel's cookie encryption wraps the value
                        $decrypted = app('encrypter')->decrypt($encryptedSessionId, false);

                        // Database sessions use format: hash|session_id
                        if (str_contains($decrypted, '|')) {
                            $sessionId = explode('|', $decrypted)[1];
                        } else {
                            $sessionId = $decrypted;
                        }
                        \Log::debug('Decrypted session ID', ['raw' => $decrypted, 'session_id' => $sessionId]);
                    } catch (\Exception $e) {
                        \Log::debug('Decryption failed', ['error' => $e->getMessage()]);
                        $sessionId = null;
                    }
                }

                $session = app('session')->driver();
                if ($sessionId) {
                    $session->setId($sessionId);
                }
                $session->start();
                $request->setLaravelSession($session);

                // Set user resolver on request so $request->user() works
                $request->setUserResolver(function () {
                    return \Illuminate\Support\Facades\Auth::user();
                });

                \Log::debug('Session started', [
                    'final_session_id' => $session->getId(),
                    'auth_check' => \Illuminate\Support\Facades\Auth::check(),
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                ]);
            }

            // Handle the proxy request
            return $this->proxyController->handle($request, $subdomain);
        }

        return $next($request);
    }
}

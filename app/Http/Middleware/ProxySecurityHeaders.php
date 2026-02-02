<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProxySecurityHeaders
{
    /**
     * Security headers for proxied subdomain requests.
     * Prevents search engine indexing and adds security measures.
     *
     * Note: Cache headers are handled in ProxyController, not here.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent search engines from indexing proxied content
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet');

        // Prevent embedding in iframes (clickjacking protection)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS filter
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer policy - don't leak subdomain info
        $response->headers->set('Referrer-Policy', 'same-origin');

        return $response;
    }
}
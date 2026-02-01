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

        // Content Security Policy for proxied content
        $response->headers->set('Content-Security-Policy', "frame-ancestors 'self'");

        // Aggressive cache control - prevent any caching of proxied content
        // This ensures users always see fresh content, especially important for auth checks
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');  // HTTP/1.0 compatibility
        $response->headers->set('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
        $response->headers->set('Vary', 'Cookie');  // Different cache per user session

        return $response;
    }
}

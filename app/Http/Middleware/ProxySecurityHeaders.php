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

        // Check if this is an app_subdomain request (set by ProxyController)
        $isAppSubdomain = $request->attributes->get('is_app_subdomain', false);

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
        // For app_subdomain, be more permissive to allow HA's full functionality
        if ($isAppSubdomain) {
            // Don't set restrictive CSP for app_subdomain - let HA's CSP through
            $response->headers->remove('Content-Security-Policy');
        } else {
            $response->headers->set('Content-Security-Policy', "frame-ancestors 'self'");
        }

        // Cache control based on content type
        // For app_subdomain, don't override cache headers - let HA control caching
        if (! $isAppSubdomain) {
            $contentType = $response->headers->get('Content-Type', '');
            $isStaticAsset = preg_match('#^(image/|audio/|video/|font/|application/javascript|application/json|text/css|text/javascript|application/x-javascript|application/font|application/vnd.ms-fontobject|application/wasm)#i', $contentType);

            if ($isStaticAsset) {
                // Allow browser caching for static assets, but not shared caches
                $response->headers->set('Cache-Control', 'private, max-age=3600');
            } else {
                // Prevent caching for HTML and dynamic content (auth checks matter here)
                $response->headers->set('Cache-Control', 'private, no-store');
                $response->headers->set('Vary', 'Cookie');
            }
        }

        return $response;
    }
}

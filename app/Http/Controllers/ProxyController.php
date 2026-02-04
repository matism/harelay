<?php

namespace App\Http\Controllers;

use App\Models\HaConnection;
use App\Services\TunnelManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProxyController extends Controller
{
    public function __construct(
        private TunnelManager $tunnelManager
    ) {}

    /**
     * Handle all proxied requests to Home Assistant.
     */
    public function handle(Request $request, string $subdomain): Response
    {
        // Serve robots.txt for subdomains - prevent indexing
        if ($request->path() === 'robots.txt') {
            return response(
                "User-agent: *\nDisallow: /\n",
                200,
                ['Content-Type' => 'text/plain']
            );
        }

        // Find connection by either regular subdomain or app_subdomain
        ['connection' => $connection, 'is_app_subdomain' => $isAppSubdomain] = HaConnection::findBySubdomain($subdomain);

        if (! $connection) {
            return response()->view('errors.not-found', [], 404);
        }

        // App subdomain access - no authentication required (URL is the auth)
        // Regular subdomain access - requires login
        if (! $isAppSubdomain) {
            if (! $request->user()) {
                session(['url.intended' => $request->fullUrl()]);

                return response()->view('errors.auth-required', [
                    'connection' => $connection,
                ], 401);
            }

            // Check authorization - user must own this connection
            if ($request->user()->id !== $connection->user_id) {
                return response()->view('errors.unauthorized', [
                    'connection' => $connection,
                ], 403);
            }
        }

        // Check tunnel connection (always uses the regular subdomain for tunnel operations)
        $tunnelSubdomain = $connection->subdomain;
        if (! $this->tunnelManager->isConnected($tunnelSubdomain)) {
            return response()->view('errors.tunnel-disconnected', [
                'connection' => $connection,
            ], 503);
        }

        // Build request
        $method = $request->method();
        $uri = $request->getRequestUri();
        $headers = $this->filterRequestHeaders($request->headers->all(), $request);
        $body = $request->getContent();
        $contentType = $request->header('Content-Type', '');

        // Handle multipart/form-data - PHP auto-parses it, so reconstruct from $_POST
        if (str_contains($contentType, 'multipart/form-data') && empty($body)) {
            $body = http_build_query($request->all());
            $headers['content-type'] = 'application/x-www-form-urlencoded';
            unset($headers['Content-Type']);
        }

        // Proxy the request (using tunnel subdomain)
        $response = $this->tunnelManager->proxyRequest(
            subdomain: $tunnelSubdomain,
            method: $method,
            uri: $uri,
            headers: $headers,
            body: $body ?: null
        );

        if ($response === null) {
            return response()->view('errors.tunnel-timeout', [
                'connection' => $connection,
            ], 504);
        }

        return $this->buildResponse($response, $subdomain);
    }

    /**
     * Filter headers that should be forwarded to Home Assistant.
     */
    private function filterRequestHeaders(array $headers, Request $request): array
    {
        $skipHeaders = [
            'host',
            'connection',
            'proxy-connection',
            'keep-alive',
            'proxy-authenticate',
            'proxy-authorization',
            'te',
            'trailers',
            'transfer-encoding',
            'upgrade',
            'x-csrf-token',
        ];

        $filtered = [];
        foreach ($headers as $name => $values) {
            if (! in_array(strtolower($name), $skipHeaders)) {
                $filtered[$name] = is_array($values) ? implode(', ', $values) : $values;
            }
        }

        // Always set X-Forwarded-For with client IP for consistent auth flows
        // HA tracks IP during login_flow and rejects if it changes
        $clientIp = $request->ip();
        $filtered['X-Forwarded-For'] = $clientIp;
        $filtered['X-Real-IP'] = $clientIp;

        return $filtered;
    }

    /**
     * Build an HTTP response from tunnel response data.
     */
    private function buildResponse(array $responseData, string $subdomain): Response
    {
        $statusCode = $responseData['status_code'] ?? 502;
        $headers = $responseData['headers'] ?? [];
        $body = $responseData['body'] ?? '';

        // Check if this is a static asset that can be cached
        $contentType = $headers['Content-Type'] ?? $headers['content-type'] ?? '';
        $isStaticAsset = $this->isStaticAsset($contentType);

        // Debug logging for JS responses
        if (str_contains($contentType, 'javascript')) {
            $contentEncoding = $headers['Content-Encoding'] ?? $headers['content-encoding'] ?? 'NONE';
            $isGzip = strlen($body) >= 2 && ord($body[0]) === 0x1f && ord($body[1]) === 0x8b;
            \Log::info('JS Debug', [
                'uri' => request()->getRequestUri(),
                'content_encoding_header' => $contentEncoding,
                'body_is_gzip' => $isGzip,
                'body_len' => strlen($body),
                'all_headers' => array_keys($headers),
            ]);
        }

        // Base headers to always skip
        // Note: We preserve Content-Encoding so browser knows how to decode the response.
        // The add-on must use auto_decompress=False to keep body compressed.
        $skipHeaders = [
            'transfer-encoding',
            'connection',
            'keep-alive',
            'content-length',  // Let PHP recalculate based on actual body
            'set-cookie',      // Handle Set-Cookie separately below
        ];

        // For non-static assets, also skip cache headers (security)
        if (! $isStaticAsset) {
            $skipHeaders = array_merge($skipHeaders, [
                'cache-control',
                'pragma',
                'expires',
                'etag',
                'last-modified',
            ]);
        }

        $filteredHeaders = [];
        foreach ($headers as $name => $value) {
            if (! in_array(strtolower($name), $skipHeaders)) {
                $filteredHeaders[$name] = $value;
            }
        }

        // Add aggressive caching for static assets
        if ($isStaticAsset && $statusCode === 200) {
            $filteredHeaders['Cache-Control'] = 'public, max-age=31536000, immutable';
        }

        $response = response($body, $statusCode, $filteredHeaders);

        // Pass through all Set-Cookie headers (strip Domain so browser uses request origin)
        $this->passThruSetCookies($response, $headers);

        return $response;
    }

    /**
     * Check if content type indicates a static asset that can be cached.
     */
    private function isStaticAsset(string $contentType): bool
    {
        $staticTypes = [
            'application/javascript',
            'text/javascript',
            'text/css',
            'image/',
            'font/',
            'application/font',
            'application/x-font',
            'audio/',
            'video/',
            'application/wasm',
        ];

        foreach ($staticTypes as $type) {
            if (str_contains($contentType, $type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Pass through all Set-Cookie headers from HA.
     * We strip the Domain attribute so the browser uses the request origin.
     */
    private function passThruSetCookies(Response $response, array $headers): void
    {
        $setCookies = $headers['Set-Cookie'] ?? $headers['set-cookie'] ?? null;
        if (! $setCookies) {
            return;
        }

        // Normalize to array
        if (! is_array($setCookies)) {
            $setCookies = [$setCookies];
        }

        $proxyDomain = config('app.proxy_domain', 'harelay.com');
        $secure = config('app.proxy_secure', true);

        foreach ($setCookies as $cookie) {
            // Strip any Domain attribute (HA might set localhost or its internal domain)
            // This lets the browser associate the cookie with the request origin
            $cookie = preg_replace('/;\s*Domain=[^;]*/i', '', $cookie);

            // Ensure Secure flag matches our proxy setting
            if ($secure && ! preg_match('/;\s*Secure/i', $cookie)) {
                $cookie .= '; Secure';
            }

            // Ensure SameSite is set for cross-origin compatibility
            if (! preg_match('/;\s*SameSite=/i', $cookie)) {
                $cookie .= '; SameSite=Lax';
            }

            // Use false for $replace to allow multiple Set-Cookie headers
            $response->headers->set('Set-Cookie', $cookie, false);
        }
    }
}

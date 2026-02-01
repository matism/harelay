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

        // Use the original subdomain for cookie domain (app or regular)
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
            'cookie',  // We handle specific cookies separately
            'x-csrf-token',
        ];

        $filtered = [];
        foreach ($headers as $name => $values) {
            if (! in_array(strtolower($name), $skipHeaders)) {
                $filtered[$name] = is_array($values) ? implode(', ', $values) : $values;
            }
        }

        // Forward ingress_session cookie if present (needed for HA add-on ingress)
        $ingressSession = $request->cookie('ingress_session');
        if ($ingressSession) {
            $filtered['cookie'] = 'ingress_session='.$ingressSession;
        }

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

        // Decode base64 body
        if ($responseData['is_base64'] ?? false) {
            $body = base64_decode($body);
        }

        // Filter response headers - remove headers that could interfere with our security/caching
        $skipHeaders = [
            'transfer-encoding',
            'connection',
            'keep-alive',
            'content-encoding',
            'content-length',
            // Remove HA's cache headers so our middleware's headers take precedence
            'cache-control',
            'pragma',
            'expires',
            'etag',
            'last-modified',
            'set-cookie',  // Handle separately to rewrite domain
        ];

        $filteredHeaders = [];
        foreach ($headers as $name => $value) {
            if (! in_array(strtolower($name), $skipHeaders)) {
                $filteredHeaders[$name] = $value;
            }
        }

        $response = response($body, $statusCode, $filteredHeaders);

        // Rewrite Set-Cookie headers for ingress_session to use HARelay domain
        $this->rewriteIngressCookies($response, $headers, $subdomain);

        return $response;
    }

    /**
     * Rewrite ingress_session Set-Cookie headers to use HARelay domain.
     */
    private function rewriteIngressCookies(Response $response, array $headers, string $subdomain): void
    {
        $proxyDomain = config('app.proxy_domain', 'harelay.com');
        $cookieDomain = "{$subdomain}.{$proxyDomain}";
        $secure = config('app.proxy_secure', true);

        // Find Set-Cookie headers (can be array or string)
        $setCookies = $headers['Set-Cookie'] ?? $headers['set-cookie'] ?? null;
        if (! $setCookies) {
            return;
        }

        // Normalize to array
        if (! is_array($setCookies)) {
            $setCookies = [$setCookies];
        }

        foreach ($setCookies as $cookie) {
            // Only rewrite ingress_session cookies
            if (! str_starts_with($cookie, 'ingress_session=')) {
                continue;
            }

            // Parse the cookie value
            if (preg_match('/^ingress_session=([^;]+)/', $cookie, $matches)) {
                $value = trim($matches[1]);

                // Extract Path if present (usually /api/hassio_ingress/)
                $path = '/';
                if (preg_match('/Path=([^;]+)/i', $cookie, $pathMatches)) {
                    $path = trim($pathMatches[1]);
                }

                // Set the cookie with HARelay domain
                $response->headers->setCookie(
                    cookie(
                        name: 'ingress_session',
                        value: $value,
                        minutes: 60 * 24,  // 24 hours
                        path: $path,
                        domain: $cookieDomain,
                        secure: $secure,
                        httpOnly: true,
                        sameSite: 'Lax'
                    )
                );
            }
        }
    }
}

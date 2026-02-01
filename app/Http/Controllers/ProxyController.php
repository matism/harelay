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

        $connection = HaConnection::where('subdomain', $subdomain)->first();

        if (! $connection) {
            return response()->view('errors.not-found', [], 404);
        }

        // Check authentication
        if (! $request->user()) {
            session(['url.intended' => $request->fullUrl()]);

            return response()->view('errors.auth-required', [
                'connection' => $connection,
            ], 401);
        }

        // Check authorization
        if ($request->user()->id !== $connection->user_id) {
            return response()->view('errors.unauthorized', [
                'connection' => $connection,
            ], 403);
        }

        // Check tunnel connection
        if (! $this->tunnelManager->isConnected($subdomain)) {
            return response()->view('errors.tunnel-disconnected', [
                'connection' => $connection,
            ], 503);
        }

        // Build request
        $method = $request->method();
        $uri = $request->getRequestUri();
        $headers = $this->filterRequestHeaders($request->headers->all());
        $body = $request->getContent();
        $contentType = $request->header('Content-Type', '');

        // Handle multipart/form-data - PHP auto-parses it, so reconstruct from $_POST
        if (str_contains($contentType, 'multipart/form-data') && empty($body)) {
            $body = http_build_query($request->all());
            $headers['content-type'] = 'application/x-www-form-urlencoded';
            unset($headers['Content-Type']);
        }

        // Proxy the request
        $response = $this->tunnelManager->proxyRequest(
            subdomain: $subdomain,
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

        return $this->buildResponse($response);
    }

    /**
     * Filter headers that should be forwarded to Home Assistant.
     */
    private function filterRequestHeaders(array $headers): array
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
            'cookie',
            'x-csrf-token',
        ];

        $filtered = [];
        foreach ($headers as $name => $values) {
            if (! in_array(strtolower($name), $skipHeaders)) {
                $filtered[$name] = is_array($values) ? implode(', ', $values) : $values;
            }
        }

        return $filtered;
    }

    /**
     * Build an HTTP response from tunnel response data.
     */
    private function buildResponse(array $responseData): Response
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
        ];

        $filteredHeaders = [];
        foreach ($headers as $name => $value) {
            if (! in_array(strtolower($name), $skipHeaders)) {
                $filteredHeaders[$name] = $value;
            }
        }

        return response($body, $statusCode, $filteredHeaders);
    }
}

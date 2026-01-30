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
        // Find the connection
        $connection = HaConnection::where('subdomain', $subdomain)->first();

        if (! $connection) {
            return response('Connection not found', 404);
        }

        // Check if user is authenticated and authorized
        if (! $request->user()) {
            // Store intended URL and redirect to login
            session(['url.intended' => $request->fullUrl()]);

            return response()->view('errors.auth-required', [
                'connection' => $connection,
            ], 401);
        }

        if ($request->user()->id !== $connection->user_id) {
            return response('Unauthorized', 403);
        }

        // Check if the tunnel is connected
        if (! $this->tunnelManager->isConnected($subdomain)) {
            return response()->view('errors.tunnel-disconnected', [
                'connection' => $connection,
            ], 503);
        }

        // Build the request to proxy
        $method = $request->method();
        $uri = $request->getRequestUri();
        $headers = $this->filterRequestHeaders($request->headers->all());
        $body = $request->getContent();

        // Send through tunnel and wait for response
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

        // Build and return the response
        return $this->buildResponse($response);
    }

    /**
     * Filter headers that should be forwarded to Home Assistant.
     */
    private function filterRequestHeaders(array $headers): array
    {
        $filtered = [];
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
            'cookie', // Don't forward our session cookies
            'x-csrf-token',
        ];

        foreach ($headers as $name => $values) {
            $lowerName = strtolower($name);
            if (! in_array($lowerName, $skipHeaders)) {
                // Flatten the array of values to a single value
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

        // Check if body is base64 encoded
        $isBase64 = ($headers['X-HARelay-Base64'] ?? '0') === '1';
        if ($isBase64) {
            $body = base64_decode($body);
        }

        // Filter response headers
        $skipHeaders = [
            'transfer-encoding',
            'connection',
            'keep-alive',
            'content-encoding', // We handle decompression on the add-on side
            'x-harelay-base64', // Internal header
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

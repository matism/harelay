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
        $connection = HaConnection::where('subdomain', $subdomain)->first();

        if (! $connection) {
            return response('Connection not found', 404);
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
            return response('Unauthorized', 403);
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

        return $this->buildResponse($response, $subdomain);
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
    private function buildResponse(array $responseData, string $subdomain = ''): Response
    {
        $statusCode = $responseData['status_code'] ?? 502;
        $headers = $responseData['headers'] ?? [];
        $body = $responseData['body'] ?? '';

        // Decode base64 body
        if ($responseData['is_base64'] ?? false) {
            $body = base64_decode($body);
        }

        // Get content type (case-insensitive)
        $contentType = '';
        foreach ($headers as $name => $value) {
            if (strtolower($name) === 'content-type') {
                $contentType = $value;
                break;
            }
        }

        // Inject WebSocket proxy script into HTML pages
        if (str_contains($contentType, 'text/html') && $subdomain) {
            $body = $this->injectWebSocketProxy($body, $subdomain);
        }

        // Filter response headers
        $skipHeaders = [
            'transfer-encoding',
            'connection',
            'keep-alive',
            'content-encoding',
            'content-length',
        ];

        $filteredHeaders = [];
        foreach ($headers as $name => $value) {
            if (! in_array(strtolower($name), $skipHeaders)) {
                $filteredHeaders[$name] = $value;
            }
        }

        return response($body, $statusCode, $filteredHeaders);
    }

    /**
     * Inject WebSocket proxy script into HTML to redirect HA WebSocket connections.
     */
    private function injectWebSocketProxy(string $html, string $subdomain): string
    {
        $host = request()->getHost();
        $wsProxyPort = (int) (env('WS_PROXY_PORT', 8082));
        $scheme = request()->secure() ? 'wss' : 'ws';

        // Minified, production-ready script
        $script = <<<JS
<script>
(function(){
    var O=window.WebSocket,s='{$subdomain}',u='{$scheme}://{$host}:{$wsProxyPort}';
    window.WebSocket=function(a,p){
        var r=new URL(a,location.href);
        if(r.pathname.indexOf('/api/websocket')>-1||r.pathname.indexOf('/api/hassio')>-1){
            var w=new O(u,p);
            w.addEventListener('open',function(){
                w.send(JSON.stringify({type:'auth',subdomain:s,path:r.pathname}));
            },{once:true});
            return w;
        }
        return new O(a,p);
    };
    window.WebSocket.prototype=O.prototype;
    window.WebSocket.CONNECTING=O.CONNECTING;
    window.WebSocket.OPEN=O.OPEN;
    window.WebSocket.CLOSING=O.CLOSING;
    window.WebSocket.CLOSED=O.CLOSED;
})();
</script>
JS;

        // Inject at the start of <head> to override WebSocket before any scripts load
        if (str_contains($html, '<head>')) {
            return str_replace('<head>', '<head>'.$script, $html);
        }
        if (str_contains($html, '<head ')) {
            return preg_replace('/(<head[^>]*>)/i', '$1'.$script, $html);
        }
        if (str_contains($html, '</head>')) {
            return str_replace('</head>', $script.'</head>', $html);
        }
        if (str_contains($html, '<body')) {
            return preg_replace('/(<body[^>]*>)/i', '$1'.$script, $html);
        }

        return $script.$html;
    }
}

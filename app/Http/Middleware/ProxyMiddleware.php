<?php

namespace App\Http\Middleware;

use App\Models\HaConnection;
use App\Services\TunnelManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProxyMiddleware
{
    public function __construct(
        private TunnelManager $tunnelManager
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // Extract subdomain from host (e.g., abc123.harelay.io -> abc123)
        $subdomain = $this->extractSubdomain($host);

        if (! $subdomain) {
            return $next($request);
        }

        // Find the connection for this subdomain
        $connection = HaConnection::where('subdomain', $subdomain)->first();

        if (! $connection) {
            abort(404, 'Connection not found');
        }

        // Check if user is authenticated and owns this connection
        if (! $request->user()) {
            return redirect()->route('login')->with('intended', $request->fullUrl());
        }

        if ($request->user()->id !== $connection->user_id) {
            abort(403, 'Unauthorized access to this connection');
        }

        // Check if the tunnel is connected
        if (! $this->tunnelManager->isConnected($subdomain)) {
            return response()->view('errors.tunnel-disconnected', [
                'connection' => $connection,
            ], 503);
        }

        // Store connection in request for proxy handling
        $request->attributes->set('ha_connection', $connection);
        $request->attributes->set('tunnel_socket_id', $this->tunnelManager->getSocketId($subdomain));

        return $next($request);
    }

    private function extractSubdomain(string $host): ?string
    {
        // Match subdomain.harelay.io or subdomain.localhost for development
        if (preg_match('/^([a-z0-9]+)\.harelay\.io$/i', $host, $matches)) {
            return strtolower($matches[1]);
        }

        // Development: subdomain.localhost:8000 or similar
        if (preg_match('/^([a-z0-9]+)\.localhost/i', $host, $matches)) {
            return strtolower($matches[1]);
        }

        return null;
    }
}

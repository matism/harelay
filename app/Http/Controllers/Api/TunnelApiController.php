<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HaConnection;
use App\Services\TunnelManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TunnelApiController extends Controller
{
    public function __construct(
        private TunnelManager $tunnelManager
    ) {}

    /**
     * Register add-on connection (called when add-on starts)
     */
    public function connect(Request $request): JsonResponse
    {
        $request->validate([
            'subdomain' => 'required|string',
            'token' => 'required|string',
        ]);

        $connection = $this->authenticateConnection($request->subdomain, $request->token);

        if (! $connection) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $this->tunnelManager->connect($connection);

        return response()->json([
            'success' => true,
            'subdomain' => $connection->subdomain,
            'websocket' => [
                'host' => config('broadcasting.connections.reverb.options.host'),
                'port' => config('broadcasting.connections.reverb.options.port'),
                'scheme' => config('broadcasting.connections.reverb.options.scheme', 'http'),
                'key' => config('broadcasting.connections.reverb.key'),
                'channel' => "private-tunnel.{$connection->subdomain}",
            ],
            'api' => [
                'auth_endpoint' => url('/api/tunnel/auth'),
                'response_endpoint' => url('/api/tunnel/response'),
                'heartbeat_endpoint' => url('/api/tunnel/heartbeat'),
            ],
        ]);
    }

    /**
     * Disconnect add-on (called when add-on stops)
     */
    public function disconnect(Request $request): JsonResponse
    {
        $request->validate([
            'subdomain' => 'required|string',
            'token' => 'required|string',
        ]);

        $connection = $this->authenticateConnection($request->subdomain, $request->token);

        if (! $connection) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $this->tunnelManager->disconnect($connection);

        return response()->json(['success' => true]);
    }

    /**
     * Heartbeat to keep connection alive
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $request->validate([
            'subdomain' => 'required|string',
            'token' => 'required|string',
        ]);

        $connection = $this->authenticateConnection($request->subdomain, $request->token);

        if (! $connection) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $this->tunnelManager->heartbeat($connection);

        // Return count of pending requests (for polling fallback)
        $pendingRequests = $this->tunnelManager->getPendingRequests($connection->subdomain);

        return response()->json([
            'success' => true,
            'pending_requests' => count($pendingRequests),
        ]);
    }

    /**
     * Submit response from add-on for a proxied request
     */
    public function submitResponse(Request $request): JsonResponse
    {
        $request->validate([
            'subdomain' => 'required|string',
            'token' => 'required|string',
            'request_id' => 'required|string',
            'status_code' => 'required|integer',
            'headers' => 'required|array',
            'body' => 'nullable|string',
        ]);

        $connection = $this->authenticateConnection($request->subdomain, $request->token);

        if (! $connection) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $this->tunnelManager->storeResponse(
            requestId: $request->input('request_id'),
            statusCode: $request->input('status_code'),
            headers: $request->input('headers'),
            body: $request->input('body', ''),
        );

        return response()->json(['success' => true]);
    }

    /**
     * Poll for pending requests (alternative to WebSocket)
     */
    public function pollRequests(Request $request): JsonResponse
    {
        $request->validate([
            'subdomain' => 'required|string',
            'token' => 'required|string',
        ]);

        $connection = $this->authenticateConnection($request->subdomain, $request->token);

        if (! $connection) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Update heartbeat
        $this->tunnelManager->heartbeat($connection);

        $pendingRequests = $this->tunnelManager->getPendingRequests($connection->subdomain);

        \Log::debug('Poll request', [
            'subdomain' => $connection->subdomain,
            'pending_count' => count($pendingRequests),
        ]);

        if (empty($pendingRequests)) {
            return response()->json([
                'success' => true,
                'request' => null,
            ]);
        }

        // Return the first pending request
        $requestId = array_key_first($pendingRequests);
        $requestData = $pendingRequests[$requestId];

        \Log::debug('Returning pending request', [
            'request_id' => $requestId,
            'method' => $requestData['method'] ?? 'unknown',
            'uri' => $requestData['uri'] ?? 'unknown',
        ]);

        return response()->json([
            'success' => true,
            'request' => array_merge(['id' => $requestId], $requestData),
        ]);
    }

    /**
     * Authenticate connection using subdomain and token
     */
    private function authenticateConnection(string $subdomain, string $token): ?HaConnection
    {
        $connection = HaConnection::where('subdomain', $subdomain)->first();

        if (! $connection) {
            return null;
        }

        if (! Hash::check($token, $connection->connection_token)) {
            return null;
        }

        return $connection;
    }
}

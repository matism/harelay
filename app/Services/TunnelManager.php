<?php

namespace App\Services;

use App\Events\TunnelConnected;
use App\Events\TunnelDisconnected;
use App\Events\TunnelRequest;
use App\Models\HaConnection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TunnelManager
{
    private const CACHE_PREFIX = 'tunnel:';

    private const CONNECTION_TTL = 120; // seconds - connection considered stale after this

    private const REQUEST_TTL = 30; // seconds - request timeout

    private const RESPONSE_TTL = 30; // seconds - response kept in cache

    public function authenticate(string $subdomain, string $token): ?HaConnection
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

    public function connect(HaConnection $connection, ?string $socketId = null): void
    {
        $connection->update([
            'status' => 'connected',
            'last_connected_at' => now(),
        ]);

        Cache::put(
            $this->getConnectionCacheKey($connection->subdomain),
            [
                'socket_id' => $socketId,
                'connected_at' => now()->toIso8601String(),
            ],
            self::CONNECTION_TTL
        );

        event(new TunnelConnected($connection));
    }

    public function disconnect(HaConnection $connection): void
    {
        $connection->update([
            'status' => 'disconnected',
        ]);

        Cache::forget($this->getConnectionCacheKey($connection->subdomain));

        event(new TunnelDisconnected($connection));
    }

    public function isConnected(string $subdomain): bool
    {
        // Check if connection exists in cache (refreshed by heartbeat)
        if (! Cache::has($this->getConnectionCacheKey($subdomain))) {
            return false;
        }

        // Also verify the database status
        $connection = HaConnection::where('subdomain', $subdomain)->first();
        if (! $connection || $connection->status !== 'connected') {
            return false;
        }

        // Check if last_connected_at is within the TTL
        if ($connection->last_connected_at && $connection->last_connected_at->diffInSeconds(now()) > self::CONNECTION_TTL) {
            // Connection is stale, mark as disconnected
            $connection->update(['status' => 'disconnected']);
            Cache::forget($this->getConnectionCacheKey($subdomain));

            return false;
        }

        return true;
    }

    public function getSocketId(string $subdomain): ?string
    {
        $data = Cache::get($this->getConnectionCacheKey($subdomain));

        return $data['socket_id'] ?? null;
    }

    public function heartbeat(HaConnection $connection): void
    {
        $connection->update([
            'status' => 'connected',
            'last_connected_at' => now(),
        ]);

        $data = Cache::get($this->getConnectionCacheKey($connection->subdomain));
        if ($data) {
            Cache::put(
                $this->getConnectionCacheKey($connection->subdomain),
                $data,
                self::CONNECTION_TTL
            );
        }
    }

    /**
     * Send a proxied request through the tunnel.
     *
     * @return array|null The response data or null on timeout
     */
    public function proxyRequest(
        string $subdomain,
        string $method,
        string $uri,
        array $headers,
        ?string $body = null
    ): ?array {
        $requestId = Str::uuid()->toString();

        // Store the request in pending cache
        $pendingKey = $this->getPendingCacheKey($subdomain);
        $pendingRequests = Cache::get($pendingKey, []);
        $pendingRequests[$requestId] = [
            'method' => $method,
            'uri' => $uri,
            'headers' => $headers,
            'body' => $body,
            'created_at' => now()->toIso8601String(),
        ];
        Cache::put($pendingKey, $pendingRequests, self::REQUEST_TTL);

        // Broadcast the request to the add-on via Reverb
        broadcast(new TunnelRequest(
            subdomain: $subdomain,
            requestId: $requestId,
            method: $method,
            uri: $uri,
            headers: $headers,
            body: $body,
        ));

        // Wait for response (polling with exponential backoff)
        $responseKey = $this->getResponseCacheKey($requestId);
        $maxWait = self::REQUEST_TTL;
        $waited = 0;
        $interval = 50000; // Start with 50ms

        while ($waited < $maxWait * 1000000) {
            $response = Cache::get($responseKey);
            if ($response !== null) {
                // Clean up
                Cache::forget($responseKey);
                $this->removePendingRequest($subdomain, $requestId);

                return $response;
            }

            usleep($interval);
            $waited += $interval;

            // Increase interval up to 200ms
            $interval = min($interval * 1.5, 200000);
        }

        // Timeout - clean up pending request
        $this->removePendingRequest($subdomain, $requestId);

        return null;
    }

    /**
     * Store a response from the add-on.
     */
    public function storeResponse(string $requestId, int $statusCode, array $headers, string $body): void
    {
        $responseKey = $this->getResponseCacheKey($requestId);
        Cache::put($responseKey, [
            'status_code' => $statusCode,
            'headers' => $headers,
            'body' => $body,
            'received_at' => now()->toIso8601String(),
        ], self::RESPONSE_TTL);
    }

    /**
     * Get pending requests for a subdomain (for polling fallback).
     */
    public function getPendingRequests(string $subdomain): array
    {
        return Cache::get($this->getPendingCacheKey($subdomain), []);
    }

    private function removePendingRequest(string $subdomain, string $requestId): void
    {
        $pendingKey = $this->getPendingCacheKey($subdomain);
        $pendingRequests = Cache::get($pendingKey, []);
        unset($pendingRequests[$requestId]);

        if (empty($pendingRequests)) {
            Cache::forget($pendingKey);
        } else {
            Cache::put($pendingKey, $pendingRequests, self::REQUEST_TTL);
        }
    }

    private function getConnectionCacheKey(string $subdomain): string
    {
        return self::CACHE_PREFIX.'connection:'.$subdomain;
    }

    private function getPendingCacheKey(string $subdomain): string
    {
        return self::CACHE_PREFIX.'pending:'.$subdomain;
    }

    private function getResponseCacheKey(string $requestId): string
    {
        return self::CACHE_PREFIX.'response:'.$requestId;
    }
}

<?php

namespace App\Services;

use App\Models\HaConnection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Manages tunnel connections and proxied requests.
 *
 * Uses file cache for communication with the tunnel server process
 * to avoid database connection issues in long-running processes.
 */
class TunnelManager
{
    private const CACHE_PREFIX = 'tunnel:';

    private const CONNECTION_TTL = 120; // seconds

    private const REQUEST_TTL = 60; // seconds

    /**
     * Check if a tunnel connection is active.
     */
    public function isConnected(string $subdomain): bool
    {
        $connection = HaConnection::where('subdomain', $subdomain)->first();

        if (! $connection || $connection->status !== 'connected') {
            return false;
        }

        // Check if last_connected_at is within the TTL (heartbeats update this)
        if ($connection->last_connected_at &&
            $connection->last_connected_at->diffInSeconds(now()) > self::CONNECTION_TTL) {
            $connection->update(['status' => 'disconnected']);

            return false;
        }

        return true;
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

        // Store request in file cache (base64 encode body for safe JSON transport)
        $pendingKey = $this->getPendingCacheKey($subdomain);
        $pendingRequests = Cache::store('file')->get($pendingKey, []);
        $pendingRequests[$requestId] = [
            'method' => $method,
            'uri' => $uri,
            'headers' => $headers,
            'body' => $body ? base64_encode($body) : null,
            'body_encoded' => true,
            'created_at' => now()->toIso8601String(),
        ];
        Cache::store('file')->put($pendingKey, $pendingRequests, self::REQUEST_TTL);

        // Wait for response with exponential backoff
        $responseKey = $this->getResponseCacheKey($requestId);
        $maxWaitMicroseconds = self::REQUEST_TTL * 1000000;
        $waited = 0;
        $interval = 50000; // Start at 50ms

        while ($waited < $maxWaitMicroseconds) {
            $response = Cache::store('file')->get($responseKey);

            if ($response !== null) {
                Cache::store('file')->forget($responseKey);
                $this->removePendingRequest($subdomain, $requestId);

                return $response;
            }

            usleep($interval);
            $waited += $interval;
            $interval = min((int) ($interval * 1.5), 200000); // Cap at 200ms
        }

        // Timeout - clean up
        $this->removePendingRequest($subdomain, $requestId);

        return null;
    }

    /**
     * Get pending requests for a subdomain.
     */
    public function getPendingRequests(string $subdomain): array
    {
        return Cache::store('file')->get($this->getPendingCacheKey($subdomain), []);
    }

    private function removePendingRequest(string $subdomain, string $requestId): void
    {
        $pendingKey = $this->getPendingCacheKey($subdomain);
        $pendingRequests = Cache::store('file')->get($pendingKey, []);
        unset($pendingRequests[$requestId]);

        if (empty($pendingRequests)) {
            Cache::store('file')->forget($pendingKey);
        } else {
            Cache::store('file')->put($pendingKey, $pendingRequests, self::REQUEST_TTL);
        }
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

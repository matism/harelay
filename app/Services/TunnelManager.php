<?php

namespace App\Services;

use App\Models\HaConnection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Manages tunnel connections and proxied requests.
 *
 * Uses Redis cache for communication with the tunnel server process
 * for fast, reliable IPC between web requests and the tunnel server.
 */
class TunnelManager
{
    private const CACHE_PREFIX = 'tunnel:';

    private const CONNECTION_TTL = 120; // seconds

    private const REQUEST_TTL = 60; // seconds

    private const CANCELLED_TTL = 10; // seconds - short TTL for cancelled request markers

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

        // Store request in pending list (base64 encode body for safe JSON transport)
        $pendingKey = $this->getPendingCacheKey($subdomain);
        $pendingRequests = Cache::store('redis')->get($pendingKey, []);
        $pendingRequests[$requestId] = [
            'method' => $method,
            'uri' => $uri,
            'headers' => $headers,
            'body' => $body ? base64_encode($body) : null,
            'body_encoded' => true,
            'created_at' => now()->toIso8601String(),
        ];
        Cache::store('redis')->put($pendingKey, $pendingRequests, self::REQUEST_TTL);

        // Wait for response with fast polling
        $responseKey = $this->getResponseCacheKey($requestId);
        $maxWaitMicroseconds = self::REQUEST_TTL * 1000000;
        $waited = 0;
        $interval = 2000; // Start at 2ms for fast initial response
        $checkCancelInterval = 100000; // Check for cancellation every 100ms
        $lastCancelCheck = 0;

        while ($waited < $maxWaitMicroseconds) {
            $response = Cache::store('redis')->get($responseKey);

            if ($response !== null) {
                Cache::store('redis')->forget($responseKey);
                $this->removePendingRequest($subdomain, $requestId);

                return $response;
            }

            // Periodically check if client disconnected (every 100ms)
            if ($waited - $lastCancelCheck >= $checkCancelInterval) {
                if (connection_aborted()) {
                    // Client disconnected - mark request as cancelled and clean up
                    $this->cancelRequest($requestId);
                    $this->removePendingRequest($subdomain, $requestId);

                    return null;
                }
                $lastCancelCheck = $waited;
            }

            usleep($interval);
            $waited += $interval;
            // Stay at 2ms for first 200ms, then gradually increase to 20ms
            if ($waited > 200000) {
                $interval = min($interval + 2000, 20000);
            }
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
        return Cache::store('redis')->get($this->getPendingCacheKey($subdomain), []);
    }

    private function removePendingRequest(string $subdomain, string $requestId): void
    {
        $pendingKey = $this->getPendingCacheKey($subdomain);
        $pendingRequests = Cache::store('redis')->get($pendingKey, []);
        unset($pendingRequests[$requestId]);

        if (empty($pendingRequests)) {
            Cache::store('redis')->forget($pendingKey);
        } else {
            Cache::store('redis')->put($pendingKey, $pendingRequests, self::REQUEST_TTL);
        }
    }

    /**
     * Mark a request as cancelled so tunnel server can skip it.
     */
    private function cancelRequest(string $requestId): void
    {
        Cache::store('redis')->put(
            $this->getCancelledCacheKey($requestId),
            true,
            self::CANCELLED_TTL
        );
    }

    private function getPendingCacheKey(string $subdomain): string
    {
        return self::CACHE_PREFIX.'pending:'.$subdomain;
    }

    private function getResponseCacheKey(string $requestId): string
    {
        return self::CACHE_PREFIX.'response:'.$requestId;
    }

    private function getCancelledCacheKey(string $requestId): string
    {
        return self::CACHE_PREFIX.'cancelled:'.$requestId;
    }
}

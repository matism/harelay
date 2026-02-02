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
        $completed = false;

        // Register shutdown function to clean up if request is interrupted
        // This catches client disconnects, timeouts, and other terminations
        register_shutdown_function(function () use ($subdomain, $requestId, &$completed) {
            if (! $completed) {
                // Request was interrupted - mark as cancelled and clean up
                $this->cancelRequest($requestId);
                $this->removePendingRequest($subdomain, $requestId);
            }
        });

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
        $interval = 500; // Start at 0.5ms for fastest initial response

        while ($waited < $maxWaitMicroseconds) {
            $response = Cache::store('redis')->get($responseKey);

            if ($response !== null) {
                $completed = true;
                Cache::store('redis')->forget($responseKey);
                $this->removePendingRequest($subdomain, $requestId);

                return $response;
            }

            usleep($interval);
            $waited += $interval;
            // Gradually increase polling interval to reduce CPU usage
            // 0.5ms for first 50ms, then ramp up to 10ms max
            if ($waited > 50000) {
                $interval = min($interval + 500, 10000);
            }
        }

        // Timeout - mark as completed so shutdown function doesn't double-cancel
        $completed = true;
        $this->cancelRequest($requestId);
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

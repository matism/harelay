<?php

namespace App\Services;

use App\Models\HaConnection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

/**
 * Manages tunnel connections and proxied requests.
 *
 * Uses Redis for communication with the tunnel server process.
 * Pending requests use Redis Hash for atomic operations to prevent race conditions.
 */
class TunnelManager
{
    private const CACHE_PREFIX = 'tunnel:';

    private const CONNECTION_TTL = 120; // seconds

    private const REQUEST_TTL = 60; // seconds

    private const CANCELLED_TTL = 10; // seconds - short TTL for cancelled request markers

    private const STATIC_CACHE_TTL = 86400; // 24 hours for static files

    private const STATIC_CACHE_PATHS = ['/frontend_latest/', '/static/', '/hacsfiles/', '/api/hassio/app/'];

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
        // Check if this is a cacheable static file (GET request to static paths)
        $isCacheable = $method === 'GET' && $this->isStaticPath($uri);

        // Check static cache first (skips entire tunnel round-trip)
        if ($isCacheable) {
            $staticCacheKey = $this->getStaticCacheKey($subdomain, $uri);
            $cached = Cache::store('redis')->get($staticCacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

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

        // Store request atomically using Redis HSET (prevents race conditions)
        $this->addPendingRequest($subdomain, $requestId, [
            'method' => $method,
            'uri' => $uri,
            'headers' => $headers,
            'body' => $body,
            'created_at' => now()->toIso8601String(),
        ]);

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

                // Cache successful static file responses
                if ($isCacheable && ($response['status_code'] ?? 0) === 200) {
                    $staticCacheKey = $this->getStaticCacheKey($subdomain, $uri);
                    Cache::store('redis')->put($staticCacheKey, $response, self::STATIC_CACHE_TTL);
                }

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
     * Check if a URI is a static cacheable path.
     */
    private function isStaticPath(string $uri): bool
    {
        foreach (self::STATIC_CACHE_PATHS as $path) {
            if (str_starts_with($uri, $path)) {
                return true;
            }
        }

        return false;
    }

    private function getStaticCacheKey(string $subdomain, string $uri): string
    {
        return self::CACHE_PREFIX.'static:'.$subdomain.':'.$uri;
    }

    /**
     * Add a pending request atomically using Redis HSET.
     */
    private function addPendingRequest(string $subdomain, string $requestId, array $data): void
    {
        $hashKey = $this->getPendingHashKey($subdomain);
        // Serialize the request data for storage in hash field
        $serialized = serialize($data);
        Redis::connection('cache')->hset($hashKey, $requestId, $serialized);
        // Set TTL on the hash (refreshes with each new request)
        Redis::connection('cache')->expire($hashKey, self::REQUEST_TTL);
    }

    /**
     * Remove a pending request atomically using Redis HDEL.
     */
    private function removePendingRequest(string $subdomain, string $requestId): void
    {
        $hashKey = $this->getPendingHashKey($subdomain);
        Redis::connection('cache')->hdel($hashKey, $requestId);
    }

    /**
     * Get all pending requests for a subdomain.
     */
    public function getPendingRequests(string $subdomain): array
    {
        $hashKey = $this->getPendingHashKey($subdomain);
        $raw = Redis::connection('cache')->hgetall($hashKey);

        if (empty($raw)) {
            return [];
        }

        $requests = [];
        foreach ($raw as $requestId => $serialized) {
            $requests[$requestId] = unserialize($serialized);
        }

        return $requests;
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

    private function getPendingHashKey(string $subdomain): string
    {
        // Use 'harelay:' prefix to match the cache store prefix
        return 'harelay:'.self::CACHE_PREFIX.'pending:'.$subdomain;
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
#!/usr/bin/env php
<?php

/**
 * HARelay Tunnel Server
 *
 * A WebSocket-based tunnel server that handles:
 * - Add-on connections (port 8081): Receives HTTP requests, forwards to HA add-on
 * - Browser WebSocket proxy (port 8082): Proxies WebSocket connections to Home Assistant
 *
 * Both servers run in a single process to share connection state.
 *
 * Environment variables:
 * - TUNNEL_HOST: Host to bind to (default: 0.0.0.0)
 * - TUNNEL_PORT: Port for add-on connections (default: 8081)
 * - WS_PROXY_PORT: Port for browser WebSocket proxy (default: 8082)
 * - TUNNEL_DEBUG: Enable verbose logging (default: false)
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\HaConnection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Workerman\Connection\TcpConnection;
use Workerman\Redis\Client as RedisClient;
use Workerman\Timer;
use Workerman\Worker;

// Configuration
$host = getenv('TUNNEL_HOST') ?: '0.0.0.0';
$tunnelPort = (int) (getenv('TUNNEL_PORT') ?: 8081);
$wsProxyPort = (int) (getenv('WS_PROXY_PORT') ?: 8082);
$debug = filter_var(getenv('TUNNEL_DEBUG'), FILTER_VALIDATE_BOOLEAN);

/**
 * Log a message to stdout.
 */
function tunnelLog(string $message, bool $debugOnly = false): void
{
    global $debug;
    if ($debugOnly && ! $debug) {
        return;
    }
    $timestamp = date('Y-m-d H:i:s');
    echo "[{$timestamp}] {$message}\n";
}

/**
 * Traffic tracking buffer - accumulates bytes and flushes periodically.
 * Structure: ['subdomain' => ['in' => bytes, 'out' => bytes], ...]
 */
$trafficBuffer = [];

/**
 * Track traffic bytes for a subdomain (buffered).
 * Accumulates in memory and flushes to database periodically.
 */
function trackTraffic(string $subdomain, int $bytesIn = 0, int $bytesOut = 0): void
{
    global $trafficBuffer;

    if (! isset($trafficBuffer[$subdomain])) {
        $trafficBuffer[$subdomain] = ['in' => 0, 'out' => 0];
    }

    $trafficBuffer[$subdomain]['in'] += $bytesIn;
    $trafficBuffer[$subdomain]['out'] += $bytesOut;
}

/**
 * Find a connection by subdomain (regular or app).
 * Returns [connection, is_app_subdomain, tunnel_subdomain] or [null, false, null] if not found.
 *
 * @return array{connection: object|null, is_app_subdomain: bool, tunnel_subdomain: string|null}
 */
function findConnectionBySubdomain(string $subdomain): array
{
    try {
        DB::reconnect();

        // First check regular subdomain (more common case)
        $connection = DB::table('ha_connections')
            ->where('subdomain', $subdomain)
            ->first();

        if ($connection) {
            return [
                'connection' => $connection,
                'is_app_subdomain' => false,
                'tunnel_subdomain' => $connection->subdomain,
            ];
        }

        // Then check app_subdomain
        $connection = DB::table('ha_connections')
            ->where('app_subdomain', $subdomain)
            ->first();

        if ($connection) {
            return [
                'connection' => $connection,
                'is_app_subdomain' => true,
                'tunnel_subdomain' => $connection->subdomain,
            ];
        }

        return ['connection' => null, 'is_app_subdomain' => false, 'tunnel_subdomain' => null];
    } catch (\Exception $e) {
        tunnelLog("Find connection error: {$e->getMessage()}");

        return ['connection' => null, 'is_app_subdomain' => false, 'tunnel_subdomain' => null];
    }
}

/**
 * Authenticate a session cookie and verify ownership of subdomain.
 * Returns user_id if valid, null otherwise.
 */
function authenticateSession(string $encryptedSessionId, string $subdomain): ?int
{
    try {
        // URL-decode the cookie value (browsers send cookies URL-encoded)
        $encryptedSessionId = urldecode($encryptedSessionId);

        // Decrypt the session cookie
        $decrypted = app('encrypter')->decrypt($encryptedSessionId, false);

        // Database sessions use format: hash|session_id
        $sessionId = str_contains($decrypted, '|')
            ? explode('|', $decrypted)[1]
            : $decrypted;

        DB::reconnect();

        // Query sessions table for user_id
        $session = DB::table('sessions')
            ->where('id', $sessionId)
            ->first();

        if (! $session || ! $session->user_id) {
            return null;
        }

        // Check session hasn't expired
        $lifetime = config('session.lifetime', 120) * 60;
        if (time() - $session->last_activity > $lifetime) {
            return null;
        }

        // Verify user owns this subdomain
        $connection = DB::table('ha_connections')
            ->where('subdomain', $subdomain)
            ->where('user_id', $session->user_id)
            ->first();

        return $connection ? (int) $session->user_id : null;
    } catch (\Exception $e) {
        tunnelLog("Session auth error: {$e->getMessage()}");

        return null;
    }
}

/**
 * Flush traffic buffer to database.
 * Updates both cumulative stats on ha_connections and daily stats on daily_traffic.
 * Reconnects if connection is stale.
 */
function flushTrafficBuffer(): void
{
    global $trafficBuffer;

    if (empty($trafficBuffer)) {
        return;
    }

    $today = date('Y-m-d');

    try {
        // Reconnect to handle stale connections in long-running process
        DB::reconnect();

        foreach ($trafficBuffer as $subdomain => $bytes) {
            // Get connection ID
            $connectionId = DB::table('ha_connections')
                ->where('subdomain', $subdomain)
                ->value('id');

            if (! $connectionId) {
                continue;
            }

            // Update cumulative stats on ha_connections (existing behavior)
            if ($bytes['in'] > 0 || $bytes['out'] > 0) {
                DB::table('ha_connections')
                    ->where('id', $connectionId)
                    ->update([
                        'bytes_in' => DB::raw("bytes_in + {$bytes['in']}"),
                        'bytes_out' => DB::raw("bytes_out + {$bytes['out']}"),
                    ]);
            }

            // Upsert daily stats
            DB::statement(
                'INSERT INTO daily_traffic (ha_connection_id, date, bytes_in, bytes_out)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                 bytes_in = bytes_in + VALUES(bytes_in),
                 bytes_out = bytes_out + VALUES(bytes_out)',
                [$connectionId, $today, $bytes['in'], $bytes['out']]
            );
        }

        $totalIn = array_sum(array_column($trafficBuffer, 'in'));
        $totalOut = array_sum(array_column($trafficBuffer, 'out'));
        $count = count($trafficBuffer);
        tunnelLog("Traffic flushed: {$count} connections, in={$totalIn}, out={$totalOut}", true);

        // Clear buffer after successful flush
        $trafficBuffer = [];
    } catch (\Exception $e) {
        tunnelLog("Traffic flush error: {$e->getMessage()}");
        // Keep buffer to retry next time
    }
}

tunnelLog('HARelay Tunnel Server starting');
tunnelLog("  Add-on port: {$tunnelPort}");
tunnelLog("  WebSocket proxy port: {$wsProxyPort}");
tunnelLog('  Debug mode: '.($debug ? 'enabled' : 'disabled'));

// Shared state
$addonConnections = [];     // subdomain => TcpConnection
$browserWsConnections = []; // subdomain => [conn_id => TcpConnection]
$addonWsStreams = [];       // subdomain => [stream_id => browser_conn_id]

// =============================================================================
// Main Worker
// =============================================================================

$tunnelWorker = new Worker("websocket://{$host}:{$tunnelPort}");
$tunnelWorker->count = 1;
$tunnelWorker->name = 'HARelayTunnel';

$tunnelWorker->onWorkerStart = function () use (&$addonConnections, &$browserWsConnections, &$addonWsStreams, $host, $wsProxyPort) {
    tunnelLog('Worker started');

    // ---------------------------------------------------------------------
    // Browser WebSocket Proxy Server
    // ---------------------------------------------------------------------
    $wsProxy = new Worker("websocket://{$host}:{$wsProxyPort}");

    $wsProxy->onConnect = function (TcpConnection $conn) {
        tunnelLog("WS proxy: browser connected (id={$conn->id})", true);
        $conn->subdomain = null;
        $conn->streamId = null;
        $conn->authenticated = false;
        $conn->transparentAuth = false;
    };

    // Transparent WebSocket authentication via session cookie during HTTP upgrade
    $wsProxy->onWebSocketConnect = function (TcpConnection $conn, $request) use (&$addonConnections) {
        // Extract subdomain from Host header (supports 16 or 32 char subdomains)
        $host = $request->host();
        $proxyDomain = config('app.proxy_domain', 'harelay.com');

        if (! preg_match('/^([a-z0-9]{2,32})\.'.preg_quote($proxyDomain, '/').'$/i', $host, $matches)) {
            tunnelLog("WS proxy: invalid host: {$host}");
            $conn->close();

            return;
        }
        $subdomain = strtolower($matches[1]);

        // Validate path - only HA WebSocket paths trigger transparent auth
        $path = $request->path();
        if (! preg_match('#^/api/websocket$#', $path)) {
            // Not /api/websocket - let onMessage handle with explicit auth message
            return;
        }

        // Find connection by subdomain or app_subdomain
        [
            'connection' => $connection,
            'is_app_subdomain' => $isAppSubdomain,
            'tunnel_subdomain' => $tunnelSubdomain,
        ] = findConnectionBySubdomain($subdomain);

        if (! $connection) {
            tunnelLog("WS proxy: connection not found for {$subdomain}");
            $conn->close();

            return;
        }

        // Check tunnel connected (using the regular subdomain, not app_subdomain)
        if (! isset($addonConnections[$tunnelSubdomain])) {
            tunnelLog("WS proxy: tunnel not connected for {$tunnelSubdomain}");
            $conn->close();

            return;
        }

        // App subdomain access - no authentication required (URL is the auth)
        if ($isAppSubdomain) {
            $conn->subdomain = $subdomain;
            $conn->tunnelSubdomain = $tunnelSubdomain;
            $conn->path = $path;
            $conn->userId = $connection->user_id;
            $conn->authenticated = true;
            $conn->streamId = bin2hex(random_bytes(16));
            $conn->transparentAuth = true;

            tunnelLog("WS proxy: app_subdomain access {$subdomain} -> {$tunnelSubdomain}", true);

            return;
        }

        // Regular subdomain - authenticate via session cookie
        $userId = null;
        $sessionCookie = $request->cookie(config('session.cookie', 'laravel_session'));
        if ($sessionCookie) {
            $userId = authenticateSession($sessionCookie, $subdomain);
        }

        if (! $userId) {
            tunnelLog("WS proxy: auth failed for {$subdomain}");
            $conn->close();

            return;
        }

        // Mark as authenticated
        $conn->subdomain = $subdomain;
        $conn->tunnelSubdomain = $tunnelSubdomain;
        $conn->path = $path;
        $conn->userId = $userId;
        $conn->authenticated = true;
        $conn->streamId = bin2hex(random_bytes(16));
        $conn->transparentAuth = true;

        tunnelLog("WS proxy: authenticated {$subdomain} via cookie (user={$userId})", true);
    };

    // Set up stream after transparent auth handshake completes
    $wsProxy->onWebSocketConnected = function (TcpConnection $conn, $request) use (&$addonConnections, &$browserWsConnections, &$addonWsStreams) {
        // Only for transparent auth (cookie-based or app_subdomain)
        if (! ($conn->transparentAuth ?? false)) {
            return;
        }

        // Use tunnel subdomain for add-on communication (regular subdomain, not app_subdomain)
        $tunnelSubdomain = $conn->tunnelSubdomain ?? $conn->subdomain;

        if (! isset($browserWsConnections[$tunnelSubdomain])) {
            $browserWsConnections[$tunnelSubdomain] = [];
        }
        if (! isset($addonWsStreams[$tunnelSubdomain])) {
            $addonWsStreams[$tunnelSubdomain] = [];
        }

        $browserWsConnections[$tunnelSubdomain][$conn->id] = $conn;
        $addonWsStreams[$tunnelSubdomain][$conn->streamId] = $conn->id;

        // Tell add-on to open WebSocket to HA
        $addonConnections[$tunnelSubdomain]->send(json_encode([
            'type' => 'ws_open',
            'stream_id' => $conn->streamId,
            'path' => $conn->path,
        ]));

        tunnelLog("WS proxy: stream opened {$tunnelSubdomain} (stream={$conn->streamId})", true);
    };

    $wsProxy->onMessage = function (TcpConnection $conn, $data) use (&$addonConnections, &$browserWsConnections, &$addonWsStreams) {
        // If not using transparent auth, first message must be explicit authentication
        if (! $conn->authenticated) {
            $message = json_decode($data, true);

            if (! $message || ($message['type'] ?? '') !== 'auth' || empty($message['subdomain'])) {
                $conn->send(json_encode(['type' => 'error', 'error' => 'Invalid auth message']));
                $conn->close();

                return;
            }

            $subdomain = preg_replace('/[^a-z0-9]/', '', strtolower($message['subdomain']));
            $path = $message['path'] ?? '/api/websocket';

            // Validate path to prevent path traversal
            if (! preg_match('#^/api/websocket$#', $path)) {
                $conn->send(json_encode(['type' => 'error', 'error' => 'Invalid WebSocket path']));
                $conn->close();

                return;
            }

            // Find connection by subdomain or app_subdomain
            [
                'connection' => $connection,
                'is_app_subdomain' => $isAppSubdomain,
                'tunnel_subdomain' => $tunnelSubdomain,
            ] = findConnectionBySubdomain($subdomain);

            if (! $connection) {
                tunnelLog("WS proxy: connection not found for {$subdomain}");
                $conn->send(json_encode(['type' => 'error', 'error' => 'Connection not found']));
                $conn->close();

                return;
            }

            // Check if add-on is connected (using tunnel subdomain)
            if (! isset($addonConnections[$tunnelSubdomain])) {
                tunnelLog("WS proxy: auth failed - tunnel not connected for {$tunnelSubdomain}");
                $conn->send(json_encode(['type' => 'error', 'error' => 'Tunnel not connected']));
                $conn->close();

                return;
            }

            $conn->subdomain = $subdomain;
            $conn->tunnelSubdomain = $tunnelSubdomain;
            $conn->streamId = bin2hex(random_bytes(16));
            $conn->authenticated = true;

            // Initialize subdomain arrays if needed (using tunnel subdomain)
            if (! isset($browserWsConnections[$tunnelSubdomain])) {
                $browserWsConnections[$tunnelSubdomain] = [];
            }
            if (! isset($addonWsStreams[$tunnelSubdomain])) {
                $addonWsStreams[$tunnelSubdomain] = [];
            }

            // Register connections
            $browserWsConnections[$tunnelSubdomain][$conn->id] = $conn;
            $addonWsStreams[$tunnelSubdomain][$conn->streamId] = $conn->id;

            $streamCount = count($addonWsStreams[$tunnelSubdomain]);
            tunnelLog("WS proxy: authenticated {$subdomain} -> {$tunnelSubdomain} (stream={$conn->streamId}, total streams={$streamCount})", true);

            // Tell add-on to open WebSocket to HA
            $addonConnections[$tunnelSubdomain]->send(json_encode([
                'type' => 'ws_open',
                'stream_id' => $conn->streamId,
                'path' => $path,
            ]));

            return;
        }

        // Forward subsequent messages to add-on (using tunnel subdomain)
        $tunnelSubdomain = $conn->tunnelSubdomain ?? $conn->subdomain;
        if (isset($addonConnections[$tunnelSubdomain])) {
            // Track incoming WebSocket bytes
            trackTraffic($tunnelSubdomain, strlen($data), 0);

            $addonConnections[$tunnelSubdomain]->send(json_encode([
                'type' => 'ws_message',
                'stream_id' => $conn->streamId,
                'message' => $data,
            ]));
        }
    };

    $wsProxy->onClose = function (TcpConnection $conn) use (&$addonConnections, &$browserWsConnections, &$addonWsStreams) {
        if (! $conn->subdomain || ! $conn->streamId) {
            return;
        }

        // Use tunnel subdomain for add-on communication
        $tunnelSubdomain = $conn->tunnelSubdomain ?? $conn->subdomain;

        tunnelLog("WS proxy: browser disconnected {$conn->subdomain} (stream={$conn->streamId})", true);

        // Tell add-on to close HA WebSocket
        if (isset($addonConnections[$tunnelSubdomain])) {
            $addonConnections[$tunnelSubdomain]->send(json_encode([
                'type' => 'ws_close',
                'stream_id' => $conn->streamId,
            ]));
        }

        // Cleanup
        unset($browserWsConnections[$tunnelSubdomain][$conn->id]);
        unset($addonWsStreams[$tunnelSubdomain][$conn->streamId]);
    };

    $wsProxy->listen();
    tunnelLog("WebSocket proxy listening on port {$wsProxyPort}");

    // ---------------------------------------------------------------------
    // HTTP Request Polling
    // ---------------------------------------------------------------------
    Timer::add(0.005, function () use (&$addonConnections) {  // 5ms polling for fast response
        foreach ($addonConnections as $subdomain => $conn) {
            $pendingKey = "tunnel:pending:{$subdomain}";
            $requests = Cache::store('redis')->get($pendingKey, []);

            if (empty($requests)) {
                continue;
            }

            foreach ($requests as $requestId => $request) {
                // Check if request was cancelled by client disconnect
                $cancelledKey = "tunnel:cancelled:{$requestId}";
                if (Cache::store('redis')->get($cancelledKey)) {
                    tunnelLog("HTTP -> {$subdomain}: CANCELLED {$request['method']} {$request['uri']}", true);
                    Cache::store('redis')->forget($cancelledKey);

                    continue;
                }

                tunnelLog("HTTP -> {$subdomain}: {$request['method']} {$request['uri']}", true);

                // Track incoming bytes (request body from user)
                $bodyBytes = 0;
                if (! empty($request['body'])) {
                    if ($request['body_encoded'] ?? false) {
                        $bodyBytes = (int) (strlen($request['body']) * 3 / 4); // Base64 decode estimate
                    } else {
                        $bodyBytes = strlen($request['body']);
                    }
                }
                if ($bodyBytes > 0) {
                    trackTraffic($subdomain, $bodyBytes, 0);
                }

                $conn->send(json_encode([
                    'type' => 'request',
                    'request_id' => $requestId,
                    'method' => $request['method'],
                    'uri' => $request['uri'],
                    'headers' => $request['headers'],
                    'body' => $request['body'] ?? null,
                    'body_encoded' => $request['body_encoded'] ?? false,
                ]));
            }

            Cache::store('redis')->forget($pendingKey);
        }
    });

    // ---------------------------------------------------------------------
    // Subdomain Change Subscription (event-driven via Redis Pub/Sub)
    // ---------------------------------------------------------------------
    $redisHost = config('database.redis.default.host', '127.0.0.1');
    $redisPort = config('database.redis.default.port', 6379);
    $redisPassword = config('database.redis.default.password');
    // Laravel's Redis facade adds this prefix to channel names
    $redisPrefix = config('database.redis.options.prefix', '');

    $redisOptions = $redisPassword ? ['auth' => $redisPassword] : [];

    // Channel name must match what Laravel publishes (with prefix)
    $subdomainChangeChannel = $redisPrefix.'tunnel:subdomain_changes';

    tunnelLog("Redis subscriber connecting to {$redisHost}:{$redisPort}");

    $redisSubscriber = new RedisClient("redis://{$redisHost}:{$redisPort}", $redisOptions, function ($success, $client) use (&$addonConnections, $subdomainChangeChannel) {
        if (! $success) {
            tunnelLog('ERROR: Redis subscriber failed to connect: '.$client->error());

            return;
        }
        tunnelLog("Redis subscriber connected, subscribing to {$subdomainChangeChannel}");

        $client->subscribe($subdomainChangeChannel, function ($channel, $message) use (&$addonConnections) {
            tunnelLog("Redis pub/sub received: {$message}");

            $change = json_decode($message, true);
            if (! $change || empty($change['old']) || empty($change['new'])) {
                tunnelLog('Invalid subdomain change message');

                return;
            }

            $oldSubdomain = $change['old'];
            $newSubdomain = $change['new'];

            // Only process if the old subdomain has an active connection
            if (! isset($addonConnections[$oldSubdomain])) {
                tunnelLog("Subdomain change ignored (not connected): {$oldSubdomain} -> {$newSubdomain}");

                return;
            }

            $conn = $addonConnections[$oldSubdomain];
            tunnelLog("Subdomain change: {$oldSubdomain} -> {$newSubdomain}");

            // Update the connection mapping
            unset($addonConnections[$oldSubdomain]);
            $conn->subdomain = $newSubdomain;
            $addonConnections[$newSubdomain] = $conn;

            // Notify the add-on of the new subdomain
            $conn->send(json_encode([
                'type' => 'subdomain_changed',
                'old_subdomain' => $oldSubdomain,
                'new_subdomain' => $newSubdomain,
            ]));
        });
    });

    // ---------------------------------------------------------------------
    // Traffic Buffer Flush (every 30 seconds)
    // ---------------------------------------------------------------------
    Timer::add(30, function () {
        flushTrafficBuffer();
    });

    // ---------------------------------------------------------------------
    // Server-side Keepalive Check (every 30 seconds)
    // Ping all connected add-ons and close stale connections
    // ---------------------------------------------------------------------
    Timer::add(30, function () use (&$addonConnections) {
        $now = time();
        $staleTimeout = 60; // Close connections with no response for 60 seconds

        foreach ($addonConnections as $subdomain => $conn) {
            // Check if connection is stale (no pong received recently)
            $lastPong = $conn->lastPong ?? $now;
            $timeSinceLastPong = $now - $lastPong;

            if ($timeSinceLastPong > $staleTimeout) {
                tunnelLog("Add-on: stale connection for {$subdomain} (no response for {$timeSinceLastPong}s), closing");
                $conn->close();
                continue;
            }

            // Send ping to check if connection is alive
            try {
                $conn->send(json_encode(['type' => 'ping']));
            } catch (\Exception $e) {
                tunnelLog("Add-on: failed to ping {$subdomain}: {$e->getMessage()}");
                $conn->close();
            }
        }
    });
};

// =============================================================================
// Add-on Connection Handlers
// =============================================================================

$tunnelWorker->onConnect = function (TcpConnection $conn) {
    tunnelLog("Add-on: new connection (id={$conn->id})", true);
    $conn->authenticated = false;
    $conn->subdomain = null;
    $conn->lastPong = time();  // Track last response for keepalive
};

$tunnelWorker->onMessage = function (TcpConnection $conn, $data) use (&$addonConnections, &$browserWsConnections, &$addonWsStreams) {
    $message = json_decode($data, true);

    if (! is_array($message) || ! isset($message['type'])) {
        $conn->send(json_encode(['type' => 'error', 'error' => 'Invalid message format']));

        return;
    }

    $type = $message['type'];

    // -------------------------------------------------------------------------
    // Authentication
    // -------------------------------------------------------------------------
    if ($type === 'auth') {
        $subdomain = preg_replace('/[^a-z0-9]/', '', strtolower($message['subdomain'] ?? ''));
        $token = $message['token'] ?? '';

        if (empty($subdomain) || empty($token)) {
            $conn->send(json_encode(['type' => 'auth_result', 'success' => false, 'error' => 'Missing credentials']));
            $conn->close();

            return;
        }

        $haConn = HaConnection::where('subdomain', $subdomain)->first();

        if (! $haConn || ! Hash::check($token, $haConn->connection_token)) {
            tunnelLog("Add-on: auth failed for {$subdomain}");
            $conn->send(json_encode(['type' => 'auth_result', 'success' => false, 'error' => 'Invalid credentials']));
            $conn->close();

            return;
        }

        // Disconnect existing connection for this subdomain
        if (isset($addonConnections[$subdomain])) {
            tunnelLog("Add-on: replacing existing connection for {$subdomain}");
            $addonConnections[$subdomain]->close();
        }

        $conn->authenticated = true;
        $conn->subdomain = $subdomain;
        $addonConnections[$subdomain] = $conn;

        $haConn->update(['status' => 'connected', 'last_connected_at' => now()]);

        tunnelLog("Add-on: authenticated {$subdomain}");
        $conn->send(json_encode(['type' => 'auth_result', 'success' => true, 'subdomain' => $subdomain]));

        return;
    }

    // Require authentication for all other message types
    if (! $conn->authenticated) {
        $conn->send(json_encode(['type' => 'error', 'error' => 'Not authenticated']));

        return;
    }

    // -------------------------------------------------------------------------
    // HTTP Response
    // -------------------------------------------------------------------------
    if ($type === 'response') {
        $requestId = $message['request_id'] ?? '';
        if (empty($requestId)) {
            return;
        }

        // Check if request was cancelled - discard response immediately
        $cancelledKey = "tunnel:cancelled:{$requestId}";
        if (Cache::store('redis')->get($cancelledKey)) {
            tunnelLog("HTTP <- {$conn->subdomain}: DISCARDED (cancelled)", true);
            Cache::store('redis')->forget($cancelledKey);

            return;
        }

        $statusCode = (int) ($message['status_code'] ?? 502);
        tunnelLog("HTTP <- {$conn->subdomain}: {$statusCode}", true);

        // Track outgoing bytes (response body to user)
        $body = $message['body'] ?? '';
        if (! empty($body)) {
            // Body is base64 encoded, calculate actual size
            $bodyBytes = (int) (strlen($body) * 3 / 4);
            trackTraffic($conn->subdomain, 0, $bodyBytes);
        }

        // Store response in cache - TunnelManager is polling for this
        Cache::store('redis')->put("tunnel:response:{$requestId}", [
            'status_code' => $statusCode,
            'headers' => $message['headers'] ?? [],
            'body' => $body,
            'is_base64' => true,
        ], 60);

        return;
    }

    // -------------------------------------------------------------------------
    // WebSocket Message from HA
    // -------------------------------------------------------------------------
    if ($type === 'ws_message') {
        $streamId = $message['stream_id'] ?? '';
        $wsMessage = $message['message'] ?? '';
        $subdomain = $conn->subdomain;

        if (empty($streamId)) {
            tunnelLog('WS: received message with empty stream_id', true);

            return;
        }

        if (! isset($addonWsStreams[$subdomain][$streamId])) {
            tunnelLog("WS: stream {$streamId} not found (stale message?)", true);

            return;
        }

        $browserConnId = $addonWsStreams[$subdomain][$streamId];
        if (isset($browserWsConnections[$subdomain][$browserConnId])) {
            try {
                // Track outgoing WebSocket bytes
                trackTraffic($subdomain, 0, strlen($wsMessage));

                $browserWsConnections[$subdomain][$browserConnId]->send($wsMessage);
            } catch (\Exception $e) {
                tunnelLog("WS: failed to send to browser for stream {$streamId}: {$e->getMessage()}");
                // Clean up broken connection
                unset($browserWsConnections[$subdomain][$browserConnId]);
                unset($addonWsStreams[$subdomain][$streamId]);
            }
        } else {
            tunnelLog("WS: browser connection {$browserConnId} not found for stream {$streamId}", true);
            unset($addonWsStreams[$subdomain][$streamId]);
        }

        return;
    }

    // -------------------------------------------------------------------------
    // WebSocket Closed by HA
    // -------------------------------------------------------------------------
    if ($type === 'ws_closed') {
        $streamId = $message['stream_id'] ?? '';
        $subdomain = $conn->subdomain;

        tunnelLog("WS: stream closed by HA {$streamId}", true);

        if (isset($addonWsStreams[$subdomain][$streamId])) {
            $browserConnId = $addonWsStreams[$subdomain][$streamId];
            if (isset($browserWsConnections[$subdomain][$browserConnId])) {
                $browserWsConnections[$subdomain][$browserConnId]->close();
            }
            unset($addonWsStreams[$subdomain][$streamId]);
        }

        return;
    }

    // -------------------------------------------------------------------------
    // Heartbeat (from add-on)
    // -------------------------------------------------------------------------
    if ($type === 'heartbeat') {
        $conn->lastPong = time();  // Add-on is alive
        if ($conn->subdomain) {
            HaConnection::where('subdomain', $conn->subdomain)
                ->update(['last_connected_at' => now()]);
        }
        $conn->send(json_encode(['type' => 'pong']));

        return;
    }

    // -------------------------------------------------------------------------
    // Pong (response to our ping)
    // -------------------------------------------------------------------------
    if ($type === 'pong') {
        $conn->lastPong = time();  // Add-on responded to our ping

        return;
    }
};

$tunnelWorker->onClose = function (TcpConnection $conn) use (&$addonConnections, &$browserWsConnections, &$addonWsStreams) {
    if (! $conn->subdomain) {
        return;
    }

    tunnelLog("Add-on: disconnected {$conn->subdomain}");
    unset($addonConnections[$conn->subdomain]);

    // Close all browser WebSocket connections for this subdomain
    if (isset($browserWsConnections[$conn->subdomain])) {
        foreach ($browserWsConnections[$conn->subdomain] as $browserConn) {
            $browserConn->close();
        }
        unset($browserWsConnections[$conn->subdomain]);
    }
    unset($addonWsStreams[$conn->subdomain]);

    HaConnection::where('subdomain', $conn->subdomain)
        ->update(['status' => 'disconnected']);
};

$tunnelWorker->onWorkerStop = function () {
    tunnelLog('Worker stopping - flushing traffic buffer...');
    flushTrafficBuffer();
    tunnelLog('Traffic buffer flushed');
};

// =============================================================================
// Run
// =============================================================================

Worker::$daemonize = false;
Worker::runAll();

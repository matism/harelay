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
 * Track traffic bytes for a subdomain.
 * Uses atomic increment to handle concurrent updates.
 */
function trackTraffic(string $subdomain, int $bytesIn = 0, int $bytesOut = 0): void
{
    try {
        if ($bytesIn > 0) {
            DB::table('ha_connections')
                ->where('subdomain', $subdomain)
                ->increment('bytes_in', $bytesIn);
        }
        if ($bytesOut > 0) {
            DB::table('ha_connections')
                ->where('subdomain', $subdomain)
                ->increment('bytes_out', $bytesOut);
        }
    } catch (\Exception $e) {
        tunnelLog("Traffic tracking error: {$e->getMessage()}", true);
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
    };

    $wsProxy->onMessage = function (TcpConnection $conn, $data) use (&$addonConnections, &$browserWsConnections, &$addonWsStreams) {
        // First message must be authentication
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
            if (! preg_match('#^/api/(websocket|hassio)#', $path)) {
                $conn->send(json_encode(['type' => 'error', 'error' => 'Invalid WebSocket path']));
                $conn->close();

                return;
            }

            // Check if add-on is connected
            if (! isset($addonConnections[$subdomain])) {
                tunnelLog("WS proxy: auth failed - tunnel not connected for {$subdomain}");
                $conn->send(json_encode(['type' => 'error', 'error' => 'Tunnel not connected']));
                $conn->close();

                return;
            }

            $conn->subdomain = $subdomain;
            $conn->streamId = bin2hex(random_bytes(16));
            $conn->authenticated = true;

            // Initialize subdomain arrays if needed
            if (! isset($browserWsConnections[$subdomain])) {
                $browserWsConnections[$subdomain] = [];
            }
            if (! isset($addonWsStreams[$subdomain])) {
                $addonWsStreams[$subdomain] = [];
            }

            // Register connections
            $browserWsConnections[$subdomain][$conn->id] = $conn;
            $addonWsStreams[$subdomain][$conn->streamId] = $conn->id;

            $streamCount = count($addonWsStreams[$subdomain]);
            tunnelLog("WS proxy: authenticated {$subdomain} (stream={$conn->streamId}, total streams={$streamCount})", true);

            // Tell add-on to open WebSocket to HA
            $addonConnections[$subdomain]->send(json_encode([
                'type' => 'ws_open',
                'stream_id' => $conn->streamId,
                'path' => $path,
            ]));

            return;
        }

        // Forward subsequent messages to add-on
        if (isset($addonConnections[$conn->subdomain])) {
            // Track incoming WebSocket bytes
            trackTraffic($conn->subdomain, strlen($data), 0);

            $addonConnections[$conn->subdomain]->send(json_encode([
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

        tunnelLog("WS proxy: browser disconnected {$conn->subdomain} (stream={$conn->streamId})", true);

        // Tell add-on to close HA WebSocket
        if (isset($addonConnections[$conn->subdomain])) {
            $addonConnections[$conn->subdomain]->send(json_encode([
                'type' => 'ws_close',
                'stream_id' => $conn->streamId,
            ]));
        }

        // Cleanup
        unset($browserWsConnections[$conn->subdomain][$conn->id]);
        unset($addonWsStreams[$conn->subdomain][$conn->streamId]);
    };

    $wsProxy->listen();
    tunnelLog("WebSocket proxy listening on port {$wsProxyPort}");

    // ---------------------------------------------------------------------
    // HTTP Request Polling
    // ---------------------------------------------------------------------
    Timer::add(0.05, function () use (&$addonConnections) {
        foreach ($addonConnections as $subdomain => $conn) {
            $pendingKey = "tunnel:pending:{$subdomain}";
            $requests = Cache::store('file')->get($pendingKey, []);

            if (empty($requests)) {
                continue;
            }

            foreach ($requests as $requestId => $request) {
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

            Cache::store('file')->forget($pendingKey);
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

        $statusCode = (int) ($message['status_code'] ?? 502);
        tunnelLog("HTTP <- {$conn->subdomain}: {$statusCode}", true);

        // Track outgoing bytes (response body to user)
        $body = $message['body'] ?? '';
        if (! empty($body)) {
            // Body is base64 encoded, calculate actual size
            $bodyBytes = (int) (strlen($body) * 3 / 4);
            trackTraffic($conn->subdomain, 0, $bodyBytes);
        }

        Cache::store('file')->put("tunnel:response:{$requestId}", [
            'status_code' => $statusCode,
            'headers' => $message['headers'] ?? [],
            'body' => $body,
            'is_base64' => true,
        ], 30);

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
    // Heartbeat
    // -------------------------------------------------------------------------
    if ($type === 'heartbeat') {
        if ($conn->subdomain) {
            HaConnection::where('subdomain', $conn->subdomain)
                ->update(['last_connected_at' => now()]);
        }
        $conn->send(json_encode(['type' => 'pong']));

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

// =============================================================================
// Run
// =============================================================================

Worker::$daemonize = false;
Worker::runAll();

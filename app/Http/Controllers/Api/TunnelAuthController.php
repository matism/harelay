<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HaConnection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Pusher\Pusher;

class TunnelAuthController extends Controller
{
    /**
     * Authenticate an HA add-on for the tunnel channel.
     * This is a custom auth endpoint that uses connection tokens
     * instead of user sessions.
     */
    public function auth(Request $request): JsonResponse
    {
        $request->validate([
            'socket_id' => 'required|string',
            'channel_name' => 'required|string|starts_with:private-tunnel.',
            'subdomain' => 'required|string',
            'token' => 'required|string',
        ]);

        $subdomain = $request->input('subdomain');
        $token = $request->input('token');
        $channelName = $request->input('channel_name');
        $socketId = $request->input('socket_id');

        // Verify the channel matches the subdomain
        $expectedChannel = "private-tunnel.{$subdomain}";
        if ($channelName !== $expectedChannel) {
            return response()->json(['error' => 'Channel mismatch'], 403);
        }

        // Find and verify the connection
        $connection = HaConnection::where('subdomain', $subdomain)->first();

        if (! $connection) {
            return response()->json(['error' => 'Connection not found'], 404);
        }

        if (! Hash::check($token, $connection->connection_token)) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        // Generate Pusher auth signature
        $pusher = new Pusher(
            config('broadcasting.connections.reverb.key'),
            config('broadcasting.connections.reverb.secret'),
            config('broadcasting.connections.reverb.app_id'),
            [
                'host' => config('broadcasting.connections.reverb.options.host'),
                'port' => config('broadcasting.connections.reverb.options.port'),
                'scheme' => config('broadcasting.connections.reverb.options.scheme'),
            ]
        );

        $auth = $pusher->authorizeChannel($channelName, $socketId);

        // Update connection status
        $connection->update([
            'status' => 'connected',
            'last_connected_at' => now(),
        ]);

        return response()->json(json_decode($auth, true));
    }
}

<?php

use App\Models\HaConnection;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Tunnel channel for HA add-ons
// Note: Authentication for this channel is handled via custom TunnelAuthController
// because add-ons authenticate with connection tokens, not user sessions
Broadcast::channel('tunnel.{subdomain}', function ($user, $subdomain) {
    // For browser clients (dashboard), check if user owns this connection
    if ($user) {
        $connection = HaConnection::where('subdomain', $subdomain)
            ->where('user_id', $user->id)
            ->first();

        return $connection !== null;
    }

    return false;
});

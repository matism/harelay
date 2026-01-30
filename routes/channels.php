<?php

use App\Models\HaConnection;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Note: Tunnel communication uses WebSocket via tunnel-server.php
// This channel is reserved for future dashboard real-time features
Broadcast::channel('tunnel.{subdomain}', function ($user, $subdomain) {
    if (! $user) {
        return false;
    }

    return HaConnection::where('subdomain', $subdomain)
        ->where('user_id', $user->id)
        ->exists();
});

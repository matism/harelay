<?php

use App\Http\Controllers\Api\DeviceCodeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Note: Tunnel communication uses WebSocket via tunnel-server.php
| instead of HTTP API endpoints.
|
*/

// Device pairing (no auth required - add-on calls these)
Route::prefix('device')->group(function () {
    Route::post('/code', [DeviceCodeController::class, 'create']);
    Route::get('/poll/{deviceCode}', [DeviceCodeController::class, 'poll']);
});

// Connection status check (for dashboard auto-refresh)
Route::middleware(['web', 'auth'])->get('/connection/status', function (Request $request) {
    $connection = $request->user()->haConnection;

    if (! $connection) {
        return response()->json(['connected' => false, 'exists' => false]);
    }

    return response()->json([
        'connected' => $connection->isConnected(),
        'exists' => true,
        'last_connected_at' => $connection->last_connected_at?->toIso8601String(),
    ]);
});

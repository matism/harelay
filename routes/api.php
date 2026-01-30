<?php

use App\Http\Controllers\Api\TunnelApiController;
use App\Http\Controllers\Api\TunnelAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

// Tunnel API endpoints (for HA add-on communication)
Route::prefix('tunnel')->group(function () {
    // Add-on authentication for WebSocket channel
    Route::post('/auth', [TunnelAuthController::class, 'auth']);

    // Add-on lifecycle
    Route::post('/connect', [TunnelApiController::class, 'connect']);
    Route::post('/disconnect', [TunnelApiController::class, 'disconnect']);
    Route::post('/heartbeat', [TunnelApiController::class, 'heartbeat']);

    // Request/Response handling
    Route::post('/response', [TunnelApiController::class, 'submitResponse']);
    Route::post('/poll', [TunnelApiController::class, 'pollRequests']);
});

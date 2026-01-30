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
// Rate limited to prevent abuse
Route::prefix('tunnel')->middleware('throttle:120,1')->group(function () {
    // Add-on authentication for WebSocket channel
    Route::post('/auth', [TunnelAuthController::class, 'auth']);

    // Add-on lifecycle
    Route::post('/connect', [TunnelApiController::class, 'connect'])
        ->middleware('throttle:10,1'); // 10 connects per minute max

    Route::post('/disconnect', [TunnelApiController::class, 'disconnect'])
        ->middleware('throttle:10,1');

    Route::post('/heartbeat', [TunnelApiController::class, 'heartbeat']);

    // Request/Response handling - higher limit for active tunneling
    Route::post('/response', [TunnelApiController::class, 'submitResponse'])
        ->middleware('throttle:300,1'); // Allow high throughput for responses

    Route::post('/poll', [TunnelApiController::class, 'pollRequests']);
});

<?php

use App\Http\Controllers\Api\DeviceCodeController;
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

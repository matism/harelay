<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceCodeController extends Controller
{
    public function create(Request $request): JsonResponse
    {
        $request->validate(['device_name' => 'nullable|string|max:100']);

        $deviceCode = DeviceCode::create([
            'device_code' => DeviceCode::generateDeviceCode(),
            'user_code' => DeviceCode::generateUserCode(),
            'device_name' => $request->input('device_name', 'Home Assistant'),
            'status' => 'pending',
            'expires_at' => now()->addMinutes(15),
        ]);

        return response()->json([
            'device_code' => $deviceCode->device_code,
            'user_code' => $deviceCode->user_code,
            'verification_url' => config('app.url').'/link',
            'expires_in' => 900,
            'interval' => 5,
        ]);
    }

    public function poll(string $deviceCode): JsonResponse
    {
        $device = DeviceCode::where('device_code', $deviceCode)->first();

        if (! $device) {
            return response()->json(['error' => 'Invalid device code'], 404);
        }

        if ($device->isExpired()) {
            $device->update(['status' => 'expired']);

            return response()->json(['status' => 'expired'], 410);
        }

        if ($device->status === 'linked') {
            $response = [
                'status' => 'linked',
                'subdomain' => $device->subdomain,
                'token' => $device->connection_token,
                'server_url' => config('app.url'),
            ];
            $device->update(['status' => 'used', 'connection_token' => null]);

            return response()->json($response);
        }

        return response()->json(['status' => $device->status]);
    }
}

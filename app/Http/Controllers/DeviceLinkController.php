<?php

namespace App\Http\Controllers;

use App\Models\DeviceCode;
use App\Models\HaConnection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DeviceLinkController extends Controller
{
    public function show(): View
    {
        return view('device.link');
    }

    public function link(Request $request): RedirectResponse
    {
        $request->validate(['user_code' => 'required|string|size:9']);

        $device = DeviceCode::where('user_code', strtoupper($request->user_code))
            ->where('status', 'pending')
            ->first();

        if (! $device || $device->isExpired()) {
            return back()->withErrors(['user_code' => 'Invalid or expired code']);
        }

        $user = $request->user();

        $connection = $user->haConnection;
        if (! $connection) {
            $plainToken = HaConnection::generateConnectionToken();
            $connection = HaConnection::create([
                'user_id' => $user->id,
                'subdomain' => HaConnection::generateSubdomain(),
                'connection_token' => Hash::make($plainToken),
                'status' => 'disconnected',
            ]);
        } else {
            $plainToken = HaConnection::generateConnectionToken();
            $connection->update([
                'connection_token' => Hash::make($plainToken),
                'status' => 'disconnected',
            ]);
        }

        $device->update([
            'user_id' => $user->id,
            'subdomain' => $connection->subdomain,
            'connection_token' => $plainToken,
            'status' => 'linked',
            'linked_at' => now(),
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Device linked successfully! Your add-on will connect automatically.');
    }
}

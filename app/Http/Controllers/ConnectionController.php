<?php

namespace App\Http\Controllers;

use App\Models\HaConnection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ConnectionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->haConnection) {
            return redirect()->route('dashboard.settings')
                ->with('error', 'You already have a connection configured.');
        }

        $plainToken = HaConnection::generateConnectionToken();

        $user->haConnection()->create([
            'subdomain' => HaConnection::generateSubdomain(),
            'connection_token' => Hash::make($plainToken),
            'status' => 'disconnected',
        ]);

        return redirect()->route('dashboard.setup')
            ->with('plain_token', $plainToken)
            ->with('success', 'Connection created successfully!');
    }

    public function regenerateToken(Request $request): RedirectResponse
    {
        $connection = $request->user()->haConnection;

        if (! $connection) {
            return redirect()->route('dashboard.settings')
                ->with('error', 'No connection found.');
        }

        $plainToken = HaConnection::generateConnectionToken();

        $connection->update([
            'connection_token' => Hash::make($plainToken),
            'status' => 'disconnected',
        ]);

        return redirect()->route('dashboard.settings')
            ->with('plain_token', $plainToken)
            ->with('success', 'Token regenerated successfully!');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $connection = $request->user()->haConnection;

        if (! $connection) {
            return redirect()->route('dashboard.settings')
                ->with('error', 'No connection found.');
        }

        $connection->delete();

        return redirect()->route('dashboard.settings')
            ->with('success', 'Connection deleted successfully.');
    }
}

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

    public function updateSubdomain(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->can_set_subdomain) {
            return redirect()->route('dashboard.settings')
                ->with('error', 'You do not have permission to set a custom subdomain.');
        }

        $connection = $user->haConnection;

        if (! $connection) {
            return redirect()->route('dashboard.settings')
                ->with('error', 'No connection found.');
        }

        $request->validate([
            'subdomain' => [
                'required',
                'string',
                'min:3',
                'max:20',  // Shorter max to distinguish from 32-char app_subdomains
                'regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$|^[a-z0-9]$/',
                'unique:ha_connections,subdomain,'.$connection->id,
                'unique:ha_connections,app_subdomain',  // Can't collide with any app_subdomain
            ],
        ], [
            'subdomain.regex' => 'Subdomain must contain only lowercase letters, numbers, and hyphens. It cannot start or end with a hyphen.',
            'subdomain.unique' => 'This subdomain is already taken.',
        ]);

        $connection->update([
            'subdomain' => strtolower($request->subdomain),
            'status' => 'disconnected',
        ]);

        return redirect()->route('dashboard.settings')
            ->with('success', 'Subdomain updated successfully! Please reconnect your add-on.');
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

    /**
     * Generate or regenerate the app subdomain for mobile app access.
     */
    public function generateAppSubdomain(Request $request): RedirectResponse
    {
        $connection = $request->user()->haConnection;

        if (! $connection) {
            return redirect()->route('dashboard.settings')
                ->with('error', 'No connection found.');
        }

        $isNew = ! $connection->app_subdomain;

        $connection->update([
            'app_subdomain' => HaConnection::generateAppSubdomain(),
        ]);

        $message = $isNew
            ? 'Mobile app URL generated successfully!'
            : 'Mobile app URL regenerated successfully! Update the URL in your Home Assistant app.';

        return redirect()->route('dashboard.settings')
            ->with('success', $message);
    }

    /**
     * Revoke the app subdomain, disabling mobile app access.
     */
    public function revokeAppSubdomain(Request $request): RedirectResponse
    {
        $connection = $request->user()->haConnection;

        if (! $connection) {
            return redirect()->route('dashboard.settings')
                ->with('error', 'No connection found.');
        }

        $connection->update([
            'app_subdomain' => null,
        ]);

        return redirect()->route('dashboard.settings')
            ->with('success', 'Mobile app URL revoked. Apps using it will no longer have access.');
    }
}

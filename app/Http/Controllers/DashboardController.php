<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        // Eager load relations to avoid N+1 queries
        $user = $request->user()->load(['haConnection', 'subscription']);

        return view('dashboard.index', [
            'connection' => $user->haConnection,
            'subscription' => $user->subscription,
        ]);
    }

    public function setup(Request $request): View
    {
        $user = $request->user()->load('haConnection');

        return view('dashboard.setup', [
            'connection' => $user->haConnection,
        ]);
    }

    public function settings(Request $request): View
    {
        $user = $request->user()->load('haConnection');

        return view('dashboard.settings', [
            'connection' => $user->haConnection,
        ]);
    }

    public function subscription(Request $request): View
    {
        $user = $request->user()->load('subscription');

        return view('dashboard.subscription', [
            'subscription' => $user->subscription,
        ]);
    }
}

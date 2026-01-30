<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('dashboard.index', [
            'connection' => $user->haConnection,
            'subscription' => $user->subscription,
        ]);
    }

    public function setup(Request $request): View
    {
        return view('dashboard.setup', [
            'connection' => $request->user()->haConnection,
        ]);
    }

    public function settings(Request $request): View
    {
        return view('dashboard.settings', [
            'connection' => $request->user()->haConnection,
        ]);
    }

    public function subscription(Request $request): View
    {
        return view('dashboard.subscription', [
            'subscription' => $request->user()->subscription,
        ]);
    }
}

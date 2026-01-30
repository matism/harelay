<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function show(Request $request): View
    {
        return view('dashboard.subscription', [
            'subscription' => $request->user()->subscription,
        ]);
    }
}

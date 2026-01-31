<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class MarketingController extends Controller
{
    public function home(): View
    {
        return view('marketing.home');
    }

    public function howItWorks(): View
    {
        return view('marketing.how-it-works');
    }

    public function privacy(): View
    {
        return view('marketing.privacy');
    }

    public function imprint(): View
    {
        return view('marketing.imprint');
    }

    public function vsNabuCasa(): View
    {
        return view('marketing.vs-nabu-casa');
    }

    public function vsHomeflow(): View
    {
        return view('marketing.vs-homeflow');
    }
}

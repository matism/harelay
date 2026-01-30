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
}

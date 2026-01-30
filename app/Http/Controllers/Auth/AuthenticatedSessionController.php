<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $user = $request->authenticate();

        // If 2FA is required, redirect to challenge
        if ($user) {
            $request->session()->put('login.id', $user->id);
            $request->session()->put('login.remember', $request->boolean('remember'));

            // Store redirect URL for after 2FA
            $redirect = $request->input('redirect');
            if ($redirect && $this->isValidRedirectUrl($redirect)) {
                $request->session()->put('url.intended', $redirect);
            }

            return redirect()->route('two-factor.challenge');
        }

        $request->session()->regenerate();

        // Check for explicit redirect URL (from subdomain auth-required page)
        $redirect = $request->input('redirect');
        if ($redirect && $this->isValidRedirectUrl($redirect)) {
            return redirect()->away($redirect);
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Validate that the redirect URL is safe (same domain or subdomain).
     */
    private function isValidRedirectUrl(string $url): bool
    {
        $parsed = parse_url($url);
        if (! $parsed || ! isset($parsed['host'])) {
            return false;
        }

        $proxyDomain = config('app.proxy_domain', 'harelay.com');

        // Allow main domain and any subdomain
        return $parsed['host'] === $proxyDomain
            || str_ends_with($parsed['host'], '.'.$proxyDomain);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

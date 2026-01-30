<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Check for explicit redirect URL (from subdomain auth-required page)
        $redirect = $request->input('redirect');
        if ($redirect && $this->isValidRedirectUrl($redirect)) {
            return redirect()->away($redirect);
        }

        return redirect(route('dashboard', absolute: false));
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
}

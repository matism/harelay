<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA;
    }

    /**
     * Show the 2FA challenge page.
     */
    public function show(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Verify the 2FA code.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        if (! $request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        $user = User::find($request->session()->get('login.id'));

        if (! $user) {
            return redirect()->route('login');
        }

        // Try TOTP code first
        if ($request->filled('code')) {
            $valid = $this->google2fa->verifyKey($user->two_factor_secret, $request->code);

            if (! $valid) {
                return back()->withErrors(['code' => 'The provided code is invalid.']);
            }
        }
        // Try recovery code
        elseif ($request->filled('recovery_code')) {
            $recoveryCodes = $user->two_factor_recovery_codes ?? [];
            $code = $request->recovery_code;

            if (! in_array($code, $recoveryCodes)) {
                return back()->withErrors(['recovery_code' => 'The provided recovery code is invalid.']);
            }

            // Remove used recovery code
            $user->update([
                'two_factor_recovery_codes' => array_values(array_diff($recoveryCodes, [$code])),
            ]);
        } else {
            return back()->withErrors(['code' => 'Please enter a code.']);
        }

        // Clear 2FA session data and log in
        $remember = $request->session()->pull('login.remember', false);
        $request->session()->forget('login.id');

        Auth::login($user, $remember);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}

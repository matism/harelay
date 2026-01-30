<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA;
    }

    /**
     * Show the 2FA settings page.
     */
    public function show(Request $request): View
    {
        $user = $request->user();

        return view('auth.two-factor.show', [
            'enabled' => $user->hasTwoFactorEnabled(),
        ]);
    }

    /**
     * Show the 2FA setup page with QR code.
     */
    public function setup(Request $request): View
    {
        $user = $request->user();

        // Generate a new secret if not already set (or if re-enabling)
        if (! $user->two_factor_secret || $user->two_factor_confirmed_at) {
            $user->update([
                'two_factor_secret' => $this->google2fa->generateSecretKey(),
                'two_factor_confirmed_at' => null,
            ]);
            $user->refresh();
        }

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->two_factor_secret
        );

        // Generate QR code SVG
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return view('auth.two-factor.setup', [
            'secret' => $user->two_factor_secret,
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }

    /**
     * Confirm 2FA setup with a code.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if (! $user->two_factor_secret) {
            return redirect()->route('two-factor.show')
                ->with('error', 'Please start the 2FA setup process first.');
        }

        $valid = $this->google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (! $valid) {
            return back()->withErrors(['code' => 'The provided code is invalid.']);
        }

        // Generate recovery codes
        $recoveryCodes = collect(range(1, 8))->map(fn () => Str::random(10).'-'.Str::random(10))->all();

        $user->update([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $recoveryCodes,
        ]);

        return redirect()->route('two-factor.recovery-codes')
            ->with('status', 'Two-factor authentication has been enabled.');
    }

    /**
     * Show recovery codes.
     */
    public function recoveryCodes(Request $request): View
    {
        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.show');
        }

        return view('auth.two-factor.recovery-codes', [
            'recoveryCodes' => $user->two_factor_recovery_codes,
        ]);
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.show');
        }

        $recoveryCodes = collect(range(1, 8))->map(fn () => Str::random(10).'-'.Str::random(10))->all();

        $user->update([
            'two_factor_recovery_codes' => $recoveryCodes,
        ]);

        return redirect()->route('two-factor.recovery-codes')
            ->with('status', 'Recovery codes have been regenerated.');
    }

    /**
     * Disable 2FA.
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $request->user()->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return redirect()->route('two-factor.show')
            ->with('status', 'Two-factor authentication has been disabled.');
    }
}

<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-xl font-semibold text-white mb-2">Verify Your Email</h2>
        <p class="text-slate-400 text-sm">We've sent a verification link to your email address.</p>
    </div>

    <div class="mb-6 text-sm text-slate-300 bg-white/5 rounded-xl p-4">
        {{ __('Please verify your email address by clicking the link we sent you. Check your spam folder if you don\'t see it.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 bg-green-500/10 border border-green-500/20 rounded-lg p-3">
            <p class="text-sm text-green-400 text-center">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </p>
        </div>
    @endif

    <div class="flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-cyan-500 hover:bg-cyan-400 border border-transparent rounded-lg text-sm font-semibold text-slate-900 transition">
                {{ __('Resend Verification Email') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="text-sm text-slate-400 hover:text-white transition">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>

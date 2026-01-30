<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-xl font-semibold text-white mb-2">Reset Password</h2>
        <p class="text-slate-400 text-sm">Enter your email and we'll send you a password reset link.</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-4 bg-green-500/10 border border-green-500/20 rounded-lg p-3">
            <p class="text-sm text-green-400 text-center">{{ session('status') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-slate-300">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                class="mt-1 block w-full rounded-lg border-0 bg-white/10 text-white placeholder-slate-400 shadow-sm ring-1 ring-inset ring-white/20 focus:ring-2 focus:ring-cyan-400 sm:text-sm px-4 py-3">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-6">
            <button type="submit"
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-slate-900 bg-cyan-400 hover:bg-cyan-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-400 focus:ring-offset-slate-900 transition">
                Send Reset Link
            </button>
        </div>

        <p class="mt-6 text-center text-sm text-slate-400">
            Remember your password?
            <a href="{{ route('login') }}" class="text-cyan-400 hover:text-cyan-300 font-medium transition">
                Sign in
            </a>
        </p>
    </form>
</x-guest-layout>

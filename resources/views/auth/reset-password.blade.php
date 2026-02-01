<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-xl font-semibold text-white mb-2">Set New Password</h2>
        <p class="text-slate-400 text-sm">Choose a new password for your account.</p>
    </div>

    <!-- Generic Error (e.g., session expired) -->
    @if($errors->has('error'))
        <div class="mb-4 bg-red-500/10 border border-red-500/20 rounded-lg p-3">
            <p class="text-sm text-red-400 text-center">{{ $errors->first('error') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-slate-300">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                class="mt-1 block w-full rounded-lg border-0 bg-white/10 text-white placeholder-slate-400 shadow-sm ring-1 ring-inset ring-white/20 focus:ring-2 focus:ring-cyan-400 sm:text-sm px-4 py-3">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label for="password" class="block text-sm font-medium text-slate-300">New Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                class="mt-1 block w-full rounded-lg border-0 bg-white/10 text-white placeholder-slate-400 shadow-sm ring-1 ring-inset ring-white/20 focus:ring-2 focus:ring-cyan-400 sm:text-sm px-4 py-3">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <label for="password_confirmation" class="block text-sm font-medium text-slate-300">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                class="mt-1 block w-full rounded-lg border-0 bg-white/10 text-white placeholder-slate-400 shadow-sm ring-1 ring-inset ring-white/20 focus:ring-2 focus:ring-cyan-400 sm:text-sm px-4 py-3">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6">
            <button type="submit"
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-slate-900 bg-cyan-400 hover:bg-cyan-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-400 focus:ring-offset-slate-900 transition">
                Reset Password
            </button>
        </div>
    </form>
</x-guest-layout>

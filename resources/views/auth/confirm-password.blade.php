<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-xl font-semibold text-white mb-2">Confirm Password</h2>
        <p class="text-slate-400 text-sm">This is a secure area. Please confirm your password before continuing.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-slate-300">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="mt-1 block w-full rounded-lg border-0 bg-white/10 text-white placeholder-slate-400 shadow-sm ring-1 ring-inset ring-white/20 focus:ring-2 focus:ring-cyan-400 sm:text-sm px-4 py-3">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-6">
            <button type="submit"
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-slate-900 bg-cyan-400 hover:bg-cyan-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-400 focus:ring-offset-slate-900 transition">
                {{ __('Confirm') }}
            </button>
        </div>
    </form>
</x-guest-layout>

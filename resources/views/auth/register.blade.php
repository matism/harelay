<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        @if(request('redirect'))
            <input type="hidden" name="redirect" value="{{ request('redirect') }}">
        @endif

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-slate-300">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                class="mt-1 block w-full rounded-lg border-0 bg-white/10 text-white placeholder-slate-400 shadow-sm ring-1 ring-inset ring-white/20 focus:ring-2 focus:ring-cyan-400 sm:text-sm px-4 py-3">
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <label for="email" class="block text-sm font-medium text-slate-300">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                class="mt-1 block w-full rounded-lg border-0 bg-white/10 text-white placeholder-slate-400 shadow-sm ring-1 ring-inset ring-white/20 focus:ring-2 focus:ring-cyan-400 sm:text-sm px-4 py-3">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label for="password" class="block text-sm font-medium text-slate-300">Password</label>
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
                Create account
            </button>
        </div>

        <p class="mt-6 text-center text-sm text-slate-400">
            Already have an account?
            <a href="{{ route('login') }}{{ request('redirect') ? '?redirect=' . urlencode(request('redirect')) : '' }}" class="text-cyan-400 hover:text-cyan-300 font-medium transition">
                Sign in
            </a>
        </p>
    </form>
</x-guest-layout>

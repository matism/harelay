<section>
    <header>
        <h2 class="text-lg font-semibold text-white">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-slate-400">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-sm font-medium text-slate-300">Current Password</label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                class="mt-1 block w-full rounded-lg border-0 bg-white/5 text-white placeholder-slate-400 shadow-sm ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-cyan-400 sm:text-sm px-4 py-3">
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password" class="block text-sm font-medium text-slate-300">New Password</label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                class="mt-1 block w-full rounded-lg border-0 bg-white/5 text-white placeholder-slate-400 shadow-sm ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-cyan-400 sm:text-sm px-4 py-3">
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-sm font-medium text-slate-300">Confirm Password</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                class="mt-1 block w-full rounded-lg border-0 bg-white/5 text-white placeholder-slate-400 shadow-sm ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-cyan-400 sm:text-sm px-4 py-3">
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-cyan-500 hover:bg-cyan-400 border border-transparent rounded-lg text-sm font-semibold text-slate-900 transition">
                Save
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>

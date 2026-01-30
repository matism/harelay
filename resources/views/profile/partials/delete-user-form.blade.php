<section class="space-y-6">
    <header>
        <h2 class="text-lg font-semibold text-red-400">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-slate-400">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-500 border border-transparent rounded-lg text-sm font-semibold text-white transition"
    >{{ __('Delete Account') }}</button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 bg-slate-800">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-white">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-1 text-sm text-slate-400">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-6">
                <label for="password" class="sr-only">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="{{ __('Password') }}"
                    class="block w-full rounded-lg border-0 bg-white/10 text-white placeholder-slate-400 shadow-sm ring-1 ring-inset ring-white/20 focus:ring-2 focus:ring-cyan-400 sm:text-sm px-4 py-3"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" x-on:click="$dispatch('close')"
                    class="inline-flex items-center px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/10 rounded-lg text-sm font-semibold text-white transition">
                    {{ __('Cancel') }}
                </button>

                <button type="submit"
                    class="ms-3 inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-500 border border-transparent rounded-lg text-sm font-semibold text-white transition">
                    {{ __('Delete Account') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>

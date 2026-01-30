<section>
    <header>
        <h2 class="text-lg font-semibold text-white">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-slate-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block text-sm font-medium text-slate-300">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
                class="mt-1 block w-full rounded-lg border-0 bg-white/5 text-white placeholder-slate-400 shadow-sm ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-cyan-400 sm:text-sm px-4 py-3">
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-300">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                class="mt-1 block w-full rounded-lg border-0 bg-white/5 text-white placeholder-slate-400 shadow-sm ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-cyan-400 sm:text-sm px-4 py-3">
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-slate-300">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-cyan-400 hover:text-cyan-300 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-400">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-cyan-500 hover:bg-cyan-400 border border-transparent rounded-lg text-sm font-semibold text-slate-900 transition">
                Save
            </button>

            @if (session('status') === 'profile-updated')
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

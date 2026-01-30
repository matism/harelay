<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Enter the pairing code displayed in your Home Assistant add-on logs to link your device.') }}
    </div>

    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ session('status') }}
        </div>
    @endif

    @auth
        <form method="POST" action="{{ route('device.link') }}">
            @csrf

            <div>
                <x-input-label for="user_code" :value="__('Pairing Code')" />
                <x-text-input
                    id="user_code"
                    class="block mt-1 w-full text-center text-2xl tracking-widest uppercase"
                    type="text"
                    name="user_code"
                    :value="old('user_code')"
                    required
                    autofocus
                    placeholder="XXXX-XXXX"
                    maxlength="9"
                />
                <x-input-error :messages="$errors->get('user_code')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-primary-button>
                    {{ __('Link Device') }}
                </x-primary-button>
            </div>
        </form>
    @else
        <div class="text-center">
            <p class="mb-4 text-gray-600">{{ __('Please log in or create an account to link your device.') }}</p>

            <div class="flex flex-col space-y-3">
                <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Log In') }}
                </a>

                <a href="{{ route('register', ['redirect' => url()->current()]) }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Create Account') }}
                </a>
            </div>
        </div>
    @endauth
</x-guest-layout>

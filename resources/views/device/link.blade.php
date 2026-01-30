<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-xl font-semibold text-white mb-2">Link Your Device</h2>
        <p class="text-slate-400 text-sm">Enter the pairing code shown in your Home Assistant add-on</p>
    </div>

    @if (session('status'))
        <div class="mb-4 bg-green-500/10 border border-green-500/20 rounded-lg p-3">
            <p class="text-sm text-green-400 text-center">{{ session('status') }}</p>
        </div>
    @endif

    @auth
        <form method="POST" action="{{ route('device.link') }}">
            @csrf

            <div>
                <label for="user_code" class="block text-sm font-medium text-slate-300 mb-2">Pairing Code</label>
                <input
                    id="user_code"
                    type="text"
                    name="user_code"
                    value="{{ old('user_code') }}"
                    required
                    autofocus
                    placeholder="XXXX-XXXX"
                    maxlength="9"
                    class="block w-full text-center text-2xl tracking-widest uppercase rounded-lg border-0 bg-white/10 text-white placeholder-slate-500 shadow-sm ring-1 ring-inset ring-white/20 focus:ring-2 focus:ring-cyan-400 px-4 py-4 font-mono"
                />
                @error('user_code')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <button type="submit"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-slate-900 bg-cyan-400 hover:bg-cyan-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-400 focus:ring-offset-slate-900 transition">
                    Link Device
                </button>
            </div>
        </form>

        <script>
            document.getElementById('user_code').addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                if (value.length > 4) {
                    value = value.slice(0, 4) + '-' + value.slice(4, 8);
                }
                e.target.value = value;
            });
        </script>
    @else
        <div class="text-center">
            <div class="mb-6 p-4 bg-white/5 rounded-xl">
                <p class="text-slate-400 text-sm">Please log in or create an account to link your device.</p>
            </div>

            <div class="space-y-3">
                <a href="{{ route('login', ['redirect' => url()->current()]) }}"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-slate-900 bg-cyan-400 hover:bg-cyan-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-400 focus:ring-offset-slate-900 transition">
                    Sign In
                </a>

                <a href="{{ route('register', ['redirect' => url()->current()]) }}"
                    class="w-full flex justify-center py-3 px-4 rounded-lg shadow-sm text-sm font-semibold text-white bg-white/10 hover:bg-white/20 border border-white/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-400 focus:ring-offset-slate-900 transition">
                    Create Account
                </a>
            </div>
        </div>
    @endauth
</x-guest-layout>

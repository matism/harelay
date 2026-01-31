<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Set Up Two-Factor Authentication') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white/10 backdrop-blur-lg overflow-hidden shadow-xl rounded-2xl border border-white/20">
                <div class="p-6 sm:p-8">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-cyan-500/20 mb-4">
                            <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">Scan QR Code</h3>
                        <p class="text-slate-400 text-sm max-w-md mx-auto">
                            Scan this QR code with your authenticator app (Google Authenticator, 1Password, Authy, etc.)
                        </p>
                    </div>

                    <!-- QR Code -->
                    <div class="flex justify-center mb-8">
                        <div class="bg-white p-4 rounded-xl">
                            {!! $qrCodeSvg !!}
                        </div>
                    </div>

                    <!-- Manual Entry -->
                    <div class="bg-white/5 rounded-xl p-4 mb-8">
                        <p class="text-sm text-slate-400 mb-2 text-center">Or enter this code manually:</p>
                        <div class="flex items-center justify-center gap-2">
                            <code class="text-sm sm:text-lg font-mono text-cyan-400 tracking-wider select-all break-all text-center">{{ $secret }}</code>
                            <button onclick="navigator.clipboard.writeText('{{ $secret }}')"
                                class="flex-shrink-0 p-2 text-slate-400 hover:text-white transition" title="Copy">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Verification Form -->
                    <form action="{{ route('two-factor.confirm') }}" method="POST">
                        @csrf

                        <div class="mb-6">
                            <label for="code" class="block text-sm font-medium text-slate-300 mb-2">
                                Enter the 6-digit code from your app
                            </label>
                            <input type="text" id="code" name="code" required autofocus
                                maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code"
                                placeholder="000000"
                                class="block w-full text-center text-2xl tracking-widest font-mono rounded-lg border-0 bg-white/10 text-white placeholder-slate-500 shadow-sm ring-1 ring-inset ring-white/20 focus:ring-2 focus:ring-cyan-400 px-4 py-4">
                            @error('code')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <a href="{{ route('two-factor.show') }}" class="text-sm text-slate-400 hover:text-white transition">
                                Cancel
                            </a>
                            <button type="submit"
                                class="inline-flex items-center px-6 py-3 bg-cyan-500 hover:bg-cyan-400 border border-transparent rounded-lg text-sm font-semibold text-slate-900 transition">
                                Verify & Enable
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

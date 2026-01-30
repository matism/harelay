<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Recovery Codes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('status'))
                <div class="mb-6 bg-green-500/10 border border-green-500/20 rounded-xl p-4">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="ml-3 text-green-400">{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            <div class="bg-white/10 backdrop-blur-lg overflow-hidden shadow-xl rounded-2xl border border-white/20">
                <div class="p-6 sm:p-8">
                    <div class="flex items-start mb-6">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-xl bg-amber-500/20 flex items-center justify-center">
                                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-white mb-1">Save Your Recovery Codes</h3>
                            <p class="text-slate-400 text-sm">
                                Store these codes in a secure location. Each code can only be used once to access your account if you lose your authenticator device.
                            </p>
                        </div>
                    </div>

                    <!-- Recovery Codes Grid -->
                    <div class="bg-slate-900/50 rounded-xl p-6 mb-6">
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($recoveryCodes as $code)
                                <code class="text-sm font-mono text-slate-300 bg-white/5 rounded-lg px-3 py-2 text-center select-all">
                                    {{ $code }}
                                </code>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <form action="{{ route('two-factor.regenerate-recovery-codes') }}" method="POST">
                            @csrf
                            <button type="submit" onclick="return confirm('Are you sure? This will invalidate your current recovery codes.')"
                                class="inline-flex items-center px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/10 rounded-lg text-sm font-medium text-white transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Regenerate Codes
                            </button>
                        </form>

                        <a href="{{ route('two-factor.show') }}"
                            class="inline-flex items-center px-4 py-2 bg-cyan-500 hover:bg-cyan-400 border border-transparent rounded-lg text-sm font-semibold text-slate-900 transition">
                            Done
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

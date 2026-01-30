<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Two-Factor Authentication') }}
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

            @if(session('error'))
                <div class="mb-6 bg-red-500/10 border border-red-500/20 rounded-xl p-4">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span class="ml-3 text-red-400">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <div class="bg-white/10 backdrop-blur-lg overflow-hidden shadow-xl rounded-2xl border border-white/20">
                <div class="p-6 sm:p-8">
                    @if($enabled)
                        <!-- 2FA Enabled State -->
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-lg font-semibold text-white mb-1">Two-Factor Authentication is Enabled</h3>
                                <p class="text-slate-400 text-sm mb-6">
                                    Your account is protected with an additional layer of security.
                                </p>

                                <div class="flex flex-wrap gap-3">
                                    <a href="{{ route('two-factor.recovery-codes') }}"
                                        class="inline-flex items-center px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/10 rounded-lg text-sm font-medium text-white transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                        View Recovery Codes
                                    </a>

                                    <form action="{{ route('two-factor.disable') }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="document.getElementById('disable-modal').classList.remove('hidden')"
                                            class="inline-flex items-center px-4 py-2 bg-red-600/20 hover:bg-red-600/30 border border-red-500/30 rounded-lg text-sm font-medium text-red-400 transition">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                            Disable 2FA
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Disable Modal -->
                        <div id="disable-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
                            <div class="bg-slate-800 rounded-2xl p-6 max-w-md mx-4 border border-white/10">
                                <h3 class="text-lg font-semibold text-white mb-2">Disable Two-Factor Authentication</h3>
                                <p class="text-slate-400 text-sm mb-4">
                                    Enter your password to confirm you want to disable 2FA.
                                </p>

                                <form action="{{ route('two-factor.disable') }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <div class="mb-4">
                                        <input type="password" name="password" required placeholder="Your password"
                                            class="w-full rounded-lg border-0 bg-white/10 text-white placeholder-slate-400 shadow-sm ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-cyan-400 px-4 py-3">
                                        @error('password')
                                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="flex justify-end gap-3">
                                        <button type="button" onclick="document.getElementById('disable-modal').classList.add('hidden')"
                                            class="px-4 py-2 text-sm font-medium text-slate-400 hover:text-white transition">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                            class="px-4 py-2 bg-red-600 hover:bg-red-500 rounded-lg text-sm font-medium text-white transition">
                                            Disable 2FA
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- 2FA Disabled State -->
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-xl bg-slate-500/20 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-lg font-semibold text-white mb-1">Two-Factor Authentication is Disabled</h3>
                                <p class="text-slate-400 text-sm mb-6">
                                    Add an extra layer of security to your account by enabling two-factor authentication.
                                    You'll need an authenticator app like Google Authenticator or 1Password.
                                </p>

                                <a href="{{ route('two-factor.setup') }}"
                                    class="inline-flex items-center px-4 py-2 bg-cyan-500 hover:bg-cyan-400 border border-transparent rounded-lg text-sm font-semibold text-slate-900 transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    Enable Two-Factor Authentication
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-4 text-center">
                <a href="{{ route('profile.edit') }}" class="text-sm text-slate-400 hover:text-white transition">
                    &larr; Back to Profile
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

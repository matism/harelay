<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if($connection)
                <!-- Connection Info -->
                <div class="bg-white/10 backdrop-blur-lg overflow-hidden shadow-xl rounded-2xl border border-white/20">
                    <div class="p-6 sm:p-8">
                        <h3 class="text-lg font-semibold text-white mb-1">Connection Details</h3>
                        <p class="text-slate-400 text-sm mb-6">Your HARelay connection information.</p>

                        <div class="space-y-4">
                            <!-- Subdomain -->
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Your Subdomain</label>
                                @if(auth()->user()->can_set_subdomain)
                                    <form action="{{ route('connection.update-subdomain') }}" method="POST" class="flex flex-col sm:flex-row sm:items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <div class="flex items-center flex-1 min-w-0">
                                            <input type="text" name="subdomain" value="{{ $connection->subdomain }}"
                                                pattern="[a-z0-9-]+" minlength="3" maxlength="32"
                                                class="flex-1 min-w-0 rounded-lg border-0 bg-white/5 text-white placeholder-slate-400 shadow-sm ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-cyan-400 sm:text-sm px-4 py-3">
                                            <span class="ml-2 text-slate-400 whitespace-nowrap">.{{ config('app.proxy_domain') }}</span>
                                        </div>
                                        <button type="submit"
                                            class="inline-flex items-center justify-center px-4 py-3 bg-cyan-500 hover:bg-cyan-400 border border-transparent rounded-lg text-sm font-medium text-slate-900 transition whitespace-nowrap">
                                            Save
                                        </button>
                                    </form>
                                    @error('subdomain')
                                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-2 text-xs text-slate-500">You can set a custom subdomain (lowercase letters, numbers, and hyphens only).</p>
                                @else
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                        <input type="text" readonly value="{{ $connection->subdomain }}.{{ config('app.proxy_domain') }}"
                                            class="flex-1 min-w-0 rounded-lg border-0 bg-white/5 text-white placeholder-slate-400 shadow-sm ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-cyan-400 sm:text-sm px-4 py-3">
                                        <button onclick="navigator.clipboard.writeText('{{ $connection->subdomain }}.{{ config('app.proxy_domain') }}')"
                                            class="inline-flex items-center justify-center px-4 py-3 bg-white/10 hover:bg-white/20 border border-white/10 rounded-lg text-sm font-medium text-white transition whitespace-nowrap">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                            Copy
                                        </button>
                                    </div>
                                @endif
                            </div>

                            <!-- Full URL -->
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Full URL</label>
                                <a href="{{ $connection->getProxyUrl() }}" target="_blank"
                                    class="block w-full rounded-lg border-0 bg-white/5 text-cyan-400 hover:text-cyan-300 shadow-sm ring-1 ring-inset ring-white/10 sm:text-sm px-4 py-3 transition break-all">
                                    {{ $connection->getProxyUrl() }}
                                </a>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Status</label>
                                <div class="flex flex-wrap items-center gap-2">
                                    @if($connection->isConnected())
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-green-500/20 text-green-400 ring-1 ring-green-500/30">
                                            <span class="w-2 h-2 mr-2 bg-green-500 rounded-full animate-pulse"></span>
                                            Connected
                                        </span>
                                        <span class="text-sm text-slate-400">
                                            Last seen {{ $connection->last_connected_at->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-slate-500/20 text-slate-400 ring-1 ring-slate-500/30">
                                            <span class="w-2 h-2 mr-2 bg-slate-400 rounded-full"></span>
                                            Disconnected
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Data Transfer Stats -->
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Data Transfer</label>
                                <div class="grid grid-cols-3 gap-2 sm:gap-4">
                                    <div class="bg-white/5 rounded-lg p-2 sm:p-3 text-center">
                                        <p class="text-xs text-slate-400 mb-1">Downloaded</p>
                                        <p class="text-white font-medium text-sm sm:text-base break-all">{{ $connection->getFormattedBytesOut() }}</p>
                                    </div>
                                    <div class="bg-white/5 rounded-lg p-2 sm:p-3 text-center">
                                        <p class="text-xs text-slate-400 mb-1">Uploaded</p>
                                        <p class="text-white font-medium text-sm sm:text-base break-all">{{ $connection->getFormattedBytesIn() }}</p>
                                    </div>
                                    <div class="bg-white/5 rounded-lg p-2 sm:p-3 text-center">
                                        <p class="text-xs text-slate-400 mb-1">Total</p>
                                        <p class="text-white font-medium text-sm sm:text-base break-all">{{ $connection->getFormattedTotalBytes() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile App Link -->
                <div class="bg-white/10 backdrop-blur-lg overflow-hidden shadow-xl rounded-2xl border border-white/20">
                    <div class="p-6 sm:p-8">
                        <h3 class="text-lg font-semibold text-white mb-1">Mobile App Link</h3>
                        <p class="text-slate-400 text-sm mb-6">
                            Use this special link to connect the Home Assistant mobile app without browser login.
                        </p>

                        <!-- Security Warning -->
                        <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 mb-6">
                            <div class="flex">
                                <svg class="h-5 w-5 text-red-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-red-400 mb-1">Keep this link private!</p>
                                    <p class="text-sm text-red-300/80">
                                        Anyone with this link can access your Home Assistant without logging in.
                                        Do not share it publicly or with untrusted parties.
                                    </p>
                                </div>
                            </div>
                        </div>

                        @if(session('plain_app_token'))
                            <!-- Show the token once -->
                            <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-4 mb-6">
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-green-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="ml-3 flex-1 min-w-0">
                                        <p class="text-sm font-medium text-green-400 mb-2">Your mobile app link is ready!</p>
                                        <p class="text-xs text-slate-400 mb-3">Copy this link now - it won't be shown again for security reasons.</p>
                                        <div class="flex flex-col gap-2">
                                            <input type="text" readonly value="{{ $connection->getAppUrl(session('plain_app_token')) }}"
                                                id="appUrl"
                                                class="w-full rounded-lg border-0 bg-white/10 text-green-300 shadow-sm ring-1 ring-inset ring-green-500/30 text-sm px-3 py-2 font-mono break-all">
                                            <button onclick="navigator.clipboard.writeText(document.getElementById('appUrl').value); this.textContent = 'Copied!'; setTimeout(() => this.textContent = 'Copy Link', 2000)"
                                                class="inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-500 border border-transparent rounded-lg text-sm font-medium text-white transition whitespace-nowrap">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                                Copy Link
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($connection->app_token)
                            <div class="flex flex-col sm:flex-row gap-3">
                                <form action="{{ route('connection.generate-app-token') }}" method="POST">
                                    @csrf
                                    <button type="submit" onclick="return confirm('This will invalidate your current mobile app link. Any apps using it will need to be reconfigured. Continue?')"
                                        class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-500 border border-transparent rounded-lg text-sm font-medium text-white transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Regenerate Link
                                    </button>
                                </form>
                                <form action="{{ route('connection.revoke-app-token') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('This will revoke your mobile app link. Any apps using it will no longer have access. Continue?')"
                                        class="inline-flex items-center px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/10 rounded-lg text-sm font-medium text-white transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                        </svg>
                                        Revoke Link
                                    </button>
                                </form>
                            </div>
                            <p class="mt-3 text-xs text-slate-500">
                                A mobile app link has been generated. If you've lost it, you can regenerate a new one.
                            </p>
                        @else
                            <form action="{{ route('connection.generate-app-token') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-cyan-500 hover:bg-cyan-400 border border-transparent rounded-lg text-sm font-medium text-slate-900 transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    Generate Mobile App Link
                                </button>
                            </form>
                            <p class="mt-3 text-xs text-slate-500">
                                Generate a special link for the Home Assistant mobile app that doesn't require browser login.
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Relink Device -->
                <div class="bg-white/10 backdrop-blur-lg overflow-hidden shadow-xl rounded-2xl border border-white/20">
                    <div class="p-6 sm:p-8">
                        <h3 class="text-lg font-semibold text-white mb-1">Relink Device</h3>
                        <p class="text-slate-400 text-sm mb-6">
                            If you need to reconnect your Home Assistant add-on, you can generate a new pairing code.
                            This is useful if you've reinstalled the add-on or need to move to a different Home Assistant instance.
                        </p>

                        <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 mb-6">
                            <div class="flex">
                                <svg class="h-5 w-5 text-amber-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div class="ml-3">
                                    <p class="text-sm text-amber-300">
                                        Relinking will disconnect your current add-on. Make sure you have access to your Home Assistant to complete the pairing.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('dashboard.setup') }}" class="inline-flex items-center px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/10 rounded-lg text-sm font-medium text-white transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            View Setup Guide to Relink
                        </a>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="bg-red-500/10 backdrop-blur-lg overflow-hidden shadow-xl rounded-2xl border border-red-500/20">
                    <div class="p-6 sm:p-8">
                        <h3 class="text-lg font-semibold text-red-400 mb-1">Danger Zone</h3>
                        <p class="text-slate-400 text-sm mb-6">
                            Permanently delete your connection. This cannot be undone and will require you to set up a new connection.
                        </p>

                        <form action="{{ route('connection.destroy') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure you want to delete your connection? This action cannot be undone.')"
                                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-500 border border-transparent rounded-lg text-sm font-medium text-white transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Connection
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <!-- No Connection State -->
                <div class="bg-white/10 backdrop-blur-lg overflow-hidden shadow-xl rounded-2xl border border-white/20">
                    <div class="p-8 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-500/20 mb-4">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">No Connection Configured</h3>
                        <p class="text-slate-400 mb-6 max-w-md mx-auto">
                            Set up your HARelay connection to start accessing your Home Assistant remotely.
                        </p>
                        <a href="{{ route('dashboard.setup') }}" class="inline-flex items-center px-6 py-3 bg-cyan-500 hover:bg-cyan-400 text-slate-900 font-semibold rounded-lg transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Get Started
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

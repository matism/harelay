<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 bg-green-500/10 border border-green-500/20 rounded-xl p-4">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="ml-3 text-green-400">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Connection Status Card -->
            <div class="bg-white/10 backdrop-blur-lg overflow-hidden shadow-xl rounded-2xl border border-white/20">
                <div class="p-4 sm:p-8">
                    @if($connection)
                        <!-- Connected State -->
                        <div class="text-center">
                            @if($connection->isConnected())
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-500/20 mb-6">
                                    <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-white mb-2">Connected</h3>
                                <p class="text-slate-400 mb-6">Your Home Assistant is securely connected</p>
                            @else
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-cyan-500/20 mb-6">
                                    <svg class="w-10 h-10 text-cyan-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-white mb-2">Waiting for Connection</h3>
                                <p class="text-slate-400 mb-6">Start your Home Assistant add-on to connect</p>
                            @endif

                            <!-- Connection URL -->
                            <div class="bg-white/5 rounded-xl p-4 sm:p-6 mb-6">
                                <p class="text-sm text-slate-400 mb-2">Your Home Assistant URL</p>
                                <a href="{{ $connection->getProxyUrl() }}" target="_blank" class="text-base sm:text-lg font-medium text-cyan-400 hover:text-cyan-300 transition break-words hyphens-none" style="word-break: break-word;">
                                    {{ $connection->getProxyUrl() }}
                                </a>
                            </div>

                            <!-- Stats Grid -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-white/5 rounded-xl p-4">
                                    <p class="text-sm text-slate-400">Status</p>
                                    @if($connection->isConnected())
                                        <p class="text-white font-medium flex items-center justify-center mt-1">
                                            <span class="w-2 h-2 mr-2 bg-green-500 rounded-full animate-pulse"></span>
                                            Online
                                        </p>
                                    @else
                                        <p class="text-white font-medium flex items-center justify-center mt-1">
                                            <svg class="w-4 h-4 mr-2 text-cyan-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Waiting
                                        </p>
                                    @endif
                                </div>
                                <div class="bg-white/5 rounded-xl p-4">
                                    <p class="text-sm text-slate-400">Last Connected</p>
                                    <p class="text-white font-medium mt-1">
                                        {{ $connection->last_connected_at ? $connection->last_connected_at->diffForHumans() : 'Never' }}
                                    </p>
                                </div>
                            </div>

                            @if(!$connection->isConnected())
                                <!-- Help for waiting state -->
                                <div class="mt-8 pt-6 border-t border-white/10">
                                    <p class="text-slate-400 text-sm mb-2">This page will automatically update when connected.</p>
                                    <p class="text-slate-500 text-xs mb-4">Need help? Check the setup guide below.</p>
                                    <a href="{{ route('dashboard.setup') }}" class="inline-flex items-center px-4 py-2 bg-white/10 hover:bg-white/20 text-white font-medium rounded-lg transition text-sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                        View Setup Guide
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- No Connection State - Onboarding -->
                        <div>
                            <div class="text-center mb-8">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-cyan-500/20 mb-4">
                                    <x-application-logo class="w-10 h-10 text-cyan-400" />
                                </div>
                                <h3 class="text-2xl font-bold text-white mb-2">Welcome to HARelay!</h3>
                                <p class="text-slate-400 max-w-md mx-auto">
                                    Let's connect your Home Assistant. Follow these three steps:
                                </p>
                            </div>

                            <!-- Onboarding Steps -->
                            <div class="space-y-4 mb-8">
                                <div class="flex items-start bg-white/5 rounded-xl p-4">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-cyan-500 flex items-center justify-center text-slate-900 font-bold text-sm mr-4">
                                        1
                                    </div>
                                    <div>
                                        <h4 class="text-white font-medium">Install the HARelay add-on</h4>
                                        <p class="text-slate-400 text-sm mt-1">Add our repository to Home Assistant's Add-on Store and install the HARelay add-on.</p>
                                    </div>
                                </div>
                                <div class="flex items-start bg-white/5 rounded-xl p-4">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-cyan-500/30 flex items-center justify-center text-cyan-400 font-bold text-sm mr-4">
                                        2
                                    </div>
                                    <div>
                                        <h4 class="text-white font-medium">Get your pairing code</h4>
                                        <p class="text-slate-400 text-sm mt-1">Start the add-on and open its web UI. You'll see a pairing code like <code class="text-cyan-400 bg-white/10 px-1 rounded">ABCD-1234</code>.</p>
                                    </div>
                                </div>
                                <div class="flex items-start bg-white/5 rounded-xl p-4">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-cyan-500/30 flex items-center justify-center text-cyan-400 font-bold text-sm mr-4">
                                        3
                                    </div>
                                    <div>
                                        <h4 class="text-white font-medium">Enter the code here</h4>
                                        <p class="text-slate-400 text-sm mt-1">Enter the pairing code on this website to link your Home Assistant. That's it!</p>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center">
                                <a href="{{ route('dashboard.setup') }}" class="inline-flex items-center px-6 py-3 bg-cyan-500 hover:bg-cyan-400 text-slate-900 font-semibold rounded-lg transition text-lg">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                    View Detailed Setup Guide
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Links -->
            @if($connection)
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('dashboard.setup') }}" class="bg-white/5 hover:bg-white/10 backdrop-blur-lg overflow-hidden shadow-lg rounded-xl border border-white/10 hover:border-white/20 transition p-5 flex items-center">
                    <div class="w-12 h-12 rounded-lg bg-cyan-500/20 flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-white font-medium">Setup Guide</h4>
                        <p class="text-slate-400 text-sm">View installation instructions</p>
                    </div>
                </a>
                <a href="{{ route('dashboard.settings') }}" class="bg-white/5 hover:bg-white/10 backdrop-blur-lg overflow-hidden shadow-lg rounded-xl border border-white/10 hover:border-white/20 transition p-5 flex items-center">
                    <div class="w-12 h-12 rounded-lg bg-cyan-500/20 flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-white font-medium">Settings</h4>
                        <p class="text-slate-400 text-sm">Manage your connection</p>
                    </div>
                </a>
            </div>
            @endif
        </div>
    </div>

    @if($connection && !$connection->isConnected())
    <script>
        (function() {
            let checkInterval = setInterval(function() {
                fetch('/api/connection/status', {
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.connected) {
                        clearInterval(checkInterval);
                        window.location.reload();
                    }
                })
                .catch(() => {});
            }, 3000);
        })();
    </script>
    @endif
</x-app-layout>

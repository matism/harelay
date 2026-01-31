<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Setup Guide') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white/10 backdrop-blur-lg overflow-hidden shadow-xl rounded-2xl border border-white/20">
                <div class="p-4 sm:p-6 lg:p-8">
                    <h3 class="text-xl font-semibold text-white mb-2">Connect Your Home Assistant</h3>
                    <p class="text-slate-400 mb-8">Follow these steps to set up remote access in just a few minutes.</p>

                    <!-- Setup Steps -->
                    <div class="space-y-8">
                        <!-- Step 1 -->
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-cyan-500/20 text-cyan-400 font-bold text-sm sm:text-base ring-2 ring-cyan-500/30">
                                    1
                                </div>
                            </div>
                            <div class="ml-3 sm:ml-5 min-w-0">
                                <h4 class="text-lg font-medium text-white">Add the HARelay Repository</h4>
                                <p class="mt-2 text-slate-400">
                                    In Home Assistant, go to <span class="text-white">Settings</span> &rarr; <span class="text-white">Add-ons</span> &rarr; <span class="text-white">Add-on Store</span>.
                                    Click the three dots in the top right and select <span class="text-white">Repositories</span>.
                                </p>
                                <div class="mt-3 flex items-center gap-2 sm:gap-3 bg-white/5 rounded-lg p-3 sm:p-4 ring-1 ring-white/10">
                                    <code class="text-cyan-400 text-xs sm:text-sm flex-1 min-w-0 break-words" style="word-break: break-word;">https://github.com/harelay/ha-addon</code>
                                    <button onclick="navigator.clipboard.writeText('https://github.com/harelay/ha-addon')"
                                        class="flex-shrink-0 text-slate-400 hover:text-white transition p-1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-cyan-500/20 text-cyan-400 font-bold text-sm sm:text-base ring-2 ring-cyan-500/30">
                                    2
                                </div>
                            </div>
                            <div class="ml-3 sm:ml-5 min-w-0">
                                <h4 class="text-lg font-medium text-white">Install the Add-on</h4>
                                <p class="mt-2 text-slate-400">
                                    Find <span class="text-white">HARelay Tunnel</span> in the add-on store and click <span class="text-white">Install</span>.
                                    Wait for the installation to complete.
                                </p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-cyan-500/20 text-cyan-400 font-bold text-sm sm:text-base ring-2 ring-cyan-500/30">
                                    3
                                </div>
                            </div>
                            <div class="ml-3 sm:ml-5 min-w-0">
                                <h4 class="text-lg font-medium text-white">Start the Add-on</h4>
                                <p class="mt-2 text-slate-400">
                                    Click <span class="text-white">Start</span> to begin the add-on. No configuration is needed - the add-on will automatically enter pairing mode.
                                </p>
                                <p class="mt-2 text-slate-400">
                                    Enable <span class="text-white">Start on boot</span> for automatic reconnection after restarts.
                                </p>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-cyan-500/20 text-cyan-400 font-bold text-sm sm:text-base ring-2 ring-cyan-500/30">
                                    4
                                </div>
                            </div>
                            <div class="ml-3 sm:ml-5 min-w-0">
                                <h4 class="text-lg font-medium text-white">Open the Add-on Web UI</h4>
                                <p class="mt-2 text-slate-400">
                                    Click <span class="text-white">Open Web UI</span> in the add-on page. You'll see a pairing code displayed.
                                </p>
                                <div class="mt-4 bg-slate-800/50 rounded-xl p-4 sm:p-6 ring-1 ring-white/10">
                                    <p class="text-slate-400 text-sm mb-3">Example pairing code:</p>
                                    <div class="text-center">
                                        <span class="text-2xl sm:text-4xl font-mono font-bold text-white tracking-wider">ABCD-1234</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 5 -->
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-cyan-500/20 text-cyan-400 font-bold text-sm sm:text-base ring-2 ring-cyan-500/30">
                                    5
                                </div>
                            </div>
                            <div class="ml-3 sm:ml-5 min-w-0">
                                <h4 class="text-lg font-medium text-white">Enter the Code</h4>
                                <p class="mt-2 text-slate-400">
                                    Enter the pairing code shown in your add-on to link your device.
                                </p>
                                <div class="mt-4">
                                    <a href="{{ route('device.link') }}" target="_blank"
                                        class="inline-flex items-center px-5 py-3 bg-cyan-500 hover:bg-cyan-400 text-slate-900 font-semibold rounded-lg transition">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                        </svg>
                                        Enter Pairing Code
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Step 6 (Success) -->
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-green-500/20 text-green-400 ring-2 ring-green-500/30">
                                    <svg class="h-4 w-4 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 sm:ml-5 min-w-0">
                                <h4 class="text-lg font-medium text-white">Access Your Home Assistant</h4>
                                <p class="mt-2 text-slate-400">
                                    Once paired, your add-on will automatically connect. Access your Home Assistant from anywhere at:
                                </p>
                                @if($connection)
                                    <div class="mt-4 bg-white/5 rounded-xl p-3 sm:p-4 ring-1 ring-white/10">
                                        <a href="{{ $connection->getProxyUrl() }}" target="_blank"
                                            class="text-sm sm:text-lg font-medium text-cyan-400 hover:text-cyan-300 transition break-words" style="word-break: break-word;">
                                            {{ $connection->getProxyUrl() }}
                                        </a>
                                    </div>
                                @else
                                    <div class="mt-4 bg-white/5 rounded-xl p-3 sm:p-4 ring-1 ring-white/10 text-slate-400 text-sm sm:text-base">
                                        Your URL will appear here after pairing.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Help Section -->
                    <div class="mt-8 sm:mt-12 pt-6 sm:pt-8 border-t border-white/10">
                        <h4 class="text-lg font-medium text-white mb-4">Need Help?</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div class="bg-white/5 rounded-xl p-4 sm:p-5 ring-1 ring-white/10">
                                <h5 class="text-white font-medium mb-2">Add-on not starting?</h5>
                                <p class="text-slate-400 text-sm">
                                    Check the add-on logs for error messages. Make sure you have an active internet connection.
                                </p>
                            </div>
                            <div class="bg-white/5 rounded-xl p-4 sm:p-5 ring-1 ring-white/10">
                                <h5 class="text-white font-medium mb-2">Connection issues?</h5>
                                <p class="text-slate-400 text-sm">
                                    Try restarting the add-on. If problems persist, delete your connection in Settings and pair again.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

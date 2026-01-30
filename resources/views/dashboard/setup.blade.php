<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Setup Guide') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Connect Your Home Assistant</h3>

                    @if(!$connection)
                        <!-- Create Connection First -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <div class="flex">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Connection not set up</h3>
                                    <p class="mt-2 text-sm text-yellow-700">
                                        Create your connection first to get your subdomain and connection token.
                                    </p>
                                    <div class="mt-4">
                                        <form action="{{ route('connection.store') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                Create Connection
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Connection Info -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                            <div class="flex">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">Connection configured</h3>
                                    <p class="mt-1 text-sm text-green-700">
                                        Your subdomain: <strong>{{ $connection->subdomain }}.{{ config('app.proxy_domain') }}</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Setup Steps -->
                    <div class="space-y-8">
                        <!-- Step 1 -->
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 text-blue-600 font-bold">
                                    1
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900">Add the HARelay Repository</h4>
                                <p class="mt-2 text-gray-500">
                                    In Home Assistant, go to <strong>Settings &rarr; Add-ons &rarr; Add-on Store</strong>.
                                    Click the three dots in the top right and select <strong>Repositories</strong>.
                                </p>
                                <div class="mt-3 bg-gray-100 rounded-lg p-3">
                                    <code class="text-sm text-gray-800">https://github.com/harelay/ha-addon</code>
                                    <button onclick="navigator.clipboard.writeText('https://github.com/harelay/ha-addon')" class="ml-2 text-blue-600 hover:text-blue-800 text-sm">
                                        Copy
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 text-blue-600 font-bold">
                                    2
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900">Install the Add-on</h4>
                                <p class="mt-2 text-gray-500">
                                    Find <strong>HARelay Tunnel</strong> in the add-on store and click <strong>Install</strong>.
                                    Wait for the installation to complete.
                                </p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 text-blue-600 font-bold">
                                    3
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900">Configure the Add-on</h4>
                                <p class="mt-2 text-gray-500">
                                    Go to the add-on's <strong>Configuration</strong> tab and enter your connection token:
                                </p>
                                @if($connection)
                                    <div class="mt-3 bg-gray-900 rounded-lg p-4 text-green-400 font-mono text-sm">
                                        <div><span class="text-blue-400">connection_token:</span> "{{ session('plain_token') ?? '********' }}"</div>
                                        @if(session('plain_token'))
                                            <p class="mt-2 text-yellow-400 text-xs">Save this token now - it won't be shown again!</p>
                                        @else
                                            <p class="mt-2 text-gray-500 text-xs">
                                                <a href="{{ route('dashboard.settings') }}" class="text-blue-400 hover:text-blue-300">Regenerate token</a> to see it again.
                                            </p>
                                        @endif
                                    </div>
                                @else
                                    <div class="mt-3 bg-gray-100 rounded-lg p-3 text-gray-500">
                                        Create your connection above to see your token.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 text-blue-600 font-bold">
                                    4
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900">Start the Add-on</h4>
                                <p class="mt-2 text-gray-500">
                                    Click <strong>Start</strong> to begin the tunnel connection. Enable <strong>Start on boot</strong> for automatic reconnection.
                                </p>
                            </div>
                        </div>

                        <!-- Step 5 -->
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-10 w-10 rounded-full bg-green-100 text-green-600 font-bold">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900">Access Your Home Assistant</h4>
                                <p class="mt-2 text-gray-500">
                                    Once connected, access your Home Assistant at:
                                </p>
                                @if($connection)
                                    <div class="mt-3">
                                        <a href="{{ $connection->getProxyUrl() }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium">
                                            {{ $connection->getProxyUrl() }}
                                        </a>
                                    </div>
                                @else
                                    <div class="mt-3 text-gray-400">
                                        Create your connection to get your URL.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

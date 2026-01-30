<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Connection Settings -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900">Connection Settings</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage your HARelay connection and API token.
                    </p>

                    @if($connection)
                        <div class="mt-6 space-y-6">
                            <!-- Subdomain -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Your Subdomain</label>
                                <div class="mt-1 flex items-center">
                                    <input type="text" readonly value="{{ $connection->subdomain }}.{{ config('app.proxy_domain') }}" class="block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <button onclick="navigator.clipboard.writeText('{{ $connection->subdomain }}.{{ config('app.proxy_domain') }}')" class="ml-2 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Copy
                                    </button>
                                </div>
                            </div>

                            <!-- Connection Token -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Connection Token</label>
                                <p class="text-sm text-gray-500 mb-2">
                                    Use this token in your Home Assistant add-on configuration.
                                </p>
                                @if(session('plain_token'))
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                        <div class="flex items-start">
                                            <svg class="h-5 w-5 text-green-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <div class="ml-3 flex-1">
                                                <h4 class="text-sm font-medium text-green-800">New Token Generated</h4>
                                                <p class="mt-1 text-sm text-green-700">Save this token now - it won't be shown again!</p>
                                                <div class="mt-2 bg-white rounded border border-green-300 p-2">
                                                    <code class="text-sm text-gray-900 break-all">{{ session('plain_token') }}</code>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-gray-100 rounded-lg p-3 text-gray-500 text-sm">
                                        Token is stored securely. Regenerate to see a new one.
                                    </div>
                                @endif

                                <form action="{{ route('connection.regenerate-token') }}" method="POST" class="mt-4">
                                    @csrf
                                    <button type="submit" onclick="return confirm('This will invalidate your current token. Your add-on will need to be reconfigured. Continue?')" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Regenerate Token
                                    </button>
                                </form>
                            </div>

                            <!-- Connection Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <div class="mt-1">
                                    @if($connection->isConnected())
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            <span class="w-2 h-2 mr-2 bg-green-500 rounded-full animate-pulse"></span>
                                            Connected
                                        </span>
                                        <span class="ml-2 text-sm text-gray-500">
                                            Last seen {{ $connection->last_connected_at->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                            <span class="w-2 h-2 mr-2 bg-gray-400 rounded-full"></span>
                                            Disconnected
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Danger Zone -->
                        <div class="mt-10 pt-6 border-t border-gray-200">
                            <h4 class="text-lg font-medium text-red-600">Danger Zone</h4>
                            <p class="mt-1 text-sm text-gray-500">
                                Permanently delete your connection. This cannot be undone.
                            </p>
                            <form action="{{ route('connection.destroy') }}" method="POST" class="mt-4">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Are you sure you want to delete your connection? This action cannot be undone.')" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Delete Connection
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="mt-6 bg-gray-50 rounded-lg p-6 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No connection configured</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating your connection.</p>
                            <div class="mt-6">
                                <form action="{{ route('connection.store') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Create Connection
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

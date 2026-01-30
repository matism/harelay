<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Connection Status Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Connection Status</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Your Home Assistant connection status
                            </p>
                        </div>
                        @if($connection)
                            @if($connection->isConnected())
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <span class="w-2 h-2 mr-2 bg-green-500 rounded-full animate-pulse"></span>
                                    Connected
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                    <span class="w-2 h-2 mr-2 bg-gray-400 rounded-full"></span>
                                    Disconnected
                                </span>
                            @endif
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                Not Configured
                            </span>
                        @endif
                    </div>

                    @if($connection)
                        <div class="mt-6 border-t border-gray-200 pt-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Your URL</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <a href="https://{{ $connection->subdomain }}.{{ config('app.proxy_domain') }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                            {{ $connection->subdomain }}.{{ config('app.proxy_domain') }}
                                        </a>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Last Connected</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $connection->last_connected_at ? $connection->last_connected_at->diffForHumans() : 'Never' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    @else
                        <div class="mt-6">
                            <a href="{{ route('dashboard.setup') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Set Up Connection
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Setup Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900">Setup Guide</h4>
                                <p class="mt-1 text-sm text-gray-500">Learn how to install the add-on</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('dashboard.setup') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View Guide &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Settings Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900">Connection Settings</h4>
                                <p class="mt-1 text-sm text-gray-500">Manage your API token</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('dashboard.settings') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View Settings &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Subscription Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900">Subscription</h4>
                                <p class="mt-1 text-sm text-gray-500">
                                    @if($subscription)
                                        {{ ucfirst($subscription->plan) }} Plan
                                    @else
                                        Free Plan
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('dashboard.subscription') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Manage Subscription &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

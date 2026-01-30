<x-marketing-layout>
    <x-slot name="title">How It Works - HARelay</x-slot>
    <x-slot name="description">Learn how HARelay provides secure remote access to your Home Assistant in three simple steps.</x-slot>

    <div class="bg-white">
        <!-- Header -->
        <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl sm:tracking-tight lg:text-6xl">
                    How HARelay Works
                </h1>
                <p class="mt-5 max-w-xl mx-auto text-xl text-gray-500">
                    Secure remote access in three simple steps. No networking expertise required.
                </p>
            </div>
        </div>

        <!-- Steps -->
        <div class="bg-gray-50 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="space-y-16">
                    <!-- Step 1 -->
                    <div class="lg:grid lg:grid-cols-2 lg:gap-8 lg:items-center">
                        <div>
                            <div class="flex items-center justify-center h-12 w-12 rounded-full bg-blue-600 text-white text-xl font-bold">
                                1
                            </div>
                            <h3 class="mt-6 text-2xl font-extrabold text-gray-900">
                                Create Your Account
                            </h3>
                            <p class="mt-4 text-lg text-gray-500">
                                Sign up for a free HARelay account. You'll get a unique subdomain (like <code class="bg-gray-200 px-2 py-1 rounded">yourname.{{ config('app.proxy_domain') }}</code>) and a connection token for your Home Assistant.
                            </p>
                            <div class="mt-6">
                                <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                                    Create Free Account
                                </a>
                            </div>
                        </div>
                        <div class="mt-10 lg:mt-0">
                            <div class="bg-white rounded-lg shadow-lg p-6">
                                <div class="border-b border-gray-200 pb-4 mb-4">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                    <div class="h-10 bg-gray-100 rounded"></div>
                                    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                                    <div class="h-10 bg-gray-100 rounded"></div>
                                    <div class="h-10 bg-blue-600 rounded text-center text-white text-sm flex items-center justify-center">
                                        Sign Up
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="lg:grid lg:grid-cols-2 lg:gap-8 lg:items-center">
                        <div class="lg:order-2">
                            <div class="flex items-center justify-center h-12 w-12 rounded-full bg-blue-600 text-white text-xl font-bold">
                                2
                            </div>
                            <h3 class="mt-6 text-2xl font-extrabold text-gray-900">
                                Install the Home Assistant Add-on
                            </h3>
                            <p class="mt-4 text-lg text-gray-500">
                                Add our repository to Home Assistant and install the HARelay add-on. Enter your connection token in the add-on configuration - that's it!
                            </p>
                            <div class="mt-6">
                                <ul class="space-y-3 text-gray-500">
                                    <li class="flex items-start">
                                        <svg class="flex-shrink-0 h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span class="ml-3">No port forwarding required</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="flex-shrink-0 h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span class="ml-3">No dynamic DNS needed</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="flex-shrink-0 h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span class="ml-3">Works behind CGNAT and firewalls</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="mt-10 lg:mt-0 lg:order-1">
                            <div class="bg-gray-900 rounded-lg shadow-lg p-6 text-green-400 font-mono text-sm">
                                <div class="mb-4 text-gray-500"># Home Assistant Add-on Configuration</div>
                                <div class="space-y-2">
                                    <div><span class="text-blue-400">connection_token:</span> "your-token-here"</div>
                                    <div><span class="text-blue-400">server:</span> "wss://tunnel.{{ config('app.proxy_domain') }}"</div>
                                    <div class="text-gray-500"># That's all you need!</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="lg:grid lg:grid-cols-2 lg:gap-8 lg:items-center">
                        <div>
                            <div class="flex items-center justify-center h-12 w-12 rounded-full bg-blue-600 text-white text-xl font-bold">
                                3
                            </div>
                            <h3 class="mt-6 text-2xl font-extrabold text-gray-900">
                                Access From Anywhere
                            </h3>
                            <p class="mt-4 text-lg text-gray-500">
                                Visit your unique subdomain from any device, anywhere in the world. Log in with your HARelay account and you'll be securely connected to your Home Assistant.
                            </p>
                            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <p class="text-blue-800">
                                    <strong>Your URL:</strong> <code class="bg-blue-100 px-2 py-1 rounded">yourname.{{ config('app.proxy_domain') }}</code>
                                </p>
                            </div>
                        </div>
                        <div class="mt-10 lg:mt-0">
                            <div class="bg-white rounded-lg shadow-lg p-6 border-2 border-blue-500">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                        <span class="text-sm text-gray-500">Connected</span>
                                    </div>
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Secure</span>
                                </div>
                                <div class="text-center py-8">
                                    <svg class="w-16 h-16 mx-auto text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    <p class="mt-4 text-lg font-medium text-gray-900">Home Assistant</p>
                                    <p class="text-sm text-gray-500">Your smart home, accessible everywhere</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Architecture Diagram -->
        <div class="py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-extrabold text-gray-900">
                        Behind the Scenes
                    </h2>
                    <p class="mt-4 text-lg text-gray-500">
                        HARelay creates a secure tunnel between your Home Assistant and our servers.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
                        <!-- Your Device -->
                        <div class="text-center">
                            <div class="bg-white rounded-lg shadow p-6">
                                <svg class="w-12 h-12 mx-auto text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                <p class="mt-4 font-medium">Your Device</p>
                                <p class="text-sm text-gray-500">Phone, laptop, tablet</p>
                            </div>
                        </div>

                        <!-- HARelay Server -->
                        <div class="text-center">
                            <div class="flex items-center justify-center mb-4">
                                <div class="h-px bg-blue-300 w-full"></div>
                                <span class="px-2 text-sm text-blue-600">HTTPS</span>
                                <div class="h-px bg-blue-300 w-full"></div>
                            </div>
                            <div class="bg-blue-600 rounded-lg shadow p-6 text-white">
                                <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                </svg>
                                <p class="mt-4 font-medium">HARelay</p>
                                <p class="text-sm text-blue-200">Secure tunnel server</p>
                            </div>
                            <div class="flex items-center justify-center mt-4">
                                <div class="h-px bg-green-300 w-full"></div>
                                <span class="px-2 text-sm text-green-600">WebSocket</span>
                                <div class="h-px bg-green-300 w-full"></div>
                            </div>
                        </div>

                        <!-- Home Assistant -->
                        <div class="text-center">
                            <div class="bg-white rounded-lg shadow p-6">
                                <svg class="w-12 h-12 mx-auto text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                <p class="mt-4 font-medium">Home Assistant</p>
                                <p class="text-sm text-gray-500">Your smart home</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="bg-blue-600">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8 lg:flex lg:items-center lg:justify-between">
                <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                    <span class="block">Ready to connect?</span>
                    <span class="block text-blue-200">Get started in under 5 minutes.</span>
                </h2>
                <div class="mt-8 flex lg:mt-0 lg:flex-shrink-0">
                    <div class="inline-flex rounded-md shadow">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-blue-50">
                            Get Started Free
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-marketing-layout>

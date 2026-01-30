<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'HARelay - Secure Remote Access for Home Assistant' }}</title>
        <meta name="description" content="{{ $description ?? 'Access your Home Assistant from anywhere without port forwarding. Secure WebSocket tunnel with easy setup.' }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white/5 backdrop-blur-lg border-b border-white/10 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="/" class="flex items-center space-x-2">
                            <x-application-logo class="w-8 h-8 text-cyan-400" />
                            <span class="text-xl font-bold text-white">HARelay</span>
                        </a>
                        <div class="hidden sm:ml-10 sm:flex sm:space-x-8">
                            <a href="{{ route('marketing.how-it-works') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-slate-300 hover:text-white transition">
                                How It Works
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-slate-300 hover:text-white transition">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-slate-300 hover:text-white transition">
                                Sign in
                            </a>
                            <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-cyan-500 hover:bg-cyan-400 border border-transparent rounded-lg font-semibold text-sm text-slate-900 transition">
                                Get Started
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="bg-white/5 backdrop-blur-lg border-t border-white/10">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- Brand -->
                    <div class="md:col-span-2">
                        <a href="/" class="flex items-center space-x-2">
                            <x-application-logo class="w-8 h-8 text-cyan-400" />
                            <span class="text-xl font-bold text-white">HARelay</span>
                        </a>
                        <p class="mt-4 text-slate-400 text-sm max-w-md">
                            Secure remote access to your Home Assistant without port forwarding. Simple setup, powerful connection.
                        </p>
                    </div>

                    <!-- Links -->
                    <div>
                        <h3 class="text-sm font-semibold text-white uppercase tracking-wider">Product</h3>
                        <ul class="mt-4 space-y-3">
                            <li>
                                <a href="{{ route('marketing.how-it-works') }}" class="text-slate-400 hover:text-white text-sm transition">
                                    How It Works
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('register') }}" class="text-slate-400 hover:text-white text-sm transition">
                                    Get Started
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Legal -->
                    <div>
                        <h3 class="text-sm font-semibold text-white uppercase tracking-wider">Legal</h3>
                        <ul class="mt-4 space-y-3">
                            <li>
                                <a href="{{ route('marketing.privacy') }}" class="text-slate-400 hover:text-white text-sm transition">
                                    Privacy Policy
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('marketing.imprint') }}" class="text-slate-400 hover:text-white text-sm transition">
                                    Imprint
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-12 pt-8 border-t border-white/10 flex flex-col md:flex-row justify-between items-center">
                    <p class="text-slate-500 text-sm">
                        &copy; {{ date('Y') }} HARelay. All rights reserved.
                    </p>
                    <p class="text-slate-500 text-sm mt-4 md:mt-0">
                        Made for the Home Assistant community
                    </p>
                </div>
            </div>
        </footer>
    </body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $pageTitle = $title ?? 'HARelay - Secure Remote Access for Home Assistant';
            $pageDescription = $description ?? 'Access your Home Assistant from anywhere without port forwarding. Secure WebSocket tunnel with easy setup.';
            $canonicalUrl = url()->current();
            $siteName = 'HARelay';
            $isHomePage = request()->is('/');
        @endphp

        <title>{{ $pageTitle }}</title>
        <meta name="description" content="{{ $pageDescription }}">
        <meta name="keywords" content="Home Assistant, remote access, smart home, WebSocket tunnel, no port forwarding, secure access, home automation">
        <meta name="author" content="HARelay">
        <meta name="robots" content="index, follow">

        <!-- Canonical URL -->
        <link rel="canonical" href="{{ $canonicalUrl }}">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ $canonicalUrl }}">
        <meta property="og:title" content="{{ $pageTitle }}">
        <meta property="og:description" content="{{ $pageDescription }}">
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:locale" content="en_US">
        <meta property="og:image" content="{{ asset('og-image.png') }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ $canonicalUrl }}">
        <meta name="twitter:title" content="{{ $pageTitle }}">
        <meta name="twitter:description" content="{{ $pageDescription }}">
        <meta name="twitter:image" content="{{ asset('og-image.png') }}">

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <link rel="icon" type="image/png" href="/favicon.png">
        <link rel="apple-touch-icon" href="/favicon.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- JSON-LD Structured Data -->
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "SoftwareApplication",
            "name": "HARelay",
            "applicationCategory": "WebApplication",
            "operatingSystem": "Any",
            "description": "{{ $pageDescription }}",
            "url": "https://harelay.com",
            "author": {
                "@@type": "Person",
                "name": "Mathias Placho"
            },
            "offers": {
                "@@type": "Offer",
                "price": "0",
                "priceCurrency": "USD"
            },
            "aggregateRating": {
                "@@type": "AggregateRating",
                "ratingValue": "5",
                "ratingCount": "1"
            }
        }
        </script>

        @if($isHomePage)
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "Organization",
            "name": "HARelay",
            "url": "https://harelay.com",
            "logo": "{{ asset('favicon.png') }}",
            "description": "Secure remote access to Home Assistant without port forwarding",
            "contactPoint": {
                "@@type": "ContactPoint",
                "email": "mathias@harelay.com",
                "contactType": "customer service"
            },
            "address": {
                "@@type": "PostalAddress",
                "streetAddress": "Frauengasse 7",
                "addressLocality": "Graz",
                "postalCode": "8010",
                "addressCountry": "AT"
            }
        }
        </script>
        @endif

        {{-- Page-specific structured data --}}
        @isset($structuredData)
            {!! $structuredData !!}
        @endisset
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white/5 backdrop-blur-lg border-b border-white/10 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <a href="/" class="flex items-center space-x-2">
                                <x-application-logo class="w-8 h-8 text-cyan-400" />
                                <span class="text-xl font-bold text-white">HARelay</span>
                            </a>
                        </div>
                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            <a href="{{ route('marketing.how-it-works') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('marketing.how-it-works') ? 'border-cyan-400 text-white' : 'border-transparent text-slate-400 hover:text-white hover:border-slate-400' }}">
                                How It Works
                            </a>
                            <a href="{{ route('marketing.security') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('marketing.security') ? 'border-cyan-400 text-white' : 'border-transparent text-slate-400 hover:text-white hover:border-slate-400' }}">
                                Security
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-slate-300 hover:text-white transition">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="hidden sm:inline-flex text-sm font-medium text-slate-300 hover:text-white transition">
                                Sign in
                            </a>
                            <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-cyan-500 hover:bg-cyan-400 border border-transparent rounded-lg font-semibold text-sm text-slate-900 transition">
                                Get Started
                            </a>
                        @endauth

                        <!-- Mobile menu button -->
                        <button type="button" class="sm:hidden inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-white hover:bg-white/10 transition" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                            <span class="sr-only">Open main menu</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile menu -->
                <div class="hidden sm:hidden" id="mobile-menu">
                    <div class="space-y-1 pb-3 pt-2 border-t border-white/10">
                        <a href="{{ route('marketing.how-it-works') }}" class="block px-3 py-2 text-base font-medium {{ request()->routeIs('marketing.how-it-works') ? 'text-cyan-400' : 'text-slate-300 hover:text-white' }}">
                            How It Works
                        </a>
                        <a href="{{ route('marketing.security') }}" class="block px-3 py-2 text-base font-medium {{ request()->routeIs('marketing.security') ? 'text-cyan-400' : 'text-slate-300 hover:text-white' }}">
                            Security
                        </a>
                        @guest
                            <a href="{{ route('login') }}" class="block px-3 py-2 text-base font-medium text-slate-300 hover:text-white">
                                Sign in
                            </a>
                        @endguest
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
                <div class="grid grid-cols-2 md:grid-cols-5 gap-8">
                    <!-- Brand -->
                    <div class="col-span-2">
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
                                <a href="{{ route('marketing.security') }}" class="text-slate-400 hover:text-white text-sm transition">
                                    Security
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('register') }}" class="text-slate-400 hover:text-white text-sm transition">
                                    Get Started
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Compare -->
                    <div>
                        <h3 class="text-sm font-semibold text-white uppercase tracking-wider">Compare</h3>
                        <ul class="mt-4 space-y-3">
                            <li>
                                <a href="{{ route('marketing.vs-nabu-casa') }}" class="text-slate-400 hover:text-white text-sm transition">
                                    Nabu Casa vs HARelay
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('marketing.vs-homeflow') }}" class="text-slate-400 hover:text-white text-sm transition">
                                    Homeflow.io vs HARelay
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

                <div class="mt-12 pt-8 border-t border-white/10">
                    <div class="flex flex-col md:flex-row justify-between items-center">
                        <p class="text-slate-500 text-sm">
                            &copy; {{ date('Y') }} HARelay. All rights reserved.
                        </p>
                        <p class="text-slate-500 text-sm mt-4 md:mt-0">
                            Sponsored by <a href="https://www.userbrain.com/en/" target="_blank" rel="noopener noreferrer" class="underline hover:text-white transition-colors">Userbrain</a>
                            | Made with &hearts; by <a href="https://www.linkedin.com/in/mathiasplacho" target="_blank" rel="noopener noreferrer" class="underline hover:text-white transition-colors">Mathias Placho</a>
                        </p>
                    </div>
                    <p class="text-slate-600 text-xs text-center mt-6">
                        HARelay is an independent service, not affiliated with or endorsed by Home Assistant.
                    </p>
                </div>
            </div>
        </footer>
    </body>
</html>

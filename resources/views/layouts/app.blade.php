<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'HARelay') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <link rel="icon" type="image/png" href="/favicon.png">

        <!-- No index for authenticated pages -->
        <meta name="robots" content="noindex, nofollow">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex flex-col bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="border-b border-white/10">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-1">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="border-t border-white/10 mt-auto">
                <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <p class="text-slate-500 text-sm">
                            &copy; {{ date('Y') }} HARelay. All rights reserved.
                        </p>
                        <div class="flex items-center gap-6">
                            <a href="{{ route('marketing.privacy') }}" class="text-slate-500 hover:text-slate-300 text-sm transition">
                                Privacy Policy
                            </a>
                            <a href="{{ route('marketing.imprint') }}" class="text-slate-500 hover:text-slate-300 text-sm transition">
                                Imprint
                            </a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>

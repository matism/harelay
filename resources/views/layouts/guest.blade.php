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

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900">
            <!-- Logo and Brand -->
            <div class="text-center mb-6">
                <a href="/" class="inline-flex items-center space-x-3">
                    <x-application-logo class="w-12 h-12 text-cyan-400" />
                    <span class="text-2xl font-bold text-white">HARelay</span>
                </a>
                <p class="mt-2 text-sm text-slate-400">Secure remote access to Home Assistant</p>
            </div>

            <!-- Card -->
            <div class="w-full sm:max-w-md px-6 py-8 bg-white/10 backdrop-blur-lg shadow-xl overflow-hidden sm:rounded-2xl border border-white/20">
                {{ $slot }}
            </div>

            <!-- Footer -->
            <p class="mt-8 text-center text-sm text-slate-500">
                <a href="/" class="hover:text-slate-400 transition">&larr; Back to home</a>
            </p>
        </div>
    </body>
</html>

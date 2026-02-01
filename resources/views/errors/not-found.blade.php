<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Not Found - HARelay</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        // Clear any cached Home Assistant service workers and caches for this subdomain
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(registrations => {
                registrations.forEach(r => r.unregister());
            });
        }
        if ('caches' in window) {
            caches.keys().then(names => {
                names.forEach(name => caches.delete(name));
            });
        }
    </script>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 mb-6">
                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-900">
                        Page Not Found
                    </h2>

                    <p class="mt-4 text-gray-600">
                        This Home Assistant instance doesn't exist or hasn't been set up yet.
                    </p>

                    <div class="mt-6 space-y-3">
                        @php
                            $mainDomain = config('app.proxy_domain');
                            $port = config('app.proxy_port');
                            $scheme = config('app.proxy_secure') ? 'https' : 'http';
                            $baseUrl = $port ? "{$scheme}://{$mainDomain}:{$port}" : "{$scheme}://{$mainDomain}";
                        @endphp
                        <a href="{{ $baseUrl }}" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Go to HARelay
                        </a>
                    </div>

                    <p class="mt-6 text-sm text-gray-500">
                        Looking to set up remote access to your Home Assistant?
                        <a href="{{ $baseUrl }}/register" class="text-blue-600 hover:text-blue-500">
                            Get started for free
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tunnel Disconnected - HARelay</title>
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
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-6">
                        <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-900">
                        Tunnel Disconnected
                    </h2>

                    <p class="mt-4 text-gray-600">
                        The Home Assistant add-on is not currently connected. Please check that:
                    </p>

                    <ul class="mt-4 text-left text-gray-600 space-y-2">
                        <li class="flex items-start">
                            <span class="flex-shrink-0 h-5 w-5 text-gray-400 mr-2">1.</span>
                            The HARelay add-on is installed and running
                        </li>
                        <li class="flex items-start">
                            <span class="flex-shrink-0 h-5 w-5 text-gray-400 mr-2">2.</span>
                            Your Home Assistant has internet connectivity
                        </li>
                        <li class="flex items-start">
                            <span class="flex-shrink-0 h-5 w-5 text-gray-400 mr-2">3.</span>
                            The connection token is correctly configured
                        </li>
                    </ul>

                    <div class="mt-6 space-y-3">
                        <button onclick="location.reload()" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Retry Connection
                        </button>

                        <a href="{{ route('dashboard') }}" class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Go to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

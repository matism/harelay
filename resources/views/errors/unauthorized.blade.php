<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access Denied - HARelay</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-900">
                        Access Denied
                    </h2>

                    <p class="mt-4 text-gray-600">
                        You don't have permission to access this Home Assistant instance.
                    </p>

                    <p class="mt-2 text-sm text-gray-500">
                        This subdomain belongs to a different HARelay account. Please make sure you're using the correct URL for your Home Assistant.
                    </p>

                    <div class="mt-6 space-y-3">
                        @php
                            $mainDomain = config('app.proxy_domain');
                            $port = config('app.proxy_port');
                            $scheme = config('app.proxy_secure') ? 'https' : 'http';
                            $baseUrl = $port ? "{$scheme}://{$mainDomain}:{$port}" : "{$scheme}://{$mainDomain}";
                        @endphp
                        <a href="{{ $baseUrl }}/dashboard" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Go to My Dashboard
                        </a>
                        <a href="{{ $baseUrl }}/logout" class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Log Out and Switch Account
                        </a>
                    </div>

                    @if(auth()->check())
                    <p class="mt-4 text-xs text-gray-400">
                        Logged in as {{ auth()->user()->email }}
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>

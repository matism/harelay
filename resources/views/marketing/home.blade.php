<x-marketing-layout>
    <x-slot name="title">HARelay - Secure Remote Access for Home Assistant</x-slot>

    <!-- Hero Section -->
    <div class="relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
            <div class="lg:grid lg:grid-cols-2 lg:gap-16 items-center">
                <div>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-tight">
                        Access your
                        <span class="text-cyan-400">Home Assistant</span>
                        from anywhere
                    </h1>
                    <p class="mt-6 text-lg text-slate-300 max-w-xl">
                        Remote access to Home Assistant usually means port forwarding, dynamic DNS, and security headaches. HARelay eliminates all of that.
                    </p>

                    <!-- What you get -->
                    <ul class="mt-6 space-y-3">
                        <li class="flex items-start text-slate-300">
                            <svg class="flex-shrink-0 w-5 h-5 text-cyan-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>A personal URL like <code class="text-cyan-400 bg-white/10 px-1.5 py-0.5 rounded text-sm">abc123.harelay.com</code></span>
                        </li>
                        <li class="flex items-start text-slate-300">
                            <svg class="flex-shrink-0 w-5 h-5 text-cyan-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>No router configuration or open ports required</span>
                        </li>
                        <li class="flex items-start text-slate-300">
                            <svg class="flex-shrink-0 w-5 h-5 text-cyan-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Works behind any firewall, NAT, or CGNAT</span>
                        </li>
                    </ul>

                    <div class="mt-10 flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 bg-cyan-500 hover:bg-cyan-400 border border-transparent rounded-xl font-semibold text-slate-900 transition text-lg">
                            Get Started Free
                            <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                        <a href="{{ route('marketing.how-it-works') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl font-semibold text-white transition text-lg">
                            How It Works
                        </a>
                    </div>
                </div>
                <div class="mt-16 lg:mt-0">
                    <div class="bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20 p-8 shadow-2xl">
                        <div class="flex items-center mb-6">
                            <div class="w-3 h-3 rounded-full bg-green-500 mr-2 animate-pulse"></div>
                            <span class="text-green-400 text-sm font-medium">Connected</span>
                        </div>
                        <div class="text-center py-8">
                            <x-application-logo class="w-20 h-20 mx-auto text-cyan-400" />
                            <p class="mt-6 text-2xl font-semibold text-white">Your Home Assistant</p>
                            <p class="mt-2 text-slate-400">Accessible from anywhere in the world</p>
                        </div>
                        <div class="mt-6 bg-white/5 rounded-xl p-4">
                            <p class="text-sm text-slate-400 mb-1">Your URL</p>
                            <p class="text-cyan-400 font-mono break-all">abc123.{{ config('app.proxy_domain') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- How it actually works (Technical credibility) -->
    <div class="py-16 border-y border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-2 lg:gap-12 items-center">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-white">
                        Why no port forwarding?
                    </h2>
                    <p class="mt-4 text-slate-400">
                        Traditional remote access requires you to open ports on your router, exposing your network to the internet. HARelay works differently.
                    </p>
                    <p class="mt-4 text-slate-400">
                        Our add-on creates an <span class="text-white">outbound</span> connection from your Home Assistant to our servers. Since the connection goes <span class="text-white">out</span> from your network (just like browsing a website), no ports need to be opened. Your router's firewall stays intact.
                    </p>
                </div>
                <div class="mt-8 lg:mt-0">
                    <div class="bg-white/5 rounded-xl p-6 border border-white/10">
                        <div class="flex items-center justify-between text-sm">
                            <div class="text-center">
                                <div class="w-12 h-12 rounded-lg bg-cyan-500/20 flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                </div>
                                <p class="text-white font-medium">Your HA</p>
                            </div>
                            <div class="flex-1 px-4">
                                <div class="flex items-center">
                                    <div class="flex-1 border-t-2 border-dashed border-cyan-500/50"></div>
                                    <svg class="w-4 h-4 text-cyan-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 text-center">Outbound connection</p>
                            </div>
                            <div class="text-center">
                                <div class="w-12 h-12 rounded-lg bg-cyan-500/20 flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"></path>
                                    </svg>
                                </div>
                                <p class="text-white font-medium">HARelay</p>
                            </div>
                        </div>
                        <p class="mt-4 text-xs text-slate-500 text-center">
                            Your firewall allows outbound connections by default
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- What is HARelay -->
    <div class="py-24 bg-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl sm:text-4xl font-bold text-white">
                    What is HARelay?
                </h2>
                <p class="mt-4 text-lg text-slate-400 max-w-2xl mx-auto">
                    HARelay is a <span class="text-white">web service</span> combined with a <span class="text-white">Home Assistant add-on</span>. The add-on runs on your Home Assistant and creates a secure tunnel. You then access your Home Assistant through your personal HARelay URL from any browser.
                </p>
            </div>

            <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20 p-8">
                    <div class="w-14 h-14 rounded-xl bg-cyan-500/20 flex items-center justify-center">
                        <svg class="w-7 h-7 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h3 class="mt-6 text-xl font-semibold text-white">Secure by Design</h3>
                    <p class="mt-3 text-slate-400">
                        End-to-end encryption with no open ports required. Your connection stays private and protected.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20 p-8">
                    <div class="w-14 h-14 rounded-xl bg-cyan-500/20 flex items-center justify-center">
                        <svg class="w-7 h-7 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="mt-6 text-xl font-semibold text-white">Instant Setup</h3>
                    <p class="mt-3 text-slate-400">
                        Install our add-on, enter a simple pairing code, and you're connected. No complex configuration needed.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20 p-8">
                    <div class="w-14 h-14 rounded-xl bg-cyan-500/20 flex items-center justify-center">
                        <svg class="w-7 h-7 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="mt-6 text-xl font-semibold text-white">Two-Factor Authentication</h3>
                    <p class="mt-3 text-slate-400">
                        Protect your account with optional 2FA using any authenticator app. Your smart home deserves extra security.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- How It Works Preview -->
    <div class="py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl sm:text-4xl font-bold text-white">
                    Get connected in minutes
                </h2>
                <p class="mt-4 text-lg text-slate-400 max-w-2xl mx-auto">
                    Three simple steps to secure remote access.
                </p>
            </div>

            <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="w-16 h-16 rounded-full bg-cyan-500/20 flex items-center justify-center mx-auto ring-2 ring-cyan-500/30">
                        <span class="text-2xl font-bold text-cyan-400">1</span>
                    </div>
                    <h3 class="mt-6 text-xl font-semibold text-white">Create an account</h3>
                    <p class="mt-3 text-slate-400">
                        Sign up for free and get your unique subdomain instantly.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="w-16 h-16 rounded-full bg-cyan-500/20 flex items-center justify-center mx-auto ring-2 ring-cyan-500/30">
                        <span class="text-2xl font-bold text-cyan-400">2</span>
                    </div>
                    <h3 class="mt-6 text-xl font-semibold text-white">Install the add-on</h3>
                    <p class="mt-3 text-slate-400">
                        Add our repository to Home Assistant and install the HARelay add-on.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="w-16 h-16 rounded-full bg-cyan-500/20 flex items-center justify-center mx-auto ring-2 ring-cyan-500/30">
                        <span class="text-2xl font-bold text-cyan-400">3</span>
                    </div>
                    <h3 class="mt-6 text-xl font-semibold text-white">Enter the pairing code</h3>
                    <p class="mt-3 text-slate-400">
                        The add-on shows a code. Enter it here and you're connected.
                    </p>
                </div>
            </div>

            <div class="mt-12 text-center">
                <a href="{{ route('marketing.how-it-works') }}" class="text-cyan-400 hover:text-cyan-300 font-medium transition">
                    View detailed setup guide &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Free Banner -->
    <div class="py-16 bg-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-r from-cyan-500/20 to-blue-500/20 rounded-2xl border border-cyan-500/30 p-8 md:p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-cyan-500/20 flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-white">Free to Use</h2>
                <p class="mt-4 text-lg text-slate-300 max-w-2xl mx-auto">
                    HARelay is free. No credit card required. We built this for the Home Assistant community.
                </p>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-cyan-500 rounded-2xl p-8 md:p-12 text-center">
                <h2 class="text-3xl sm:text-4xl font-bold text-slate-900">
                    Ready to get started?
                </h2>
                <p class="mt-4 text-lg text-slate-700 max-w-2xl mx-auto">
                    Create your free account and connect your Home Assistant in minutes.
                </p>
                <div class="mt-8">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 bg-slate-900 hover:bg-slate-800 border border-transparent rounded-xl font-semibold text-white transition text-lg">
                        Create Free Account
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-marketing-layout>

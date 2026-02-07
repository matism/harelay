<x-marketing-layout>
    <x-slot name="title">How It Works - HARelay</x-slot>
    <x-slot name="description">Learn how HARelay provides secure remote access to your Home Assistant in three simple steps.</x-slot>
    <x-slot name="structuredData"><x-structured-data.how-it-works /></x-slot>

    <!-- Header -->
    <div class="py-16 sm:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl sm:text-5xl font-bold text-white">
                    How HARelay Works
                </h1>
                <p class="mt-6 text-xl text-slate-400 max-w-2xl mx-auto">
                    Secure remote access in three simple steps. No networking expertise required.
                </p>
            </div>
        </div>
    </div>

    <!-- Steps -->
    <div class="py-16 bg-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-24">
                <!-- Step 1 -->
                <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center">
                    <div>
                        <div class="w-14 h-14 rounded-full bg-cyan-500/20 flex items-center justify-center ring-2 ring-cyan-500/30">
                            <span class="text-2xl font-bold text-cyan-400">1</span>
                        </div>
                        <h3 class="mt-6 text-2xl font-bold text-white">
                            Create Your Account
                        </h3>
                        <p class="mt-4 text-lg text-slate-400">
                            Sign up for a free HARelay account. You'll get a unique subdomain (like <code class="bg-white/10 px-2 py-1 rounded text-cyan-400">abc123.{{ config('app.proxy_domain') }}</code>) automatically assigned to you.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('register') }}" class="inline-flex items-center px-5 py-3 bg-cyan-500 hover:bg-cyan-400 border border-transparent rounded-lg font-semibold text-slate-900 transition">
                                Create Free Account
                            </a>
                        </div>
                    </div>
                    <div class="mt-10 lg:mt-0">
                        <div class="bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20 p-6 shadow-xl">
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-3 h-3 rounded-full bg-red-500/60"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-500/60"></div>
                                <div class="w-3 h-3 rounded-full bg-green-500/60"></div>
                            </div>
                            <div class="space-y-4">
                                <div class="h-4 bg-white/10 rounded w-3/4"></div>
                                <div class="h-12 bg-white/5 rounded border border-white/10"></div>
                                <div class="h-4 bg-white/10 rounded w-1/2"></div>
                                <div class="h-12 bg-white/5 rounded border border-white/10"></div>
                                <div class="h-12 bg-cyan-500 rounded text-center text-slate-900 text-sm font-semibold flex items-center justify-center">
                                    Create Account
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center">
                    <div class="lg:order-2">
                        <div class="w-14 h-14 rounded-full bg-cyan-500/20 flex items-center justify-center ring-2 ring-cyan-500/30">
                            <span class="text-2xl font-bold text-cyan-400">2</span>
                        </div>
                        <h3 class="mt-6 text-2xl font-bold text-white">
                            Install the Home Assistant App (formerly Add-on)
                        </h3>
                        <p class="mt-4 text-lg text-slate-400">
                            Add our repository to Home Assistant's built-in <span class="text-white">App Store</span> (not HACS) and install the HARelay app. Start the app and it will automatically enter pairing mode.
                        </p>
                        <div class="mt-4 bg-amber-500/10 border border-amber-500/20 rounded-xl p-4">
                            <p class="text-amber-300 text-sm">
                                <strong>Where to find the App Store:</strong> Settings &rarr; Apps &rarr; Install app &rarr; Three dots menu &rarr; Repositories
                            </p>
                        </div>
                        <div class="mt-6">
                            <ul class="space-y-3">
                                <li class="flex items-start text-slate-300">
                                    <svg class="flex-shrink-0 w-6 h-6 text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="ml-3">No port forwarding required</span>
                                </li>
                                <li class="flex items-start text-slate-300">
                                    <svg class="flex-shrink-0 w-6 h-6 text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="ml-3">No dynamic DNS needed</span>
                                </li>
                                <li class="flex items-start text-slate-300">
                                    <svg class="flex-shrink-0 w-6 h-6 text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="ml-3">Works behind CGNAT and firewalls</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="mt-10 lg:mt-0 lg:order-1">
                        <div class="bg-slate-800 rounded-2xl shadow-xl p-4 sm:p-6 font-mono text-sm">
                            <div class="text-slate-500 mb-4"># Add to Home Assistant App Store (not HACS)</div>
                            <div class="space-y-2">
                                <div class="text-cyan-400 break-all">https://github.com/harelay/ha-app</div>
                                <div class="mt-6 text-slate-500"># That's all you need to add!</div>
                                <div class="text-slate-500"># No configuration required - just start the app</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center">
                    <div>
                        <div class="w-14 h-14 rounded-full bg-cyan-500/20 flex items-center justify-center ring-2 ring-cyan-500/30">
                            <span class="text-2xl font-bold text-cyan-400">3</span>
                        </div>
                        <h3 class="mt-6 text-2xl font-bold text-white">
                            Enter the Pairing Code
                        </h3>
                        <p class="mt-4 text-lg text-slate-400">
                            The app displays a simple pairing code. Open the app's web interface, copy the code, and enter it on HARelay. Your Home Assistant is now connected and accessible from anywhere!
                        </p>
                        <div class="mt-6 bg-cyan-500/10 border border-cyan-500/20 rounded-xl p-4">
                            <p class="text-cyan-300">
                                <strong>Your URL:</strong> <code class="bg-cyan-500/20 px-2 py-1 rounded">abc123.{{ config('app.proxy_domain') }}</code>
                            </p>
                        </div>
                    </div>
                    <div class="mt-10 lg:mt-0">
                        <div class="bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20 p-8 shadow-xl">
                            <div class="text-center">
                                <p class="text-slate-400 text-sm mb-4">Your pairing code</p>
                                <div class="text-4xl font-mono font-bold text-white tracking-wider">
                                    ABCD-1234
                                </div>
                                <p class="mt-4 text-slate-400 text-sm">Enter this code at harelay.com/link</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Architecture Diagram -->
    <div class="py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-white">
                    Behind the Scenes
                </h2>
                <p class="mt-4 text-lg text-slate-400">
                    HARelay creates a secure tunnel between your Home Assistant and our servers.
                </p>
            </div>

            <div class="bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
                    <!-- Your Device -->
                    <div class="text-center">
                        <div class="bg-white/10 rounded-xl p-6 border border-white/10">
                            <svg class="w-12 h-12 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <p class="mt-4 font-medium text-white">Your Device</p>
                            <p class="text-sm text-slate-400">Phone, laptop, tablet</p>
                        </div>
                    </div>

                    <!-- HARelay Server -->
                    <div class="text-center">
                        <div class="flex items-center justify-center mb-4">
                            <div class="h-px bg-cyan-500/30 w-full"></div>
                            <span class="px-3 text-sm text-cyan-400 whitespace-nowrap">HTTPS</span>
                            <div class="h-px bg-cyan-500/30 w-full"></div>
                        </div>
                        <div class="bg-cyan-500 rounded-xl p-6">
                            <svg class="w-12 h-12 mx-auto text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                            </svg>
                            <p class="mt-4 font-medium text-slate-900">HARelay</p>
                            <p class="text-sm text-slate-700">Secure tunnel server</p>
                        </div>
                        <div class="flex items-center justify-center mt-4">
                            <div class="h-px bg-green-500/30 w-full"></div>
                            <span class="px-3 text-sm text-green-400 whitespace-nowrap">WebSocket</span>
                            <div class="h-px bg-green-500/30 w-full"></div>
                        </div>
                    </div>

                    <!-- Home Assistant -->
                    <div class="text-center">
                        <div class="bg-white/10 rounded-xl p-6 border border-white/10">
                            <x-application-logo class="w-12 h-12 mx-auto text-cyan-400" />
                            <p class="mt-4 font-medium text-white">Home Assistant</p>
                            <p class="text-sm text-slate-400">Your smart home</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="py-24 bg-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-3 lg:gap-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">
                        Frequently asked questions
                    </h2>
                    <p class="mt-4 text-lg text-slate-400">
                        Can't find the answer you're looking for? Feel free to reach out.
                    </p>
                </div>
                <div class="mt-12 lg:mt-0 lg:col-span-2">
                    <dl class="space-y-10">
                        <div>
                            <dt class="text-lg font-semibold text-white">
                                Is HARelay really free?
                            </dt>
                            <dd class="mt-3 text-slate-400">
                                Yes! HARelay is completely free to use. We built this for the Home Assistant community and want everyone to have secure remote access without barriers.
                            </dd>
                        </div>
                        <div>
                            <dt class="text-lg font-semibold text-white">
                                Do I need to open any ports on my router?
                            </dt>
                            <dd class="mt-3 text-slate-400">
                                No. HARelay uses outbound WebSocket connections, so your Home Assistant stays behind your firewall with no ports exposed. This is actually more secure than traditional port forwarding.
                            </dd>
                        </div>
                        <div>
                            <dt class="text-lg font-semibold text-white">
                                Is my data secure?
                            </dt>
                            <dd class="mt-3 text-slate-400">
                                Absolutely. All connections are encrypted using TLS, and we never store your Home Assistant credentials. The tunnel only forwards traffic - we can't see your data.
                            </dd>
                        </div>
                        <div>
                            <dt class="text-lg font-semibold text-white">
                                What about performance?
                            </dt>
                            <dd class="mt-3 text-slate-400">
                                HARelay uses WebSocket connections for low latency. Most users don't notice any difference compared to a direct local connection. Real-time features like live dashboards work seamlessly.
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <x-cta-box />
</x-marketing-layout>

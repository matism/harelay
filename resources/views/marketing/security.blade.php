<x-marketing-layout>
    <x-slot name="title">Security - HARelay</x-slot>
    <x-slot name="description">Learn how HARelay keeps your Home Assistant secure. No open ports, encrypted connections, and zero access to your data.</x-slot>

    <!-- Header -->
    <div class="py-16 sm:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-500/20 ring-2 ring-green-500/30 mb-6">
                    <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <h1 class="text-4xl sm:text-5xl font-bold text-white">
                    Security First
                </h1>
                <p class="mt-6 text-xl text-slate-400 max-w-3xl mx-auto">
                    Your smart home is personal. HARelay is designed from the ground up to keep your Home Assistant secure and your data private.
                </p>
            </div>
        </div>
    </div>

    <!-- Key Security Promise -->
    <div class="py-12 bg-green-500/10 border-y border-green-500/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-2xl font-bold text-white mb-4">Our Security Promise</h2>
                <p class="text-lg text-green-300 max-w-3xl mx-auto">
                    <strong>HARelay cannot access your Home Assistant.</strong> We don't see your dashboards, automations, devices, or any data. We simply forward encrypted traffic between you and your home.
                </p>
            </div>
        </div>
    </div>

    <!-- Security Features Grid -->
    <div class="py-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-white">How We Keep You Safe</h2>
                <p class="mt-4 text-lg text-slate-400">Multiple layers of security protect your smart home</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- No Open Ports -->
                <div class="bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 p-8">
                    <div class="w-12 h-12 rounded-xl bg-cyan-500/20 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-3">No Open Ports</h3>
                    <p class="text-slate-400">
                        Your Home Assistant stays completely behind your firewall. No port forwarding, no exposed services. The connection is initiated outbound from your network, keeping your home invisible to the internet.
                    </p>
                </div>

                <!-- End-to-End Encryption -->
                <div class="bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 p-8">
                    <div class="w-12 h-12 rounded-xl bg-cyan-500/20 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-3">Encrypted Connections</h3>
                    <p class="text-slate-400">
                        All traffic is encrypted using TLS (the same encryption banks use). Your data travels securely from your device to your Home Assistant. We can't read or modify anything passing through.
                    </p>
                </div>

                <!-- No Data Storage -->
                <div class="bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 p-8">
                    <div class="w-12 h-12 rounded-xl bg-cyan-500/20 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-3">No Data Storage</h3>
                    <p class="text-slate-400">
                        We don't log, store, or analyze your Home Assistant traffic. No history of your automations, no record of your device states. Your smart home data stays in your home.
                    </p>
                </div>

                <!-- Your HA Login -->
                <div class="bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 p-8">
                    <div class="w-12 h-12 rounded-xl bg-cyan-500/20 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-3">Your Credentials Stay Yours</h3>
                    <p class="text-slate-400">
                        We never see your Home Assistant password. You log in directly to your Home Assistant through the tunnel. Your HA credentials are never shared with or stored by HARelay.
                    </p>
                </div>

                <!-- Unique Subdomains -->
                <div class="bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 p-8">
                    <div class="w-12 h-12 rounded-xl bg-cyan-500/20 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-3">Private Subdomains</h3>
                    <p class="text-slate-400">
                        Each user gets a unique, random subdomain. There's no directory or list of users. Your subdomain is virtually impossible to guess, with billions of possible combinations.
                    </p>
                </div>

                <!-- Full Control -->
                <div class="bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 p-8">
                    <div class="w-12 h-12 rounded-xl bg-cyan-500/20 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-3">You're in Control</h3>
                    <p class="text-slate-400">
                        Disconnect anytime by simply stopping the add-on. Regenerate your connection token if you suspect it's compromised. Delete your account and all data is permanently removed.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- How It Works (Security Focus) -->
    <div class="py-24 bg-white/5">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-white">Why This Architecture Is Secure</h2>
                <p class="mt-4 text-lg text-slate-400">Understanding the security benefits of our approach</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Traditional Port Forwarding (Bad) -->
                <div class="bg-red-500/10 backdrop-blur-lg rounded-2xl border border-red-500/20 p-8">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-full bg-red-500/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <h3 class="ml-4 text-xl font-semibold text-white">Traditional: Port Forwarding</h3>
                    </div>
                    <ul class="space-y-3 text-slate-300">
                        <li class="flex items-start">
                            <svg class="flex-shrink-0 w-5 h-5 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span class="ml-3">Opens your network to the entire internet</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="flex-shrink-0 w-5 h-5 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span class="ml-3">Vulnerable to port scanning and attacks</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="flex-shrink-0 w-5 h-5 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span class="ml-3">Requires SSL certificate management</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="flex-shrink-0 w-5 h-5 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span class="ml-3">Your IP address is publicly visible</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="flex-shrink-0 w-5 h-5 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span class="ml-3">If compromised, attacker is inside your network</span>
                        </li>
                    </ul>
                </div>

                <!-- HARelay (Good) -->
                <div class="bg-green-500/10 backdrop-blur-lg rounded-2xl border border-green-500/20 p-8">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h3 class="ml-4 text-xl font-semibold text-white">HARelay: Outbound Tunnel</h3>
                    </div>
                    <ul class="space-y-3 text-slate-300">
                        <li class="flex items-start">
                            <svg class="flex-shrink-0 w-5 h-5 text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="ml-3">No ports open on your network</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="flex-shrink-0 w-5 h-5 text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="ml-3">Invisible to port scanners and bots</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="flex-shrink-0 w-5 h-5 text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="ml-3">SSL handled automatically (free, always valid)</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="flex-shrink-0 w-5 h-5 text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="ml-3">Your home IP stays private</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="flex-shrink-0 w-5 h-5 text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="ml-3">Works behind CGNAT and strict firewalls</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- What We Can and Cannot See -->
    <div class="py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-white">What HARelay Can See</h2>
                <p class="mt-4 text-lg text-slate-400">Complete transparency about our access</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- What We CAN See -->
                <div class="bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 p-8">
                    <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                        <svg class="w-6 h-6 text-amber-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        What We Can See
                    </h3>
                    <ul class="space-y-4 text-slate-300">
                        <li class="flex items-start">
                            <span class="text-amber-400 mr-3">-</span>
                            <span>Your email address (for account login)</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-amber-400 mr-3">-</span>
                            <span>Your subdomain and connection status</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-amber-400 mr-3">-</span>
                            <span>Data transfer amounts (not content)</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-amber-400 mr-3">-</span>
                            <span>When your connection was last active</span>
                        </li>
                    </ul>
                </div>

                <!-- What We CANNOT See -->
                <div class="bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 p-8">
                    <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                        <svg class="w-6 h-6 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                        </svg>
                        What We Cannot See
                    </h3>
                    <ul class="space-y-4 text-slate-300">
                        <li class="flex items-start">
                            <span class="text-green-400 mr-3">-</span>
                            <span>Your Home Assistant login credentials</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-3">-</span>
                            <span>Your dashboards, automations, or scripts</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-3">-</span>
                            <span>Your device states or sensor data</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-3">-</span>
                            <span>Your camera feeds or media</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-3">-</span>
                            <span>The content of any traffic passing through</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-3">-</span>
                            <span>Your home IP address or location</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Security Features -->
    <div class="py-24 bg-white/5">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-white">Additional Protections</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-cyan-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-white">Two-Factor Authentication</h3>
                        <p class="mt-2 text-slate-400">Optional 2FA for your HARelay account adds an extra layer of protection.</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-cyan-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-white">Token Regeneration</h3>
                        <p class="mt-2 text-slate-400">Instantly invalidate old connections by regenerating your connection token.</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-cyan-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-white">No Search Engine Indexing</h3>
                        <p class="mt-2 text-slate-400">All subdomains are marked as noindex. Your connection won't appear in search results.</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-cyan-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-white">Secure Token Storage</h3>
                        <p class="mt-2 text-slate-400">Connection tokens are hashed using bcrypt. Even we can't see your actual token.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ -->
    <div class="py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-white">Security Questions</h2>
            </div>

            <div class="space-y-6">
                <div class="bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-white">Can HARelay access my Home Assistant?</h3>
                    <p class="mt-3 text-slate-400">
                        No. HARelay acts as a secure relay, forwarding encrypted traffic between your devices and your Home Assistant. We cannot decrypt, read, or modify the traffic. Think of it like a sealed envelope - we deliver it, but we can't open it.
                    </p>
                </div>

                <div class="bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-white">What happens if HARelay gets hacked?</h3>
                    <p class="mt-3 text-slate-400">
                        Even in that unlikely scenario, attackers couldn't access your Home Assistant. They would only see encrypted traffic. Your HA login credentials are never transmitted to or stored by HARelay. Additionally, your home network has no open ports, so there's no direct path in.
                    </p>
                </div>

                <div class="bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-white">Is this more secure than port forwarding?</h3>
                    <p class="mt-3 text-slate-400">
                        Yes, significantly. Port forwarding exposes your home network directly to the internet, making it visible to attackers and vulnerable to exploits. With HARelay, your network stays completely closed. The only connection is outbound, initiated by you.
                    </p>
                </div>

                <div class="bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-white">What if I want to disconnect immediately?</h3>
                    <p class="mt-3 text-slate-400">
                        Simply stop the HARelay add-on in Home Assistant. The tunnel closes instantly and no one can access your HA through HARelay until you start it again. You can also regenerate your connection token to invalidate any existing credentials.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <x-cta-box />
</x-marketing-layout>

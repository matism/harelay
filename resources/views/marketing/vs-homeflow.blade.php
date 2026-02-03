<x-marketing-layout>
    <x-slot name="title">Homeflow.io vs HARelay - Free Home Assistant Remote Access Alternative</x-slot>
    <x-slot name="description">Compare HARelay and Homeflow.io for Home Assistant remote access. HARelay offers free, simple remote access with minimal setup.</x-slot>

    <x-slot name="structuredData">
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebPage",
            "name": "Homeflow.io vs HARelay - Free Home Assistant Remote Access Alternative",
            "description": "Compare HARelay and Homeflow.io for Home Assistant remote access. HARelay offers free, simple remote access with minimal setup.",
            "url": "{{ url()->current() }}",
            "mainEntity": {
                "@type": "ItemList",
                "name": "Home Assistant Remote Access Solutions Comparison",
                "itemListElement": [
                    {
                        "@type": "ListItem",
                        "position": 1,
                        "name": "HARelay",
                        "description": "Free remote access for Home Assistant with no monthly fees, TLS encryption, and servers in Germany."
                    },
                    {
                        "@type": "ListItem",
                        "position": 2,
                        "name": "Homeflow.io",
                        "description": "Remote access solution for smart home systems including Home Assistant."
                    }
                ]
            }
        }
        </script>
    </x-slot>

    <!-- Hero -->
    <div class="py-20 sm:py-28 relative overflow-hidden">
        <!-- Background decoration -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-cyan-500/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center">
                <div class="inline-flex items-center px-4 py-2 rounded-full bg-white/10 border border-white/20 text-sm text-slate-300 mb-8">
                    <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                    Simple & Free
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white">
                    Homeflow.io vs <span class="bg-gradient-to-r from-cyan-400 to-blue-400 bg-clip-text text-transparent">HARelay</span>
                </h1>
                <p class="mt-6 text-xl text-slate-400 max-w-3xl mx-auto leading-relaxed">
                    Both services provide remote access without port forwarding. HARelay is purpose-built for Home Assistant with the simplest possible setup.
                </p>
            </div>
        </div>
    </div>

    <!-- Comparison Table -->
    <div class="py-12 relative">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold text-white">
                    Side by Side
                </h2>
                <p class="mt-4 text-slate-400">See how the two services compare</p>
            </div>

            <div class="bg-gradient-to-b from-white/10 to-white/5 backdrop-blur-lg rounded-3xl border border-white/20 overflow-hidden shadow-2xl overflow-x-auto">
                <table class="w-full min-w-[500px]">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="px-6 py-5 text-left text-sm font-semibold text-slate-300">Feature</th>
                            <th class="px-6 py-5 text-center text-sm font-semibold text-slate-400">Homeflow.io</th>
                            <th class="px-6 py-5 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-lg bg-cyan-500/20 text-cyan-400 text-sm font-semibold">
                                    HARelay
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-5 text-slate-300 font-medium">Price</td>
                            <td class="px-6 py-5 text-center text-slate-400">Free tier + paid</td>
                            <td class="px-6 py-5 text-center">
                                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold bg-green-500/20 text-green-400 ring-1 ring-green-500/30">
                                    Free
                                </span>
                            </td>
                        </tr>
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-5 text-slate-300 font-medium">Remote Access</td>
                            <td class="px-6 py-5 text-center">
                                <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-500/20">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-500/20">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-5 text-slate-300 font-medium">No Port Forwarding</td>
                            <td class="px-6 py-5 text-center">
                                <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-500/20">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-500/20">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-5 text-slate-300 font-medium">SSL/HTTPS Included</td>
                            <td class="px-6 py-5 text-center">
                                <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-500/20">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-500/20">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-5 text-slate-300 font-medium">Setup Complexity</td>
                            <td class="px-6 py-5 text-center text-slate-400">Configuration required</td>
                            <td class="px-6 py-5 text-center">
                                <span class="text-cyan-400 font-medium">Simple pairing code</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-5 text-slate-300 font-medium">Custom Domain</td>
                            <td class="px-6 py-5 text-center">
                                <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-500/20">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="text-slate-400">Coming soon</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Key Differences -->
    <div class="py-20 bg-gradient-to-b from-transparent via-white/5 to-transparent">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-white">
                    Choose What's Right for You
                </h2>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Homeflow Card -->
                <div class="relative bg-white/5 backdrop-blur-lg rounded-3xl border border-white/10 p-8 flex flex-col">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-slate-700 flex items-center justify-center">
                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white">Choose Homeflow.io if...</h3>
                    </div>
                    <ul class="space-y-4 flex-grow">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-slate-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-slate-400">You need a <strong class="text-slate-300">custom domain</strong></span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-slate-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-slate-400">You want <strong class="text-slate-300">advanced features</strong> like access controls</span>
                        </li>
                    </ul>
                    <div class="mt-8 pt-6 border-t border-white/10">
                        <p class="text-slate-500">Free tier with paid upgrades</p>
                    </div>
                </div>

                <!-- HARelay Card -->
                <div class="group relative flex">
                    <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/20 to-blue-500/20 rounded-3xl blur-xl opacity-50 group-hover:opacity-75 transition-opacity"></div>
                    <div class="relative bg-gradient-to-b from-white/10 to-white/5 backdrop-blur-lg rounded-3xl border border-white/20 p-8 flex flex-col w-full">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-white">Choose HARelay if...</h3>
                        </div>
                        <ul class="space-y-4 flex-grow">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-cyan-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-slate-300">You want <strong class="text-white">100% free access</strong> with no limits</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-cyan-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-slate-300">You prefer the <strong class="text-white">easiest possible setup</strong></span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-cyan-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-slate-300">You only need <strong class="text-white">Home Assistant</strong> access</span>
                            </li>
                        </ul>
                        <div class="mt-8 pt-6 border-t border-white/10">
                            <p class="text-green-400 font-semibold text-lg">Always free, no credit card</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ for SEO -->
    <div class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold text-white">
                    Common Questions
                </h2>
            </div>

            <div class="space-y-4">
                <details class="group bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 overflow-hidden">
                    <summary class="flex items-center justify-between px-6 py-5 cursor-pointer list-none">
                        <h3 class="text-lg font-semibold text-white">What is Homeflow.io?</h3>
                        <svg class="w-5 h-5 text-slate-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="px-6 pb-5">
                        <p class="text-slate-400">Homeflow.io is a tunnel service that lets you expose local services to the internet without port forwarding. It supports various applications and offers features like custom domains.</p>
                    </div>
                </details>

                <details class="group bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 overflow-hidden">
                    <summary class="flex items-center justify-between px-6 py-5 cursor-pointer list-none">
                        <h3 class="text-lg font-semibold text-white">Is HARelay really free?</h3>
                        <svg class="w-5 h-5 text-slate-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="px-6 pb-5">
                        <p class="text-slate-400">Yes, HARelay is completely free to use. There are no hidden fees, no premium tiers, and no credit card required. We focus exclusively on Home Assistant remote access.</p>
                    </div>
                </details>

                <details class="group bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 overflow-hidden">
                    <summary class="flex items-center justify-between px-6 py-5 cursor-pointer list-none">
                        <h3 class="text-lg font-semibold text-white">Which is easier to set up?</h3>
                        <svg class="w-5 h-5 text-slate-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="px-6 pb-5">
                        <p class="text-slate-400">HARelay is designed to be as simple as possible. Install the add-on from the Home Assistant Add-on Store, enter a pairing code, and you're connected. No configuration files or manual token copying needed.</p>
                    </div>
                </details>

                <details class="group bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 overflow-hidden">
                    <summary class="flex items-center justify-between px-6 py-5 cursor-pointer list-none">
                        <h3 class="text-lg font-semibold text-white">Can I use both services?</h3>
                        <svg class="w-5 h-5 text-slate-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="px-6 pb-5">
                        <p class="text-slate-400">Yes, you can use HARelay for Home Assistant remote access while using Homeflow.io for other services. They operate independently and won't interfere with each other.</p>
                    </div>
                </details>
            </div>
        </div>
    </div>

    <x-cta-box />
</x-marketing-layout>

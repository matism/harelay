<x-marketing-layout>
    <x-slot name="title">Pricing - HARelay</x-slot>
    <x-slot name="description">Simple, transparent pricing for Home Assistant remote access. Start free today.</x-slot>

    <div class="bg-white">
        <div class="max-w-7xl mx-auto py-24 px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:flex-col sm:align-center">
                <h1 class="text-5xl font-extrabold text-gray-900 sm:text-center">Pricing Plans</h1>
                <p class="mt-5 text-xl text-gray-500 sm:text-center">
                    Start for free, upgrade when you need more.
                </p>
            </div>

            <div class="mt-12 space-y-4 sm:mt-16 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-6 lg:max-w-4xl lg:mx-auto xl:max-w-none xl:grid-cols-3">
                <!-- Free Plan -->
                <div class="border border-gray-200 rounded-lg shadow-sm divide-y divide-gray-200">
                    <div class="p-6">
                        <h2 class="text-lg leading-6 font-medium text-gray-900">Free</h2>
                        <p class="mt-4 text-sm text-gray-500">Perfect for trying out HARelay.</p>
                        <p class="mt-8">
                            <span class="text-4xl font-extrabold text-gray-900">$0</span>
                            <span class="text-base font-medium text-gray-500">/mo</span>
                        </p>
                        <a href="{{ route('register') }}" class="mt-8 block w-full bg-blue-600 border border-transparent rounded-md py-2 text-sm font-semibold text-white text-center hover:bg-blue-700">
                            Get Started
                        </a>
                    </div>
                    <div class="pt-6 pb-8 px-6">
                        <h3 class="text-xs font-medium text-gray-900 tracking-wide uppercase">What's included</h3>
                        <ul role="list" class="mt-6 space-y-4">
                            <li class="flex space-x-3">
                                <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-500">1 Home Assistant instance</span>
                            </li>
                            <li class="flex space-x-3">
                                <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-500">Unique subdomain</span>
                            </li>
                            <li class="flex space-x-3">
                                <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-500">Secure WebSocket tunnel</span>
                            </li>
                            <li class="flex space-x-3">
                                <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-500">Full access during beta</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Pro Plan (Coming Soon) -->
                <div class="border border-gray-200 rounded-lg shadow-sm divide-y divide-gray-200 opacity-75">
                    <div class="p-6">
                        <h2 class="text-lg leading-6 font-medium text-gray-900">Pro</h2>
                        <p class="mt-4 text-sm text-gray-500">For power users and families.</p>
                        <p class="mt-8">
                            <span class="text-4xl font-extrabold text-gray-900">$5</span>
                            <span class="text-base font-medium text-gray-500">/mo</span>
                        </p>
                        <span class="mt-8 block w-full bg-gray-300 border border-transparent rounded-md py-2 text-sm font-semibold text-gray-600 text-center">
                            Coming Soon
                        </span>
                    </div>
                    <div class="pt-6 pb-8 px-6">
                        <h3 class="text-xs font-medium text-gray-900 tracking-wide uppercase">What's included</h3>
                        <ul role="list" class="mt-6 space-y-4">
                            <li class="flex space-x-3">
                                <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-500">Everything in Free</span>
                            </li>
                            <li class="flex space-x-3">
                                <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-500">Custom subdomain</span>
                            </li>
                            <li class="flex space-x-3">
                                <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-500">Priority support</span>
                            </li>
                            <li class="flex space-x-3">
                                <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-500">Multiple connections</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Team Plan (Coming Soon) -->
                <div class="border border-gray-200 rounded-lg shadow-sm divide-y divide-gray-200 opacity-75">
                    <div class="p-6">
                        <h2 class="text-lg leading-6 font-medium text-gray-900">Team</h2>
                        <p class="mt-4 text-sm text-gray-500">For businesses and organizations.</p>
                        <p class="mt-8">
                            <span class="text-4xl font-extrabold text-gray-900">$20</span>
                            <span class="text-base font-medium text-gray-500">/mo</span>
                        </p>
                        <span class="mt-8 block w-full bg-gray-300 border border-transparent rounded-md py-2 text-sm font-semibold text-gray-600 text-center">
                            Coming Soon
                        </span>
                    </div>
                    <div class="pt-6 pb-8 px-6">
                        <h3 class="text-xs font-medium text-gray-900 tracking-wide uppercase">What's included</h3>
                        <ul role="list" class="mt-6 space-y-4">
                            <li class="flex space-x-3">
                                <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-500">Everything in Pro</span>
                            </li>
                            <li class="flex space-x-3">
                                <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-500">Unlimited connections</span>
                            </li>
                            <li class="flex space-x-3">
                                <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-500">Team management</span>
                            </li>
                            <li class="flex space-x-3">
                                <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-500">Dedicated support</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="bg-gray-50">
        <div class="max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:py-20 lg:px-8">
            <div class="lg:grid lg:grid-cols-3 lg:gap-8">
                <div>
                    <h2 class="text-3xl font-extrabold text-gray-900">
                        Frequently asked questions
                    </h2>
                    <p class="mt-4 text-lg text-gray-500">
                        Can't find the answer you're looking for? Contact us and we'll help you out.
                    </p>
                </div>
                <div class="mt-12 lg:mt-0 lg:col-span-2">
                    <dl class="space-y-12">
                        <div>
                            <dt class="text-lg leading-6 font-medium text-gray-900">
                                Is HARelay really free?
                            </dt>
                            <dd class="mt-2 text-base text-gray-500">
                                Yes! During our beta period, HARelay is completely free to use with full functionality. We'll introduce paid plans in the future for advanced features.
                            </dd>
                        </div>
                        <div>
                            <dt class="text-lg leading-6 font-medium text-gray-900">
                                Do I need to open any ports on my router?
                            </dt>
                            <dd class="mt-2 text-base text-gray-500">
                                No. HARelay uses outbound WebSocket connections, so your Home Assistant stays behind your firewall with no ports exposed.
                            </dd>
                        </div>
                        <div>
                            <dt class="text-lg leading-6 font-medium text-gray-900">
                                Is my data secure?
                            </dt>
                            <dd class="mt-2 text-base text-gray-500">
                                Absolutely. All connections are encrypted using TLS, and we never store your Home Assistant credentials. The tunnel only forwards traffic - we can't see your data.
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-marketing-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="p-6 sm:p-8 bg-white/10 backdrop-blur-lg shadow-xl rounded-2xl border border-white/20">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="p-6 sm:p-8 bg-white/10 backdrop-blur-lg shadow-xl rounded-2xl border border-white/20">
                @include('profile.partials.update-password-form')
            </div>

            <!-- Two-Factor Authentication -->
            <div class="p-6 sm:p-8 bg-white/10 backdrop-blur-lg shadow-xl rounded-2xl border border-white/20">
                <h2 class="text-lg font-semibold text-white mb-1">
                    {{ __('Two-Factor Authentication') }}
                </h2>
                <p class="text-sm text-slate-400 mb-4">
                    {{ __('Add an extra layer of security to your account using two-factor authentication.') }}
                </p>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center">
                        @if(auth()->user()->hasTwoFactorEnabled())
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400 ring-1 ring-green-500/30">
                                <span class="w-1.5 h-1.5 mr-1.5 bg-green-500 rounded-full"></span>
                                Enabled
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-500/20 text-slate-400 ring-1 ring-slate-500/30">
                                <span class="w-1.5 h-1.5 mr-1.5 bg-slate-400 rounded-full"></span>
                                Disabled
                            </span>
                        @endif
                    </div>

                    <a href="{{ route('two-factor.show') }}"
                        class="inline-flex items-center justify-center px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/10 rounded-lg text-sm font-medium text-white transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Manage
                    </a>
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-red-500/10 backdrop-blur-lg shadow-xl rounded-2xl border border-red-500/20">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>

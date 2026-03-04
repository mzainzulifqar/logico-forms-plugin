{{-- Default navigation — publish and customize for your app --}}
<div x-data="{ mobileNav: false }" class="hero-gradient border-b border-slate-200/50">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-8 py-4 sm:py-8">
        <div class="flex items-center justify-between">
            {{-- Left: Logo + User Info --}}
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <a href="{{ route('forms.index') }}" class="shrink-0">
                    <img src="{{ asset(config('forms.brand.logo_url', '/logo.svg')) }}" alt="{{ config('forms.brand.name', 'Forms') }}" class="w-9 h-9 rounded-xl shadow-lg shadow-indigo-200">
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-2xl font-bold text-slate-900 mb-0.5">{{ config('forms.brand.name', 'Forms') }}</h1>
                    @auth
                    <p class="text-xs sm:text-sm text-slate-500 truncate">
                        {{ auth()->user()->name }}
                        <span class="hidden sm:inline">&middot; <span class="text-slate-400">{{ auth()->user()->email }}</span></span>
                    </p>
                    @endauth
                </div>
            </div>

            {{-- Right: Desktop nav --}}
            <div class="hidden lg:flex items-center gap-2">
                <a href="{{ route('forms.templates') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:border-slate-300 hover:bg-slate-50 transition-all shadow-sm">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                    Templates
                </a>
                <a href="{{ route('forms.ai-builder') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:border-slate-300 hover:bg-slate-50 transition-all shadow-sm">
                    <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                    AI Builder
                </a>
                @if(!($atLimit ?? false))
                    <a href="{{ route('forms.create') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-500 to-violet-500 rounded-xl hover:from-indigo-600 hover:to-violet-600 transition-all shadow-lg shadow-indigo-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        New Form
                    </a>
                @endif
            </div>

            {{-- Mobile hamburger --}}
            <button @click="mobileNav = !mobileNav" class="lg:hidden flex items-center justify-center w-10 h-10 text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">
                <svg x-show="!mobileNav" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                <svg x-show="mobileNav" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Mobile menu --}}
        <div x-show="mobileNav" x-cloak x-transition class="lg:hidden mt-4 pt-4 border-t border-slate-200/50 space-y-2">
            <a href="{{ route('forms.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-slate-700 rounded-xl hover:bg-white/60 transition-all">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                Dashboard
            </a>
            <a href="{{ route('forms.templates') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-slate-700 rounded-xl hover:bg-white/60 transition-all">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                Templates
            </a>
            <a href="{{ route('forms.ai-builder') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-slate-700 rounded-xl hover:bg-white/60 transition-all">
                <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                AI Builder
            </a>
        </div>
    </div>
</div>

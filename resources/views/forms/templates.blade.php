<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/logo.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Form Templates — Logicoforms</title>
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    <style>
        *{font-family:'DM Sans', system-ui, sans-serif}
        .hero-gradient { background: linear-gradient(135deg, #eef2ff 0%, #faf5ff 50%, #fdf4ff 100%); }
        .template-card{transition:all .2s ease}
        .template-card:hover{transform:translateY(-4px)}
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen">

<div x-data="{ activeCategory: 'all' }">

{{-- Hero Section --}}
<div class="hero-gradient border-b border-slate-200/50">
    <div class="max-w-[1400px] mx-auto px-8 py-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center shadow-sm hover:bg-slate-50 transition-all shrink-0" title="Back to dashboard">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 mb-0.5">Form Templates</h1>
                    <p class="text-sm text-slate-500">Choose a template to get started quickly</p>
                </div>
            </div>
            <a href="{{ route('forms.create') }}" class="flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-500 to-violet-500 rounded-xl hover:from-indigo-600 hover:to-violet-600 transition-all shadow-lg shadow-indigo-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Blank Form
            </a>
        </div>
    </div>
</div>

<div class="max-w-[1400px] mx-auto px-8 py-8">

    {{-- Category Filter Pills --}}
    <div class="flex flex-wrap items-center gap-2 mb-8">
        <button @click="activeCategory = 'all'"
                :class="activeCategory === 'all' ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-200' : 'bg-white text-slate-600 border border-slate-200 hover:border-indigo-300 hover:text-indigo-600'"
                class="px-4 py-2 text-sm font-medium rounded-xl transition-all">
            All Templates
        </button>
        @foreach($categories as $key => $label)
            <button @click="activeCategory = '{{ $key }}'"
                    :class="activeCategory === '{{ $key }}' ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-200' : 'bg-white text-slate-600 border border-slate-200 hover:border-indigo-300 hover:text-indigo-600'"
                    class="px-4 py-2 text-sm font-medium rounded-xl transition-all">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Templates Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach($templates as $template)
            <div class="template-card bg-white border border-slate-200 rounded-2xl overflow-hidden hover:border-indigo-300 hover:shadow-xl hover:shadow-indigo-100/50 group"
                 x-show="activeCategory === 'all' || activeCategory === '{{ $template['category'] }}'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                {{-- Card Header --}}
                <div class="p-6 pb-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            {{-- Icon --}}
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                                @switch($template['category'])
                                    @case('feedback') bg-amber-50 @break
                                    @case('registration') bg-blue-50 @break
                                    @case('hr') bg-rose-50 @break
                                    @case('order') bg-emerald-50 @break
                                    @case('marketing') bg-violet-50 @break
                                    @case('support') bg-cyan-50 @break
                                    @case('education') bg-teal-50 @break
                                    @case('quiz') bg-orange-50 @break
                                @endswitch
                            ">
                                @switch($template['icon'])
                                    @case('star')
                                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
                                        @break
                                    @case('globe')
                                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>
                                        @break
                                    @case('calendar')
                                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                        @break
                                    @case('book')
                                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                                        @break
                                    @case('briefcase')
                                        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                        @break
                                    @case('heart')
                                        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                                        @break
                                    @case('cart')
                                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                                        @break
                                    @case('utensils')
                                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0L3 16.5m15-3.379a48.474 48.474 0 00-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 013 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 016 13.12M12.265 3.11a.375.375 0 11-.53 0L12 2.845l.265.265z"/></svg>
                                        @break
                                    @case('mail')
                                        <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                        @break
                                    @case('zap')
                                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                                        @break
                                    @case('bug')
                                        <svg class="w-5 h-5 text-cyan-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 12.75c1.148 0 2.278.08 3.383.237 1.037.146 1.866.966 1.866 2.013 0 3.728-2.35 6.75-5.25 6.75S6.75 18.728 6.75 15c0-1.046.83-1.867 1.866-2.013A24.204 24.204 0 0112 12.75zm0 0c2.883 0 5.647.508 8.207 1.44a23.91 23.91 0 01-1.152-6.135 8.713 8.713 0 00-2.347.137c-.654.12-1.254-.467-1.081-1.108.21-.781.329-1.607.329-2.459 0-1.036-.17-2.032-.484-2.962a.5.5 0 00-.63-.312A17.71 17.71 0 0012 2.25c-1.062 0-2.1.093-3.109.273a.5.5 0 00-.39.39c-.263.968-.404 1.99-.404 3.047 0 .734.087 1.447.252 2.129.148.61-.35 1.16-.965 1.085a8.73 8.73 0 00-2.676.044 23.93 23.93 0 01-1.207 6.39A24.28 24.28 0 0112 12.75z"/></svg>
                                        @break
                                    @case('lifebuoy')
                                        <svg class="w-5 h-5 text-cyan-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.712 4.33a9.027 9.027 0 011.652 1.306c.51.51.944 1.064 1.306 1.652M16.712 4.33l-3.448 4.138m3.448-4.138a9.014 9.014 0 00-9.424 0M19.67 7.288l-4.138 3.448m4.138-3.448a9.014 9.014 0 010 9.424m-4.138-5.976a3.736 3.736 0 00-.88-1.388 3.737 3.737 0 00-1.388-.88m2.268 2.268a3.765 3.765 0 010 2.528m-2.268-4.796l4.138-3.448m0 14.136a9.027 9.027 0 01-1.306 1.652c-.51.51-1.064.944-1.652 1.306m0 0l-3.448-4.138m3.448 4.138a9.014 9.014 0 01-9.424 0m5.976-4.138a3.765 3.765 0 01-2.528 0m4.796 2.268l3.448 4.138m-14.136 0a9.027 9.027 0 01-1.652-1.306 9.027 9.027 0 01-1.306-1.652m0 0l4.138-3.448M4.33 16.712a9.014 9.014 0 010-9.424m4.138 5.976a3.765 3.765 0 010-2.528m-2.268 4.796L2.062 19.67m0-14.136a9.027 9.027 0 011.306-1.652A9.014 9.014 0 014.33 2.69m0 0l3.448 4.138M7.288 4.33a9.014 9.014 0 019.424 0M7.288 4.33L4.33 7.288m5.976 2.268a3.737 3.737 0 00-.88 1.388 3.765 3.765 0 000 2.528c.178.513.48.968.88 1.388"/></svg>
                                        @break
                                    @case('academic')
                                        <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
                                        @break
                                    @case('chart')
                                        <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                                        @break
                                    @case('clipboard')
                                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                                        @break
                                    @case('xCircle')
                                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @break
                                @endswitch
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-base text-slate-900 group-hover:text-indigo-600 transition-colors truncate">{{ $template['title'] }}</h3>
                            </div>
                        </div>
                        <span class="shrink-0 ml-2 px-2.5 py-1 text-[10px] font-bold rounded-lg uppercase tracking-wide
                            @switch($template['category'])
                                @case('feedback') bg-amber-50 text-amber-600 @break
                                @case('registration') bg-blue-50 text-blue-600 @break
                                @case('hr') bg-rose-50 text-rose-600 @break
                                @case('order') bg-emerald-50 text-emerald-600 @break
                                @case('marketing') bg-violet-50 text-violet-600 @break
                                @case('support') bg-cyan-50 text-cyan-600 @break
                                @case('education') bg-teal-50 text-teal-600 @break
                                @case('quiz') bg-orange-50 text-orange-600 @break
                            @endswitch
                        ">{{ $categories[$template['category']] ?? $template['category'] }}</span>
                    </div>

                    <p class="text-sm text-slate-500 leading-relaxed mb-4">{{ $template['description'] }}</p>

                    {{-- Theme Preview --}}
                    @if(!empty($template['theme']))
                        <div class="mb-4 rounded-xl overflow-hidden border border-slate-100" style="background-color: {{ $template['theme']['background_color'] }}; padding: 10px 14px;">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-2 h-2 rounded-full" style="background-color: {{ $template['theme']['question_color'] }}; opacity: 0.5;"></div>
                                <div class="h-1.5 rounded-full" style="background-color: {{ $template['theme']['question_color'] }}; opacity: 0.25; width: 60%;"></div>
                            </div>
                            <div class="h-6 rounded-md mb-2" style="border: 1.5px solid {{ $template['theme']['answer_color'] }}; opacity: 0.5;"></div>
                            <div class="flex justify-end">
                                <div class="h-5 w-14 rounded-md" style="background-color: {{ $template['theme']['button_color'] }};"></div>
                            </div>
                        </div>
                    @endif

                    {{-- Meta info --}}
                    <div class="flex items-center gap-4 mb-4">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
                            <span class="text-sm font-semibold text-slate-600">{{ count($template['questions']) }}</span>
                            <span class="text-xs text-slate-400">questions</span>
                        </div>
                        @php
                            $hasLogic = collect($template['questions'])->contains(fn($q) => !empty($q['logic']));
                        @endphp
                        @if($hasLogic)
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                                <span class="text-xs font-medium text-indigo-500">Branching logic</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Card Footer --}}
                <div class="px-6 pb-5">
                    <form method="POST" action="{{ route('forms.create-from-template', $template['slug']) }}" x-data="{ loading: false }" @submit="loading = true">
                        @csrf
                        <button type="submit" :disabled="loading"
                                :class="loading ? 'opacity-60 cursor-wait' : ''"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-xl hover:bg-indigo-100 hover:border-indigo-300 transition-all group-hover:bg-indigo-500 group-hover:text-white group-hover:border-indigo-500 group-hover:shadow-lg group-hover:shadow-indigo-200">
                            <template x-if="!loading">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            </template>
                            <template x-if="loading">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            </template>
                            <span x-text="loading ? 'Creating...' : 'Use this template'"></span>
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

</div>

</div>

</body>
</html>

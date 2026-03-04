<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/logo.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard — Logicoforms</title>
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    <style>
        *{font-family:'DM Sans', system-ui, sans-serif}
        .hero-gradient { background: linear-gradient(135deg, #eef2ff 0%, #faf5ff 50%, #fdf4ff 100%); }
        .form-card{transition:all .2s ease}
        .form-card:hover{transform:translateY(-4px)}
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen">

@php
    $atLimit = $atLimit ?? false;
@endphp

<div x-data="dashboard()" x-cloak>

@include(config('forms.views.nav', 'forms::partials.nav'))

<div class="max-w-[1400px] mx-auto px-4 sm:px-8 py-8">

    @if(session('success'))
        <div class="mb-6 px-5 py-4 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Quick Stats --}}
    @php
        $totalForms = $forms->count();
        $publishedForms = $forms->where('status', 'published')->count();
        $totalResponses = $forms->sum('sessions_count');
        $totalQuestions = $forms->sum('questions_count');
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-slate-200 rounded-xl p-4 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-900">{{ $totalForms }}</p>
                <p class="text-xs text-slate-400 font-medium">Total Forms</p>
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-900">{{ $publishedForms }}</p>
                <p class="text-xs text-slate-400 font-medium">Published</p>
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-violet-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-900">{{ $totalResponses }}</p>
                <p class="text-xs text-slate-400 font-medium">Responses</p>
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-900">{{ $totalQuestions }}</p>
                <p class="text-xs text-slate-400 font-medium">Questions</p>
            </div>
        </div>
    </div>

    @if($atLimit)
        <div class="mb-6 px-5 py-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700 flex items-center gap-3">
            <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
            You've reached the form limit for your current plan.
        </div>
    @endif

    @if($forms->isEmpty())
        {{-- Empty State --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-100 to-violet-100 flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            </div>
            <h2 class="text-xl font-bold text-slate-900 mb-2">Create your first form</h2>
            <p class="text-slate-500 mb-6 max-w-md mx-auto">Build beautiful, interactive forms with conditional logic and AI generation.</p>
            <div class="flex items-center justify-center gap-3">
                <a href="{{ route('forms.create') }}" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white bg-gradient-to-r from-indigo-500 to-violet-500 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    New Form
                </a>
                <a href="{{ route('forms.templates') }}" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                    Templates
                </a>
                <a href="{{ route('forms.ai-builder') }}" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl">
                    <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                    AI Builder
                </a>
            </div>
        </div>
    @else
        {{-- Toolbar: Search + Bulk Actions --}}
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3 flex-1">
                {{-- Search --}}
                <div class="relative max-w-xs w-full">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    <input type="text"
                           x-model="search"
                           placeholder="Search forms..."
                           class="w-full pl-9 pr-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder-slate-400">
                </div>
                <span class="text-sm text-slate-400" x-text="filteredForms().length + ' form' + (filteredForms().length !== 1 ? 's' : '')"></span>
            </div>

            {{-- Bulk Actions --}}
            <div class="flex items-center gap-3" x-show="selected.length > 0" x-transition>
                <span class="text-sm font-medium text-indigo-600" x-text="selected.length + ' selected'"></span>
                <button @click="bulkDelete()" class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-rose-600 bg-rose-50 border border-rose-200 rounded-xl hover:bg-rose-100 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 0 00-7.5 0"/></svg>
                    Delete
                </button>
            </div>
        </div>

        {{-- Forms Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            {{-- Create New Form Card --}}
            @if(!$atLimit)
                <a href="{{ route('forms.create') }}" class="form-card flex flex-col items-center justify-center bg-gradient-to-br from-slate-50 to-white border-2 border-dashed border-slate-200 rounded-2xl p-8 hover:border-indigo-300 hover:bg-indigo-50/30 transition-all group min-h-[220px]"
                   x-show="!search">
                    <div class="w-14 h-14 rounded-2xl bg-white border-2 border-dashed border-slate-200 group-hover:border-indigo-300 group-hover:bg-indigo-50 flex items-center justify-center mb-4 transition-all">
                        <svg class="w-7 h-7 text-slate-300 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-400 group-hover:text-indigo-600 transition-colors">Create New Form</p>
                </a>
            @endif

            @foreach($forms as $form)
                <div class="form-card bg-white border border-slate-200 rounded-2xl overflow-hidden hover:border-indigo-300 hover:shadow-xl hover:shadow-indigo-100/50 group relative"
                     x-show="matchesSearch({{ json_encode($form->title) }}, {{ json_encode($form->description) }})"
                     x-transition>

                    {{-- Checkbox --}}
                    <label class="absolute top-4 left-4 z-10 cursor-pointer" @click.stop>
                        <input type="checkbox" :value="{{ $form->id }}"
                               x-model.number="selected"
                               class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 transition-all">
                    </label>

                    {{-- Card Header with gradient --}}
                    <div class="h-1.5 bg-gradient-to-r {{ $form->status === 'published' ? 'from-emerald-400 to-teal-400' : ($form->status === 'closed' ? 'from-rose-400 to-pink-400' : 'from-slate-300 to-slate-400') }}"></div>

                    <div class="p-6 pl-10">
                        <div class="flex items-start justify-between mb-3">
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('forms.edit', $form) }}" class="block">
                                    <h3 class="font-bold text-lg text-slate-900 truncate group-hover:text-indigo-600 transition-colors">{{ $form->title }}</h3>
                                </a>
                                <p class="text-xs text-slate-400 mt-1 font-mono truncate">/f/{{ $form->slug }}</p>
                            </div>
                            <span class="shrink-0 ml-3 px-2.5 py-1 text-[10px] font-bold rounded-lg uppercase tracking-wide {{ $form->status === 'published' ? 'bg-emerald-50 text-emerald-600' : ($form->status === 'closed' ? 'bg-rose-50 text-rose-500' : 'bg-slate-100 text-slate-500') }}">
                                {{ $form->status }}
                            </span>
                        </div>

                        <p class="text-sm text-slate-500 mb-5 line-clamp-2 leading-relaxed min-h-[40px]">{{ $form->description ?: 'No description' }}</p>

                        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                            <div class="flex items-center gap-5">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
                                    <span class="text-sm font-semibold text-slate-600">{{ $form->questions_count }}</span>
                                    <span class="text-xs text-slate-400">Qs</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                                    <span class="text-sm font-semibold text-slate-600">{{ $form->sessions_count }}</span>
                                    <span class="text-xs text-slate-400">resp</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                @if($form->status === 'published')
                                    <a href="{{ route('forms.public', $form->slug) }}" target="_blank" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 rounded-lg transition-all" title="Preview">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                    </a>
                                @endif
                                <a href="{{ route('forms.show', $form) }}" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-emerald-500 hover:bg-emerald-50 rounded-lg transition-all" title="Responses">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                                </a>
                                <a href="{{ route('forms.edit', $form) }}" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 rounded-lg transition-all" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    @endif

</div>
</div>

<script>
function dashboard() {
    return {
        search: '',
        selected: [],

        matchesSearch(title, desc) {
            if (!this.search) return true;
            const q = this.search.toLowerCase();
            return (title || '').toLowerCase().includes(q) || (desc || '').toLowerCase().includes(q);
        },

        filteredForms() {
            if (!this.search) return @json($forms->pluck('id'));
            const q = this.search.toLowerCase();
            return @json($forms->map(fn($f) => ['id' => $f->id, 'title' => $f->title, 'desc' => $f->description])).filter(f =>
                (f.title || '').toLowerCase().includes(q) || (f.desc || '').toLowerCase().includes(q)
            ).map(f => f.id);
        },

        async bulkDelete() {
            if (!confirm(`Delete ${this.selected.length} form(s)? This cannot be undone.`)) return;
            const token = document.querySelector('meta[name="csrf-token"]').content;
            for (const id of this.selected) {
                await fetch(`/forms/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
                });
            }
            window.location.reload();
        }
    };
}
</script>

</body>
</html>

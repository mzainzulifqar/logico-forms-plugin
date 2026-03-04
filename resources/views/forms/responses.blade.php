<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/logo.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responses: {{ $form->title }}</title>
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|jetbrains-mono:400" rel="stylesheet" />
    <style>
        *{font-family:'DM Sans', system-ui, sans-serif}
        .mono{font-family:'JetBrains Mono', monospace}
        .pattern-bg{
            background-color:#f8fafc;
            background-image:radial-gradient(#e2e8f0 1px,transparent 1px);
            background-size:24px 24px;
        }
        ::-webkit-scrollbar{width:6px;height:6px}
        ::-webkit-scrollbar-track{background:transparent}
        ::-webkit-scrollbar-thumb{background:#e5e7eb;border-radius:3px}
        ::-webkit-scrollbar-thumb:hover{background:#d1d5db}
    </style>
</head>
<body class="pattern-bg text-slate-800 antialiased min-h-screen">

<div class="max-w-[1500px] mx-auto px-8 py-8">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('forms.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-slate-600 hover:border-slate-300 hover:shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900">{{ $form->title }}</h1>
                    <span class="px-3 py-1 text-xs font-semibold text-indigo-600 bg-indigo-50 rounded-lg ring-1 ring-indigo-100">Responses</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('forms.edit', $form) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:border-slate-300 hover:bg-slate-50 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                Edit Form
            </a>
        </div>
    </div>

    {{-- Stats cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-10">
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                </div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Starts</p>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ $totalSessions }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Completions</p>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ $completedSessions }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl {{ $completionRate >= 50 ? 'bg-emerald-50' : 'bg-amber-50' }} flex items-center justify-center">
                    <svg class="w-5 h-5 {{ $completionRate >= 50 ? 'text-emerald-500' : 'text-amber-500' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605"/></svg>
                </div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Completion Rate</p>
            </div>
            <p class="text-3xl font-bold {{ $completionRate >= 50 ? 'text-emerald-600' : 'text-amber-500' }}">{{ $completionRate }}%</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
                </div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Questions</p>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ $questions->count() }}</p>
        </div>
    </div>

    {{-- Question-by-question summary --}}
    @if($completedSessions > 0)
    <div class="mb-10">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-8 h-8 rounded-lg bg-violet-500 flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
            </div>
            <h2 class="text-lg font-bold text-slate-800">Summary</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($questionStats as $stat)
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-slate-300 transition-all">
                    <div class="flex items-start gap-3 mb-4">
                        <span class="w-7 h-7 rounded-lg bg-indigo-500 flex items-center justify-center text-xs font-bold text-white shrink-0">{{ $loop->iteration }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-800 leading-snug">{{ $stat['question']->question_text }}</p>
                        </div>
                        <span class="text-xs text-slate-400 shrink-0">{{ $stat['count'] }} answers</span>
                    </div>

                    @if($stat['breakdown'])
                        <div class="space-y-3">
                            @foreach($stat['breakdown'] as $label => $count)
                                @php $pct = $stat['count'] > 0 ? round(($count / $stat['count']) * 100) : 0; @endphp
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <span class="font-medium text-slate-700">{{ $label }}</span>
                                        <span class="text-slate-400">{{ $count }} ({{ $pct }}%)</span>
                                    </div>
                                    <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-indigo-400 to-violet-400 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif(isset($stat['avg']))
                        <div class="flex items-center gap-6">
                            <div class="text-center px-4 py-2 bg-slate-50 rounded-xl">
                                <p class="text-xs text-slate-400 mb-1">Avg</p>
                                <p class="text-lg font-bold text-slate-900">{{ $stat['avg'] }}</p>
                            </div>
                            <div class="text-center px-4 py-2 bg-slate-50 rounded-xl">
                                <p class="text-xs text-slate-400 mb-1">Min</p>
                                <p class="text-lg font-bold text-slate-900">{{ $stat['min'] }}</p>
                            </div>
                            <div class="text-center px-4 py-2 bg-slate-50 rounded-xl">
                                <p class="text-xs text-slate-400 mb-1">Max</p>
                                <p class="text-lg font-bold text-slate-900">{{ $stat['max'] }}</p>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/></svg>
                            <span class="text-sm">{{ $stat['count'] }} text responses</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Response table --}}
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125"/></svg>
                </div>
                <h2 class="text-lg font-bold text-slate-800">Individual Responses</h2>
            </div>
            <span class="px-3 py-1 text-xs font-semibold text-slate-500 bg-slate-100 rounded-lg">{{ $sessions->count() }} completed</span>
        </div>

        @if($sessions->isEmpty())
            <div class="p-16 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-slate-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162M3.75 17.25h16.5"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-700 mb-2">No completed responses yet</h3>
                <p class="text-slate-500">Responses will appear here once someone completes the form.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="text-left px-5 py-4 font-semibold text-slate-500 whitespace-nowrap">#</th>
                            @foreach($questions as $q)
                                <th class="text-left px-5 py-4 font-semibold text-slate-500 whitespace-nowrap max-w-[200px] truncate" title="{{ $q->question_text }}">{{ Str::limit($q->question_text, 30) }}</th>
                            @endforeach
                            <th class="text-left px-5 py-4 font-semibold text-slate-500 whitespace-nowrap">Completed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sessions as $idx => $session)
                            <tr class="border-b border-slate-50 hover:bg-indigo-50/30 transition-colors">
                                <td class="px-5 py-4 text-slate-400 whitespace-nowrap font-medium">{{ $idx + 1 }}</td>
                                @foreach($questions as $q)
                                    <td class="px-5 py-4 text-slate-700 max-w-[200px] truncate" title="{{ $session['answers'][$q->id] ?? '—' }}">
                                        {{ $session['answers'][$q->id] ?? '—' }}
                                    </td>
                                @endforeach
                                <td class="px-5 py-4 text-slate-400 whitespace-nowrap text-xs mono">{{ $session['completed_at'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

</body>
</html>

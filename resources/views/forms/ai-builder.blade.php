<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/logo.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Form Builder — Logicoforms</title>
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700,800&family=jetbrains-mono:400,500" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { font-family: 'DM Sans', system-ui, sans-serif; }
        code, pre, .mono { font-family: 'JetBrains Mono', monospace; }
        [x-cloak] { display: none !important; }

        body {
            background: linear-gradient(to bottom, #ffffff 0%, #f5f3ff 50%, #ede9fe 100%);
            min-height: 100vh;
        }

        .chat-scroll::-webkit-scrollbar { width: 6px; }
        .chat-scroll::-webkit-scrollbar-track { background: transparent; }
        .chat-scroll::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.08); border-radius: 3px; }
        .chat-scroll::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.15); }

        .msg-enter { animation: msgSlide 0.3s ease-out; }
        @keyframes msgSlide {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .typing-dot {
            width: 7px; height: 7px; border-radius: 50%; background: #8b5cf6;
            animation: typing 1.4s infinite ease-in-out both;
        }
        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }
        @keyframes typing {
            0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
            40% { transform: scale(1); opacity: 1; }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
        }
        .float-anim { animation: float 4s ease-in-out infinite; }
        .float-anim-delay { animation: float 4s ease-in-out 1s infinite; }

        .suggestion-card {
            background: #fff;
            border: 1px solid #d8d0f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.25s ease;
            border-radius: 1rem;
        }
        .suggestion-card:hover {
            border-color: #a78bfa;
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(139,92,246,0.15);
        }

        .chat-panel {
            background: #fff;
            border: 1px solid #d8d0f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .prose-ai { font-size: 0.9375rem; line-height: 1.75; color: #475569; }
        .prose-ai > *:first-child { margin-top: 0; }
        .prose-ai > *:last-child { margin-bottom: 0; }
        .prose-ai p { margin: 0.75em 0; }
        .prose-ai h1, .prose-ai h2, .prose-ai h3 { font-weight: 700; color: #1e1b4b; margin: 1.25em 0 0.5em 0; }
        .prose-ai h1 { font-size: 1.25rem; }
        .prose-ai h2 { font-size: 1.1rem; color: #7c3aed; }
        .prose-ai h3 { font-size: 1rem; }
        .prose-ai strong { font-weight: 600; color: #1e1b4b; }
        .prose-ai ul, .prose-ai ol { margin: 0.75em 0; padding-left: 1.5em; }
        .prose-ai li { margin: 0.4em 0; }
        .prose-ai code { background: #f3f0ff; color: #7c3aed; padding: 2px 6px; border-radius: 4px; font-size: 0.85em; }
        .prose-ai pre { background: #1e1b4b; color: #e2e8f0; padding: 1rem; border-radius: 0.75rem; overflow-x: auto; margin: 1em 0; }
        .prose-ai pre code { background: none; color: inherit; padding: 0; }
        .prose-ai a { color: #7c3aed; text-decoration: underline; }

        .timeline-scroll::-webkit-scrollbar { width: 6px; }
        .timeline-scroll::-webkit-scrollbar-track { background: transparent; }
        .timeline-scroll::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.08); border-radius: 3px; }
        .timeline-scroll::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.15); }

        .input-glow:focus-within {
            border-color: #a78bfa !important;
            box-shadow: 0 0 0 3px rgba(139,92,246,0.12), 0 4px 24px rgba(139,92,246,0.1);
        }
    </style>
</head>
<body class="text-gray-800 antialiased h-screen overflow-hidden">

<div x-data="aiBuilder()" class="h-screen flex flex-col relative">

    {{-- Header --}}
    <header class="flex-shrink-0 bg-white border-b border-gray-200 shadow-sm px-4 sm:px-6 py-3">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3 sm:gap-4">
                <a href="/forms" class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                </a>
                <div class="flex items-center gap-3">
                    <img src="{{ asset('logo.svg') }}" alt="Logicoforms" class="w-9 h-9 rounded-xl shadow-sm">
                    <div>
                        <h1 class="font-bold text-gray-900 leading-tight text-sm sm:text-base">AI Form Builder</h1>
                        <p class="text-xs text-gray-400 hidden sm:block">Describe your form in plain English</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 sm:gap-3">
                @if($creditBalance !== null)
                    <div class="flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1.5 rounded-xl bg-purple-50 border border-purple-100">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                        <span class="text-sm font-semibold" x-text="credits" :class="credits <= 0 ? 'text-rose-500' : 'text-gray-900'"></span>
                        <span class="text-xs text-gray-400 hidden sm:inline">credits</span>
                    </div>
                @endif
                <template x-if="conversationId">
                    <button x-on:click="resetChat()" class="text-sm text-gray-400 hover:text-gray-700 px-3 py-1.5 rounded-xl hover:bg-gray-100 transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        New
                    </button>
                </template>
            </div>
        </div>
    </header>

    {{-- Mobile sticky status bar --}}
    <div x-cloak x-show="hasSentFirstPrompt" class="lg:hidden flex-shrink-0 bg-white border-b border-gray-200 shadow-sm">
        <button x-on:click="showTimeline = !showTimeline" class="w-full px-4 py-2.5 flex items-center justify-between">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-2 h-2 rounded-full flex-shrink-0"
                     :class="loading ? 'bg-purple-400 animate-pulse' : (error ? 'bg-rose-400' : 'bg-emerald-400')"></div>
                <span class="text-xs font-medium truncate" :class="loading ? 'text-purple-600' : (error ? 'text-rose-600' : 'text-gray-600')" x-text="sidebarStatus()"></span>
            </div>
            <div class="flex items-center gap-1.5 flex-shrink-0 text-gray-400">
                <span class="text-[10px] uppercase tracking-wider" x-text="showTimeline ? 'Hide' : 'Timeline'"></span>
                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="showTimeline ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
            </div>
        </button>

        <div x-show="showTimeline"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 max-h-0"
             x-transition:enter-end="opacity-100 max-h-64"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 max-h-64"
             x-transition:leave-end="opacity-0 max-h-0"
             class="overflow-hidden">
            <div class="max-h-52 overflow-y-auto timeline-scroll px-4 pb-3 border-t border-gray-100">
                <template x-if="timeline.length === 0">
                    <div class="text-xs text-gray-400 py-2">Send a prompt to see progress here.</div>
                </template>

                <template x-for="(item, idx) in timeline" :key="item.id">
                    <div class="flex gap-2.5 py-1.5" :class="idx === 0 ? 'pt-2.5' : ''">
                        <div class="w-1.5 h-1.5 rounded-full mt-1.5 flex-shrink-0"
                             :class="idx === timeline.length - 1 && loading ? 'bg-purple-400' : 'bg-gray-300'"></div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wide" x-text="item.kind"></span>
                                <span class="text-[10px] text-gray-300 mono" x-text="item.at"></span>
                            </div>
                            <div class="text-xs text-gray-500 leading-snug break-words" x-text="item.message"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Main Area --}}
    <div class="flex-1 overflow-hidden">
        <div class="h-full max-w-6xl mx-auto px-4 sm:px-6 py-6 flex gap-6">

            {{-- Chat Area --}}
            <div class="flex-1 min-w-0 chat-scroll" :class="messages.length ? 'overflow-y-auto' : 'overflow-hidden'" x-ref="chatContainer">
                <div class="w-full max-w-4xl mx-auto">

            {{-- Empty state --}}
            <template x-if="messages.length === 0 && !loading">
                <div class="flex flex-col items-center justify-center" style="min-height: calc(100vh - 220px);">
                    {{-- Hero --}}
                    <div class="text-center mb-10">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-purple-50 border border-purple-100 mb-6 float-anim">
                            <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z"/></svg>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-3">What form would you like to build?</h2>
                        <p class="text-gray-500 max-w-md mx-auto text-sm sm:text-base leading-relaxed">Describe it in plain English and I'll create it for you — with questions, options, and smart branching logic.</p>
                    </div>

                    {{-- Suggestion cards --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full max-w-2xl">
                        <button x-on:click="sendSuggestion('Create a customer feedback survey with rating questions and open-ended feedback')"
                            class="suggestion-card px-5 py-5 text-left group cursor-pointer">
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
                                </div>
                                <div>
                                    <span class="font-bold text-gray-900 group-hover:text-purple-600 transition-colors">Customer feedback</span>
                                    <p class="text-sm text-gray-400 mt-0.5">Ratings, open-ended questions, and NPS score</p>
                                </div>
                            </div>
                        </button>

                        <button x-on:click="sendSuggestion('Create a job application form with personal info, experience, and different follow-up questions based on the role type selected')"
                            class="suggestion-card px-5 py-5 text-left group cursor-pointer">
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                </div>
                                <div>
                                    <span class="font-bold text-gray-900 group-hover:text-purple-600 transition-colors">Job application</span>
                                    <p class="text-sm text-gray-400 mt-0.5">Role-based branching with experience paths</p>
                                </div>
                            </div>
                        </button>

                        <button x-on:click="sendSuggestion('Create an event registration form with name, email, dietary preferences, and workshop session selection')"
                            class="suggestion-card px-5 py-5 text-left group cursor-pointer">
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                </div>
                                <div>
                                    <span class="font-bold text-gray-900 group-hover:text-purple-600 transition-colors">Event registration</span>
                                    <p class="text-sm text-gray-400 mt-0.5">Dietary, sessions, and ticket type selection</p>
                                </div>
                            </div>
                        </button>

                        <button x-on:click="sendSuggestion('Create a PR pathway assessment for Germany vs Ireland with conditional logic based on qualifications, language skills, and job offers')"
                            class="suggestion-card px-5 py-5 text-left group cursor-pointer">
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>
                                </div>
                                <div>
                                    <span class="font-bold text-gray-900 group-hover:text-purple-600 transition-colors">PR pathway assessment</span>
                                    <p class="text-sm text-gray-400 mt-0.5">Germany vs Ireland with smart conditionals</p>
                                </div>
                            </div>
                        </button>
                    </div>

                    <p class="text-sm text-purple-400 mt-8 font-medium">Or type your own description below</p>
                </div>
            </template>

            {{-- Message list --}}
            <template x-for="(msg, i) in messages" :key="i">
                <div class="msg-enter mb-5" :class="msg.role === 'user' ? 'flex justify-end' : 'flex items-start gap-3'">
                    {{-- AI avatar --}}
                    <div x-show="msg.role === 'assistant'" class="w-8 h-8 rounded-xl bg-white border border-gray-200 shadow-sm flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                    </div>
                    {{-- AI message --}}
                    <div x-show="msg.role === 'assistant'" class="chat-panel rounded-2xl rounded-tl-md px-5 py-4 max-w-3xl">
                        <div class="prose-ai" x-html="renderMarkdown(msg.content)"></div>
                    </div>
                    {{-- User message --}}
                    <div x-show="msg.role === 'user'" class="bg-gradient-to-r from-purple-600 to-violet-600 text-white rounded-2xl rounded-tr-md px-5 py-3 max-w-xl shadow-lg shadow-purple-500/15">
                        <div class="text-sm leading-relaxed" x-text="msg.content"></div>
                    </div>
                </div>
            </template>

            {{-- Loading with status --}}
            <template x-if="loading">
                <div class="flex items-start gap-3 msg-enter mb-4">
                    <div class="w-8 h-8 rounded-xl bg-white border border-gray-200 shadow-sm flex items-center justify-center flex-shrink-0 animate-pulse">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                    </div>
                    <div class="chat-panel rounded-2xl rounded-tl-md px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5">
                                <div class="typing-dot"></div>
                                <div class="typing-dot"></div>
                                <div class="typing-dot"></div>
                            </div>
                            <span x-text="thinkingStatus" class="text-sm text-gray-400 italic"></span>
                        </div>
                    </div>
                </div>
            </template>

            {{-- No credits warning --}}
            <template x-if="credits !== null && credits <= 0 && !loading">
                <div class="flex justify-center msg-enter mb-4">
                    <div class="bg-amber-50 border border-amber-200 text-amber-700 rounded-xl px-4 py-3 text-sm flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
                        <span>You have no AI credits remaining.</span>
                    </div>
                </div>
            </template>

            {{-- Error --}}
            <template x-if="error">
                <div class="flex justify-center msg-enter mb-4">
                    <div class="bg-rose-50 border border-rose-200 text-rose-700 rounded-xl px-4 py-3 text-sm flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                        <span x-text="error"></span>
                        <button x-on:click="error = null" class="underline hover:no-underline font-medium text-rose-800">Dismiss</button>
                    </div>
                </div>
            </template>
                </div>
            </div>

            {{-- Timeline — Desktop sidebar --}}
            <aside x-cloak x-show="hasSentFirstPrompt" class="hidden lg:flex w-80 flex-shrink-0">
                <div class="w-full bg-white border border-gray-200 rounded-2xl overflow-hidden flex flex-col shadow-lg shadow-black/5" x-ref="timelineContent">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">Timeline</div>
                            <div class="text-xs text-gray-400">What the agent is doing</div>
                        </div>
                        <button
                            type="button"
                            x-on:click="clearTimeline()"
                            class="text-xs text-gray-400 hover:text-gray-600 px-2 py-1 rounded-lg hover:bg-gray-50 transition-all"
                            :disabled="timeline.length === 0"
                        >
                            Clear
                        </button>
                    </div>

                    <div class="px-4 py-3 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full"
                                 :class="loading ? 'bg-purple-400 animate-pulse' : (error ? 'bg-rose-400' : 'bg-emerald-400')"></div>
                            <div class="text-sm text-gray-700 truncate" x-text="currentStepLabel()"></div>
                        </div>
                        <div class="text-xs text-gray-400 mt-1 truncate" x-text="sidebarStatus()"></div>
                    </div>

                    <div class="flex-1 overflow-y-auto timeline-scroll px-4 py-4">
                        <template x-if="timeline.length === 0">
                            <div class="text-sm text-gray-400">
                                Send a prompt to see live progress here.
                            </div>
                        </template>

                        <template x-for="(item, idx) in timeline" :key="item.id">
                            <div class="flex gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="w-2.5 h-2.5 rounded-full mt-1"
                                         :class="idx === timeline.length - 1 && loading ? 'bg-purple-400' : 'bg-gray-200'"></div>
                                    <div class="w-px flex-1 my-1 bg-gray-100" x-show="idx !== timeline.length - 1"></div>
                                </div>
                                <div class="pb-4 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <div class="text-xs font-medium text-gray-400 uppercase tracking-wide" x-text="item.kind"></div>
                                        <div class="text-xs text-gray-300 mono" x-text="item.at"></div>
                                    </div>
                                    <div class="text-sm text-gray-500 leading-snug break-words" x-text="item.message"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </aside>

        </div>
    </div>

    {{-- Input Area --}}
    <div class="flex-shrink-0 px-4 sm:px-6 pb-5 pt-3">
        <div class="max-w-3xl mx-auto">
            {{-- Ghost suggestion --}}
            <div x-show="showGhost()"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="mb-2 flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-50 border border-amber-200 w-fit">
                <span class="text-xs text-amber-600 italic leading-snug">+ "{{ $ghostSuggestion }}"</span>
                <button type="button" x-on:click="acceptGhost()" class="text-[10px] font-semibold text-amber-700 bg-amber-100 border border-amber-200 rounded px-1.5 py-0.5 leading-none hover:bg-amber-200 transition-colors cursor-pointer">Tab</button>
            </div>
            <form x-on:submit.prevent="send()" class="relative">
                <div class="rounded-2xl input-glow transition-all bg-white shadow-xl shadow-black/8 border border-gray-200 overflow-hidden">
                    <textarea
                        x-ref="input"
                        x-model="input"
                        x-on:keydown.enter.prevent="if (!$event.shiftKey) send()"
                        x-on:keydown.tab.prevent="acceptGhost()"
                        placeholder="Describe the form you want to create..."
                        rows="1"
                        :disabled="loading"
                        class="w-full resize-none bg-transparent pl-5 pr-14 py-4 text-sm text-gray-800 border-0 focus:outline-none focus:ring-0 disabled:opacity-50 placeholder:text-gray-400 transition-all"
                        style="max-height: 150px; min-height: 52px;"
                        x-on:input="autoResize($event.target); if (ghostAccepted && !input.includes(ghostText)) ghostAccepted = false"
                    ></textarea>
                    <button
                        type="submit"
                        :disabled="loading || !input.trim() || (credits !== null && credits <= 0)"
                        class="absolute right-3 bottom-2.5 w-10 h-10 rounded-xl bg-gradient-to-br from-purple-400 to-violet-500 text-white flex items-center justify-center hover:from-purple-500 hover:to-violet-600 disabled:opacity-30 disabled:cursor-not-allowed transition-all shadow-md shadow-purple-300/30"
                    >
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function aiBuilder() {
    return {
        messages: [],
        input: '',
        loading: false,
        conversationId: null,
        error: null,
        thinkingStatus: 'Thinking...',
        timeline: [],
        timelineSeq: 0,
        hasSentFirstPrompt: false,
        showTimeline: false,
        credits: {{ $creditBalance !== null ? $creditBalance : 'null' }},
        ghostText: @js($ghostSuggestion),
        ghostAccepted: false,

        showGhost() {
            if (this.ghostAccepted || this.loading) return false;
            return true;
        },

        acceptGhost() {
            if (this.showGhost()) {
                this.input = this.input.trimEnd() + (this.input.trim() ? '. ' : '') + this.ghostText;
                this.ghostAccepted = true;
                this.$nextTick(() => this.autoResize(this.$refs.input));
            }
        },

        sendSuggestion(text) {
            this.input = text;
            this.send();
        },

        clearTimeline() {
            this.timeline = [];
            this.timelineSeq = 0;
        },

        nowStamp() {
            const d = new Date();
            return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        },

        inferKind(message) {
            const m = (message || '').toLowerCase();
            if (m.includes('validation_error') || m.includes('too shallow') || m.includes('failed')) return 'fix';
            if (m.includes('logic wiring invalid') || m.includes('unreachable')) return 'fix';
            if (m.includes('creating form') || m.includes('adding') || m.includes('publishing')) return 'tool';
            if (m.includes('thinking')) return 'think';
            return 'status';
        },

        humanizeStatus(message) {
            const raw = (message || '').toString();
            const m = raw.toLowerCase();

            if (m.includes('draft invalid')) return 'Refining the draft…';
            if (m.includes('logic wiring invalid') || m.includes('unreachable')) return 'Checking the logic…';
            if (m.includes('branching too shallow')) return 'Improving the flow…';
            if (m.includes('creating form')) return 'Building your form…';
            if (m.includes('publishing')) return 'Publishing…';
            if (m.includes('thinking')) return 'Thinking…';

            return raw;
        },

        pushTimeline(rawMessage, kind = null) {
            if (!rawMessage) return;
            const last = this.timeline[this.timeline.length - 1];
            if (last && last.raw === rawMessage) return;

            const inferredKind = kind || this.inferKind(rawMessage);
            this.timelineSeq += 1;
            this.timeline.push({
                id: `${Date.now()}_${this.timelineSeq}`,
                at: this.nowStamp(),
                kind: inferredKind,
                raw: rawMessage,
                message: this.humanizeStatus(rawMessage),
            });

            if (this.timeline.length > 60) {
                this.timeline = this.timeline.slice(-60);
            }
        },

        currentStepLabel() {
            if (this.error) return 'Error';
            if (this.loading) return 'Working';
            if (this.messages.length === 0) return 'Idle';
            return 'Ready';
        },

        sidebarStatus() {
            if (this.loading) return this.thinkingStatus;
            if (this.error) return 'Something went wrong';
            if (this.hasSentFirstPrompt) return 'Ready';
            return 'Send a prompt to begin';
        },

        async send() {
            const text = this.input.trim();
            if (!text || this.loading) return;

            this.hasSentFirstPrompt = true;
            this.input = '';
            this.error = null;
            this.messages.push({ role: 'user', content: text });
            this.loading = true;
            this.thinkingStatus = 'Thinking...';
            this.clearTimeline();
            this.pushTimeline('Prompt received', 'status');
            this.pushTimeline('Thinking...', 'think');
            this.scrollToBottom();
            this.$refs.input.style.height = '48px';

            if (this.credits !== null) {
                this.credits = Math.max(0, this.credits - 1);
            }

            try {
                const res = await fetch('{{ route("forms.ai-builder.chat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        message: text,
                        conversation_id: this.conversationId,
                    }),
                });

                if (!res.ok) throw new Error(`Request failed (${res.status})`);

                const { job_id } = await res.json();
                this.pollForEvents(job_id, 0);
            } catch (e) {
                this.error = e.message || 'Something went wrong. Please try again.';
                this.pushTimeline(this.error, 'error');
                this.loading = false;
                this.scrollToBottom();
                this.$nextTick(() => this.$refs.input.focus());
            }
        },

        pollForEvents(jobId, offset) {
            fetch(`/forms/ai-builder/chat/${jobId}/events?offset=${offset}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            })
            .then(res => res.json())
            .then(data => {
                for (const event of data.events) {
                    if (event.type === 'status') {
                        this.thinkingStatus = this.humanizeStatus(event.message);
                        this.pushTimeline(event.message);
                        this.scrollToBottom();
                    } else if (event.type === 'response') {
                        this.conversationId = event.conversation_id;
                        this.messages.push({ role: 'assistant', content: event.message });
                        this.pushTimeline('Response ready', 'done');
                        this.thinkingStatus = 'Ready';
                    } else if (event.type === 'error') {
                        this.error = event.message;
                        this.pushTimeline(event.message, 'error');
                    }
                }

                if (data.done) {
                    this.loading = false;
                    this.scrollToBottom();
                    this.$nextTick(() => this.$refs.input.focus());
                } else {
                    setTimeout(() => this.pollForEvents(jobId, data.offset), 2000);
                }
            })
            .catch(() => {
                this.error = 'Lost connection. Please try again.';
                this.pushTimeline(this.error, 'error');
                this.loading = false;
                this.scrollToBottom();
                this.$nextTick(() => this.$refs.input.focus());
            });
        },

        resetChat() {
            this.messages = [];
            this.conversationId = null;
            this.error = null;
            this.input = '';
            this.hasSentFirstPrompt = false;
            this.ghostAccepted = false;
            this.clearTimeline();
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.chatContainer;
                if (container) container.scrollTop = container.scrollHeight;
            });
        },

        autoResize(el) {
            el.style.height = '48px';
            el.style.height = Math.min(el.scrollHeight, 150) + 'px';
        },

        renderMarkdown(text) {
            if (!text) return '';
            let html = text;
            html = html.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            html = html.replace(/```(\w*)\n?([\s\S]*?)```/g, '<pre><code>$2</code></pre>');
            html = html.replace(/`([^`]+)`/g, '<code>$1</code>');
            html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
            html = html.replace(/(?<![a-zA-Z])\*([^*]+)\*(?![a-zA-Z])/g, '<em>$1</em>');
            html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, (_, label, url) => {
                if (/^https?:\/\/|^mailto:/i.test(url)) {
                    return '<a href="' + url + '" target="_blank">' + label + '</a>';
                }
                return label;
            });
            html = html.replace(/(?<!")(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank">$1</a>');
            html = html.replace(/^### (.+)$/gm, '<h3>$1</h3>');
            html = html.replace(/^## (.+)$/gm, '<h2>$1</h2>');
            html = html.replace(/^# (.+)$/gm, '<h1>$1</h1>');
            html = html.replace(/^[-*] (.+)$/gm, '<li>$1</li>');
            html = html.replace(/(<li>.*<\/li>\n?)+/gs, '<ul>$&</ul>');
            html = html.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');
            html = html.replace(/\n\n+/g, '</p><p>');
            html = html.replace(/(?<!<\/(?:h[1-6]|p|ul|ol|li|pre)>)\n(?!<)/g, '<br>');
            return '<p>' + html + '</p>';
        }
    };
}
</script>

</body>
</html>

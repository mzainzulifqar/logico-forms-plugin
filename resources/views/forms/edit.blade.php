<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/logo.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $form->title }} — Builder</title>
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700" rel="stylesheet" />
    <style>
        *{font-family:'DM Sans', system-ui, sans-serif}
        [x-cloak]{display:none!important}
        .pattern-bg{
            background-color:#f8fafc;
            background-image:radial-gradient(rgba(148,163,184,0.18) 1px, transparent 1px);
            background-size:28px 28px;
        }
        ::-webkit-scrollbar{width:8px;height:8px}
        ::-webkit-scrollbar-track{background:transparent}
        ::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:4px}
        ::-webkit-scrollbar-thumb:hover{background:#94a3b8}

        .toggle-switch{position:relative;width:36px;height:20px;flex-shrink:0}
        .toggle-switch input{opacity:0;width:0;height:0}
        .toggle-slider{position:absolute;cursor:pointer;inset:0;background:#D1D5DB;border-radius:9999px;transition:.2s}
        .toggle-slider:before{content:"";position:absolute;height:16px;width:16px;left:2px;bottom:2px;background:#fff;border-radius:50%;transition:.2s}
        .toggle-switch input:checked+.toggle-slider{background:#6366F1}
        .toggle-switch input:checked+.toggle-slider:before{transform:translateX(16px)}
    </style>
</head>
<body class="pattern-bg text-slate-800 antialiased">

<div class="h-screen flex flex-col overflow-hidden" x-data="formBuilder()" x-init="init()" x-cloak @keydown.window="handleKeydown($event)">

    {{-- Top bar --}}
    <header class="bg-white/80 backdrop-blur-sm border-b border-slate-200 px-3 py-3 md:px-6 md:py-4 shrink-0 sticky top-0 z-10">
        <div class="w-full flex items-center justify-between gap-2 md:gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <a href="{{ route('forms.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-slate-600 hover:border-slate-300 hover:shadow-sm transition-all shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                </a>

                <div class="min-w-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <span contenteditable="true"
                              class="text-lg font-bold text-slate-900 truncate outline-none border-b border-transparent hover:border-slate-300 focus:border-indigo-400 transition-colors px-0.5"
                              @blur="saveFormField('title', $el.innerText.trim())"
                              @keydown.enter.prevent="$el.blur()"
                              x-text="formTitle"
                              x-ref="titleInput"></span>
                        <span class="shrink-0 px-3 py-1 text-xs font-semibold rounded-lg ring-1"
                              :class="formStatus === 'published'
                                  ? 'text-emerald-600 bg-emerald-50 ring-emerald-100'
                                  : formStatus === 'closed'
                                      ? 'text-rose-600 bg-rose-50 ring-rose-100'
                                      : 'text-slate-600 bg-slate-100 ring-slate-200'"
                              x-text="formStatus.charAt(0).toUpperCase() + formStatus.slice(1)"></span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                {{-- Save status --}}
                <span class="text-xs flex items-center gap-1.5 transition-opacity" :class="saveStatus === 'idle' ? 'opacity-0' : 'opacity-100'">
                    <template x-if="saveStatus === 'saving'">
                        <span class="text-slate-400 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                            Saving...
                        </span>
                    </template>
                    <template x-if="saveStatus === 'saved'">
                        <span class="text-emerald-600 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Saved
                        </span>
                    </template>
                    <template x-if="saveStatus === 'error'">
                        <span class="text-rose-500 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                            Error
                        </span>
                    </template>
                </span>

                <a href="{{ route('forms.show', $form) }}" class="hidden md:flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:border-slate-300 hover:bg-slate-50 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    Responses
                </a>

                <a href="{{ route('forms.logic-tree', $form) }}" class="hidden md:flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:border-slate-300 hover:bg-slate-50 transition-all shadow-sm">
                    <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                    Logic Tree
                </a>

                <template x-if="formStatus === 'published'">
                    <a href="{{ route('forms.public', $form->slug) }}" target="_blank" class="hidden md:flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-500 to-violet-500 rounded-xl hover:from-indigo-600 hover:to-violet-600 transition-all shadow-lg shadow-indigo-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                        Preview
                    </a>
                </template>

                {{-- Overflow menu --}}
                <div class="relative" x-data="{ menuOpen: false }">
                    <button @click="menuOpen = !menuOpen" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-slate-600 hover:border-slate-300 hover:bg-slate-50 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="4" r="1.5"/><circle cx="10" cy="10" r="1.5"/><circle cx="10" cy="16" r="1.5"/></svg>
                    </button>
                    <div x-show="menuOpen" @click.outside="menuOpen = false" x-transition
                         class="absolute right-0 mt-2 w-52 bg-white border border-slate-200 rounded-xl shadow-xl py-1 z-50">
                        <a href="{{ route('forms.show', $form) }}" class="md:hidden flex items-center gap-2 px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                            Responses
                        </a>
                        @if($form->status === 'published')
                        <a href="{{ route('forms.public', $form->slug) }}" target="_blank" class="md:hidden flex items-center gap-2 px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                            Preview
                        </a>
                        @endif
                        <a href="{{ route('forms.logic-tree', $form) }}" class="flex items-center gap-2 px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                            <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                            Logic Tree
                        </a>
                        <div class="border-t border-slate-100 my-1"></div>
                        <form method="POST" action="{{ route('forms.destroy', $form) }}" onsubmit="return confirm('Delete this form and all its data?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full flex items-center gap-2 px-3 py-2.5 text-sm text-rose-600 hover:bg-rose-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 0 00-7.5 0"/></svg>
                                Delete Form
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Main content --}}
    <div class="flex-1 overflow-hidden min-h-0">
        <div class="px-3 py-3 md:px-6 md:py-5 h-full box-border">
            <div class="h-full flex flex-col md:flex-row gap-3 md:gap-5 min-h-0">

                {{-- Left sidebar --}}
                <aside class="w-full md:w-[300px] shrink-0 min-h-0" :class="selectedIdx !== null ? 'hidden md:block' : ''">
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm h-full flex flex-col overflow-hidden">

                        {{-- Form meta --}}
                        <div class="p-5 space-y-4 border-b border-slate-100">
                            {{-- Description click-to-edit --}}
                            <div>
                                <template x-if="!editingDescription">
                                    <p @click="editingDescription = true; $nextTick(() => $refs.descInput.focus())"
                                       class="text-sm text-slate-500 cursor-pointer hover:text-slate-700 transition-colors min-h-[20px] max-h-[80px] overflow-y-auto leading-relaxed"
                                       x-text="formDescription || 'Add a description...'"></p>
                                </template>
                                <template x-if="editingDescription">
                                    <textarea x-ref="descInput"
                                              x-model="formDescription"
                                              @blur="editingDescription = false; saveFormField('description', formDescription)"
                                              @keydown.escape.prevent="editingDescription = false"
                                              rows="3"
                                              placeholder="Add a description..."
                                              class="w-full px-3 py-2.5 text-sm bg-white border border-indigo-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-300 transition-all resize-none"></textarea>
                                </template>
                            </div>

                            {{-- Status pill toggle --}}
                            <div class="flex items-center bg-slate-100 rounded-xl p-1 gap-1">
                                <template x-for="s in ['draft', 'published', 'closed']" :key="s">
                                    <button @click="saveFormField('status', s); formStatus = s"
                                            class="flex-1 px-2 py-1.5 text-xs font-semibold rounded-lg transition-all text-center"
                                            :class="formStatus === s
                                                ? (s === 'published' ? 'bg-white text-emerald-600 shadow-sm ring-1 ring-emerald-100' : s === 'closed' ? 'bg-white text-rose-600 shadow-sm ring-1 ring-rose-100' : 'bg-white text-slate-700 shadow-sm ring-1 ring-slate-200')
                                                : 'text-slate-600 hover:text-slate-800 hover:bg-white/60'"
                                            x-text="s.charAt(0).toUpperCase() + s.slice(1)">
                                    </button>
                                </template>
                            </div>

                            {{-- Builder tabs --}}
                            <div class="flex items-center bg-slate-100 rounded-xl p-1 gap-1">
                                <button @click="setActiveTab('questions')"
                                        class="flex-1 px-3 py-2 text-sm font-bold rounded-lg transition-all text-center"
                                        :class="activeTab === 'questions' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-500 hover:text-slate-800 hover:bg-white/60'">
                                    Questions
                                </button>
                                @if($canCustomizeDesign)
                                <button @click="setActiveTab('design')"
                                        class="flex-1 px-3 py-2 text-sm font-bold rounded-lg transition-all text-center"
                                        :class="activeTab === 'design' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-500 hover:text-slate-800 hover:bg-white/60'">
                                    Design
                                </button>
                                @else
                                <span class="flex-1 px-3 py-2 text-sm font-bold rounded-lg text-center text-slate-400 flex items-center justify-center gap-1.5 cursor-not-allowed"
                                      title="Custom themes are not available on your current plan">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                    Design
                                </span>
                                @endif
                            </div>
                        </div>

                        {{-- Question list --}}
                        <template x-if="activeTab === 'questions'">
                            <div class="flex-1 overflow-y-auto p-4">
                                <p class="text-xs text-slate-400 px-1 mb-3 font-semibold uppercase tracking-wide" x-text="questions.length + ' question' + (questions.length !== 1 ? 's' : '')"></p>
                                <div class="space-y-1">
                                    <template x-for="(q, idx) in questions" :key="q.id">
                                        <button @click="selectQuestion(idx)" class="w-full text-left px-3 py-3 rounded-xl transition-all group min-w-0"
                                                :class="selectedIdx === idx ? 'bg-indigo-50 ring-1 ring-indigo-200 shadow-sm' : 'hover:bg-slate-50'">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="w-6 h-6 rounded-lg flex items-center justify-center text-[11px] font-bold shrink-0"
                                                      :class="selectedIdx === idx ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600'" x-text="idx + 1"></span>
                                                <span class="overflow-hidden text-ellipsis whitespace-nowrap text-[13px]"
                                                      :class="selectedIdx === idx ? 'text-indigo-700 font-semibold' : 'text-slate-800 font-medium'"
                                                      x-text="q.question_text || '(untitled)'"></span>
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-0.5 ml-7">
                                                <span class="text-[11px] text-slate-400" x-text="typeLabels[q.type] || q.type"></span>
                                                <template x-if="['select','radio','checkbox','picture_choice'].includes(q.type) && q.options.length">
                                                    <span class="text-[11px] text-slate-300" x-text="'· ' + q.options.length + ' choices'"></span>
                                                </template>
                                                <template x-if="q.type === 'opinion_scale'">
                                                    <span class="text-[11px] text-slate-300" x-text="'· ' + ((q.settings && q.settings.rows && q.settings.rows.length) || 0) + ' rows'"></span>
                                                </template>
                                                <template x-if="q.is_required">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 shrink-0 ml-auto"></span>
                                                </template>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>

                        @if($canCustomizeDesign)
                        <template x-if="activeTab === 'design'">
                            <div class="flex-1 overflow-y-auto p-4">
                                <p class="text-xs text-slate-400 px-1 mb-3 font-semibold uppercase tracking-wide">Design</p>
                                <div class="space-y-2">
                                    <button class="w-full text-left px-3 py-3 rounded-xl border transition-all"
                                            :class="designSection === 'theme' ? 'bg-indigo-50 border-indigo-200 shadow-sm' : 'bg-white border-slate-200 hover:bg-slate-50'"
                                            @click="designSection = 'theme'">
                                        <p class="text-sm font-semibold" :class="designSection === 'theme' ? 'text-indigo-700' : 'text-slate-800'">Theme</p>
                                        <p class="text-xs mt-0.5" :class="designSection === 'theme' ? 'text-indigo-600/80' : 'text-slate-500'">Colors, font, radius</p>
                                    </button>
                                    <button class="w-full text-left px-3 py-3 rounded-xl border transition-all"
                                            :class="designSection === 'end' ? 'bg-indigo-50 border-indigo-200 shadow-sm' : 'bg-white border-slate-200 hover:bg-slate-50'"
                                            @click="designSection = 'end'">
                                        <p class="text-sm font-semibold" :class="designSection === 'end' ? 'text-indigo-700' : 'text-slate-800'">End screen</p>
                                        <p class="text-xs mt-0.5" :class="designSection === 'end' ? 'text-indigo-600/80' : 'text-slate-500'">Title, message, image</p>
                                    </button>
                                </div>

                                {{-- Preview question selector --}}
                                <div class="mt-4 pt-4 border-t border-slate-100" x-show="designSection === 'theme'">
                                    <p class="text-xs text-slate-400 px-1 mb-2 font-semibold uppercase tracking-wide">Preview question</p>
                                    <div class="space-y-1">
                                        <template x-for="(q, idx) in questions" :key="q.id">
                                            <button @click="selectedIdx = idx"
                                                    class="w-full text-left px-3 py-2 rounded-lg transition-all text-[12px] truncate"
                                                    :class="selectedIdx === idx ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50'">
                                                <span class="font-bold mr-1" x-text="idx + 1 + '.'"></span>
                                                <span x-text="q.question_text ? q.question_text.substring(0, 28) : '(untitled)'"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                        @endif

                        {{-- Add question (sticky bottom) --}}
                        <template x-if="activeTab === 'questions'">
                            <div class="p-4 border-t border-slate-100">
                                <button @click="addQuestion()" class="w-full flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold text-indigo-600 border border-dashed border-indigo-200 rounded-xl hover:bg-indigo-50 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    Add Question
                                </button>
                            </div>
                        </template>
                    </div>
                </aside>

                {{-- Center: Question Editor / Design Preview --}}
                <main class="flex-1 min-w-0 min-h-0" :class="selectedIdx === null ? 'hidden md:block' : ''">
                    <div class="h-full border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

                        {{-- Questions tab: CRUD editor --}}
                        <template x-if="activeTab === 'questions' && selectedIdx !== null && questions[selectedIdx]">
                            <div class="h-full overflow-y-auto bg-white">
                                <div class="px-4 py-4 md:py-6 md:px-8 space-y-0">

                                    {{-- Mobile back button --}}
                                    <button @click="selectedIdx = null" class="md:hidden flex items-center gap-1.5 text-sm font-medium text-indigo-600 mb-3 -ml-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                                        Questions
                                    </button>

                                    {{-- Row 1: # + Required + Delete --}}
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-7 h-7 rounded-md flex items-center justify-center text-xs font-bold bg-indigo-600 text-white shrink-0"
                                                  x-text="selectedIdx + 1"></span>
                                            <span class="text-sm font-semibold text-slate-700" x-text="questions[selectedIdx].question_text || '(untitled)'"></span>
                                        </div>
                                        <div class="flex items-center gap-3 shrink-0">
                                            <label class="toggle-switch">
                                                <input type="checkbox" x-model="questions[selectedIdx].is_required" @change="markDirty()">
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <span class="text-xs font-semibold" :class="questions[selectedIdx].is_required ? 'text-indigo-600' : 'text-slate-400'"
                                                  x-text="questions[selectedIdx].is_required ? 'Required' : 'Optional'"></span>
                                            <div class="w-px h-5 bg-slate-200"></div>
                                            <button @click="deleteQuestion()" class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-all" title="Delete question">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 0 00-7.5 0"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Row 2: Type pills --}}
                                    <div class="flex flex-wrap gap-2 mb-5 pb-5 border-b border-slate-100">
                                        <template x-for="t in typeOptions" :key="t.value">
                                            <button @click="setQuestionType(t.value)"
                                                    class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-xl transition-all"
                                                    :class="questions[selectedIdx].type === t.value
                                                        ? t.activeClass
                                                        : 'bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700'">
                                                <span x-html="t.icon" class="w-3.5 h-3.5 flex items-center justify-center"></span>
                                                <span x-text="t.label"></span>
                                            </button>
                                        </template>
                                    </div>

                                    {{-- Section: Question Content --}}
                                    <div class="bg-slate-100/80 rounded-xl p-5 mb-5">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Content</p>
                                        <div class="space-y-3">
                                            <div>
                                                <label class="text-xs font-medium text-slate-500 mb-1 block">Question</label>
                                                <input type="text"
                                                       x-model="questions[selectedIdx].question_text"
                                                       @input="markDirty()"
                                                       placeholder="Type your question here..."
                                                       class="w-full px-3.5 py-2.5 text-base font-semibold bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all text-slate-900 placeholder-slate-300">
                                            </div>
                                            <div>
                                                <label class="text-xs font-medium text-slate-500 mb-1 block">Description</label>
                                                <input type="text"
                                                       x-model="questions[selectedIdx].help_text"
                                                       @input="markDirty()"
                                                       placeholder="Add a description (optional)"
                                                       class="w-full px-3.5 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all text-slate-600 placeholder-slate-300">
                                            </div>
                                            <template x-if="['text','email','number'].includes(questions[selectedIdx].type)">
                                                <div>
                                                    <label class="text-xs font-medium text-slate-500 mb-1 block">Placeholder</label>
                                                    <input type="text"
                                                           x-model="questions[selectedIdx].settings.placeholder"
                                                           @input="markDirty()"
                                                           placeholder="Type your answer here..."
                                                           class="w-full px-3.5 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all text-slate-500 placeholder-slate-300">
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- Section: Choices (select/radio/checkbox) --}}
                                    <template x-if="['select','radio','checkbox'].includes(questions[selectedIdx].type)">
                                        <div class="bg-slate-100/80 rounded-xl p-5 mb-5">
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Choices</p>
                                            <div class="grid grid-cols-2 gap-2">
                                                <template x-for="(opt, oi) in questions[selectedIdx].options" :key="oi">
                                                    <div class="flex items-center gap-2 group/choice">
                                                        <span class="w-6 h-6 rounded-md flex items-center justify-center text-[11px] font-bold bg-white text-slate-500 border border-slate-200 shrink-0"
                                                              x-text="String.fromCharCode(65 + oi)"></span>
                                                        <input type="text"
                                                               x-model="opt.label"
                                                               @input="opt.value = slugify(opt.label); markDirty()"
                                                               :placeholder="'Choice ' + (oi + 1)"
                                                               class="flex-1 min-w-0 px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                                                        <button @click="questions[selectedIdx].options.splice(oi, 1); markDirty()"
                                                                class="text-slate-300 hover:text-rose-500 transition-colors shrink-0 opacity-0 group-hover/choice:opacity-100">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                            <button @click="questions[selectedIdx].options.push({ label: '', value: '' }); markDirty()" class="mt-3 text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition-colors flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                Add choice
                                            </button>
                                        </div>
                                    </template>

                                    {{-- Section: Picture Choice --}}
                                    <template x-if="questions[selectedIdx].type === 'picture_choice'">
                                        <div class="bg-slate-100/80 rounded-xl p-5 mb-5">
                                            <div class="flex items-center justify-between mb-3">
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Picture Choices</p>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[10px] font-medium text-slate-400">Allow multiple</span>
                                                    <label class="toggle-switch">
                                                        <input type="checkbox" x-model="questions[selectedIdx].settings.multiple" @change="markDirty()">
                                                        <span class="toggle-slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-3">
                                                <template x-for="(opt, oi) in questions[selectedIdx].options" :key="oi">
                                                    <div class="group/choice bg-white border border-slate-200 rounded-lg p-3">
                                                        <div class="flex items-center gap-2 mb-2">
                                                            <span class="w-6 h-6 rounded-md flex items-center justify-center text-[11px] font-bold bg-slate-50 text-slate-500 border border-slate-200 shrink-0"
                                                                  x-text="String.fromCharCode(65 + oi)"></span>
                                                            <input type="text"
                                                                   x-model="opt.label"
                                                                   @input="opt.value = slugify(opt.label); markDirty()"
                                                                   :placeholder="'Label ' + (oi + 1)"
                                                                   class="flex-1 min-w-0 px-3 py-1.5 text-sm bg-white border border-slate-200 rounded-md focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                                                            <button @click="questions[selectedIdx].options.splice(oi, 1); markDirty()"
                                                                    class="text-slate-300 hover:text-rose-500 transition-colors shrink-0 opacity-0 group-hover/choice:opacity-100">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                                                            </button>
                                                        </div>
                                                        <input type="url"
                                                               x-model="opt.image_url"
                                                               @input="markDirty()"
                                                               placeholder="Image URL (optional)"
                                                               class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-md focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                                                    </div>
                                                </template>
                                            </div>
                                            <button @click="questions[selectedIdx].options.push({ label: '', value: '', image_url: '' }); markDirty()"
                                                    class="mt-3 text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition-colors flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                Add choice
                                            </button>
                                        </div>
                                    </template>

                                    {{-- Section: Rating --}}
                                    <template x-if="questions[selectedIdx].type === 'rating'">
                                        <div class="bg-slate-100/80 rounded-xl p-5 mb-5">
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Max Rating</p>
                                            <div class="flex gap-2 flex-wrap">
                                                <template x-for="n in 10" :key="n">
                                                    <button @click="questions[selectedIdx].settings.max = n; markDirty()"
                                                            class="w-10 h-10 rounded-lg text-sm font-semibold transition-all"
                                                            :class="n <= (questions[selectedIdx].settings?.max || 5)
                                                                ? 'bg-amber-400 text-white shadow-sm'
                                                                : 'bg-white text-slate-400 border border-slate-200 hover:border-slate-300'"
                                                            x-text="n"></button>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Section: Opinion Scale --}}
                                    <template x-if="questions[selectedIdx].type === 'opinion_scale'">
                                        <div class="space-y-5 mb-5">
                                            {{-- Rows & Columns editor --}}
                                            <div class="bg-slate-100/80 rounded-xl p-5">
                                                <div class="grid grid-cols-2 gap-5">
                                                    <div>
                                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Rows (statements)</p>
                                                        <div class="space-y-1.5">
                                                            <template x-for="(row, ri) in (questions[selectedIdx].settings.rows || [])" :key="ri">
                                                                <div class="flex items-center gap-2 group/row">
                                                                    <input type="text"
                                                                           :value="row"
                                                                           @input="questions[selectedIdx].settings.rows[ri] = $event.target.value; markDirty()"
                                                                           placeholder="Row label"
                                                                           class="flex-1 px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                                                                    <button @click="questions[selectedIdx].settings.rows.splice(ri, 1); markDirty()"
                                                                            class="text-slate-300 hover:text-rose-500 transition-colors shrink-0 opacity-0 group-hover/row:opacity-100">
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                                                                    </button>
                                                                </div>
                                                            </template>
                                                        </div>
                                                        <button @click="questions[selectedIdx].settings.rows.push(''); markDirty()"
                                                                class="mt-2 text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition-colors flex items-center gap-1">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                            Add row
                                                        </button>
                                                    </div>
                                                    <div>
                                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Columns (scale)</p>
                                                        <div class="space-y-1.5">
                                                            <template x-for="(col, ci) in (questions[selectedIdx].settings.columns || [])" :key="ci">
                                                                <div class="flex items-center gap-2 group/col">
                                                                    <input type="text"
                                                                           :value="col"
                                                                           @input="questions[selectedIdx].settings.columns[ci] = $event.target.value; markDirty()"
                                                                           placeholder="Column label"
                                                                           class="flex-1 px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                                                                    <button @click="questions[selectedIdx].settings.columns.splice(ci, 1); markDirty()"
                                                                            class="text-slate-300 hover:text-rose-500 transition-colors shrink-0 opacity-0 group-hover/col:opacity-100">
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                                                                    </button>
                                                                </div>
                                                            </template>
                                                        </div>
                                                        <button @click="questions[selectedIdx].settings.columns.push(''); markDirty()"
                                                                class="mt-2 text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition-colors flex items-center gap-1">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                            Add column
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Live preview of the matrix --}}
                                            <div class="bg-white border border-slate-200 rounded-xl p-5">
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Preview</p>
                                                <div class="overflow-x-auto" x-show="(questions[selectedIdx].settings.rows || []).filter(r => r).length && (questions[selectedIdx].settings.columns || []).filter(c => c).length">
                                                    <div class="min-w-[360px]">
                                                        <div class="grid" :style="'grid-template-columns: minmax(120px, 1fr) repeat(' + (questions[selectedIdx].settings.columns || []).filter(c => c).length + ', 80px);'">
                                                            {{-- Header row --}}
                                                            <div></div>
                                                            <template x-for="col in (questions[selectedIdx].settings.columns || []).filter(c => c)" :key="col">
                                                                <div class="text-center text-[11px] font-semibold text-slate-500 py-2 px-1" x-text="col"></div>
                                                            </template>
                                                            {{-- Data rows --}}
                                                            <template x-for="(row, ri) in (questions[selectedIdx].settings.rows || []).filter(r => r)" :key="ri">
                                                                <template x-if="true">
                                                                    <div class="contents">
                                                                        <div class="py-2.5 pr-3 text-sm font-medium text-slate-700" x-text="row"></div>
                                                                        <template x-for="col in (questions[selectedIdx].settings.columns || []).filter(c => c)" :key="col">
                                                                            <div class="py-2.5 flex items-center justify-center">
                                                                                <div class="w-5 h-5 rounded-full border-2 border-slate-300"></div>
                                                                            </div>
                                                                        </template>
                                                                    </div>
                                                                </template>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-slate-400 italic" x-show="!(questions[selectedIdx].settings.rows || []).filter(r => r).length || !(questions[selectedIdx].settings.columns || []).filter(c => c).length">
                                                    Add rows and columns above to see a preview
                                                </p>
                                            </div>
                                        </div>
                                    </template>

                                </div>
                            </div>
                        </template>

                        {{-- Design tab: Typeform-style live preview --}}
                        @if($canCustomizeDesign)
                        <template x-if="activeTab === 'design' && selectedIdx !== null && questions[selectedIdx]">
                            <div class="h-full overflow-y-auto transition-colors duration-300" x-cloak>
                                {{-- Question preview --}}
                                <div x-show="designSection === 'theme'" class="h-full flex items-center justify-center p-8"
                                     :style="'background:' + theme.background_color + '; font-family:' + (theme.font === 'System' ? 'system-ui, sans-serif' : theme.font + ', system-ui, sans-serif')">
                                    <div class="w-full max-w-[620px]">
                                        <div class="flex items-center gap-1.5 mb-3">
                                            <span class="w-6 h-6 rounded flex items-center justify-center text-[11px] font-extrabold"
                                                  :style="'background:' + theme.answer_color + '; color:' + theme.button_text_color"
                                                  x-text="selectedIdx + 1"></span>
                                            <span class="text-sm" :style="'color: color-mix(in srgb, ' + theme.question_color + ' 30%, ' + theme.background_color + ')'">&#8594;</span>
                                        </div>
                                        <div class="flex items-baseline gap-1 mb-1">
                                            <p class="flex-1 min-w-0" style="font-size: 1.625rem; font-weight: 700; line-height: 1.35; letter-spacing: -0.01em;"
                                               :style="'color:' + theme.question_color"
                                               x-text="questions[selectedIdx].question_text || 'Your question...'"></p>
                                            <span x-show="questions[selectedIdx].is_required" class="text-2xl font-medium shrink-0" style="color:#E74C3C">*</span>
                                        </div>
                                        <p x-show="questions[selectedIdx].help_text" class="mb-0"
                                           style="font-size: 0.9375rem; line-height: 1.55;"
                                           :style="'color: color-mix(in srgb, ' + theme.question_color + ' 55%, ' + theme.background_color + ')'"
                                           x-text="questions[selectedIdx].help_text"></p>

                                        <div class="mt-8">
                                            <template x-if="['text','email','number'].includes(questions[selectedIdx].type)">
                                                <div class="pb-3" :style="'border-bottom: 2px solid color-mix(in srgb, ' + theme.question_color + ' 22%, ' + theme.background_color + ')'">
                                                    <span class="text-lg" :style="'color: color-mix(in srgb, ' + theme.question_color + ' 30%, ' + theme.background_color + ')'"
                                                          x-text="questions[selectedIdx].settings?.placeholder || 'Type your answer here...'"></span>
                                                </div>
                                            </template>
                                            <template x-if="['radio','checkbox'].includes(questions[selectedIdx].type)">
                                                <div class="flex flex-col gap-3">
                                                    <template x-for="(opt, oi) in questions[selectedIdx].options.filter(o => o.label)" :key="oi">
                                                        <div class="flex items-center gap-3.5 px-4 py-3.5"
                                                             :style="'border: 1px solid color-mix(in srgb, ' + theme.question_color + ' 22%, ' + theme.background_color + '); border-radius:' + radiusValue()">
                                                            <span class="w-7 h-7 rounded flex items-center justify-center text-xs font-bold shrink-0"
                                                                  :style="'border: 1px solid color-mix(in srgb, ' + theme.question_color + ' 22%, ' + theme.background_color + '); color:' + theme.answer_color + '; border-radius:' + radiusValue()"
                                                                  x-text="String.fromCharCode(65 + oi)"></span>
                                                            <span class="text-base font-medium" :style="'color:' + theme.question_color" x-text="opt.label"></span>
                                                        </div>
                                                    </template>
                                                    <template x-if="!questions[selectedIdx].options.filter(o => o.label).length">
                                                        <p class="text-sm italic" :style="'color: color-mix(in srgb, ' + theme.question_color + ' 35%, ' + theme.background_color + ')'">No choices yet</p>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="questions[selectedIdx].type === 'select'">
                                                <div class="px-4 py-3.5 flex items-center justify-between"
                                                     :style="'border: 1px solid color-mix(in srgb, ' + theme.question_color + ' 22%, ' + theme.background_color + '); border-radius:' + radiusValue() + '; color: color-mix(in srgb, ' + theme.question_color + ' 40%, ' + theme.background_color + ')'">
                                                    <span class="text-base" x-text="questions[selectedIdx].options.find(o => o.label)?.label || 'Select an option...'"></span>
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                                                </div>
                                            </template>
                                            <template x-if="questions[selectedIdx].type === 'rating'">
                                                <div class="flex gap-2 flex-wrap">
                                                    <template x-for="n in (questions[selectedIdx].settings?.max || 5)" :key="n">
                                                        <div class="w-12 h-12 flex items-center justify-center text-lg font-semibold"
                                                             :style="'border: 1px solid color-mix(in srgb, ' + theme.question_color + ' 22%, ' + theme.background_color + '); border-radius:' + radiusValue() + '; color: color-mix(in srgb, ' + theme.question_color + ' 40%, ' + theme.background_color + ')'"
                                                             x-text="n"></div>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="questions[selectedIdx].type === 'picture_choice'">
                                                <div class="grid grid-cols-2 gap-3">
                                                    <template x-for="(opt, oi) in questions[selectedIdx].options.filter(o => o.label)" :key="oi">
                                                        <div class="overflow-hidden" :style="'border: 1px solid color-mix(in srgb, ' + theme.question_color + ' 22%, ' + theme.background_color + '); border-radius:' + radiusValue()">
                                                            <div class="aspect-[4/3] bg-cover bg-center"
                                                                 :style="opt.image_url ? 'background-image: url(' + opt.image_url + ')' : 'background: color-mix(in srgb, ' + theme.question_color + ' 8%, ' + theme.background_color + ')'">
                                                                <template x-if="!opt.image_url">
                                                                    <div class="h-full flex items-center justify-center">
                                                                        <svg class="w-8 h-8" :style="'color: color-mix(in srgb, ' + theme.question_color + ' 20%, ' + theme.background_color + ')'" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.41a2.25 2.25 0 013.182 0l2.909 2.91m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                            <div class="flex items-center gap-2 px-3 py-2.5" :style="'border-top: 1px solid color-mix(in srgb, ' + theme.question_color + ' 12%, ' + theme.background_color + ')'">
                                                                <span class="w-5 h-5 rounded flex items-center justify-center text-[10px] font-bold shrink-0"
                                                                      :style="'border: 1px solid color-mix(in srgb, ' + theme.question_color + ' 22%, ' + theme.background_color + '); color:' + theme.answer_color"
                                                                      x-text="String.fromCharCode(65 + oi)"></span>
                                                                <span class="text-sm font-medium" :style="'color:' + theme.question_color" x-text="opt.label"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="questions[selectedIdx].type === 'opinion_scale'">
                                                <div class="overflow-x-auto">
                                                    <div class="min-w-[400px]">
                                                        <div class="grid" :style="'grid-template-columns: 160px repeat(' + ((questions[selectedIdx].settings.columns || []).length || 1) + ', 1fr);'">
                                                            <div></div>
                                                            <template x-for="col in (questions[selectedIdx].settings.columns || [])" :key="col">
                                                                <div class="text-center text-xs font-medium py-2 px-1"
                                                                     :style="'color: color-mix(in srgb, ' + theme.question_color + ' 55%, ' + theme.background_color + ')'"
                                                                     x-text="col"></div>
                                                            </template>
                                                            <template x-for="row in (questions[selectedIdx].settings.rows || [])" :key="row">
                                                                <template x-if="true">
                                                                    <div class="contents">
                                                                        <div class="py-2.5 pr-3 text-sm font-medium" :style="'color:' + theme.question_color" x-text="row"></div>
                                                                        <template x-for="col in (questions[selectedIdx].settings.columns || [])" :key="col">
                                                                            <div class="py-2.5 flex items-center justify-center">
                                                                                <div class="w-5 h-5 rounded-full" :style="'border: 2px solid color-mix(in srgb, ' + theme.question_color + ' 25%, ' + theme.background_color + ')'"></div>
                                                                            </div>
                                                                        </template>
                                                                    </div>
                                                                </template>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>

                                        <div class="mt-8 flex items-center gap-3">
                                            <div class="px-6 py-2.5 font-semibold text-sm flex items-center gap-2"
                                                 :style="'background:' + theme.button_color + '; color:' + theme.button_text_color + '; border-radius:' + radiusValue()">
                                                OK
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                            </div>
                                            <span class="text-xs font-medium" :style="'color: color-mix(in srgb, ' + theme.question_color + ' 40%, ' + theme.background_color + ')'">
                                                press <span class="px-1.5 py-0.5 text-[11px] font-semibold"
                                                            :style="'border: 1px solid color-mix(in srgb, ' + theme.question_color + ' 20%, ' + theme.background_color + '); border-radius: 4px; color: color-mix(in srgb, ' + theme.question_color + ' 40%, ' + theme.background_color + ')'">Enter &#8629;</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- End screen preview --}}
                                <div x-show="designSection === 'end'" class="h-full"
                                     :style="'background:' + theme.background_color + '; font-family:' + (theme.font === 'System' ? 'system-ui, sans-serif' : theme.font + ', system-ui, sans-serif')">
                                        <template x-if="endScreen.image_url">
                                            <div class="flex h-full">
                                                <div class="flex-[5] flex flex-col justify-center px-12 py-8">
                                                    <h4 class="text-2xl font-extrabold tracking-tight" :style="'color:' + theme.question_color"
                                                        x-text="endScreen.title || 'Thank you!'"></h4>
                                                    <p class="mt-3 text-sm leading-relaxed max-w-xs" :style="'color: color-mix(in srgb, ' + theme.question_color + ' 62%, ' + theme.background_color + ')'"
                                                       x-text="endScreen.message || 'Your response has been recorded.'"></p>
                                                    <div class="mt-5 px-5 py-2 font-semibold text-sm inline-flex items-center gap-2 w-fit"
                                                         :style="'background:' + theme.button_color + '; color:' + theme.button_text_color + '; border-radius:' + radiusValue()">
                                                        Done
                                                    </div>
                                                </div>
                                                <div class="flex-[5] min-w-0 overflow-hidden">
                                                    <img class="w-full h-full object-cover" :src="endScreen.image_url" alt=""
                                                         x-on:error="$el.src = 'data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 400 300%27%3e%3crect fill=%27%23fef2f2%27 width=%27400%27 height=%27300%27/%3e%3cpath d=%27M90 80l220 160M310 80L90 240%27 stroke=%27%23f87171%27 stroke-width=%2712%27/%3e%3c/svg%3e'">
                                                </div>
                                            </div>
                                        </template>
                                        <template x-if="!endScreen.image_url">
                                            <div class="h-full flex items-center justify-center">
                                                <div class="text-center">
                                                    <div class="w-16 h-16 rounded-full mx-auto flex items-center justify-center"
                                                         :style="'background: color-mix(in srgb, ' + theme.answer_color + ' 18%, transparent); color:' + theme.answer_color">
                                                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                    </div>
                                                    <h4 class="mt-5 text-3xl font-extrabold tracking-tight" :style="'color:' + theme.question_color"
                                                        x-text="endScreen.title || 'Thank you!'"></h4>
                                                    <p class="mt-3 text-base leading-relaxed" :style="'color: color-mix(in srgb, ' + theme.question_color + ' 62%, ' + theme.background_color + ')'"
                                                       x-text="endScreen.message || 'Your response has been recorded.'"></p>
                                                    <div class="mt-6 px-6 py-2.5 font-semibold text-sm inline-flex items-center gap-2"
                                                         :style="'background:' + theme.button_color + '; color:' + theme.button_text_color + '; border-radius:' + radiusValue()">
                                                        Done
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                </div>
                            </div>
                        </template>
                        @endif

                        {{-- Empty state --}}
                        <template x-if="activeTab === 'questions' && selectedIdx === null">
                            <div class="h-full min-h-[520px] flex items-center justify-center p-8 bg-white">
                                <div class="text-center max-w-sm border border-dashed border-slate-200 rounded-2xl bg-slate-50 p-10">
                                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227 7.917-3.286-.672zM12 2.25V4.5m5.834.166l-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243l-1.59-1.59"/></svg>
                                    <p class="text-base font-bold text-slate-800">Select a question</p>
                                    <p class="text-sm text-slate-600 mt-1">Choose from the list or <button @click="addQuestion()" class="text-indigo-600 hover:text-indigo-700 font-semibold">add a new one</button>.</p>
                                </div>
                            </div>
                        </template>
                    </div>
                </main>

                {{-- Right sidebar: Logic (Questions) / Theme controls (Design) --}}
                <aside class="hidden md:block w-[320px] shrink-0 min-h-0 transition-all duration-200"
                       x-show="(activeTab === 'questions' && selectedIdx !== null && questions[selectedIdx]){{ $canCustomizeDesign ? " || activeTab === 'design'" : '' }}"
                       x-transition:enter="transition ease-out duration-200"
                       x-transition:enter-start="opacity-0 translate-x-4"
                       x-transition:enter-end="opacity-100 translate-x-0"
                       x-transition:leave="transition ease-in duration-150"
                       x-transition:leave-start="opacity-100 translate-x-0"
                       x-transition:leave-end="opacity-0 translate-x-4">
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm h-full flex flex-col overflow-hidden">

                        {{-- Questions tab: Logic rules only --}}
                        <div x-show="activeTab === 'questions' && selectedIdx !== null && questions[selectedIdx]" class="flex-1 overflow-y-auto p-5 space-y-4">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-violet-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                                <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wide">Q<span x-text="selectedIdx + 1"></span> Logic</h3>
                            </div>

                            <template x-if="questions[selectedIdx].logic_rules.length === 0">
                                <div class="text-center py-6">
                                    <svg class="w-10 h-10 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                                    <p class="text-xs text-slate-500 mb-1">No logic rules</p>
                                    <p class="text-[11px] text-slate-400 mb-3">Next question will be shown by default.</p>
                                    <button @click="questions[selectedIdx].logic_rules.push({ operator: 'equals', value: '', next_question_id: '' })"
                                            class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition-colors flex items-center gap-1 mx-auto">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                        Add rule
                                    </button>
                                </div>
                            </template>

                            <div class="space-y-2">
                                <template x-for="(rule, ri) in questions[selectedIdx].logic_rules" :key="ri">
                                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 space-y-2 group/logic">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[11px] text-slate-500 font-semibold shrink-0 w-6">If</span>
                                            <select x-model="rule.operator" @change="markDirty()"
                                                    class="flex-1 px-2 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 focus:outline-none focus:border-indigo-300 focus:ring-1 focus:ring-indigo-100 appearance-none cursor-pointer">
                                                <optgroup label="Text">
                                                    <option value="equals">equals</option>
                                                    <option value="not_equals">not equals</option>
                                                    <option value="contains">contains</option>
                                                    <option value="not_contains">not contains</option>
                                                </optgroup>
                                                <optgroup label="Numeric">
                                                    <option value="greater_than">greater than</option>
                                                    <option value="less_than">less than</option>
                                                </optgroup>
                                                <optgroup label="Other">
                                                    <option value="always">always</option>
                                                </optgroup>
                                            </select>
                                        </div>
                                        <template x-if="rule.operator !== 'always'">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[11px] text-slate-500 font-semibold shrink-0 w-6">Val</span>
                                                <input type="text" x-model="rule.value" @input="markDirty()" placeholder="value"
                                                       class="flex-1 px-2 py-1.5 bg-white border border-slate-200 rounded-lg text-xs focus:outline-none focus:border-indigo-300 focus:ring-1 focus:ring-indigo-100">
                                            </div>
                                        </template>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[11px] text-slate-500 font-semibold shrink-0 w-6">Go</span>
                                            <select x-model="rule.next_question_id" @change="markDirty()"
                                                    x-init="$nextTick(() => { $el.value = rule.next_question_id || '' })"
                                                    class="flex-1 px-2 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 focus:outline-none focus:border-indigo-300 focus:ring-1 focus:ring-indigo-100 appearance-none cursor-pointer">
                                                <option value="">Select...</option>
                                                <template x-for="q in questions" :key="q.id">
                                                    <option :value="String(q.id)" x-text="q.question_text ? q.question_text.substring(0, 30) : '(untitled)'" :disabled="q.id === questions[selectedIdx].id"></option>
                                                </template>
                                            </select>
                                            <button @click="questions[selectedIdx].logic_rules.splice(ri, 1); markDirty()"
                                                    class="text-slate-300 hover:text-rose-500 transition-colors shrink-0 opacity-0 group-hover/logic:opacity-100">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <template x-if="questions[selectedIdx].logic_rules.length > 0">
                                <button @click="questions[selectedIdx].logic_rules.push({ operator: 'equals', value: '', next_question_id: '' })" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition-colors flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    Add rule
                                </button>
                            </template>
                        </div>

                        {{-- Design tab: Theme + End screen controls --}}
                        @if($canCustomizeDesign)
                        <div x-show="activeTab === 'design'" class="flex-1 overflow-y-auto p-5 space-y-5">

                            {{-- Theme section --}}
                            <div x-show="designSection === 'theme'" class="space-y-4">
                                <h3 class="text-sm font-bold text-slate-900">Theme</h3>

                                {{-- Presets --}}
                                <div class="space-y-1.5">
                                    <template x-for="p in themePresets" :key="p.name">
                                        <button @click="applyThemePreset(p)"
                                                class="w-full text-left rounded-xl border border-slate-200 hover:border-slate-300 hover:shadow-sm transition-all p-2.5 bg-white flex items-center gap-3">
                                            <div class="flex items-center gap-1 shrink-0">
                                                <span class="w-4 h-4 rounded-full border border-slate-200" :style="'background:' + p.background_color"></span>
                                                <span class="w-4 h-4 rounded-full border border-slate-200" :style="'background:' + p.question_color"></span>
                                                <span class="w-4 h-4 rounded-full border border-slate-200" :style="'background:' + p.answer_color"></span>
                                                <span class="w-4 h-4 rounded-full border border-slate-200" :style="'background:' + p.button_color"></span>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold text-slate-800" x-text="p.name"></p>
                                                <p class="text-[11px] text-slate-400" x-text="p.note"></p>
                                            </div>
                                        </button>
                                    </template>
                                </div>

                                {{-- Color pickers --}}
                                <div class="space-y-3">
                                    <div class="space-y-1">
                                        <label class="text-xs font-semibold text-slate-500">Background</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" x-model="theme.background_color" @input="markDirty('form')" class="w-8 h-8 rounded-lg border border-slate-200 bg-white shrink-0 cursor-pointer">
                                            <input type="text" x-model="theme.background_color" @input="markDirty('form')" class="flex-1 px-2.5 py-2 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-300 focus:ring-1 focus:ring-indigo-100 transition-all">
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-semibold text-slate-500">Question text</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" x-model="theme.question_color" @input="markDirty('form')" class="w-8 h-8 rounded-lg border border-slate-200 bg-white shrink-0 cursor-pointer">
                                            <input type="text" x-model="theme.question_color" @input="markDirty('form')" class="flex-1 px-2.5 py-2 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-300 focus:ring-1 focus:ring-indigo-100 transition-all">
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-semibold text-slate-500">Answer accent</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" x-model="theme.answer_color" @input="markDirty('form')" class="w-8 h-8 rounded-lg border border-slate-200 bg-white shrink-0 cursor-pointer">
                                            <input type="text" x-model="theme.answer_color" @input="markDirty('form')" class="flex-1 px-2.5 py-2 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-300 focus:ring-1 focus:ring-indigo-100 transition-all">
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-semibold text-slate-500">Button</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" x-model="theme.button_color" @input="markDirty('form')" class="w-8 h-8 rounded-lg border border-slate-200 bg-white shrink-0 cursor-pointer">
                                            <input type="text" x-model="theme.button_color" @input="markDirty('form')" class="flex-1 px-2.5 py-2 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-300 focus:ring-1 focus:ring-indigo-100 transition-all">
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-semibold text-slate-500">Button text</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" x-model="theme.button_text_color" @input="markDirty('form')" class="w-8 h-8 rounded-lg border border-slate-200 bg-white shrink-0 cursor-pointer">
                                            <input type="text" x-model="theme.button_text_color" @input="markDirty('form')" class="flex-1 px-2.5 py-2 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-300 focus:ring-1 focus:ring-indigo-100 transition-all">
                                        </div>
                                    </div>
                                </div>

                                {{-- Font --}}
                                <div class="space-y-1">
                                    <label class="text-xs font-semibold text-slate-500">Font</label>
                                    <select x-model="theme.font" @change="markDirty('form')"
                                            class="w-full px-2.5 py-2 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-300 focus:ring-1 focus:ring-indigo-100 transition-all">
                                        <option value="Inter">Inter</option>
                                        <option value="System">System</option>
                                        <option value="Georgia">Georgia</option>
                                        <option value="Times New Roman">Times New Roman</option>
                                        <option value="Arial">Arial</option>
                                        <option value="Helvetica">Helvetica</option>
                                    </select>
                                </div>

                                {{-- Border radius --}}
                                <div class="space-y-1">
                                    <label class="text-xs font-semibold text-slate-500">Border radius</label>
                                    <div class="flex items-center bg-slate-100 rounded-lg p-0.5">
                                        <template x-for="r in ['none','small','medium','large']" :key="r">
                                            <button @click="theme.border_radius = r; markDirty('form')"
                                                    class="flex-1 px-2 py-1.5 text-[11px] font-semibold rounded-md transition-all text-center"
                                                    :class="theme.border_radius === r ? 'bg-white text-slate-700 shadow-sm ring-1 ring-slate-200' : 'text-slate-400 hover:text-slate-600'"
                                                    x-text="r.charAt(0).toUpperCase() + r.slice(1)"></button>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- End screen section --}}
                            <div x-show="designSection === 'end'" class="space-y-4">
                                <h3 class="text-sm font-bold text-slate-900">End Screen</h3>
                                <p class="text-xs text-slate-500">Shown when the respondent completes the form.</p>

                                <div class="space-y-3">
                                    <div class="space-y-1">
                                        <label class="text-xs font-semibold text-slate-500">Title</label>
                                        <input type="text" x-model="endScreen.title" @input="markDirty('form')"
                                               placeholder="Thank you!"
                                               class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100 transition-all">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-semibold text-slate-500">Message</label>
                                        <textarea x-model="endScreen.message" @input="markDirty('form')" rows="4"
                                                  placeholder="Your response has been recorded."
                                                  class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100 transition-all resize-none"></textarea>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-semibold text-slate-500">Image URL</label>
                                        <input type="url" x-model="endScreen.image_url" @input="markDirty('form')"
                                               placeholder="https://..."
                                               class="w-full px-3 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100 transition-all">
                                        <p class="text-[11px] text-slate-400">When set, the end screen uses a split layout.</p>
                                    </div>
                                </div>
                            </div>

                        </div>
                        @endif
                    </div>
                </aside>

            </div>
        </div>
    </div>

<script>
function formBuilder() {
    return {
        init() {
            if (this.selectedIdx === null && Array.isArray(this.questions) && this.questions.length > 0) {
                this.selectedIdx = 0;
                this.ensureQuestionDefaults(this.questions[0]);
            }
        },
        formId: {{ $form->id }},
        formBaseUrl: @json(rtrim(route('forms.show', $form), '/')),
        formTitle: @json($form->title),
        formDescription: @json($form->description),
        formStatus: @json($form->status),
        activeTab: 'questions',
        designSection: 'theme',
        theme: @json($theme),
        endScreen: {
            title: @json($form->end_screen_title ?? ''),
            message: @json($form->end_screen_message ?? ''),
            image_url: @json($form->end_screen_image_url ?? ''),
        },
        themePresets: [
            { name: 'Classic Blue', note: 'Clean and trustworthy', background_color: '#FFFFFF', question_color: '#191919', answer_color: '#0445AF', button_color: '#0445AF', button_text_color: '#FFFFFF', font: 'Inter', border_radius: 'medium' },
            { name: 'Midnight', note: 'High contrast', background_color: '#0B1220', question_color: '#F8FAFC', answer_color: '#60A5FA', button_color: '#3B82F6', button_text_color: '#FFFFFF', font: 'Inter', border_radius: 'medium' },
            { name: 'Warm Sand', note: 'Soft + friendly', background_color: '#FFFBF5', question_color: '#1F2937', answer_color: '#EA580C', button_color: '#EA580C', button_text_color: '#FFFFFF', font: 'Inter', border_radius: 'large' },
            { name: 'Forest', note: 'Natural and calm', background_color: '#F0F5F0', question_color: '#1A2E1A', answer_color: '#16A34A', button_color: '#15803D', button_text_color: '#FFFFFF', font: 'Inter', border_radius: 'medium' },
            { name: 'Lavender', note: 'Gentle and modern', background_color: '#F5F3FF', question_color: '#1E1B4B', answer_color: '#7C3AED', button_color: '#6D28D9', button_text_color: '#FFFFFF', font: 'Inter', border_radius: 'large' },
            { name: 'Rose', note: 'Warm and inviting', background_color: '#FFF1F2', question_color: '#1C1917', answer_color: '#E11D48', button_color: '#BE123C', button_text_color: '#FFFFFF', font: 'Georgia', border_radius: 'medium' },
            { name: 'Ocean', note: 'Deep and professional', background_color: '#0C1929', question_color: '#E2E8F0', answer_color: '#06B6D4', button_color: '#0891B2', button_text_color: '#FFFFFF', font: 'Inter', border_radius: 'small' },
            { name: 'Sunrise', note: 'Energetic and bold', background_color: '#FFFBEB', question_color: '#1C1917', answer_color: '#D97706', button_color: '#F59E0B', button_text_color: '#1C1917', font: 'Inter', border_radius: 'large' },
            { name: 'Slate', note: 'Minimal and neutral', background_color: '#F8FAFC', question_color: '#0F172A', answer_color: '#475569', button_color: '#334155', button_text_color: '#FFFFFF', font: 'Inter', border_radius: 'small' },
            { name: 'Coral Reef', note: 'Playful and bright', background_color: '#FFFFFF', question_color: '#18181B', answer_color: '#F97316', button_color: '#FB923C', button_text_color: '#FFFFFF', font: 'Inter', border_radius: 'large' },
            { name: 'Charcoal', note: 'Sleek dark mode', background_color: '#18181B', question_color: '#FAFAFA', answer_color: '#A78BFA', button_color: '#8B5CF6', button_text_color: '#FFFFFF', font: 'Inter', border_radius: 'medium' },
            { name: 'Mint', note: 'Fresh and clean', background_color: '#F0FDFA', question_color: '#134E4A', answer_color: '#0D9488', button_color: '#14B8A6', button_text_color: '#FFFFFF', font: 'Inter', border_radius: 'medium' },
        ],
        questions: @json($questionsJson),
        selectedIdx: null,
        editingDescription: false,

        // Auto-save
        saveTimer: null,
        pendingSaveKind: 'question', // question | form
        saveStatus: 'idle', // idle | saving | saved | error

        typeLabels: {
            text: 'Text',
            email: 'Email',
            number: 'Number',
            select: 'Dropdown',
            radio: 'Single choice',
            checkbox: 'Multiple choice',
            rating: 'Rating',
            picture_choice: 'Picture choice',
            opinion_scale: 'Opinion scale',
        },

        typeOptions: [
            { value: 'text', label: 'Text', activeClass: 'bg-indigo-600 text-white shadow-sm', icon: '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/></svg>' },
            { value: 'email', label: 'Email', activeClass: 'bg-indigo-600 text-white shadow-sm', icon: '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>' },
            { value: 'number', label: 'Number', activeClass: 'bg-indigo-600 text-white shadow-sm', icon: '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5l-3.9 19.5m-2.1-19.5l-3.9 19.5"/></svg>' },
            { value: 'select', label: 'Dropdown', activeClass: 'bg-indigo-600 text-white shadow-sm', icon: '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/></svg>' },
            { value: 'radio', label: 'Single Choice', activeClass: 'bg-indigo-600 text-white shadow-sm', icon: '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4" fill="currentColor"/></svg>' },
            { value: 'checkbox', label: 'Multi Choice', activeClass: 'bg-indigo-600 text-white shadow-sm', icon: '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
            { value: 'rating', label: 'Rating', activeClass: 'bg-indigo-600 text-white shadow-sm', icon: '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>' },
            { value: 'picture_choice', label: 'Picture Choice', activeClass: 'bg-indigo-600 text-white shadow-sm', icon: '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6.75A2.25 2.25 0 015.25 4.5h13.5A2.25 2.25 0 0121 6.75v10.5A2.25 2.25 0 0118.75 19.5H5.25A2.25 2.25 0 013 17.25V6.75z"/><path d="M7.5 14.25l2.25-2.25 3 3 3.75-3.75L19.5 15"/><path d="M8.25 9.75h.008v.008H8.25V9.75z"/></svg>' },
            { value: 'opinion_scale', label: 'Opinion Scale', activeClass: 'bg-indigo-600 text-white shadow-sm', icon: '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4.5 19.5h15M6 17V7.5m4 9.5V4.5m4 12.5V9m4 8V6"/></svg>' },
        ],

        radiusValue() {
            const map = { none: '0px', small: '4px', medium: '8px', large: '16px' };
            return map[this.theme.border_radius] || '8px';
        },

        slugify(text) {
            return text.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
        },

        markDirty(kind = 'question') {
            clearTimeout(this.saveTimer);
            this.pendingSaveKind = kind;
            this.saveTimer = setTimeout(() => {
                if (this.pendingSaveKind === 'form') return this.autoSaveForm();
                return this.autoSaveQuestion();
            }, 900);
        },

        ensureQuestionDefaults(q) {
            if (!q.settings || Array.isArray(q.settings)) q.settings = {};
            q.options = q.options || [];
            q.logic_rules = q.logic_rules || [];

            if (q.type === 'rating') {
                q.settings.max = q.settings.max || 5;
            }
            if (q.type === 'picture_choice') {
                q.settings.multiple = !!q.settings.multiple;
                q.options = q.options.map(o => ({ label: o.label ?? '', value: o.value ?? '', image_url: o.image_url ?? '' }));
                if (q.options.length === 0) q.options = [{ label: '', value: '', image_url: '' }, { label: '', value: '', image_url: '' }];
            }
            if (['select', 'radio', 'checkbox'].includes(q.type)) {
                q.options = q.options.map(o => ({ label: o.label ?? '', value: o.value ?? '' }));
                if (q.options.length === 0) q.options = [{ label: '', value: '' }, { label: '', value: '' }];
            }
            if (q.type === 'opinion_scale') {
                if (!Array.isArray(q.settings.rows)) q.settings.rows = ['Service quality', 'Speed'];
                if (!Array.isArray(q.settings.columns)) q.settings.columns = ['Strongly disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly agree'];
            }
        },

        setQuestionType(newType) {
            if (this.selectedIdx === null) return;
            const q = this.questions[this.selectedIdx];
            q.type = newType;
            this.ensureQuestionDefaults(q);
            this.markDirty('question');
        },

        async autoSaveQuestion() {
            if (this.selectedIdx === null) return;
            this.saveStatus = 'saving';
            const q = this.questions[this.selectedIdx];
            this.ensureQuestionDefaults(q);
            try {
                const isOptionType = ['select', 'radio', 'checkbox', 'picture_choice'].includes(q.type);
                const options = isOptionType
                    ? q.options
                        .filter(o => o.label)
                        .map(o => ({
                            label: o.label,
                            value: o.value || this.slugify(o.label),
                            image_url: q.type === 'picture_choice' ? (o.image_url || null) : null,
                        }))
                    : [];

                const body = {
                    type: q.type,
                    question_text: q.question_text,
                    help_text: q.help_text || null,
                    is_required: q.is_required,
                    settings: q.settings,
                    options: options,
                    logic: q.logic_rules
                        .filter(r => r.next_question_id && (r.operator === 'always' || r.value))
                        .map(r => ({ operator: r.operator, value: r.value || '', next_question_id: r.next_question_id })),
                };
                const res = await fetch(`${this.formBaseUrl}/questions/${q.id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(body),
                });
                if (!res.ok) throw new Error('Save failed');
                // Don't overwrite local state from server response —
                // it strips incomplete options/rules the user is still editing.
                this.saveStatus = 'saved';
                setTimeout(() => { if (this.saveStatus === 'saved') this.saveStatus = 'idle'; }, 2000);
            } catch (e) {
                console.error(e);
                this.saveStatus = 'error';
                setTimeout(() => { if (this.saveStatus === 'error') this.saveStatus = 'idle'; }, 3000);
            }
        },

        applyThemePreset(preset) {
            this.theme.background_color = preset.background_color;
            this.theme.question_color = preset.question_color;
            this.theme.answer_color = preset.answer_color;
            this.theme.button_color = preset.button_color;
            this.theme.button_text_color = preset.button_text_color;
            this.theme.font = preset.font;
            this.theme.border_radius = preset.border_radius;
            this.markDirty('form');
        },

        async autoSaveForm() {
            this.saveStatus = 'saving';
            try {
                const body = {
                    theme: this.theme,
                    end_screen_title: this.endScreen.title || null,
                    end_screen_message: this.endScreen.message || null,
                    end_screen_image_url: this.endScreen.image_url || null,
                };
                const res = await fetch(`${this.formBaseUrl}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(body),
                });
                if (!res.ok) throw new Error('Save failed');
                this.saveStatus = 'saved';
                setTimeout(() => { if (this.saveStatus === 'saved') this.saveStatus = 'idle'; }, 2000);
            } catch (e) {
                console.error(e);
                this.saveStatus = 'error';
                setTimeout(() => { if (this.saveStatus === 'error') this.saveStatus = 'idle'; }, 3000);
            }
        },

        async saveFormField(field, value) {
            try {
                const body = {};
                body[field] = value;
                await fetch(`${this.formBaseUrl}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(body),
                });
                if (field === 'title') this.formTitle = value;
                if (field === 'status') this.formStatus = value;
                if (field === 'description') this.formDescription = value;
            } catch (e) { console.error(e); }
        },

        async setActiveTab(tab) {
            if (this.saveTimer) {
                clearTimeout(this.saveTimer);
                this.saveTimer = null;
                if (this.pendingSaveKind === 'form') await this.autoSaveForm();
                else await this.autoSaveQuestion();
            }
            this.activeTab = tab;
            this.saveStatus = 'idle';
        },

        async selectQuestion(idx) {
            // Flush pending save before switching
            if (this.saveTimer) {
                clearTimeout(this.saveTimer);
                this.saveTimer = null;
                if (this.pendingSaveKind === 'form') await this.autoSaveForm();
                else await this.autoSaveQuestion();
            }
            this.activeTab = 'questions';
            this.selectedIdx = idx;
            this.saveStatus = 'idle';
            this.ensureQuestionDefaults(this.questions[idx]);
        },

        async addQuestion() {
            try {
                const res = await fetch(`${this.formBaseUrl}/questions`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ type: 'text', question_text: 'New question', is_required: false }),
                });
                const json = await res.json();
                const q = json.data;
                this.questions.push({
                    id: q.id, type: q.type, question_text: q.question_text,
                    help_text: q.help_text, is_required: q.is_required,
                    order_index: q.order_index, settings: q.settings ?? {},
                    options: (q.options || []).map(o => ({ label: o.label, value: o.value, image_url: o.image_url ?? '' })),
                    logic_rules: [],
                });
                this.selectedIdx = this.questions.length - 1;
                this.ensureQuestionDefaults(this.questions[this.selectedIdx]);
            } catch (e) { console.error(e); }
        },

        async deleteQuestion() {
            if (this.selectedIdx === null) return;
            if (!confirm('Delete this question?')) return;
            const q = this.questions[this.selectedIdx];
            try {
                await fetch(`${this.formBaseUrl}/questions/${q.id}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                });
                this.questions.splice(this.selectedIdx, 1);
                this.selectedIdx = null;
            } catch (e) { console.error(e); }
        },

        handleKeydown(e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 's') {
                e.preventDefault();
                clearTimeout(this.saveTimer);
                this.saveTimer = null;
                if (this.activeTab === 'design') this.autoSaveForm();
                else this.autoSaveQuestion();
            }
        },
    };
}
</script>

</div>

</body>
</html>

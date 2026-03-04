<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/logo.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $form->title }} — Logic Tree</title>
    @vite(['resources/css/app.css'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700" rel="stylesheet" />
    <style>
        *{font-family:'DM Sans', system-ui, sans-serif}
        .pattern-bg{
            background-color:#f8fafc;
            background-image:radial-gradient(#e2e8f0 1px,transparent 1px);
            background-size:24px 24px;
        }
        ::-webkit-scrollbar{width:8px;height:8px}
        ::-webkit-scrollbar-track{background:#f1f5f9;border-radius:4px}
        ::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:4px}
        ::-webkit-scrollbar-thumb:hover{background:#94a3b8}
    </style>
</head>
<body class="pattern-bg text-slate-800 antialiased">

<div class="min-h-screen flex flex-col">

    {{-- Header --}}
    <header class="bg-white/80 backdrop-blur-sm border-b border-slate-200 px-6 py-4 flex items-center justify-between shrink-0 sticky top-0 z-10">
        <div class="flex items-center gap-4 min-w-0">
            <a href="{{ route('forms.edit', $form) }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-slate-600 hover:border-slate-300 hover:shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-lg font-bold text-slate-900 truncate">{{ $form->title }}</h1>
                <span class="px-3 py-1 text-xs font-semibold text-violet-600 bg-violet-50 rounded-lg ring-1 ring-violet-100">Logic Tree</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="downloadJSON()" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-indigo-600 bg-white border border-indigo-200 rounded-xl hover:bg-indigo-50 hover:border-indigo-300 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12M12 16.5V3"/></svg>
                Download JSON
            </button>
            <button onclick="downloadMermaid()" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-violet-600 bg-white border border-violet-200 rounded-xl hover:bg-violet-50 hover:border-violet-300 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12M12 16.5V3"/></svg>
                Download Mermaid
            </button>
            <button onclick="downloadSVG()" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-indigo-500 to-violet-500 rounded-xl hover:from-indigo-600 hover:to-violet-600 transition-all shadow-lg shadow-indigo-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12M12 16.5V3"/></svg>
                Download SVG
            </button>
        </div>
    </header>

    {{-- Diagram --}}
    <main class="flex-1 p-8 overflow-auto">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-10 min-w-fit">
            <pre class="mermaid">
{{ $mermaid }}
            </pre>
        </div>
        
        {{-- Legend --}}
        <div class="mt-6 flex items-center justify-center gap-8">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded border-2 border-slate-400 bg-white"></div>
                <span class="text-sm text-slate-500">Question</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded border-2 border-indigo-400 bg-indigo-50"></div>
                <span class="text-sm text-slate-500">With Logic</span>
            </div>
            <div class="flex items-center gap-2">
                <svg class="w-6 h-4 text-slate-400" viewBox="0 0 24 16"><path d="M0 8h20M16 4l4 4-4 4" stroke="currentColor" stroke-width="2" fill="none"/></svg>
                <span class="text-sm text-slate-500">Flow Direction</span>
            </div>
        </div>
    </main>

</div>

<script>
    const treeData = @json($tree);
    const mermaidSource = @json($mermaid);

    function downloadJSON() {
        const blob = new Blob([JSON.stringify(treeData, null, 2)], { type: 'application/json' });
        downloadBlob(blob, '{{ Str::slug($form->title) }}-logic-tree.json');
    }

    function downloadMermaid() {
        const blob = new Blob([mermaidSource], { type: 'text/plain' });
        downloadBlob(blob, '{{ Str::slug($form->title) }}-logic-tree.mmd');
    }

    function downloadSVG() {
        const svg = document.querySelector('.mermaid svg');
        if (!svg) { alert('Diagram not rendered yet.'); return; }
        const serializer = new XMLSerializer();
        const svgStr = serializer.serializeToString(svg);
        const blob = new Blob([svgStr], { type: 'image/svg+xml' });
        downloadBlob(blob, '{{ Str::slug($form->title) }}-logic-tree.svg');
    }

    function downloadBlob(blob, filename) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
</script>

<script type="module">
    import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.esm.min.mjs';
    mermaid.initialize({ 
        startOnLoad: true, 
        theme: 'base',
        themeVariables: {
            primaryColor: '#eef2ff',
            primaryTextColor: '#3730a3',
            primaryBorderColor: '#6366f1',
            lineColor: '#94a3b8',
            secondaryColor: '#f8fafc',
            tertiaryColor: '#fff',
            fontFamily: 'DM Sans, system-ui, sans-serif',
            fontSize: '14px'
        },
        flowchart: {
            curve: 'basis',
            padding: 20,
            nodeSpacing: 50,
            rankSpacing: 60,
            htmlLabels: true
        },
        securityLevel: 'loose'
    });
</script>

</body>
</html>

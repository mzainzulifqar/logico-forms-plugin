<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/logo.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | Logicoforms</title>
    <meta name="description" content="{{ $description ? Str::limit($description, 160) : $title }}">
    <link rel="canonical" href="{{ url('/f/' . $slug) }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/f/' . $slug) }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description ? Str::limit($description, 160) : $title }}">
    <meta property="og:image" content="{{ url('/f/' . $slug . '/og-image.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="Logicoforms">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description ? Str::limit($description, 160) : $title }}">
    <meta name="twitter:image" content="{{ url('/f/' . $slug . '/og-image.png') }}">

    <meta name="robots" content="index, follow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @php
        $fontMap = [
            'Inter' => 'Inter:wght@400;500;600;700;800',
            'DM Sans' => 'DM+Sans:wght@400;500;600;700;800',
            'Plus Jakarta Sans' => 'Plus+Jakarta+Sans:wght@400;500;600;700;800',
            'Space Grotesk' => 'Space+Grotesk:wght@400;500;600;700',
            'IBM Plex Sans' => 'IBM+Plex+Sans:wght@400;500;600;700',
            'Source Sans 3' => 'Source+Sans+3:wght@400;500;600;700;800',
            'Nunito' => 'Nunito:wght@400;500;600;700;800',
            'Poppins' => 'Poppins:wght@400;500;600;700;800',
            'Roboto' => 'Roboto:wght@400;500;700;900',
            'Lato' => 'Lato:wght@400;700;900',
            'Space Mono' => 'Space+Mono:wght@400;700',
            'Merriweather' => 'Merriweather:wght@400;700;900',
        ];
        $fontFamily = $theme['font'] ?? 'Inter';
        $fontParam = $fontMap[$fontFamily] ?? 'Inter:wght@400;500;600;700;800';
        $radiusMap = ['none' => '0px', 'small' => '4px', 'medium' => '8px', 'large' => '16px'];
        $radiusValue = $radiusMap[$theme['border_radius'] ?? 'medium'] ?? '8px';
    @endphp
    <link href="https://fonts.googleapis.com/css2?family={{ $fontParam }}&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        [x-cloak] { display: none !important; }

        /* ── Theme ───────────────────────────────────────── */
        :root {
            --tf-bg: {{ $theme['background_color'] }};
            --tf-question: {{ $theme['question_color'] }};
            --tf-answer: {{ $theme['answer_color'] }};
            --tf-button: {{ $theme['button_color'] }};
            --tf-button-text: {{ $theme['button_text_color'] }};

            --tf-font: '{{ $fontFamily }}', -apple-system, BlinkMacSystemFont, sans-serif;
            --tf-radius: {{ $radiusValue }};

            /* Computed tints via color-mix */
            --tf-muted: color-mix(in srgb, var(--tf-question) 55%, var(--tf-bg));
            --tf-faint: color-mix(in srgb, var(--tf-question) 30%, var(--tf-bg));
            --tf-border: color-mix(in srgb, var(--tf-question) 22%, var(--tf-bg));
            --tf-answer-light: color-mix(in srgb, var(--tf-answer) 12%, var(--tf-bg));
            --tf-button-hover: color-mix(in srgb, var(--tf-button) 80%, black);
            --tf-error: #E74C3C;
        }

        html, body { height: 100%; }
        body {
            font-family: var(--tf-font);
            background: var(--tf-bg);
            color: var(--tf-question);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ── Progress bar ────────────────────────────────── */
        .tf-progress {
            position: fixed; top: 0; left: 0; right: 0;
            height: 3px; z-index: 100;
            background: color-mix(in srgb, var(--tf-question) 12%, var(--tf-bg));
        }
        .tf-progress-bar {
            height: 100%;
            background: var(--tf-answer);
            transition: width 0.5s cubic-bezier(.4,0,.2,1);
            border-radius: 0 2px 2px 0;
        }

        /* ── Full-screen layout ──────────────────────────── */
        .tf-screen {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 24px;
        }
        .tf-content {
            width: 100%;
            max-width: 720px;
        }

        /* ── Transition classes (used by Alpine x-transition) */
        .slide-enter { transition: opacity 0.45s cubic-bezier(.16,1,.3,1), transform 0.45s cubic-bezier(.16,1,.3,1); }
        .slide-enter-from { opacity: 0; transform: translateY(40px); }
        .slide-enter-to { opacity: 1; transform: translateY(0); }
        .slide-leave { transition: opacity 0.3s ease, transform 0.3s ease; }
        .slide-leave-from { opacity: 1; transform: translateY(0); }
        .slide-leave-to { opacity: 0; transform: translateY(-30px); }

        /* Simple fade-in for welcome/complete screens */
        .tf-fade-in { animation: tfFadeIn 0.5s cubic-bezier(.16,1,.3,1) both; }
        @keyframes tfFadeIn {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── Question number badge ───────────────────────── */
        .tf-num {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 12px;
        }
        .tf-num-badge {
            width: 24px; height: 24px;
            border-radius: 5px;
            background: var(--tf-answer);
            color: var(--tf-button-text);
            font-size: 0.6875rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .tf-num-arrow {
            font-size: 0.75rem;
            color: var(--tf-faint);
            margin-left: -2px;
        }

        /* ── Question text ───────────────────────────────── */
        .tf-question {
            font-size: 1.625rem;
            font-weight: 700;
            line-height: 1.35;
            color: var(--tf-question);
            letter-spacing: -0.01em;
        }
        .tf-required {
            color: var(--tf-error);
            font-weight: 500;
        }
        .tf-desc {
            font-size: 0.9375rem;
            color: var(--tf-muted);
            margin-top: 8px;
            line-height: 1.55;
        }

        /* ── Input area ──────────────────────────────────── */
        .tf-field { margin-top: 32px; }

        .tf-input {
            width: 100%;
            border: none;
            border-bottom: 2px solid var(--tf-border);
            padding: 12px 0;
            font-size: 1.25rem;
            font-family: inherit;
            font-weight: 400;
            outline: none;
            background: transparent;
            color: var(--tf-question);
            transition: border-color 0.25s ease;
            caret-color: var(--tf-answer);
        }
        .tf-input:focus { border-bottom-color: var(--tf-answer); }
        .tf-input::placeholder { color: var(--tf-faint); font-weight: 400; }
        .tf-input.has-error { border-bottom-color: var(--tf-error); }

        /* Hide native number spinners */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }

        /* ── Textarea (long text) ────────────────────────── */
        .tf-textarea {
            width: 100%;
            border: none;
            border-bottom: 2px solid var(--tf-border);
            padding: 12px 0;
            font-size: 1.25rem;
            font-family: inherit;
            font-weight: 400;
            outline: none;
            background: transparent;
            color: var(--tf-question);
            transition: border-color 0.25s ease;
            resize: none;
            overflow: hidden;
            min-height: 48px;
            caret-color: var(--tf-answer);
        }
        .tf-textarea:focus { border-bottom-color: var(--tf-answer); }
        .tf-textarea::placeholder { color: var(--tf-faint); font-weight: 400; }

        /* ── Select / Dropdown ───────────────────────────── */
        .tf-select {
            width: 100%;
            border: 1px solid var(--tf-border);
            border-radius: var(--tf-radius);
            padding: 14px 44px 14px 16px;
            font-size: 1.0625rem;
            font-family: inherit;
            outline: none;
            background: var(--tf-bg);
            color: var(--tf-question);
            cursor: pointer;
            transition: border-color 0.2s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b6e76' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 14px center;
            background-repeat: no-repeat;
            background-size: 20px;
        }
        .tf-select:focus { border-color: var(--tf-answer); }

        /* ── Choice cards (radio + checkbox) ─────────────── */
        .tf-choices { display: flex; flex-direction: column; gap: 12px; }
        .tf-choice {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            border: 1px solid var(--tf-border);
            border-radius: var(--tf-radius);
            cursor: pointer;
            transition: border-color 0.15s ease, background 0.15s ease;
            user-select: none;
            position: relative;
        }
        .tf-choice:hover {
            border-color: var(--tf-answer);
            background: var(--tf-answer-light);
        }
        .tf-choice.selected {
            border-color: var(--tf-answer);
            background: var(--tf-answer-light);
        }
        .tf-choice-key {
            width: 28px; height: 28px;
            border: 1px solid var(--tf-border);
            border-radius: var(--tf-radius);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.6875rem;
            font-weight: 700;
            color: var(--tf-answer);
            flex-shrink: 0;
            transition: all 0.15s ease;
        }
        .tf-choice:hover .tf-choice-key { border-color: var(--tf-answer); }
        .tf-choice.selected .tf-choice-key {
            background: var(--tf-answer);
            border-color: var(--tf-answer);
            color: var(--tf-button-text);
        }
        .tf-choice-label {
            font-size: 1rem;
            color: var(--tf-question);
            font-weight: 500;
            flex: 1;
        }
        .tf-choice-check {
            margin-left: auto;
            color: var(--tf-answer);
            opacity: 0;
            transition: opacity 0.15s ease;
            flex-shrink: 0;
        }
        .tf-choice.selected .tf-choice-check { opacity: 1; }

        /* ── Rating (stars) ──────────────────────────────── */
        .tf-rating { display: flex; gap: 8px; flex-wrap: wrap; }
        .tf-star {
            width: 52px; height: 52px;
            border: 1px solid var(--tf-border);
            border-radius: var(--tf-radius);
            background: transparent;
            cursor: pointer;
            font-size: 1.5rem;
            transition: all 0.15s ease;
            display: flex; align-items: center; justify-content: center;
            color: var(--tf-faint);
            position: relative;
        }
        .tf-star:hover,
        .tf-star.lit {
            border-color: #f59e0b;
            color: #f59e0b;
            background: #fffbeb;
        }
        .tf-star.active {
            border-color: #f59e0b;
            color: #f59e0b;
            background: #fffbeb;
        }
        .tf-star-num {
            position: absolute;
            bottom: 2px;
            right: 5px;
            font-size: 0.5625rem;
            font-weight: 700;
            color: var(--tf-faint);
            font-family: inherit;
        }
        .tf-star:hover .tf-star-num,
        .tf-star.lit .tf-star-num,
        .tf-star.active .tf-star-num { color: #b45309; }

        /* ── Error ───────────────────────────────────────── */
        .tf-error {
            margin-top: 12px;
            color: var(--tf-error);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }

        /* ── Actions bar ─────────────────────────────────── */
        .tf-actions { margin-top: 32px; display: flex; align-items: center; gap: 16px; }
        .tf-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 24px;
            background: var(--tf-button);
            color: var(--tf-button-text);
            border: none;
            border-radius: var(--tf-radius);
            font-size: 0.9375rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.15s ease, transform 0.1s ease;
        }
        .tf-btn:hover { background: var(--tf-button-hover); }
        .tf-btn:active { transform: scale(0.97); }
        .tf-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        .tf-hint { font-size: 0.75rem; color: var(--tf-faint); font-weight: 500; }
        .tf-hint kbd {
            display: inline-block;
            background: color-mix(in srgb, var(--tf-question) 8%, var(--tf-bg));
            border: 1px solid var(--tf-border);
            border-radius: 3px;
            padding: 1px 6px;
            font-family: inherit;
            font-size: 0.6875rem;
            font-weight: 600;
        }

        /* ── Welcome screen ──────────────────────────────── */
        .tf-welcome { text-align: left; }
        .tf-welcome h1 {
            font-size: 2.25rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            line-height: 1.2;
            margin-bottom: 16px;
            color: var(--tf-question);
        }
        .tf-welcome p {
            font-size: 1.125rem;
            color: var(--tf-muted);
            line-height: 1.6;
            margin-bottom: 40px;
            max-width: 540px;
        }
        .tf-start {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 14px 32px;
            background: var(--tf-button);
            color: var(--tf-button-text);
            border: none; border-radius: var(--tf-radius);
            font-size: 1.0625rem; font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.15s ease, transform 0.1s ease;
        }
        .tf-start:hover { background: var(--tf-button-hover); }
        .tf-start:active { transform: scale(0.97); }

        /* ── Thank you screen ────────────────────────────── */
        .tf-done { text-align: center; }
        .tf-done-icon {
            width: 60px; height: 60px;
            background: #22c55e;
            border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 28px;
        }
        .tf-done-icon svg { width: 30px; height: 30px; color: #fff; }
        .tf-done h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: -0.02em;
            color: var(--tf-question);
        }
        .tf-done p { font-size: 1.0625rem; color: var(--tf-muted); line-height: 1.6; }

        /* ── Picture Choice ──────────────────────────────── */
        .tf-pictures {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }
        @media (max-width: 640px) {
            .tf-pictures { grid-template-columns: repeat(2, 1fr); }
        }
        .tf-picture-card {
            border: 2px solid var(--tf-border);
            border-radius: var(--tf-radius);
            overflow: hidden;
            cursor: pointer;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
            user-select: none;
            background: var(--tf-bg);
        }
        .tf-picture-card:hover {
            border-color: var(--tf-answer);
            box-shadow: 0 2px 12px color-mix(in srgb, var(--tf-answer) 15%, transparent);
        }
        .tf-picture-card.selected {
            border-color: var(--tf-answer);
            box-shadow: 0 2px 12px color-mix(in srgb, var(--tf-answer) 20%, transparent);
        }
        .tf-picture-img {
            width: 100%;
            aspect-ratio: 4/3;
            object-fit: cover;
            display: block;
            background: var(--tf-faint);
        }
        .tf-picture-footer {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
        }
        .tf-picture-footer .tf-choice-key {
            width: 24px; height: 24px;
            font-size: 0.625rem;
        }
        .tf-picture-footer .tf-choice-label {
            font-size: 0.875rem;
        }
        .tf-picture-card.selected .tf-choice-key {
            background: var(--tf-answer);
            border-color: var(--tf-answer);
            color: var(--tf-button-text);
        }

        /* ── Opinion Scale (Likert) ─────────────────────── */
        .tf-likert { width: 100%; }
        .tf-likert-header {
            display: flex;
            padding: 0 0 12px 0;
            border-bottom: 1px solid var(--tf-border);
            margin-bottom: 4px;
        }
        .tf-likert-header-label {
            flex: 1.5;
        }
        .tf-likert-header-col {
            flex: 1;
            text-align: center;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--tf-muted);
        }
        .tf-likert-row {
            display: flex;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid color-mix(in srgb, var(--tf-border) 50%, transparent);
        }
        .tf-likert-row:last-child { border-bottom: none; }
        .tf-likert-row-label {
            flex: 1.5;
            font-size: 0.9375rem;
            font-weight: 500;
            color: var(--tf-question);
            padding-right: 16px;
        }
        .tf-likert-cell {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .tf-likert-radio {
            width: 22px; height: 22px;
            border: 2px solid var(--tf-border);
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            padding: 0;
        }
        .tf-likert-radio:hover {
            border-color: var(--tf-answer);
        }
        .tf-likert-radio.selected {
            border-color: var(--tf-answer);
            background: var(--tf-answer);
        }
        .tf-likert-radio.selected::after {
            content: '';
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--tf-button-text);
        }
        .tf-likert-clear {
            margin-top: 12px;
            font-size: 0.8125rem;
            color: var(--tf-faint);
            cursor: pointer;
            background: none;
            border: none;
            font-family: inherit;
            padding: 4px 0;
        }
        .tf-likert-clear:hover { color: var(--tf-answer); }

        /* ── End Screen with image ──────────────────────── */
        .tf-done-split {
            display: flex;
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
        }
        .tf-done-split-text {
            flex: 5;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 48px 64px;
            text-align: left;
        }
        .tf-done-split-text h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 12px;
            letter-spacing: -0.02em;
            color: var(--tf-question);
        }
        .tf-done-split-text p { font-size: 1.125rem; color: var(--tf-muted); line-height: 1.7; max-width: 480px; }
        .tf-done-split-img {
            flex: 5;
            min-width: 0;
            overflow: hidden;
        }
        .tf-done-split-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        @media (max-width: 640px) {
            .tf-done-split { flex-direction: column-reverse; }
            .tf-done-split-text { flex: 1; padding: 32px 24px; }
            .tf-done-split-img { flex: none; height: 40vh; width: 100%; }
        }

        /* ── Loading spinner ─────────────────────────────── */
        .tf-spinner {
            width: 32px; height: 32px;
            border: 3px solid var(--tf-border);
            border-top-color: var(--tf-answer);
            border-radius: 50%;
            animation: tfSpin 0.65s linear infinite;
            margin: 0 auto 16px;
        }
        @keyframes tfSpin { to { transform: rotate(360deg); } }

        /* ── Navigation arrows (bottom-right) ────────────── */
        .tf-nav {
            position: fixed;
            bottom: 24px;
            right: 24px;
            display: flex;
            flex-direction: column;
            gap: 2px;
            z-index: 50;
        }
        .tf-nav-btn {
            width: 36px; height: 36px;
            border: 1px solid var(--tf-border);
            background: var(--tf-bg);
            border-radius: 4px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: var(--tf-muted);
            transition: all 0.15s ease;
        }
        .tf-nav-btn:hover { border-color: var(--tf-answer); color: var(--tf-answer); }
        .tf-nav-btn:disabled { opacity: 0.3; cursor: not-allowed; }
        .tf-nav-btn svg { width: 16px; height: 16px; }

        /* ── Powered by Logicoforms badge ─────────────────────── */
        .tf-branding {
            position: fixed;
            bottom: 24px;
            left: 24px;
            z-index: 50;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: var(--tf-bg);
            border: 1px solid var(--tf-border);
            border-radius: 999px;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .tf-branding:hover {
            border-color: var(--tf-answer);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .tf-branding-icon {
            width: 18px; height: 18px;
            border-radius: 5px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .tf-branding-icon svg { width: 10px; height: 10px; color: #fff; }
        .tf-branding-text {
            font-size: 0.6875rem;
            font-weight: 600;
            color: var(--tf-muted);
            white-space: nowrap;
            font-family: var(--tf-font);
        }

        /* ── Reduced motion ──────────────────────────────── */
        @media (prefers-reduced-motion: reduce) {
            .slide-enter, .slide-leave { transition: none !important; }
            .tf-fade-in { animation: none !important; }
            .tf-progress-bar { transition: none !important; }
        }

        /* ── Responsive ──────────────────────────────────── */
        @media (max-width: 640px) {
            .tf-question { font-size: 1.3125rem; }
            .tf-welcome h1 { font-size: 1.625rem; }
            .tf-content { max-width: 100%; }
            .tf-screen { padding: 48px 20px; }
            .tf-star { width: 44px; height: 44px; font-size: 1.25rem; }
            .tf-nav { display: none; }
        }
    </style>
    @if(config('forms.views.analytics'))
    @include(config('forms.views.analytics'))
    @endif
</head>
<body>
    <div id="app" x-data="typeform()" x-cloak @keydown.window="handleKeyboard($event)">

        <!-- Progress bar -->
        <div class="tf-progress" x-show="screen !== 'welcome'">
            <div class="tf-progress-bar" :style="'width:' + progressPercent + '%'"></div>
        </div>

        <!-- ═══════════ Welcome Screen ═══════════ -->
        <template x-if="screen === 'welcome'">
            <div class="tf-screen">
                <div class="tf-content tf-fade-in">
                    <div class="tf-welcome">
                        <h1>{{ $title }}</h1>
                        @if($description)
                            <p>{{ $description }}</p>
                        @endif
                        <button class="tf-start" @click="startSession()">
                            Start
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- ═══════════ Loading Screen ═══════════ -->
        <template x-if="screen === 'loading'">
            <div class="tf-screen">
                <div class="tf-content" style="text-align:center;padding:40px 0">
                    <div class="tf-spinner"></div>
                    <p style="font-size:0.875rem" :style="'color:var(--tf-faint)'">Loading...</p>
                </div>
            </div>
        </template>

        <!-- ═══════════ Question Screen ═══════════ -->
        <template x-if="screen === 'question'">
            <div class="tf-screen">
                <div class="tf-content"
                     x-show="qVisible"
                     x-transition:enter="slide-enter"
                     x-transition:enter-start="slide-enter-from"
                     x-transition:enter-end="slide-enter-to"
                     x-transition:leave="slide-leave"
                     x-transition:leave-start="slide-leave-from"
                     x-transition:leave-end="slide-leave-to">

                    <!-- Question number badge -->
                    <div class="tf-num">
                        <span class="tf-num-badge" x-text="progress.current + 1"></span>
                        <span class="tf-num-arrow">&rarr;</span>
                    </div>

                    <!-- Question text -->
                    <h2 class="tf-question">
                        <span x-text="question.question_text"></span>
                        <span class="tf-required" x-show="question.is_required"> *</span>
                    </h2>

                    <!-- Help text -->
                    <p class="tf-desc" x-show="question.help_text" x-text="question.help_text"></p>

                    <!-- ─── Field area ─── -->
                    <div class="tf-field">

                        <!-- TEXT -->
                        <template x-if="question.type === 'text'">
                            <input type="text"
                                   class="tf-input"
                                   :class="error && 'has-error'"
                                   :placeholder="(question.settings && question.settings.placeholder) || 'Type your answer here...'"
                                   x-model="answerValue"
                                   @input="clearError()"
                                   autocomplete="off"
                                   x-ref="textInput">
                        </template>

                        <!-- EMAIL -->
                        <template x-if="question.type === 'email'">
                            <input type="email"
                                   class="tf-input"
                                   :class="error && 'has-error'"
                                   :placeholder="(question.settings && question.settings.placeholder) || 'name@example.com'"
                                   x-model="answerValue"
                                   @input="clearError()"
                                   autocomplete="off">
                        </template>

                        <!-- NUMBER -->
                        <template x-if="question.type === 'number'">
                            <input type="number"
                                   class="tf-input"
                                   :class="error && 'has-error'"
                                   :placeholder="(question.settings && question.settings.placeholder) || 'Type your answer here...'"
                                   x-model="answerValue"
                                   @input="clearError()"
                                   autocomplete="off">
                        </template>

                        <!-- SELECT / DROPDOWN -->
                        <template x-if="question.type === 'select'">
                            <select class="tf-select" x-model="answerValue" @change="clearError()">
                                <option value="">Choose an option...</option>
                                <template x-for="opt in question.options" :key="opt.id">
                                    <option :value="opt.value" x-text="opt.label"></option>
                                </template>
                            </select>
                        </template>

                        <!-- RADIO (single choice) -->
                        <template x-if="question.type === 'radio'">
                            <div class="tf-choices">
                                <template x-for="(opt, idx) in question.options" :key="opt.id">
                                    <div class="tf-choice"
                                         :class="answerValue === opt.value && 'selected'"
                                         @click="selectRadio(opt.value)">
                                        <div class="tf-choice-key" x-text="String.fromCharCode(65 + idx)"></div>
                                        <div class="tf-choice-label" x-text="opt.label"></div>
                                        <svg class="tf-choice-check" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- CHECKBOX (multi choice) -->
                        <template x-if="question.type === 'checkbox'">
                            <div>
                                <div class="tf-choices">
                                    <template x-for="(opt, idx) in question.options" :key="opt.id">
                                        <div class="tf-choice"
                                             :class="Array.isArray(answerValue) && answerValue.includes(opt.value) && 'selected'"
                                             @click="toggleCheckbox(opt.value); clearError()">
                                            <div class="tf-choice-key" x-text="String.fromCharCode(65 + idx)"></div>
                                            <div class="tf-choice-label" x-text="opt.label"></div>
                                            <svg class="tf-choice-check" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        </div>
                                    </template>
                                </div>
                                <p style="margin-top:10px;font-size:0.8125rem" :style="'color:var(--tf-faint)'">
                                    Choose as many as you like
                                </p>
                            </div>
                        </template>

                        <!-- RATING (stars) -->
                        <template x-if="question.type === 'rating'">
                            <div class="tf-rating">
                                <template x-for="n in (question.settings && question.settings.max || 5)" :key="n">
                                    <button type="button"
                                            class="tf-star"
                                            :class="{
                                                'active': answerValue >= n && hoverRating === 0,
                                                'lit': hoverRating >= n
                                            }"
                                            @mouseenter="hoverRating = n"
                                            @mouseleave="hoverRating = 0"
                                            @click="answerValue = n; clearError()">
                                        &#9733;
                                        <span class="tf-star-num" x-text="n"></span>
                                    </button>
                                </template>
                            </div>
                        </template>

                        <!-- PICTURE CHOICE -->
                        <template x-if="question.type === 'picture_choice'">
                            <div>
                                <div class="tf-pictures">
                                    <template x-for="(opt, idx) in question.options" :key="opt.id">
                                        <div class="tf-picture-card"
                                             :class="isPictureSelected(opt.value) && 'selected'"
                                             @click="selectPictureChoice(opt.value)">
                                            <img class="tf-picture-img"
                                                 :src="opt.image_url || 'data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 400 300%27%3e%3crect fill=%27%23f1f5f9%27 width=%27400%27 height=%27300%27/%3e%3ctext x=%27200%27 y=%27150%27 text-anchor=%27middle%27 dominant-baseline=%27middle%27 font-family=%27sans-serif%27 font-size=%2714%27 fill=%27%2394a3b8%27%3eNo image%3c/text%3e%3c/svg%3e'"
                                                 :alt="opt.label">
                                            <div class="tf-picture-footer">
                                                <div class="tf-choice-key" x-text="String.fromCharCode(65 + idx)"></div>
                                                <div class="tf-choice-label" x-text="opt.label"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <template x-if="question.settings && question.settings.multiple">
                                    <p style="margin-top:10px;font-size:0.8125rem" :style="'color:var(--tf-faint)'">
                                        Choose as many as you like
                                    </p>
                                </template>
                            </div>
                        </template>

                        <!-- OPINION SCALE (Likert) -->
                        <template x-if="question.type === 'opinion_scale'">
                            <div class="tf-likert">
                                <div class="tf-likert-header">
                                    <div class="tf-likert-header-label"></div>
                                    <template x-for="col in (question.settings && question.settings.columns || [])" :key="col">
                                        <div class="tf-likert-header-col" x-text="col"></div>
                                    </template>
                                </div>
                                <template x-for="row in (question.settings && question.settings.rows || [])" :key="row">
                                    <div class="tf-likert-row">
                                        <div class="tf-likert-row-label" x-text="row"></div>
                                        <template x-for="col in (question.settings && question.settings.columns || [])" :key="col">
                                            <div class="tf-likert-cell">
                                                <button type="button"
                                                        class="tf-likert-radio"
                                                        :class="getLikertValue(row) === slugify(col) && 'selected'"
                                                        @click="setLikertValue(row, col); clearError()">
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <button type="button" class="tf-likert-clear" @click="answerValue = {}; clearError()">Clear all</button>
                            </div>
                        </template>
                    </div>

                    <!-- Error message -->
                    <div class="tf-error" x-show="error" x-cloak>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span x-text="error"></span>
                    </div>

                    <!-- OK button + Enter hint -->
                    <div class="tf-actions">
                        <button class="tf-btn" :disabled="submitting" @click="submitAnswer()">
                            <span x-text="submitting ? 'Sending...' : 'OK'"></span>
                            <svg x-show="!submitting" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </button>
                        <span class="tf-hint" x-show="!submitting">press <kbd>Enter &crarr;</kbd></span>
                    </div>
                </div>
            </div>
        </template>

        <!-- ═══════════ Thank You Screen ═══════════ -->
        <!-- With image: full-bleed split (no tf-screen/tf-content wrappers) -->
        <template x-if="screen === 'complete' && endScreen.image_url">
            <div class="tf-done-split tf-fade-in">
                <div class="tf-done-split-text">
                    <h1 x-text="endScreen.title || 'Thank you!'"></h1>
                    <p x-text="endScreen.message || 'Your response has been recorded.'"></p>
                </div>
                <div class="tf-done-split-img">
                    <img :src="endScreen.image_url" alt="">
                </div>
            </div>
        </template>
        <!-- Without image: centered with checkmark -->
        <template x-if="screen === 'complete' && !endScreen.image_url">
            <div class="tf-screen">
                <div class="tf-content tf-fade-in">
                    <div class="tf-done">
                        <div class="tf-done-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <h1 x-text="endScreen.title || 'Thank you!'"></h1>
                        <p x-text="endScreen.message || 'Your response has been recorded.'"></p>
                    </div>
                </div>
            </div>
        </template>

        <!-- ═══════════ Navigation arrows (bottom-right) ═══════════ -->
        <div class="tf-nav" x-show="screen === 'question'" x-cloak>
            <button class="tf-nav-btn" @click="submitAnswer()" title="Next" :disabled="submitting">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
            </button>
        </div>

        @if($showBranding)
        <!-- ═══════════ Powered by Logicoforms badge ═══════════ -->
        <a href="{{ url('/') }}" target="_blank" class="tf-branding">
            <span class="tf-branding-icon">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
            </span>
            <span class="tf-branding-text">Powered by Logicoforms</span>
        </a>
        @endif
    </div>

    <script>
        function typeform() {
            return {
                formId: {{ $formId }},
                sessionUuid: null,
                screen: 'welcome',
                question: null,
                progress: { current: 0, total: 1 },
                answerValue: '',
                submitting: false,
                error: null,
                qVisible: false,
                hoverRating: 0,
                autoAdvanceTimer: null,
                endScreen: @json($endScreen),

                get progressPercent() {
                    if (this.screen === 'complete') return 100;
                    if (!this.progress.total) return 0;
                    return Math.round((this.progress.current / this.progress.total) * 100);
                },

                clearError() { this.error = null; },

                sleep(ms) { return new Promise(r => setTimeout(r, ms)); },

                /* ── Validation ─────────────────────────── */
                validate() {
                    if (!this.question) return true;
                    const val = this.answerValue;
                    const type = this.question.type;
                    const required = this.question.is_required;

                    // Required check
                    if (required) {
                        if (type === 'opinion_scale') {
                            const rows = (this.question.settings && this.question.settings.rows) || [];
                            const answered = val && typeof val === 'object' ? Object.keys(val).length : 0;
                            if (answered < rows.length) { this.error = 'Please fill in all rows'; return false; }
                        } else if (Array.isArray(val) && val.length === 0) { this.error = 'Please fill this in'; return false; }
                        else if (!Array.isArray(val) && (val === '' || val === null || val === undefined)) { this.error = 'Please fill this in'; return false; }
                    }

                    // If empty and not required, skip format validation
                    if (type === 'opinion_scale') {
                        if (!val || typeof val !== 'object' || Object.keys(val).length === 0) return true;
                    } else if (Array.isArray(val) ? val.length === 0 : (val === '' || val === null)) return true;

                    if (type === 'email') {
                        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) { this.error = 'Hmm\u2026 that email doesn\u2019t look right'; return false; }
                    }
                    if (type === 'number' && val !== '' && isNaN(Number(val))) { this.error = 'Please enter a number'; return false; }
                    if (type === 'rating') {
                        const max = (this.question.settings && this.question.settings.max) || 5;
                        if (val < 1 || val > max) { this.error = 'Please select a rating'; return false; }
                    }
                    return true;
                },

                /* ── Start session ──────────────────────── */
                async startSession() {
                    this.screen = 'loading';
                    try {
                        const res = await fetch(`{{ route('forms.api.sessions.store', ['form' => $formId]) }}`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        });
                        const json = await res.json();
                        this.sessionUuid = json.data.session_uuid;
                        this.question = json.data.question;
                        this.progress = json.data.progress;
                        this.resetAnswer();

                        this.qVisible = false;
                        this.screen = 'question';
                        await this.$nextTick();
                        this.qVisible = true;
                        this.$nextTick(() => this.focusInput());
                    } catch (e) {
                        console.error(e);
                        this.screen = 'welcome';
                    }
                },

                /* ── Submit answer ──────────────────────── */
                async submitAnswer() {
                    if (this.submitting) return;
                    if (!this.validate()) return;
                    this.submitting = true;
                    clearTimeout(this.autoAdvanceTimer);

                    try {
                        const res = await fetch(`{{ rtrim(route('forms.api.answers.store', ['uuid' => '__UUID__']), '/') }}`.replace('__UUID__', this.sessionUuid), {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({ question_id: this.question.id, answer: this.answerValue }),
                        });
                        const json = await res.json();

                        // Phase 1: Exit animation
                        this.qVisible = false;
                        await this.sleep(350);

                        if (json.data.completed) {
                            this.progress = json.data.progress;
                            if (json.data.end_screen) {
                                this.endScreen = {
                                    title: json.data.end_screen.title || this.endScreen.title,
                                    message: json.data.end_screen.message || this.endScreen.message,
                                    image_url: json.data.end_screen.image_url || this.endScreen.image_url,
                                };
                            }
                            this.screen = 'complete';
                            return;
                        }

                        // Phase 2: Update data (while invisible)
                        this.question = json.data.question;
                        this.progress = json.data.progress;
                        this.resetAnswer();
                        this.error = null;
                        this.hoverRating = 0;

                        // Phase 3: Enter animation
                        await this.$nextTick();
                        this.qVisible = true;
                        this.$nextTick(() => this.focusInput());
                    } catch (e) {
                        console.error(e);
                        this.qVisible = true; // Restore visibility on error
                    } finally {
                        this.submitting = false;
                    }
                },

                /* ── Auto-advance for radio ─────────────── */
                selectRadio(value) {
                    this.answerValue = value;
                    this.clearError();
                    clearTimeout(this.autoAdvanceTimer);
                    this.autoAdvanceTimer = setTimeout(() => {
                        this.submitAnswer();
                    }, 500);
                },

                /* ── Toggle checkbox ────────────────────── */
                toggleCheckbox(value) {
                    if (!Array.isArray(this.answerValue)) this.answerValue = [];
                    const idx = this.answerValue.indexOf(value);
                    if (idx === -1) this.answerValue.push(value);
                    else this.answerValue.splice(idx, 1);
                },

                /* ── Picture choice ─────────────────────── */
                selectPictureChoice(value) {
                    const isMultiple = this.question.settings && this.question.settings.multiple;
                    if (isMultiple) {
                        this.toggleCheckbox(value);
                        this.clearError();
                    } else {
                        this.selectRadio(value);
                    }
                },

                isPictureSelected(value) {
                    const isMultiple = this.question.settings && this.question.settings.multiple;
                    if (isMultiple) {
                        return Array.isArray(this.answerValue) && this.answerValue.includes(value);
                    }
                    return this.answerValue === value;
                },

                /* ── Opinion Scale (Likert) ─────────────── */
                setLikertValue(row, col) {
                    if (typeof this.answerValue !== 'object' || Array.isArray(this.answerValue)) {
                        this.answerValue = {};
                    }
                    this.answerValue = { ...this.answerValue, [row]: this.slugify(col) };
                },

                getLikertValue(row) {
                    if (!this.answerValue || typeof this.answerValue !== 'object') return null;
                    return this.answerValue[row] || null;
                },

                slugify(text) {
                    return text.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
                },

                /* ── Reset answer value per type ────────── */
                resetAnswer() {
                    if (!this.question) return;
                    const t = this.question.type;
                    if (t === 'checkbox') this.answerValue = [];
                    else if (t === 'picture_choice' && this.question.settings && this.question.settings.multiple) this.answerValue = [];
                    else if (t === 'opinion_scale') this.answerValue = {};
                    else if (t === 'rating' || t === 'number') this.answerValue = null;
                    else this.answerValue = '';
                },

                /* ── Focus the active input ─────────────── */
                focusInput() {
                    setTimeout(() => {
                        const el = document.querySelector('.tf-input, .tf-select, .tf-textarea');
                        if (el) el.focus();
                    }, 80);
                },

                /* ── Keyboard shortcuts ─────────────────── */
                handleKeyboard(e) {
                    if (this.screen !== 'question' || !this.question) return;
                    if (this.submitting || !this.qVisible) return;

                    // Ignore if user is typing in an input/textarea
                    const tag = e.target.tagName;
                    const isTyping = (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT');
                    const type = this.question.type;

                    // Enter key — submit from anywhere
                    if (e.key === 'Enter' && !e.shiftKey) {
                        // Don't auto-submit if typing in text area (allow newlines)
                        if (tag === 'TEXTAREA') return;
                        e.preventDefault();
                        clearTimeout(this.autoAdvanceTimer);
                        this.submitAnswer();
                        return;
                    }

                    // Letter keys for choices (only when NOT typing in an input)
                    if (!isTyping && (type === 'radio' || type === 'checkbox' || type === 'picture_choice') && this.question.options) {
                        const upper = e.key.toUpperCase();
                        const idx = upper.charCodeAt(0) - 65;
                        if (idx >= 0 && idx < this.question.options.length) {
                            e.preventDefault();
                            const opt = this.question.options[idx];
                            if (type === 'picture_choice') {
                                this.selectPictureChoice(opt.value);
                            } else if (type === 'radio') {
                                this.selectRadio(opt.value);
                            } else {
                                this.toggleCheckbox(opt.value);
                                this.clearError();
                            }
                        }
                    }

                    // Number keys for rating (only when NOT typing)
                    if (!isTyping && type === 'rating') {
                        const num = parseInt(e.key);
                        const max = (this.question.settings && this.question.settings.max) || 5;
                        if (num >= 1 && num <= max) {
                            e.preventDefault();
                            this.answerValue = num;
                            this.clearError();
                        }
                    }
                },
            };
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
</body>
</html>

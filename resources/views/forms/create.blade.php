<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/logo.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Form</title>
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

<div class="max-w-2xl mx-auto px-6 py-12">

    <div class="mb-8">
        <a href="{{ route('forms.index') }}" class="text-sm text-gray-400 hover:text-gray-600 transition-colors flex items-center gap-1 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Back to forms
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Create Form</h1>
        <p class="text-sm text-gray-400 mt-1.5">Start by giving your form a title and description.</p>
    </div>

    <form method="POST" action="{{ route('forms.store') }}" class="bg-white border border-gray-200 rounded-lg p-6 space-y-5">
        @csrf

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">Title</label>
            <input type="text" name="title" id="title" value="{{ old('title') }}" required
                   class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all"
                   placeholder="e.g. Customer Feedback Survey">
            @error('title')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
            <textarea name="description" id="description" rows="3"
                      class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all resize-none"
                      placeholder="A short description for respondents...">{{ old('description') }}</textarea>
            @error('description')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('forms.index') }}" class="px-4 py-2.5 text-sm text-gray-500 bg-white border border-gray-200 rounded-lg hover:border-gray-300 hover:text-gray-700 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2.5 text-sm font-semibold text-white bg-blue-500 rounded-lg hover:bg-blue-600 transition-colors">
                Create Form
            </button>
        </div>
    </form>

</div>

</body>
</html>

<?php

use Illuminate\Support\Facades\Route;
use Logicoforms\Forms\Http\Controllers\AiFormBuilderController;
use Logicoforms\Forms\Http\Controllers\FormController;
use Logicoforms\Forms\Http\Controllers\FormThemePresetController;
use Logicoforms\Forms\Http\Controllers\QuestionController;

// ── Public Form ─────────────────────────────────────────────────
Route::get('/f/{slug}', [FormController::class, 'showPublic'])->name('forms.public');
Route::get('/f/{slug}/og-image.png', [FormController::class, 'ogImage'])->name('forms.og-image');

// ── Authenticated Form Management ──────────────────────────────
Route::middleware(config('forms.auth_middleware', ['web', 'auth']))->group(function () {
    Route::get('/dashboard', [FormController::class, 'index'])->name('dashboard');

    // AI Builder
    Route::get('/forms/ai-builder', [AiFormBuilderController::class, 'index'])->name('forms.ai-builder');
    Route::post('/forms/ai-builder/chat', [AiFormBuilderController::class, 'chat'])->middleware('throttle:10,1')->name('forms.ai-builder.chat');
    Route::get('/forms/ai-builder/chat/{jobId}/events', [AiFormBuilderController::class, 'pollEvents'])->name('forms.ai-builder.poll');

    // Templates
    Route::get('/forms/templates', [FormController::class, 'templates'])->name('forms.templates');
    Route::post('/forms/templates/{slug}', [FormController::class, 'createFromTemplate'])->name('forms.create-from-template');

    // Forms CRUD
    Route::resource('forms', FormController::class);

    // Logic tree
    Route::get('/forms/{form}/logic-tree', [FormController::class, 'logicTree'])->name('forms.logic-tree');

    // Questions
    Route::put('/forms/{form}/questions/reorder', [QuestionController::class, 'reorder'])->name('questions.reorder');
    Route::resource('forms.questions', QuestionController::class)->only(['store', 'update', 'destroy']);

    // Theme presets
    Route::get('/api/form-theme-presets', [FormThemePresetController::class, 'index']);
});

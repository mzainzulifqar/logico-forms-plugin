<?php

use Illuminate\Support\Facades\Route;
use Logicoforms\Forms\Http\Controllers\FormAnswerController;
use Logicoforms\Forms\Http\Controllers\FormSessionController;

Route::prefix(config('forms.api_prefix', 'api'))
    ->middleware('throttle:' . config('forms.api_rate_limit', '60,1'))
    ->group(function () {
        Route::post('/forms/{form}/sessions', [FormSessionController::class, 'store']);
        Route::post('/forms/sessions/{uuid}/answers', [FormAnswerController::class, 'store']);
    });

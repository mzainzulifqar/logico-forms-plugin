<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Owner Model
    |--------------------------------------------------------------------------
    |
    | The Eloquent model class that "owns" forms. Must implement
    | Logicoforms\Forms\Contracts\FormOwner.
    |
    */
    'owner_model' => env('FORMS_OWNER_MODEL', 'App\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | URL patterns used by AI tools when generating edit/public links.
    | Use {id} and {slug} as placeholders.
    |
    */
    'urls' => [
        'edit'   => env('FORMS_URL_EDIT', '/forms/{id}/edit'),
        'public' => env('FORMS_URL_PUBLIC', '/f/{slug}'),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Route Prefix
    |--------------------------------------------------------------------------
    */
    'api_prefix' => env('FORMS_API_PREFIX', 'api'),

    /*
    |--------------------------------------------------------------------------
    | API Rate Limit
    |--------------------------------------------------------------------------
    */
    'api_rate_limit' => env('FORMS_API_RATE_LIMIT', '60,1'),

    /*
    |--------------------------------------------------------------------------
    | Web Routes
    |--------------------------------------------------------------------------
    |
    | Whether the package should register its web routes (form CRUD, editor,
    | templates, AI builder, public form view). Set to false if you want to
    | define your own routes pointing to the package controllers.
    |
    */
    'register_web_routes' => true,

    /*
    |--------------------------------------------------------------------------
    | Auth Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware applied to authenticated form management routes.
    |
    */
    'auth_middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Branding
    |--------------------------------------------------------------------------
    |
    | Brand name and domain shown in the nav bar and OG images.
    | Set logo_path to an absolute path to a PNG logo file (80px recommended).
    |
    */
    'brand' => [
        'name'       => env('FORMS_BRAND_NAME', 'Logicoforms'),
        'domain'     => env('FORMS_BRAND_DOMAIN', 'logicoforms.com'),
        'tagline'    => env('FORMS_BRAND_TAGLINE', 'AI-powered forms  ·  Logic branching  ·  Smart workflows'),
        'logo_url'   => env('FORMS_BRAND_LOGO_URL', '/logo.svg'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    |
    | Configurable view partials. Set to null to disable.
    | The nav_view is included at the top of dashboard/admin pages.
    | The analytics_view is included in the <head> of the public form view.
    |
    */
    'views' => [
        'nav'       => 'forms::partials.nav',
        'analytics' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Builder
    |--------------------------------------------------------------------------
    */
    'ai_builder' => [
        'provider'        => env('FORMS_AI_BUILDER_PROVIDER'),
        'model'           => env('FORMS_AI_BUILDER_MODEL', 'gpt-5.2'),
        'timeout'         => (int) env('FORMS_AI_BUILDER_TIMEOUT', 120),
        'enforce_quality' => env('FORMS_AI_BUILDER_ENFORCE_QUALITY', true),

        'ai_critic' => [
            'enabled'      => env('FORMS_AI_CRITIC_ENABLED', true),
            'mode'         => env('FORMS_AI_CRITIC_MODE', 'on_pass'),
            'provider'     => env('FORMS_AI_CRITIC_PROVIDER'),
            'model'        => env('FORMS_AI_CRITIC_MODEL', 'gpt-4o'),
            'timeout'      => (int) env('FORMS_AI_CRITIC_TIMEOUT', 25),
            'max_attempts' => (int) env('FORMS_AI_CRITIC_MAX_ATTEMPTS', 2),
        ],
    ],
];

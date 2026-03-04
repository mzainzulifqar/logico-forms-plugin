<?php

namespace Logicoforms\Forms;

use Illuminate\Support\ServiceProvider;
use Logicoforms\Forms\Ai\ToolStatusReporter;
use Logicoforms\Forms\Contracts\FormLimiter;

class FormsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/forms.php', 'forms');

        $this->app->singletonIf(FormLimiter::class, NullFormLimiter::class);
        $this->app->singletonIf(ToolStatusReporter::class);
    }

    public function boot(): void
    {
        // Config
        $this->publishes([
            __DIR__ . '/../config/forms.php' => config_path('forms.php'),
        ], 'forms-config');

        // Migrations
        $this->publishesMigrations([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'forms-migrations');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'forms');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/forms'),
        ], 'forms-views');

        // Routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        if (config('forms.register_web_routes', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }

        // Console
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\ProcessAiFormChat::class,
            ]);
        }
    }
}

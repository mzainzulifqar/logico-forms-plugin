# Laravel Forms

A Typeform-style forms package for Laravel with logic branching, an AI form builder, and a full UI out of the box.

## Requirements

- PHP 8.2+
- Laravel 12+
- GD extension (for OG image generation)
- An AI provider API key (OpenAI, Anthropic, etc.) if using the AI builder

## Installation

```bash
composer require logicoforms/laravel-forms
```

The service provider is auto-discovered. Publish the config and run migrations:

```bash
php artisan vendor:publish --tag=forms-config
php artisan migrate
```

### Local path install (development)

Add the path repository to your app's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../packages/laravel-forms"
        }
    ],
    "require": {
        "logicoforms/laravel-forms": "@dev"
    }
}
```

Then run `composer update logicoforms/laravel-forms`.

## Setup

### 1. Implement `FormOwner` on your User model

```php
use Logicoforms\Forms\Contracts\FormOwner;
use Logicoforms\Forms\Models\Form;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements FormOwner
{
    public function forms(): HasMany
    {
        return $this->hasMany(Form::class, 'created_by');
    }
}
```

### 2. Configure (optional)

Edit `config/forms.php` after publishing. Key options:

```php
return [
    // The model that owns forms
    'owner_model' => App\Models\User::class,

    // URL patterns used by the AI builder when generating links
    'urls' => [
        'edit'   => '/forms/{id}/edit',
        'public' => '/f/{slug}',
    ],

    // Set to false to define your own web routes
    'register_web_routes' => true,

    // Middleware for authenticated routes
    'auth_middleware' => ['web', 'auth'],

    // Branding shown in nav and OG images
    'brand' => [
        'name'    => 'My App',
        'domain'  => 'myapp.com',
        'tagline' => 'Smart forms for everyone',
    ],

    // Override view partials (or set to null to disable)
    'views' => [
        'nav'       => 'forms::partials.nav', // or your own: 'partials.my-nav'
        'analytics' => null,                   // e.g. 'partials.gtag'
    ],

    // AI builder settings (requires laravel/ai)
    'ai_builder' => [
        'provider' => null, // auto-detected from laravel/ai config
        'model'    => 'gpt-4o',
        'timeout'  => 120,
    ],
];
```

### 3. Implement `FormLimiter` (optional)

By default the package uses `NullFormLimiter` which imposes no limits. To add plan-based restrictions, create your own implementation:

```php
use Logicoforms\Forms\Contracts\FormLimiter;

class AppFormLimiter implements FormLimiter
{
    public function maxResponsesPerForm($owner): ?int
    {
        return null; // unlimited
    }

    public function canCreateForm($owner): bool
    {
        return $owner->forms()->count() < 100;
    }

    public function canRemoveBranding($owner): bool
    {
        return false;
    }

    public function canUseCustomThemes($owner): bool
    {
        return true;
    }

    public function canAccessAiBuilder($owner): bool
    {
        return true;
    }

    public function hasAiCredits($owner): bool
    {
        return true;
    }

    public function deductAiCredit($owner): void
    {
        // no-op
    }

    public function getAiCreditBalance($owner): ?int
    {
        return null; // unlimited
    }

    public function formLimitRedirectUrl(): ?string
    {
        return null; // shows generic error
    }
}
```

Register it in your `AppServiceProvider`:

```php
use Logicoforms\Forms\Contracts\FormLimiter;

public function register(): void
{
    $this->app->singleton(FormLimiter::class, AppFormLimiter::class);
}
```

## What's included

### Routes

The package registers both API and web routes automatically.

**Web routes** (configurable via `register_web_routes`):

| Method | URI | Description |
|--------|-----|-------------|
| GET | `/f/{slug}` | Public form view |
| GET | `/f/{slug}/og-image.png` | OG image |
| GET | `/logico/forms` | Forms list |
| GET | `/logico/forms/create` | Create form |
| GET | `/logico/forms/{form}/edit` | Form editor |
| GET | `/logico/forms/{form}` | Responses viewer |
| GET | `/logico/forms/{form}/logic-tree` | Logic tree visualizer |
| GET | `/logico/forms/templates` | Template gallery |
| GET | `/logico/forms/ai-builder` | AI form builder |

**API routes** (always registered):

| Method | URI | Description |
|--------|-----|-------------|
| POST | `/api/forms/{form}/sessions` | Start a session |
| POST | `/api/forms/{form}/sessions/{uuid}/answers` | Submit an answer |
| POST | `/api/forms/{form}/sessions/{uuid}/complete` | Complete session |

### Models

- `Form` — title, slug, status, theme, end screen
- `FormQuestion` — type, text, help text, settings, ordering
- `QuestionOption` — label, value, image URL
- `QuestionLogic` — operator, value, next question routing
- `FormSession` — tracks a respondent's progress
- `FormAnswer` — stores individual answers
- `FormThemePreset` — reusable theme configurations

### Question types

`text`, `email`, `number`, `select`, `radio`, `checkbox`, `rating`, `picture_choice`, `opinion_scale`

### Logic branching

Each question can have logic rules that route respondents to different questions based on their answers. Supported operators: `equals`, `not_equals`, `is`, `is_not`, `greater_than`, `less_than`, `contains`, `not_contains`, `begins_with`, `ends_with`, `always`, and more.

### AI builder

The AI builder creates forms from natural language descriptions. It generates questions, options, and logic rules automatically. Requires `laravel/ai` and an API key for your chosen provider.

Set up the queue worker for AI builder to function:

```bash
php artisan queue:work
```

### Events

The package dispatches events instead of logging directly, so you can listen to them in your app:

| Event | Payload |
|-------|---------|
| `FormCreated` | `Form $form`, `array $metadata` |
| `FormUpdated` | `Form $form` |
| `FormDeleted` | `int $formId`, `string $formTitle` |
| `QuestionChanged` | `Form $form`, `string $action`, `array $metadata` |
| `AiBuilderUsed` | `array $metadata` |

### Views

All views are published under the `forms` namespace. Override any view by publishing:

```bash
php artisan vendor:publish --tag=forms-views
```

This copies views to `resources/views/vendor/forms/` where you can customize them.

## Customizing the navigation

The dashboard includes a configurable nav partial. Set `forms.views.nav` in config to point to your own Blade partial:

```php
'views' => [
    'nav' => 'partials.my-nav', // your app's nav
],
```

Set it to `null` to disable the nav entirely.

## Querying Forms & Responses

All package models are available for direct use. Import from `Logicoforms\Forms\Models\*`.

### Get a user's forms

```php
use Logicoforms\Forms\Models\Form;

$forms = $user->forms;

// Only published
$published = $user->forms()->where('status', 'published')->get();

// With question and session counts
$forms = Form::where('created_by', $user->id)
    ->withCount(['questions', 'sessions'])
    ->latest()
    ->get();
```

### Get form questions with options

```php
$form = Form::with('questions.options')->find($formId);

foreach ($form->questions as $question) {
    echo $question->question_text; // "How likely are you to recommend us?"
    echo $question->type;          // radio, text, rating, etc.

    foreach ($question->options as $option) {
        echo $option->label; // "Very likely"
        echo $option->value; // "very_likely"
    }
}
```

### Get completed responses for a form

```php
use Logicoforms\Forms\Models\FormSession;

$sessions = FormSession::where('form_id', $formId)
    ->where('is_completed', true)
    ->with('answers.question')
    ->latest()
    ->get();

foreach ($sessions as $session) {
    echo $session->session_uuid;
    echo $session->created_at;

    foreach ($session->answers as $answer) {
        echo $answer->question->question_text;
        echo $answer->answer_value; // string or array (for checkbox)
    }
}
```

### Get answers for a specific question

```php
use Logicoforms\Forms\Models\FormAnswer;

$answers = FormAnswer::where('question_id', $questionId)
    ->whereHas('session', fn ($q) => $q->where('is_completed', true))
    ->pluck('answer_value');
```

### Count responses

```php
$form = Form::withCount([
    'sessions',
    'sessions as completed_count' => fn ($q) => $q->where('is_completed', true),
])->find($formId);

echo $form->sessions_count;  // total started
echo $form->completed_count; // total completed
```

### Aggregate answers (averages, breakdowns)

```php
// Average rating
$avg = FormAnswer::where('question_id', $ratingQuestionId)
    ->whereHas('session', fn ($q) => $q->where('is_completed', true))
    ->get()
    ->avg(fn ($a) => (float) $a->answer_value);

// Option breakdown for radio/select
$breakdown = FormAnswer::where('question_id', $radioQuestionId)
    ->whereHas('session', fn ($q) => $q->where('is_completed', true))
    ->get()
    ->countBy(fn ($a) => $a->answer_value);
// Returns: ['very_likely' => 12, 'not_likely' => 3, ...]
```

### Filter responses by date

```php
$sessions = FormSession::where('form_id', $formId)
    ->where('is_completed', true)
    ->whereBetween('created_at', [$startDate, $endDate])
    ->with('answers')
    ->get();
```

### Export-friendly: all responses as rows

```php
$form = Form::with('questions')->find($formId);
$questions = $form->questions;

$rows = FormSession::where('form_id', $formId)
    ->where('is_completed', true)
    ->with('answers')
    ->get()
    ->map(function ($session) use ($questions) {
        $row = [
            'uuid' => $session->session_uuid,
            'completed_at' => $session->updated_at,
        ];
        foreach ($questions as $q) {
            $answer = $session->answers->firstWhere('question_id', $q->id);
            $val = $answer?->answer_value;
            $row[$q->question_text] = is_array($val) ? implode(', ', $val) : $val;
        }
        return $row;
    });
```

## Testing

```bash
cd packages/laravel-forms
composer install
./vendor/bin/phpunit
```

## License

MIT

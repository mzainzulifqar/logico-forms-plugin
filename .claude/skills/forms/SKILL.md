---
name: forms
description: Work with the laravel-forms package - create forms, add questions, set up logic branching, review form quality, export responses, debug issues, and manage templates/themes.
argument-hint: [what you want to do]
---

# Laravel Forms Package Skill

You are an expert at the `logicoforms/laravel-forms` Typeform-style forms package. You can create forms, add questions with options, set up conditional branching logic, review form quality, export/query responses, debug issues, and manage themes.

Parse the user's intent from `$ARGUMENTS` and perform the appropriate action.

---

## Package Models

```php
use Logicoforms\Forms\Models\Form;
use Logicoforms\Forms\Models\FormQuestion;
use Logicoforms\Forms\Models\QuestionOption;
use Logicoforms\Forms\Models\QuestionLogic;
use Logicoforms\Forms\Models\FormSession;
use Logicoforms\Forms\Models\FormAnswer;
use Logicoforms\Forms\Models\FormThemePreset;
use Logicoforms\Forms\Services\Forms\LogicEngine;
use Logicoforms\Forms\Services\FormTemplateService;
```

---

## Question Types

`text`, `email`, `number`, `radio`, `select`, `checkbox`, `rating`, `picture_choice`, `opinion_scale`

### Settings by type
- **rating**: `'settings' => ['max' => 5]` (scale 1-10)
- **opinion_scale**: `'settings' => ['rows' => [...], 'columns' => [...]]`
- **text/email/number**: `'settings' => ['placeholder' => '...']`
- **picture_choice**: `'settings' => ['multiple' => true]` + options need `image_url`

---

## Creating Forms

```php
$form = Form::create([
    'title' => 'Form Title',
    'description' => 'Description',
    'status' => 'draft',
    'created_by' => $userId,
    'theme' => [
        'background_color' => '#FFFFFF',
        'question_color' => '#191919',
        'answer_color' => '#0445AF',
        'button_color' => '#0445AF',
        'button_text_color' => '#FFFFFF',
        'font' => 'Inter',
        'border_radius' => 'large',
    ],
    'end_screen_title' => 'Thank you!',
    'end_screen_message' => 'Your response has been recorded.',
]);

$q1 = FormQuestion::create([
    'form_id' => $form->id,
    'type' => 'radio',
    'question_text' => 'How satisfied are you?',
    'is_required' => true,
    'order_index' => 0,
]);

QuestionOption::create(['question_id' => $q1->id, 'label' => 'Very satisfied', 'value' => 'very_satisfied', 'order_index' => 0]);
QuestionOption::create(['question_id' => $q1->id, 'label' => 'Not satisfied', 'value' => 'not_satisfied', 'order_index' => 1]);
```

---

## Logic Branching

### Operators

| Operator | Use for | Example value |
|----------|---------|---------------|
| `equals` | Exact match (radio/select) | `"very_satisfied"` |
| `not_equals` | Not this option | `"other"` |
| `contains` | Substring (text answers) | `"urgent"` |
| `not_contains` | Doesn't contain | `"spam"` |
| `begins_with` | Starts with | `"yes"` |
| `ends_with` | Ends with | `".com"` |
| `greater_than` | Numeric (rating/number) | `"7"` |
| `less_than` | Below threshold | `"4"` |
| `greater_equal_than` | >= | `"5"` |
| `less_equal_than` | <= | `"3"` |
| `always` | Default catch-all (MUST be last rule) | `""` |

### Adding logic rules

```php
// Jump based on answer
QuestionLogic::create([
    'form_id' => $form->id,
    'question_id' => $q1->id,
    'operator' => 'equals',
    'value' => 'very_satisfied',       // must match option VALUE (slugged), NOT label
    'next_question_id' => $q3->id,     // skip to q3
]);

// Default fallback (always last)
QuestionLogic::create([
    'form_id' => $form->id,
    'question_id' => $q1->id,
    'operator' => 'always',
    'value' => '',
    'next_question_id' => $q2->id,
]);

// End form (next_question_id = null)
QuestionLogic::create([
    'form_id' => $form->id,
    'question_id' => $q5->id,
    'operator' => 'always',
    'value' => '',
    'next_question_id' => null,
]);
```

### Rules
- Option `value` = slugged label: "Very Satisfied" → `"very_satisfied"`
- Jumps must be **forward-only** (target order_index > source order_index)
- **No loops** — never jump backwards
- **All questions reachable** from Q0 via some path
- `always` must be the **last** rule on a question
- `next_question_id = null` = end the form
- Forms with 5+ questions should have meaningful branching
- Max 20 questions per form

### Common patterns

**Two-path branch:**
```
Q0 [radio] → equals "a" → Q1 | always → Q3
Q1, Q2 (path A) → always → Q5
Q3, Q4 (path B) → always → Q5
Q5 (shared ending)
```

**NPS 3-way split:**
```
Q0 [rating] → greater_than "8" → Q1 | less_than "7" → Q3 | always → Q5
```

**Early exit:**
```
Q0 → equals "no" → null (END) | always → Q1 (continue)
```

---

## Reviewing Forms

To audit a form, fetch and analyze:

```php
$form = Form::with(['questions.options', 'questions.logicRules.nextQuestion'])->find($id);

foreach ($form->questions->sortBy('order_index') as $i => $q) {
    echo "Q{$i} (ID:{$q->id}) [{$q->type}] {$q->question_text}\n";
    foreach ($q->options as $o) echo "  [{$o->value}] {$o->label}\n";
    foreach ($q->logicRules as $r) {
        $target = $r->nextQuestion ? $r->nextQuestion->question_text : 'END';
        echo "  Rule: {$r->operator} '{$r->value}' → {$target}\n";
    }
}
```

### Check for issues
1. **Unreachable questions** — BFS from Q0 following all logic paths; flag questions not visited
2. **Value mismatches** — logic rule `value` doesn't match any option's `value`
3. **Back-jumps** — rule pointing to lower order_index (creates loops)
4. **Dead ends** — non-terminal question with no logic and not last by order_index
5. **Missing fallback** — branching question without an `always` catch-all
6. **Duplicate rules** — same operator+value on same question

---

## Querying Responses

### Stats
```php
$form = Form::withCount([
    'sessions',
    'sessions as completed_count' => fn ($q) => $q->where('is_completed', true),
])->find($id);
// $form->sessions_count, $form->completed_count
```

### All responses as rows
```php
$form = Form::with('questions')->find($id);
$questions = $form->questions->sortBy('order_index');

$rows = FormSession::where('form_id', $id)->where('is_completed', true)
    ->with('answers')->latest()->get()
    ->map(function ($session) use ($questions) {
        $row = ['uuid' => $session->session_uuid, 'completed_at' => $session->updated_at];
        foreach ($questions as $q) {
            $a = $session->answers->firstWhere('question_id', $q->id);
            $val = $a?->answer_value;
            $row[$q->question_text] = is_array($val) ? implode(', ', $val) : $val;
        }
        return $row;
    });
```

### Rating average
```php
$avg = FormAnswer::where('question_id', $qId)
    ->whereHas('session', fn ($q) => $q->where('is_completed', true))
    ->get()->avg(fn ($a) => (float) $a->answer_value);
```

### Option breakdown
```php
$breakdown = FormAnswer::where('question_id', $qId)
    ->whereHas('session', fn ($q) => $q->where('is_completed', true))
    ->get()->countBy(fn ($a) => is_array($a->answer_value) ? implode(', ', $a->answer_value) : $a->answer_value);
```

### CSV export
```php
$fp = fopen('responses.csv', 'w');
fputcsv($fp, array_merge(['UUID', 'Completed'], $questions->pluck('question_text')->toArray()));

FormSession::where('form_id', $id)->where('is_completed', true)
    ->with('answers')->chunk(100, function ($sessions) use ($fp, $questions) {
        foreach ($sessions as $s) {
            $row = [$s->session_uuid, $s->updated_at->toDateTimeString()];
            foreach ($questions as $q) {
                $a = $s->answers->firstWhere('question_id', $q->id);
                $val = $a?->answer_value;
                $row[] = is_array($val) ? implode(', ', $val) : ($val ?? '');
            }
            fputcsv($fp, $row);
        }
    });
fclose($fp);
```

### Filter by date
```php
FormSession::where('form_id', $id)->where('is_completed', true)
    ->whereBetween('created_at', [$start, $end])->with('answers')->get();
```

---

## Debugging

### Common issues

| Symptom | Cause | Fix |
|---------|-------|-----|
| 404 on `/f/{slug}` | Form status is `draft` | `$form->update(['status' => 'published'])` |
| "Method not allowed" | Wrong URL prefix | Routes are under `/logico/forms/`, not `/forms/` |
| Empty model in controller | Missing SubstituteBindings | Check `routes/api.php` middleware |
| Logic skips wrong question | Value mismatch | Compare option values vs logic rule values |
| Form never completes | No terminal rule | Add `always` with `next_question_id => null` on last question |
| AI builder not working | Queue not running | `php artisan queue:work` |
| AI builder timeout | Slow AI response | Increase `FORMS_AI_BUILDER_TIMEOUT` |

### Diagnostic commands

```bash
# Check routes
php artisan route:list --name=forms

# Check migrations
php artisan migrate:status | grep form

# Check config
php artisan tinker --execute="dd(config('forms'))"
```

### Debug a session
```php
$session = FormSession::where('session_uuid', 'UUID')->with('answers.question')->first();
echo "Completed: " . ($session->is_completed ? 'yes' : 'no') . "\n";
echo "Answers: " . $session->answers->count() . "\n";
foreach ($session->answers as $a) {
    echo "  {$a->question->question_text}: " . json_encode($a->answer_value) . "\n";
}
```

### Test logic engine
```php
$next = LogicEngine::getNextQuestion($session, $questionId, 'answer_value');
echo $next ? "Next: {$next->question_text}" : "Form ends";
```

---

## Routes Reference

| Route Name | Method | URI | Purpose |
|------------|--------|-----|---------|
| `forms.public` | GET | `/f/{slug}` | Public form |
| `forms.index` | GET | `/logico/forms` | Forms list |
| `forms.create` | GET | `/logico/forms/create` | Create form |
| `forms.store` | POST | `/logico/forms` | Save new form |
| `forms.edit` | GET | `/logico/forms/{form}/edit` | Editor |
| `forms.update` | PUT | `/logico/forms/{form}` | Update form |
| `forms.destroy` | DELETE | `/logico/forms/{form}` | Delete form |
| `forms.logic-tree` | GET | `/logico/forms/{form}/logic-tree` | Logic visualizer |
| `forms.templates` | GET | `/logico/forms/templates` | Template gallery |
| `forms.ai-builder` | GET | `/logico/forms/ai-builder` | AI builder |
| `forms.api.sessions.store` | POST | `/api/forms/{form}/sessions` | Start session |
| `forms.api.answers.store` | POST | `/api/forms/sessions/{uuid}/answers` | Submit answer |

---

## Config (env vars)

```
FORMS_AI_BUILDER_PROVIDER=openai
FORMS_AI_BUILDER_MODEL=gpt-5.2
FORMS_AI_BUILDER_TIMEOUT=120
FORMS_BRAND_NAME="My App"
FORMS_BRAND_LOGO_URL=/logo.svg
FORMS_URL_EDIT=/logico/forms/{id}/edit
FORMS_URL_PUBLIC=/f/{slug}
```

---

## Events

Listen to these in your app:
- `Logicoforms\Forms\Events\FormCreated` — `$event->form`
- `Logicoforms\Forms\Events\FormUpdated` — `$event->form`
- `Logicoforms\Forms\Events\FormDeleted` — `$event->formId`, `$event->formTitle`
- `Logicoforms\Forms\Events\QuestionChanged` — `$event->form`, `$event->action`
- `Logicoforms\Forms\Events\AiBuilderUsed` — `$event->metadata`

<?php

namespace Logicoforms\Forms\Ai\Tools;

use Logicoforms\Forms\Ai\ToolStatusReporter;
use Logicoforms\Forms\Models\Form;
use Logicoforms\Forms\Models\FormQuestion;
use Logicoforms\Forms\Models\QuestionOption;
use Logicoforms\Forms\Support\OptionValue;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CreateFormWithQuestions implements Tool
{
    private const MAX_QUESTIONS = 20;

    public function description(): Stringable|string
    {
        return 'Create a new form with all its questions and options in a single operation (max 20 questions). Returns the form ID, question IDs, and option values needed for adding logic rules. Returns VALIDATION_ERROR and creates nothing if the draft is invalid.';
    }

    public function handle(Request $request): Stringable|string
    {
        $title = trim((string) ($request['title'] ?? ''));
        $description = isset($request['description']) ? trim((string) $request['description']) : null;
        $questions = $request['questions'] ?? [];
        $questionCount = count($questions);

        $issues = $this->validateDraft($title, $description, $questions);
        if ($issues !== []) {
            app(ToolStatusReporter::class)->report('Refining the form…');

            $output = "VALIDATION_ERROR:\n";
            foreach (array_slice($issues, 0, 5) as $issue) {
                $output .= "- {$issue}\n";
            }
            $output .= "Fix and call CreateFormWithQuestions again.";

            return $output;
        }

        app(ToolStatusReporter::class)->report('Building your form…');

        return DB::transaction(function () use ($request) {
            $form = Form::create([
                'title' => $request['title'],
                'description' => $request['description'] ?? null,
                'status' => 'draft',
            ]);

            $questions = $request['questions'] ?? [];
            $editUrl = url(str_replace("{id}", $form->id, config("forms.urls.edit", "/forms/{id}/edit")));
            $output = "Form created: \"{$form->title}\" (ID: {$form->id})\n";
            $output .= "Edit URL: {$editUrl}\n";
            $output .= "Questions:\n";

            foreach ($questions as $index => $qData) {
                $type = $qData['type'] ?? 'text';
                $settings = null;

                if ($type === 'rating') {
                    $settings = ['max' => $qData['settings']['max'] ?? 5];
                } elseif ($type === 'opinion_scale') {
                    $settings = [
                        'rows' => $qData['settings']['rows'] ?? [],
                        'columns' => $qData['settings']['columns'] ?? [],
                    ];
                }

                if (isset($qData['settings']['placeholder'])) {
                    $settings = $settings ?? [];
                    $settings['placeholder'] = $qData['settings']['placeholder'];
                }

                $question = FormQuestion::create([
                    'form_id' => $form->id,
                    'type' => $type,
                    'question_text' => $qData['question_text'],
                    'help_text' => $qData['help_text'] ?? null,
                    'is_required' => $qData['is_required'] ?? false,
                    'order_index' => $index,
                    'settings' => $settings,
                ]);

                $shortText = mb_substr($qData['question_text'], 0, 50);
                $output .= "  Q" . ($index + 1) . " ID:{$question->id} [{$type}] {$shortText}";

                if (in_array($type, ['radio', 'select', 'checkbox', 'picture_choice']) && !empty($qData['options'])) {
                    $optionValues = [];
                    foreach ($qData['options'] as $optIndex => $opt) {
                        $label = $opt['label'] ?? $opt;
                        $value = OptionValue::fromLabel((string) $label);
                        $imageUrl = is_array($opt) ? ($opt['image_url'] ?? null) : null;

                        QuestionOption::create([
                            'question_id' => $question->id,
                            'label' => $label,
                            'value' => $value,
                            'image_url' => $imageUrl,
                            'order_index' => $optIndex,
                        ]);

                        $optionValues[] = $value;
                    }
                    $output .= ' vals:' . implode('|', $optionValues);
                }

                $output .= "\n";
            }

            $output .= "\nUse these exact question IDs and option VALUES (not labels) when adding logic rules.";

            return $output;
        });
    }

    private function validateDraft(string $title, ?string $description, array $questions): array
    {
        $issues = [];
        $hasBranchQuestion = false;

        if ($title === '') {
            $issues[] = 'Missing required `title`.';
        }

        if (count($questions) === 0) {
            $issues[] = 'No questions provided.';
            return $issues;
        }

        if (count($questions) > self::MAX_QUESTIONS) {
            $issues[] = 'Too many questions: max is '.self::MAX_QUESTIONS.'.';
        }

        foreach ($questions as $index => $qData) {
            $type = $qData['type'] ?? null;
            $questionText = trim((string) ($qData['question_text'] ?? ''));

            if ($questionText === '') {
                $issues[] = 'Question '.($index + 1).' is missing `question_text`.';
            }

            if (in_array($type, ['radio', 'select', 'checkbox', 'picture_choice'], true)) {
                $options = $qData['options'] ?? [];
                if (!is_array($options) || count($options) < 2) {
                    $issues[] = 'Question '.($index + 1)." is type `{$type}` but has fewer than 2 options.";
                }
            }

            if (in_array($type, ['text', 'email', 'number', 'rating', 'opinion_scale'], true) && isset($qData['options']) && is_array($qData['options']) && count($qData['options']) > 0) {
                $issues[] = 'Question '.($index + 1)." is type `{$type}` but includes `options` (options are only valid for radio/select/checkbox/picture_choice).";
            }

            if (in_array($type, ['radio', 'select'], true)) {
                $options = $qData['options'] ?? [];
                if (is_array($options) && count($options) >= 2) {
                    $hasBranchQuestion = true;
                }
            }
        }

        if (! $hasBranchQuestion) {
            $issues[] = 'Include at least one `radio` or `select` question with 2+ options (needed for branching logic).';
        }

        return $issues;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->description('The form title.')->required(),
            'description' => $schema->string()->description('The form description.'),
            'questions' => $schema->array()->description('Array of questions to create.')->max(self::MAX_QUESTIONS)->items(
                $schema->object([
                    'type' => $schema->string()->enum(['text', 'email', 'number', 'radio', 'select', 'checkbox', 'rating', 'picture_choice', 'opinion_scale'])->description('The question input type.')->required(),
                    'question_text' => $schema->string()->description('The question text shown to the respondent.')->required(),
                    'help_text' => $schema->string()->description('Additional help text shown below the question.'),
                    'is_required' => $schema->boolean()->description('Whether the question is required.')->default(false),
                    'settings' => $schema->object([
                        'max' => $schema->integer()->description('Maximum rating value (1-10). Only for rating type.')->min(1)->max(10),
                        'placeholder' => $schema->string()->description('Input placeholder text. Only for text/email/number types.'),
                        'rows' => $schema->array()->description('Row statements for opinion_scale type.')->items($schema->string()),
                        'columns' => $schema->array()->description('Scale labels (columns) for opinion_scale type.')->items($schema->string()),
                        'multiple' => $schema->boolean()->description('Allow multiple selections. Only for picture_choice type.'),
                    ])->description('Type-specific settings.'),
                    'options' => $schema->array()->description('Options for radio/select/checkbox/picture_choice types. Each option has a label and optional image_url.')->items(
                        $schema->object([
                            'label' => $schema->string()->description('The option label shown to respondents.')->required(),
                            'image_url' => $schema->string()->description('Image URL for picture_choice type options.'),
                        ])
                    ),
                ])
            )->required(),
        ];
    }
}

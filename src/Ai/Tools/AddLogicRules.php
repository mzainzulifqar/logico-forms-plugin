<?php

namespace Logicoforms\Forms\Ai\Tools;

use Logicoforms\Forms\Ai\ToolStatusReporter;
use Logicoforms\Forms\Models\Form;
use Logicoforms\Forms\Models\FormQuestion;
use Logicoforms\Forms\Models\QuestionLogic;
use Logicoforms\Forms\Support\OptionValue;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class AddLogicRules implements Tool
{
    public function description(): Stringable|string
    {
        return 'Set conditional branching logic rules for an existing form (replacing any existing rules). Rules determine which question to show next based on the respondent\'s answer. Use the exact question IDs and option VALUES returned by CreateFormWithQuestions.';
    }

    public function handle(Request $request): Stringable|string
    {
        $formId = $request['form_id'];
        $rules = $request['rules'] ?? [];

        $form = Form::find($formId);
        if (!$form) {
            return "Error: Form with ID {$formId} not found.";
        }

        $questions = FormQuestion::where('form_id', $formId)
            ->with('options')
            ->orderBy('order_index')
            ->get();

        $questionIds = $questions->pluck('id')->toArray();
        $questionIdSet = array_flip($questionIds);

        $ruleCount = count($rules);
        app(ToolStatusReporter::class)->report('Adding branching logic…');

        [$normalizedRules, $normalizationNotes, $skippedNotes] = $this->normalizeRules($rules, $questions, $questionIdSet);

        $validationIssues = $this->validateWiring($questions, $normalizedRules);
        if ($validationIssues !== []) {
            app(ToolStatusReporter::class)->report('Adjusting the logic…');

            $allIssues = array_merge($normalizationNotes, $skippedNotes, $validationIssues);
            $output = "VALIDATION_ERROR:\n";
            foreach (array_slice($allIssues, 0, 5) as $issue) {
                $output .= "- {$issue}\n";
            }
            $output .= "Fix and call AddLogicRules again. Tip: leave one option as default path (no rule), don't cover every option with equals.";

            return $output;
        }

        $createCount = count($normalizedRules);
        app(ToolStatusReporter::class)->report('Applying logic rules…');

        return DB::transaction(function () use ($form, $normalizedRules, $questionIds, $normalizationNotes, $skippedNotes) {
            $created = 0;

            QuestionLogic::where('form_id', $form->id)->delete();

            foreach ($normalizedRules as $rule) {
                $questionId = $rule['question_id'];
                $nextQuestionId = $rule['next_question_id'];

                if (!in_array($questionId, $questionIds) || !in_array($nextQuestionId, $questionIds)) {
                    continue;
                }

                QuestionLogic::create([
                    'form_id' => $form->id,
                    'question_id' => $questionId,
                    'operator' => $rule['operator'],
                    'value' => $rule['value'] ?? '',
                    'next_question_id' => $nextQuestionId,
                ]);
                $created++;
            }

            $editUrl = url(str_replace("{id}", $form->id, config("forms.urls.edit", "/forms/{id}/edit")));
            $questionCount = FormQuestion::where('form_id', $form->id)->count();
            $notes = array_merge($normalizationNotes, $skippedNotes);

            $output = "Done. Form \"{$form->title}\" (ID:{$form->id}): {$questionCount} questions, {$created} logic rules.\n";
            $output .= "Edit: {$editUrl}\n";
            if ($notes !== []) {
                $output .= "Notes: " . implode('; ', array_slice($notes, 0, 3)) . "\n";
            }

            return $output;
        });
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, string>, 2: array<int, string>}
     */
    private function normalizeRules(array $rules, $questions, array $questionIdSet): array
    {
        $questionsById = $questions->keyBy('id');
        $orderedIds = $questions->pluck('id')->values()->all();
        $defaultNext = [];
        for ($i = 0; $i < count($orderedIds); $i++) {
            $defaultNext[$orderedIds[$i]] = $orderedIds[$i + 1] ?? null;
        }

        $valid = [];
        $skippedNotes = [];
        foreach ($rules as $idx => $rule) {
            $questionIdRaw = $rule['question_id'] ?? null;
            $nextQuestionIdRaw = $rule['next_question_id'] ?? null;
            if (!is_numeric($questionIdRaw) || !is_numeric($nextQuestionIdRaw)) {
                $skippedNotes[] = "Skipped invalid rule at index {$idx} (question_id/next_question_id not in this form).";
                continue;
            }

            $questionId = (int) $questionIdRaw;
            $nextQuestionId = (int) $nextQuestionIdRaw;

            if (!isset($questionIdSet[$questionId]) || !isset($questionIdSet[$nextQuestionId])) {
                $skippedNotes[] = "Skipped invalid rule at index {$idx} (question_id/next_question_id not in this form).";
                continue;
            }

            $rule['question_id'] = $questionId;
            $rule['next_question_id'] = $nextQuestionId;
            $valid[] = $rule;
        }

        // Group by question_id preserving order
        $groups = [];
        foreach ($valid as $rule) {
            $qid = $rule['question_id'];
            $groups[$qid] ??= [];
            $groups[$qid][] = $rule;
        }

        $normalizationNotes = [];
        $normalized = [];

        foreach ($groups as $questionId => $groupRules) {
            $question = $questionsById->get($questionId);
            if (!$question) {
                continue;
            }

            // Safe auto-fix: drop redundant always-to-default-next rules (they add noise but don't change flow).
            $dn = $defaultNext[$questionId] ?? null;
            if ($dn) {
                $cleaned = [];
                foreach ($groupRules as $r) {
                    if (($r['operator'] ?? '') === 'always' && (int) ($r['next_question_id'] ?? 0) === (int) $dn) {
                        $qNum = $question->order_index + 1;
                        $normalizationNotes[] = "Removed redundant always rule on Q{$qNum}: it already flows to the next question by order_index.";
                        continue;
                    }
                    $cleaned[] = $r;
                }
                $groupRules = $cleaned;
            }

            // Normalize / validate equals values for choice questions.
            if (in_array($question->type, ['radio', 'select', 'checkbox'], true) && $question->options->isNotEmpty()) {
                $optionValues = $question->options->pluck('value')->values()->all();
                $optionSet = array_fill_keys($optionValues, true);
                $labelToValue = [];
                foreach ($question->options as $opt) {
                    $labelToValue[mb_strtolower(trim((string) $opt->label))] = (string) $opt->value;
                }
                $cleaned = [];
                foreach ($groupRules as $r) {
                    if (($r['operator'] ?? '') !== 'equals') {
                        $cleaned[] = $r;
                        continue;
                    }

                    $rawValue = (string) ($r['value'] ?? '');
                    if (isset($optionSet[$rawValue])) {
                        $cleaned[] = $r;
                        continue;
                    }

                    $rawLower = mb_strtolower(trim($rawValue));
                    if (isset($labelToValue[$rawLower])) {
                        $qNum = $question->order_index + 1;
                        $normalized = $labelToValue[$rawLower];
                        $normalizationNotes[] = "Normalized Q{$qNum} rule value \"{$rawValue}\" → \"{$normalized}\" (matched option label).";
                        $r['value'] = $normalized;
                        $cleaned[] = $r;
                        continue;
                    }

                    $slugValue = OptionValue::fromLabel($rawValue);
                    if (isset($optionSet[$slugValue])) {
                        $qNum = $question->order_index + 1;
                        $normalizationNotes[] = "Normalized Q{$qNum} rule value \"{$rawValue}\" → \"{$slugValue}\" (slug).";
                        $r['value'] = $slugValue;
                        $cleaned[] = $r;
                        continue;
                    }

                    $qNum = $question->order_index + 1;
                    $normalizationNotes[] = "Dropped invalid equals rule on Q{$qNum}: value \"{$rawValue}\" is not one of the option VALUES for that question.";
                }
                $groupRules = $cleaned;
            }

            $operators = array_values(array_unique(array_map(fn ($r) => (string) ($r['operator'] ?? ''), $groupRules)));
            $nextIds = array_values(array_unique(array_map(fn ($r) => (int) ($r['next_question_id'] ?? 0), $groupRules)));

            // Safe auto-fix: if every option value is routed via equals to the same target, the rules are redundant.
            if (in_array($question->type, ['radio', 'select'], true) && $operators === ['equals'] && count($nextIds) === 1 && $question->options->isNotEmpty()) {
                $coveredValues = array_values(array_unique(array_map(fn ($r) => (string) ($r['value'] ?? ''), $groupRules)));
                $optionValues = $question->options->pluck('value')->values()->all();
                sort($coveredValues);
                sort($optionValues);

                if ($coveredValues === $optionValues) {
                    $qNum = $question->order_index + 1;
                    $normalizationNotes[] = "Removed redundant equals rules on Q{$qNum}: all options routed to the same next question.";
                    continue;
                }
            }

            // Safe auto-fix: if a radio/select has equals rules for every option value, there is no default path and intermediate
            // questions can become unreachable. Drop one equals rule so one option follows order_index by default.
            if (in_array($question->type, ['radio', 'select'], true) && $operators === ['equals'] && $question->options->isNotEmpty()) {
                $optionValues = $question->options->pluck('value')->values()->all();
                $equalsValues = array_values(array_unique(array_map(fn ($r) => (string) ($r['value'] ?? ''), $groupRules)));

                $sortedOptionValues = $optionValues;
                $sortedEqualsValues = $equalsValues;
                sort($sortedOptionValues);
                sort($sortedEqualsValues);

                if ($sortedEqualsValues === $sortedOptionValues) {
                    $defaultNextId = $defaultNext[$questionId] ?? null;
                    $dropIndex = null;

                    if ($defaultNextId) {
                        foreach ($groupRules as $idx => $r) {
                            if (($r['operator'] ?? '') === 'equals' && (int) ($r['next_question_id'] ?? 0) === (int) $defaultNextId) {
                                $dropIndex = $idx;
                                break;
                            }
                        }
                    }

                    if ($dropIndex === null) {
                        $firstOptionValue = $optionValues[0] ?? null;
                        if ($firstOptionValue !== null) {
                            foreach ($groupRules as $idx => $r) {
                                if (($r['operator'] ?? '') === 'equals' && (string) ($r['value'] ?? '') === (string) $firstOptionValue) {
                                    $dropIndex = $idx;
                                    break;
                                }
                            }
                        }
                    }

                    if ($dropIndex !== null) {
                        $qNum = $question->order_index + 1;
                        $droppedValue = (string) ($groupRules[$dropIndex]['value'] ?? '');
                        $normalizationNotes[] = "Adjusted Q{$qNum}: removed one equals rule for value \"{$droppedValue}\" to preserve a default path (prevents unreachable questions).";
                        array_splice($groupRules, $dropIndex, 1);
                    }
                }
            }

            foreach ($groupRules as $r) {
                $normalized[] = $r;
            }
        }

        return [$normalized, $normalizationNotes, $skippedNotes];
    }

    private function validateWiring($questions, array $rules): array
    {
        $issues = [];

        if ($questions->isEmpty()) {
            return ['Form has no questions.'];
        }

        $questionsById = $questions->keyBy('id');
        $orderedIds = $questions->pluck('id')->values()->all();

        // Disallow loops/back-jumps: rules must jump forward in order_index.
        foreach ($rules as $idx => $rule) {
            $qid = (int) ($rule['question_id'] ?? 0);
            $nextQid = (int) ($rule['next_question_id'] ?? 0);
            $q = $questionsById->get($qid);
            $nq = $questionsById->get($nextQid);
            if (!$q || !$nq) {
                continue;
            }
            if ((int) $nq->order_index <= (int) $q->order_index) {
                $qNum = $q->order_index + 1;
                $nNum = $nq->order_index + 1;
                $op = (string) ($rule['operator'] ?? '');
                $val = (string) ($rule['value'] ?? '');
                return [
                    "Invalid jump on Q{$qNum} (rule index {$idx}): next_question_id points to Q{$nNum}, but rules must jump forward to a later question (prevents loops). Operator={$op}, value=\"{$val}\".",
                ];
            }
        }

        $defaultNext = [];
        for ($i = 0; $i < count($orderedIds); $i++) {
            $defaultNext[$orderedIds[$i]] = $orderedIds[$i + 1] ?? null;
        }

        $rulesByQuestion = [];
        foreach ($rules as $rule) {
            $qid = (int) ($rule['question_id'] ?? 0);
            $rulesByQuestion[$qid] ??= [];
            $rulesByQuestion[$qid][] = $rule;
        }

        $adj = [];
        foreach ($orderedIds as $qid) {
            $adj[$qid] = [];
            $q = $questionsById->get($qid);
            $dn = $defaultNext[$qid] ?? null;

            $qRules = $rulesByQuestion[$qid] ?? [];
            if ($qRules === []) {
                if ($dn) $adj[$qid][] = $dn;
                continue;
            }

            $hasAlways = false;
            foreach ($qRules as $r) {
                if (($r['operator'] ?? '') === 'always') {
                    $hasAlways = true;
                    $adj[$qid][] = (int) $r['next_question_id'];
                    break;
                }
            }

            if ($hasAlways) {
                continue;
            }

            foreach ($qRules as $r) {
                $adj[$qid][] = (int) $r['next_question_id'];
            }

            // Default path only exists if there is a possible "no match" scenario.
            $includeDefault = true;
            if (in_array($q->type, ['radio', 'select'], true) && $q->options->isNotEmpty()) {
                $optionValues = $q->options->pluck('value')->values()->all();
                $equalsValues = [];
                foreach ($qRules as $r) {
                    if (($r['operator'] ?? '') === 'equals') {
                        $equalsValues[] = (string) ($r['value'] ?? '');
                    }
                }
                $equalsValues = array_values(array_unique($equalsValues));
                sort($equalsValues);
                $sortedOptionValues = $optionValues;
                sort($sortedOptionValues);
                if ($equalsValues === $sortedOptionValues) {
                    $includeDefault = false;
                }
            }

            if ($includeDefault && $dn) {
                $adj[$qid][] = $dn;
            }

            $adj[$qid] = array_values(array_unique(array_filter($adj[$qid])));
        }

        // Reachability from Q1 through ANY possible path.
        $startId = $orderedIds[0];
        $visited = [];
        $queue = [$startId];
        while ($queue !== []) {
            $current = array_shift($queue);
            if (isset($visited[$current])) continue;
            $visited[$current] = true;
            foreach ($adj[$current] ?? [] as $nxt) {
                if ($nxt && !isset($visited[$nxt])) {
                    $queue[] = $nxt;
                }
            }
        }

        $unreachable = [];
        foreach ($orderedIds as $qid) {
            if (!isset($visited[$qid])) {
                $unreachable[] = $qid;
            }
        }

        if ($unreachable !== []) {
            $details = [];
            foreach ($unreachable as $qid) {
                $q = $questionsById->get($qid);
                $num = $q ? ($q->order_index + 1) : '?';
                $text = $q ? mb_substr($q->question_text, 0, 60) : '';
                $details[] = "Q{$num} (ID:{$qid}) \"{$text}\"";
            }
            $issues[] = 'Unreachable question(s): '.implode('; ', $details).'.';
            $issues[] = 'This usually means a branch question has equals rules for every option (no default path) and jumps over the next question(s) in order.';
        }

        return $issues;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'form_id' => $schema->integer()->description('The ID of the form to add logic rules to.')->required(),
            'rules' => $schema->array()->description('Array of logic rules to create.')->items(
                $schema->object([
                    'question_id' => $schema->integer()->description('The ID of the question this rule is attached to (evaluated after this question is answered).')->required(),
                    'operator' => $schema->string()->enum([
                        'equals', 'not_equals', 'is', 'is_not',
                        'greater_than', 'greater_equal_than',
                        'less_than', 'less_equal_than',
                        'lower_than', 'lower_equal_than',
                        'contains', 'not_contains',
                        'begins_with', 'ends_with',
                        'always',
                    ])->description('The comparison operator.')->required(),
                    'value' => $schema->string()->description('The value to compare against. Use option VALUES not labels. Empty string for "always" operator.'),
                    'next_question_id' => $schema->integer()->description('The ID of the question to jump to if the rule matches.')->required(),
                ])
            )->required(),
        ];
    }
}

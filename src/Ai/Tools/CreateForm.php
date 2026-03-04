<?php

namespace Logicoforms\Forms\Ai\Tools;

use Logicoforms\Forms\Ai\ToolStatusReporter;
use Logicoforms\Forms\Models\Form;
use Logicoforms\Forms\Models\FormQuestion;
use Logicoforms\Forms\Models\QuestionLogic;
use Logicoforms\Forms\Models\QuestionOption;
use Logicoforms\Forms\Support\OptionValue;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CreateForm implements Tool
{
    public function description(): Stringable|string
    {
        return 'Create a complete form with questions, options, and conditional logic in a single operation. Logic can target questions by 0-based index (next) or by stable question key (next_key). Choice-rule values should use option labels (auto-slugged to values). Returns form summary with edit URL.';
    }

    public function handle(Request $request): Stringable|string
    {
        $title = trim((string) ($request['title'] ?? ''));
        $description = isset($request['description']) ? trim((string) $request['description']) : null;
        $questions = $request['questions'] ?? [];

        Log::info('[CreateForm] RAW INPUT', ['title' => $title, 'questions' => $questions]);

        // Phase 0: Pre-process — auto-generate keys, normalize options
        $questions = $this->preProcess($questions);

        // Phase 1: Structural validation (critical issues only — no logic checks)
        $issues = $this->validateStructure($title, $questions);
        if ($issues !== []) {
            app(ToolStatusReporter::class)->report('Refining the form…');

            return $this->formatError($issues, 'Fix and call CreateForm again.');
        }

        // Phase 2: Resolve next targets (keys → indexes) and normalize logic rules
        [$questions, $resolveNotes, $missingKeys] = $this->resolveNextReferences($questions);

        // Phase 2.1: If logic rules reference questions that don't exist, send back to AI
        if ($missingKeys !== []) {
            $keyList = implode(', ', array_map(fn ($k) => "\"{$k}\"", array_keys($missingKeys)));
            app(ToolStatusReporter::class)->report('Fixing missing questions…');

            return $this->formatError(
                ["Logic rules reference question keys that don't exist in your questions array: {$keyList}. You must INCLUDE these questions (e.g. as endpoint/statement screens) in the questions array so the logic can jump to them."],
                "Add the missing endpoint questions to your questions array and call CreateForm again. Every next_key target must be a real question with a matching key."
            );
        }

        [$questions, $normNotes] = $this->normalizeLogic($questions);
        $normNotes = array_merge($resolveNotes, $normNotes);

        // Phase 2.5: Repair common wiring mistakes (drop invalid jumps / overly-skippy logic)
        [$questions, $repairNotes] = $this->repairWiring($questions);
        $normNotes = array_merge($normNotes, $repairNotes);

        // Phase 2.6: Auto-fix dead-end endpoints that would flow into another endpoint.
        // Detects the dual-endpoint pattern (e.g. "Congratulations!" followed by "Not a fit")
        // and adds terminal rules so each endpoint goes to Done independently.
        $questions = $this->autoFixDeadEndEndpoints($questions, $normNotes);

        Log::info('[CreateForm] AFTER PROCESSING', ['questions' => $questions, 'notes' => $normNotes]);

        // Phase 3: Validate wiring (reachability via BFS)
        $wiringIssues = $this->validateWiring($questions);
        if ($wiringIssues !== []) {
            app(ToolStatusReporter::class)->report('Checking the logic…');

            return $this->formatError(
                array_merge($normNotes, $wiringIssues),
                'Fix and call CreateForm again. Tip: leave one option as default path (no rule), don\'t cover every option with equals.'
            );
        }

        // Phase 4: Quality validation (reject lazy branching patterns)
        $qualityIssues = $this->validateQuality($questions);
        $enforceQuality = (bool) config('forms.ai_builder.enforce_quality', false);
        if ($qualityIssues !== [] && $enforceQuality) {
            app(ToolStatusReporter::class)->report('Improving the flow…');

            return $this->formatError(
                $qualityIssues,
                'Redesign the branching structure and call CreateForm again. Real branches route different answers to DIFFERENT multi-question paths.'
            );
        }
        if ($qualityIssues !== [] && ! $enforceQuality) {
            $normNotes[] = 'Quality warning: ' . $qualityIssues[0];
        }

        $questionCount = count($questions);
        app(ToolStatusReporter::class)->report('Building your form…');

        // Phase 4: Atomic save
        return DB::transaction(function () use ($title, $description, $questions, $normNotes) {
            $form = Form::create([
                'title' => $title,
                'description' => $description,
                'status' => 'draft',
            ]);

            // Save questions and collect DB IDs keyed by index
            $dbIds = [];
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

                // Save options for choice types
                if (in_array($type, ['radio', 'select', 'checkbox', 'picture_choice']) && !empty($qData['options'])) {
                    foreach ($qData['options'] as $optIndex => $optLabel) {
                        $label = is_array($optLabel) ? ($optLabel['label'] ?? $optLabel) : $optLabel;
                        $imageUrl = is_array($optLabel) ? ($optLabel['image_url'] ?? null) : null;
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'label' => $label,
                            'value' => OptionValue::fromLabel($label),
                            'image_url' => $imageUrl,
                            'order_index' => $optIndex,
                        ]);
                    }
                }

                $dbIds[$index] = $question->id;
            }

            // Save logic rules, converting indexes to DB IDs
            $logicCount = 0;
            foreach ($questions as $index => $qData) {
                $rules = $qData['logic'] ?? [];
                foreach ($rules as $rule) {
                    $nextIndex = $rule['next'] ?? null;
                    $nextDbId = ($nextIndex !== null && isset($dbIds[$nextIndex])) ? $dbIds[$nextIndex] : null;
                    QuestionLogic::create([
                        'form_id' => $form->id,
                        'question_id' => $dbIds[$index],
                        'operator' => $rule['operator'],
                        'value' => $rule['value'] ?? '',
                        'next_question_id' => $nextDbId,
                    ]);
                    $logicCount++;
                }
            }

            // Build output
            $editUrl = route('forms.edit', $form);
            $output = "Form created: \"{$form->title}\" (ID: {$form->id})\n";
            $output .= "Questions: " . count($questions) . ", Logic rules: {$logicCount}\n";
            $output .= "Edit URL: {$editUrl}\n";
            if ($normNotes !== []) {
                $output .= "Notes: " . implode('; ', array_slice($normNotes, 0, 3)) . "\n";
            }
            $output .= "\nTo publish this form, call PublishForm with form_id: {$form->id}. Do NOT call CreateForm again.";

            return $output;
        });
    }

    /**
     * Pre-process questions: auto-generate keys, normalize options format,
     * and update next_key references to match normalized keys.
     */
    public function preProcess(array $questions): array
    {
        $usedKeys = [];
        $keyRenames = []; // original → normalized

        // Pass 1: normalize keys and options
        foreach ($questions as $index => &$qData) {
            // Normalize options: if items are objects with a "label" key, extract the string
            if (isset($qData['options']) && is_array($qData['options'])) {
                $qData['options'] = array_map(function ($opt) {
                    if (is_array($opt) && isset($opt['label'])) {
                        return (string) $opt['label'];
                    }
                    return $opt;
                }, $qData['options']);
            }

            // Strip options from non-choice types
            $type = $qData['type'] ?? 'text';
            if (in_array($type, ['text', 'email', 'number', 'rating', 'opinion_scale'], true) && isset($qData['options'])) {
                unset($qData['options']);
            }

            // Normalize or auto-generate keys
            $originalKey = trim((string) ($qData['key'] ?? ''));
            $key = $originalKey;
            if ($key === '' || ! preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_-]{0,63}$/', $key)) {
                $base = $key !== '' ? $key : trim((string) ($qData['question_text'] ?? "q{$index}"));
                $key = Str::slug(Str::limit($base, 40, ''), '-');
                if ($key === '' || ! preg_match('/^[a-zA-Z]/', $key)) {
                    $key = "q{$index}";
                }
            }

            // Ensure uniqueness
            $base = $key;
            $suffix = 2;
            while (in_array($key, $usedKeys, true)) {
                $key = "{$base}-{$suffix}";
                $suffix++;
            }

            // Track renames for updating next_key references
            if ($originalKey !== '' && $originalKey !== $key) {
                $keyRenames[$originalKey] = $key;
            }

            $qData['key'] = $key;
            $usedKeys[] = $key;
        }
        unset($qData);

        // Pass 2: update next_key references to use normalized keys
        if ($keyRenames !== []) {
            foreach ($questions as &$qData) {
                $rules = $qData['logic'] ?? [];
                if (! is_array($rules) || $rules === []) {
                    continue;
                }
                foreach ($rules as &$r) {
                    if (isset($r['next_key']) && isset($keyRenames[$r['next_key']])) {
                        $r['next_key'] = $keyRenames[$r['next_key']];
                    }
                }
                unset($r);
                $qData['logic'] = $rules;
            }
            unset($qData);
        }

        return $questions;
    }

    /**
     * Validate the structural integrity of the form draft.
     *
     * @return array<int, string> List of issues (empty = valid)
     */
    public function validateStructure(string $title, array $questions): array
    {
        $issues = [];

        if ($title === '') {
            $issues[] = 'Missing required `title`.';
        }

        if (count($questions) === 0) {
            $issues[] = 'No questions provided.';
            return $issues;
        }

        $questionCount = count($questions);

        // Collect stable question keys (optional, but required if using next_key)
        $keyToIndex = [];
        foreach ($questions as $index => $qData) {
            if (! array_key_exists('key', $qData)) {
                continue;
            }

            $num = $index + 1;
            $key = trim((string) ($qData['key'] ?? ''));
            if ($key === '') {
                $issues[] = "Question {$num} has an empty `key` (remove it or set a non-empty string).";
                continue;
            }

            if (! preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_-]{0,63}$/', $key)) {
                $issues[] = "Question {$num} has invalid `key` \"{$key}\". Use 1-64 chars: letters, numbers, `_`, `-` (must start with a letter/number).";
                continue;
            }

            if (isset($keyToIndex[$key])) {
                $dupNum = $keyToIndex[$key] + 1;
                $issues[] = "Duplicate question key \"{$key}\" on Q{$dupNum} and Q{$num}. Keys must be unique.";
                continue;
            }

            $keyToIndex[$key] = $index;
        }

        foreach ($questions as $index => $qData) {
            $type = $qData['type'] ?? null;
            $questionText = trim((string) ($qData['question_text'] ?? ''));
            $num = $index + 1;

            if ($questionText === '') {
                $issues[] = "Question {$num} is missing `question_text`.";
            }

            if (in_array($type, ['radio', 'select', 'checkbox', 'picture_choice'], true)) {
                $options = $qData['options'] ?? [];
                if (!is_array($options) || count($options) < 2) {
                    $issues[] = "Question {$num} is type `{$type}` but has fewer than 2 options.";
                }
            }

            // Logic rule issues are handled gracefully in resolve/normalize/repair phases — NOT here.
        }

        return $issues;
    }

    /**
     * Resolve next targets by converting next_key → next index.
     * Uses case-insensitive + slug-normalized matching. Drops unresolvable rules.
     *
     * @return array{0: array, 1: array<int, string>, 2: array<string, int>} [questions, notes, missingKeys]
     */
    public function resolveNextReferences(array $questions): array
    {
        $notes = [];
        $missingKeys = []; // key => count of rules that referenced it
        $questionCount = count($questions);

        // Build key→index maps: exact, lowercase, and slug-normalized
        $keyToIndex = [];
        $lowerKeyToIndex = [];
        $slugKeyToIndex = [];
        foreach ($questions as $index => $qData) {
            $key = trim((string) ($qData['key'] ?? ''));
            if ($key !== '') {
                $keyToIndex[$key] = $index;
                $lowerKeyToIndex[mb_strtolower($key)] = $index;
                $slugKeyToIndex[Str::slug($key, '-')] = $index;
                $slugKeyToIndex[Str::slug($key, '_')] = $index;
            }
        }

        foreach ($questions as $index => &$qData) {
            $num = $index + 1;
            $rules = $qData['logic'] ?? [];
            if (! is_array($rules) || $rules === []) {
                continue;
            }

            $kept = [];
            foreach ($rules as $rIdx => $r) {
                if (! is_array($r)) {
                    continue;
                }

                // Detect terminal intent from next_key "end"/"done" keywords
                $nextKey = isset($r['next_key']) ? trim((string) $r['next_key']) : '';
                if (in_array(mb_strtolower($nextKey), ['end', 'done', 'end-form', 'finish'], true)) {
                    $r['next'] = null;
                    unset($r['next_key'], $r['end']);
                    $kept[] = $r;
                    continue;
                }

                // Handle "end": true — but only as terminal if there's NO resolvable jump target.
                // AI often sends end:true TOGETHER with next_key pointing to an endpoint question
                // (meaning "jump there, then end"). In that case, prefer the jump target.
                if (! empty($r['end'])) {
                    $endTargetKey = $nextKey;
                    $resolvedTarget = null;
                    if ($endTargetKey !== '') {
                        $resolvedTarget = $keyToIndex[$endTargetKey]
                            ?? $lowerKeyToIndex[mb_strtolower($endTargetKey)]
                            ?? $slugKeyToIndex[Str::slug($endTargetKey, '-')]
                            ?? $slugKeyToIndex[Str::slug($endTargetKey, '_')]
                            ?? null;
                    }

                    if ($resolvedTarget !== null) {
                        // Has a real jump target — use it, strip end flag
                        $r['next'] = $resolvedTarget;
                        unset($r['next_key'], $r['end']);
                        $kept[] = $r;
                        continue;
                    }

                    // No resolvable target — treat as terminal (end form immediately)
                    $r['next'] = null;
                    unset($r['next_key'], $r['end']);
                    $kept[] = $r;
                    continue;
                }

                // Normalize numeric next
                if (isset($r['next']) && (is_numeric($r['next']) || is_int($r['next']))) {
                    $r['next'] = (int) $r['next'];
                }

                if ($nextKey !== '') {
                    // Try exact match, then case-insensitive, then slug-normalized
                    $resolved = $keyToIndex[$nextKey]
                        ?? $lowerKeyToIndex[mb_strtolower($nextKey)]
                        ?? $slugKeyToIndex[Str::slug($nextKey, '-')]
                        ?? $slugKeyToIndex[Str::slug($nextKey, '_')]
                        ?? null;

                    if ($resolved !== null) {
                        $r['next'] = $resolved;
                        unset($r['next_key']);
                    } elseif (! isset($r['next'])) {
                        // Can't resolve and no numeric fallback — track missing key
                        $missingKeys[$nextKey] = ($missingKeys[$nextKey] ?? 0) + 1;
                        $notes[] = "Dropped Q{$num} rule {$rIdx}: unresolvable next_key \"{$nextKey}\".";
                        continue;
                    } else {
                        // Has numeric next but next_key didn't resolve — track as missing
                        $missingKeys[$nextKey] = ($missingKeys[$nextKey] ?? 0) + 1;
                        unset($r['next_key']);
                    }
                }

                // Validate numeric next is in range
                $next = $r['next'] ?? null;
                if ($next === null || (! is_int($next) && ! is_numeric($next))) {
                    $notes[] = "Dropped Q{$num} rule {$rIdx}: no valid target.";
                    continue;
                }

                $r['next'] = (int) $r['next'];
                if ($r['next'] < 0 || $r['next'] >= $questionCount) {
                    $notes[] = "Dropped Q{$num} rule {$rIdx}: next={$r['next']} out of range.";
                    continue;
                }

                $kept[] = $r;
            }

            $qData['logic'] = $kept;
        }
        unset($qData);

        return [$questions, $notes, $missingKeys];
    }

    /**
     * Best-effort repair to reduce repeated validation retries.
     * - Drop any logic rule that creates a loop/back-jump (next <= current).
     * - If the graph is still invalid due to unreachable questions, try removing `always` rules first.
     * - If still invalid, drop all logic (fallback to linear question order).
     *
     * @return array{0: array, 1: array<int, string>} [questions, notes]
     */
    public function repairWiring(array $questions): array
    {
        $notes = [];

        // 1) Drop invalid jumps (self/back). Terminal rules (next=null) are always allowed.
        foreach ($questions as $i => &$qData) {
            $rules = $qData['logic'] ?? [];
            if (! is_array($rules) || $rules === []) {
                continue;
            }

            $num = $i + 1;
            $kept = [];
            foreach ($rules as $rIdx => $r) {
                $next = $r['next'] ?? null;
                if ($next === null) {
                    $kept[] = $r; // Terminal rule (end form) — always valid
                    continue;
                }
                $next = (int) $next;
                if ($next <= $i) {
                    $op = (string) ($r['operator'] ?? '');
                    $val = (string) ($r['value'] ?? '');
                    $notes[] = "Dropped invalid jump on Q{$num}, rule {$rIdx}: next={$next} (must be forward-only). Operator={$op}, value=\"{$val}\".";
                    continue;
                }
                $kept[] = $r;
            }

            $qData['logic'] = $kept;
        }
        unset($qData);

        // 2) If still invalid due to unreachable questions, try removing ALWAYS rules (common culprit).
        $issues = $this->validateWiring($questions);
        if ($issues === []) {
            return [$questions, $notes];
        }

        if (str_contains($issues[0] ?? '', 'Unreachable question')) {
            foreach ($questions as $i => &$qData) {
                $rules = $qData['logic'] ?? [];
                if (! is_array($rules) || $rules === []) {
                    continue;
                }

                $num = $i + 1;
                $before = count($rules);
                // Remove non-terminal always rules (terminal always rules must be preserved)
                $rules = array_values(array_filter($rules, fn ($r) =>
                    ($r['operator'] ?? '') !== 'always' || ($r['next'] ?? null) === null
                ));
                if (count($rules) !== $before) {
                    $notes[] = "Removed always rule(s) on Q{$num} to restore reachability.";
                }
                $qData['logic'] = $rules;
            }
            unset($qData);
        }

        $issues = $this->validateWiring($questions);
        if ($issues === []) {
            return [$questions, $notes];
        }

        // 3) Targeted removal: drop specific rules that skip over unreachable questions.
        $questionCount = count($questions);
        $unreachableIndexes = $this->findUnreachableIndexes($questions);
        if ($unreachableIndexes !== []) {
            foreach ($unreachableIndexes as $uIdx) {
                for ($j = 0; $j < $uIdx; $j++) {
                    $rules = $questions[$j]['logic'] ?? [];
                    if (empty($rules)) {
                        continue;
                    }

                    $filtered = [];
                    $dropped = false;
                    foreach ($rules as $r) {
                        $next = (int) ($r['next'] ?? -1);
                        if ($next > $uIdx && ($r['operator'] ?? '') === 'equals') {
                            $num = $j + 1;
                            $val = (string) ($r['value'] ?? '');
                            $notes[] = "Dropped equals rule on Q{$num} (value=\"{$val}\", next={$next}) that skipped over unreachable Q" . ($uIdx + 1) . ".";
                            $dropped = true;
                            continue;
                        }
                        $filtered[] = $r;
                    }

                    if ($dropped) {
                        $questions[$j]['logic'] = $filtered;
                    }
                }
            }

            $issues = $this->validateWiring($questions);
            if ($issues === []) {
                return [$questions, $notes];
            }
        }

        // 4) Last resort: drop all logic rules. This guarantees a valid linear flow.
        $droppedCount = 0;
        foreach ($questions as &$qData) {
            if (! empty($qData['logic'])) {
                $droppedCount += is_array($qData['logic']) ? count($qData['logic']) : 1;
            }
            $qData['logic'] = [];
        }
        unset($qData);

        if ($droppedCount > 0) {
            $notes[] = "Dropped {$droppedCount} logic rule(s) as a fallback to ensure a valid form flow. You can re-add branching in the editor.";
        }

        return [$questions, $notes];
    }

    /**
     * Normalize logic rules: auto-slug values, remove redundant rules.
     *
     * @return array{0: array, 1: array<int, string>} [normalized questions, notes]
     */
    public function normalizeLogic(array $questions): array
    {
        $notes = [];
        $questionCount = count($questions);

        foreach ($questions as $index => &$qData) {
            $rules = $qData['logic'] ?? [];
            if (!is_array($rules) || $rules === []) {
                continue;
            }

            $type = $qData['type'] ?? 'text';
            $num = $index + 1;
            $defaultNext = ($index + 1 < $questionCount) ? $index + 1 : null;

            // Build option values and label → value map for choice questions
            $optionValues = [];
            $labelToValue = [];
            if (in_array($type, ['radio', 'select', 'checkbox', 'picture_choice'], true) && !empty($qData['options'])) {
                foreach ($qData['options'] as $opt) {
                    $label = is_array($opt) ? ($opt['label'] ?? $opt) : $opt;
                    $value = OptionValue::fromLabel((string) $label);
                    $optionValues[] = $value;
                    $labelToValue[mb_strtolower(trim((string) $label))] = $value;
                }
            }

            // Remove redundant always rules that point to natural next (but never strip terminal rules)
            $cleaned = [];
            foreach ($rules as $r) {
                if (($r['operator'] ?? '') === 'always' && $r['next'] !== null && $defaultNext !== null && (int) $r['next'] === $defaultNext) {
                    $notes[] = "Removed redundant always rule on Q{$num}: it already flows to the next question by order.";
                    continue;
                }
                $cleaned[] = $r;
            }
            $rules = $cleaned;

            // Normalize equals values for choice questions (accept label or value; normalize to actual option value)
            if ($optionValues !== []) {
                $optionSet = array_fill_keys($optionValues, true);
                $cleaned = [];
                foreach ($rules as $r) {
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
                        $normalized = $labelToValue[$rawLower];
                        $notes[] = "Normalized Q{$num} rule value \"{$rawValue}\" → \"{$normalized}\" (matched option label).";
                        $r['value'] = $normalized;
                        $cleaned[] = $r;
                        continue;
                    }

                    $slugValue = OptionValue::fromLabel($rawValue);
                    if (isset($optionSet[$slugValue])) {
                        $notes[] = "Normalized Q{$num} rule value \"{$rawValue}\" → \"{$slugValue}\" (slug).";
                        $r['value'] = $slugValue;
                        $cleaned[] = $r;
                        continue;
                    }

                    $notes[] = "Dropped invalid equals rule on Q{$num}: value \"{$rawValue}\" is not a valid option.";
                }
                $rules = $cleaned;
            }

            // Detect fake branching: all equals rules route to the same target
            $operators = array_values(array_unique(array_map(fn ($r) => (string) ($r['operator'] ?? ''), $rules)));
            $nextIndexes = array_values(array_unique(array_map(
                fn ($r) => $r['next'] === null ? null : (int) $r['next'],
                $rules
            )));
            $nextIndexes = array_values(array_filter($nextIndexes, fn ($n) => $n !== null));

            if (in_array($type, ['radio', 'select'], true) && $operators === ['equals'] && count($nextIndexes) === 1 && $optionValues !== []) {
                $coveredValues = array_values(array_unique(array_map(fn ($r) => (string) ($r['value'] ?? ''), $rules)));
                sort($coveredValues);
                $sortedOptionValues = $optionValues;
                sort($sortedOptionValues);

                if ($coveredValues === $sortedOptionValues) {
                    $notes[] = "Removed redundant equals rules on Q{$num}: all options routed to the same next question.";
                    $qData['logic'] = [];
                    continue;
                }
            }

            // Preserve default path: if equals covers all options, drop one rule.
            // Skip this optimization if any rule is a terminal (end form) — those have
            // distinct routing semantics that must be preserved.
            $hasTerminalRule = false;
            foreach ($rules as $r) {
                if (($r['next'] ?? null) === null) {
                    $hasTerminalRule = true;
                    break;
                }
            }

            if (! $hasTerminalRule && in_array($type, ['radio', 'select'], true) && $operators === ['equals'] && $optionValues !== []) {
                $equalsValues = array_values(array_unique(array_map(fn ($r) => (string) ($r['value'] ?? ''), $rules)));
                $sortedEquals = $equalsValues;
                $sortedOpts = $optionValues;
                sort($sortedEquals);
                sort($sortedOpts);

                if ($sortedEquals === $sortedOpts) {
                    $dropIndex = null;

                    // Prefer dropping the rule that points to the natural next question
                    if ($defaultNext !== null) {
                        foreach ($rules as $idx => $r) {
                            if ((int) $r['next'] === $defaultNext) {
                                $dropIndex = $idx;
                                break;
                            }
                        }
                    }

                    // Fallback: drop the first rule
                    if ($dropIndex === null) {
                        $dropIndex = 0;
                    }

                    $droppedValue = (string) ($rules[$dropIndex]['value'] ?? '');
                    $notes[] = "Adjusted Q{$num}: removed one equals rule for \"{$droppedValue}\" to preserve a default path.";
                    array_splice($rules, $dropIndex, 1);
                }
            }

            // Ensure "always" is last and only appears once (first match wins)
            $alwaysRules = array_values(array_filter($rules, fn ($r) => ($r['operator'] ?? '') === 'always'));
            if ($alwaysRules !== []) {
                $always = $alwaysRules[count($alwaysRules) - 1];
                if (count($alwaysRules) > 1) {
                    $notes[] = "Dropped extra always rule(s) on Q{$num}: only one always rule is allowed.";
                }
                $always['value'] = '';
                $rules = array_values(array_filter($rules, fn ($r) => ($r['operator'] ?? '') !== 'always'));
                $rules[] = $always;
            }

            $qData['logic'] = $rules;
        }
        unset($qData);

        return [$questions, $notes];
    }

    /**
     * Validate that all questions are reachable via BFS from Q0.
     *
     * @return array<int, string> List of issues (empty = valid)
     */
    public function validateWiring(array $questions): array
    {
        $questionCount = count($questions);
        if ($questionCount === 0) {
            return ['Form has no questions.'];
        }

        // Disallow cycles/back-jumps: rules must always jump forward (next > current).
        foreach ($questions as $i => $qData) {
            $num = $i + 1;
            $rules = $qData['logic'] ?? [];
            if (!is_array($rules) || $rules === []) {
                continue;
            }

            foreach ($rules as $rIdx => $r) {
                $next = $r['next'] ?? null;
                if ($next === null) {
                    continue; // Terminal rule (end form) — allowed
                }
                $next = (int) $next;
                if ($next <= $i) {
                    $op = (string) ($r['operator'] ?? '');
                    $val = (string) ($r['value'] ?? '');
                    return [
                        "Invalid jump on Q{$num}, rule {$rIdx}: next={$next} must be a later question (next > current). Operator={$op}, value=\"{$val}\". Self/back jumps create loops and break the respondent flow.",
                        'Fix: change the rule to jump forward (e.g., to the start of the common ending section).',
                    ];
                }
            }
        }

        // Build adjacency list using indexes
        $adj = [];
        for ($i = 0; $i < $questionCount; $i++) {
            $adj[$i] = [];
            $qData = $questions[$i];
            $rules = $qData['logic'] ?? [];
            $type = $qData['type'] ?? 'text';
            $defaultNext = ($i + 1 < $questionCount) ? $i + 1 : null;

            if ($rules === []) {
                if ($defaultNext !== null) {
                    $adj[$i][] = $defaultNext;
                }
                continue;
            }

            $hasAlways = false;
            foreach ($rules as $r) {
                if (($r['operator'] ?? '') === 'always') {
                    $hasAlways = true;
                    if ($r['next'] !== null) {
                        $adj[$i][] = (int) $r['next'];
                    }
                    // null next = terminal (end form) — no edge to add
                    break;
                }
            }

            if ($hasAlways) {
                continue;
            }

            foreach ($rules as $r) {
                if (($r['next'] ?? null) !== null) {
                    $adj[$i][] = (int) $r['next'];
                }
            }

            // Default path exists if not all options are covered by equals rules
            $includeDefault = true;
            if (in_array($type, ['radio', 'select'], true)) {
                $optionValues = [];
                if (!empty($qData['options'])) {
                    foreach ($qData['options'] as $opt) {
                        $label = is_array($opt) ? ($opt['label'] ?? $opt) : $opt;
                        $optionValues[] = OptionValue::fromLabel((string) $label);
                    }
                }

                if ($optionValues !== []) {
                    $equalsValues = [];
                    foreach ($rules as $r) {
                        if (($r['operator'] ?? '') === 'equals') {
                            $equalsValues[] = (string) ($r['value'] ?? '');
                        }
                    }
                    $equalsValues = array_values(array_unique($equalsValues));
                    sort($equalsValues);
                    $sortedOpts = $optionValues;
                    sort($sortedOpts);
                    if ($equalsValues === $sortedOpts) {
                        $includeDefault = false;
                    }
                }
            }

            if ($includeDefault && $defaultNext !== null) {
                $adj[$i][] = $defaultNext;
            }

            $adj[$i] = array_values(array_unique($adj[$i]));
        }

        // BFS from index 0
        $visited = [];
        $queue = [0];
        while ($queue !== []) {
            $current = array_shift($queue);
            if (isset($visited[$current])) {
                continue;
            }
            $visited[$current] = true;
            foreach ($adj[$current] ?? [] as $nxt) {
                if (!isset($visited[$nxt])) {
                    $queue[] = $nxt;
                }
            }
        }

        $unreachable = [];
        for ($i = 0; $i < $questionCount; $i++) {
            if (!isset($visited[$i])) {
                $unreachable[] = $i;
            }
        }

        if ($unreachable !== []) {
            $details = [];
            foreach ($unreachable as $idx) {
                $num = $idx + 1;
                $text = mb_substr($questions[$idx]['question_text'] ?? '', 0, 60);
                $details[] = "Q{$num} \"{$text}\"";
            }
            return [
                'Unreachable question(s): ' . implode('; ', $details) . '.',
                'This usually means a branch question has equals rules for every option (no default path) and jumps over the next question(s) in order.',
            ];
        }

        return [];
    }

    /**
     * Validate the quality of branching logic (reject lazy patterns).
     *
     * @return array<int, string> List of issues (empty = valid)
     */
    public function validateQuality(array $questions): array
    {
        $issues = [];
        $questionCount = count($questions);

        // Skip quality checks for very small forms (simple feedback/contact)
        if ($questionCount < 8) {
            return [];
        }

        // === 1. Detect "Other → specify" as the only branching pattern ===
        $realBranchPoints = 0;
        $otherSpecifyCount = 0;

        foreach ($questions as $index => $qData) {
            $rules = $qData['logic'] ?? [];
            if (empty($rules)) {
                continue;
            }

            $type = $qData['type'] ?? 'text';
            if (! in_array($type, ['radio', 'select', 'checkbox', 'picture_choice'], true)) {
                continue;
            }

            $meaningfulDestinations = [];
            $hasOnlyOtherSpecify = true;

            foreach ($rules as $r) {
                if (($r['operator'] ?? '') === 'always') {
                    continue;
                }

                $val = OptionValue::fromLabel((string) ($r['value'] ?? ''));
                $next = (int) ($r['next'] ?? 0);

                // Is this an "Other → text specify" pattern?
                $isOther = in_array($val, ['other', 'others', 'other_please_specify', 'none_of_the_above'], true);
                $destQ = $questions[$next] ?? null;
                $destType = $destQ['type'] ?? '';
                $destText = mb_strtolower($destQ['question_text'] ?? '');
                $looksLikeSpecify = $destType === 'text' && (
                    str_contains($destText, 'specify') ||
                    str_contains($destText, 'describe') ||
                    str_contains($destText, 'please') ||
                    str_contains($destText, 'detail') ||
                    str_contains($destText, 'explain') ||
                    str_contains($destText, 'custom') ||
                    str_contains($destText, 'which one')
                );

                if ($isOther && $looksLikeSpecify) {
                    $otherSpecifyCount++;
                } else {
                    $hasOnlyOtherSpecify = false;
                    $meaningfulDestinations[$next] = true;
                }
            }

            if (! $hasOnlyOtherSpecify && count($meaningfulDestinations) >= 1) {
                $realBranchPoints++;
            }
        }

        if ($realBranchPoints === 0 && $otherSpecifyCount > 0) {
            $issues[] = "BRANCHING_TOO_SHALLOW: The form has {$otherSpecifyCount} \"Other → please specify\" jump(s) but ZERO real branch points. \"Other → specify\" is a text clarification, not a branch. A real branch routes different substantive answers (e.g. \"Website\" vs \"Branding\" vs \"Consulting\") to DIFFERENT multi-question paths (2-3 unique follow-ups each). Redesign: pick a key segmentation question and give each answer its own dedicated follow-up path.";
        }

        // === 2. Detect arbitrary single-value conditions ===
        foreach ($questions as $index => $qData) {
            $rules = $qData['logic'] ?? [];
            $type = $qData['type'] ?? 'text';
            $options = $qData['options'] ?? [];

            if (! in_array($type, ['radio', 'select'], true) || count($options) < 3) {
                continue;
            }

            $equalsRules = array_filter($rules, fn ($r) => ($r['operator'] ?? '') === 'equals');
            $alwaysRules = array_filter($rules, fn ($r) => ($r['operator'] ?? '') === 'always');

            // Only flag: 3+ options, exactly 1 equals rule, no always rule
            if (count($equalsRules) !== 1 || count($alwaysRules) > 0) {
                continue;
            }

            $rule = array_values($equalsRules)[0];
            $slugVal = OptionValue::fromLabel((string) ($rule['value'] ?? ''));

            // Skip "Other" — handled above
            if (in_array($slugVal, ['other', 'others'], true)) {
                continue;
            }

            $num = $index + 1;
            $optCount = count($options);
            $issues[] = "ARBITRARY_CONDITION on Q{$num}: This question has {$optCount} options but only \"{$rule['value']}\" triggers a follow-up. If the follow-up is relevant for one answer, it's probably relevant for all. Either: (a) remove the logic and ask the follow-up for everyone, or (b) branch MULTIPLE options to DIFFERENT follow-ups.";
        }

        return $issues;
    }

    /**
     * Auto-fix dead-end endpoint questions that would unintentionally flow into
     * another endpoint by default order. This handles the common "dual outcome"
     * pattern (e.g., a "Congratulations!" question followed by a "Not a fit" question)
     * where non-last endpoints need terminal rules to go to Done independently.
     */
    private function autoFixDeadEndEndpoints(array $questions, array &$notes): array
    {
        $count = count($questions);
        if ($count < 2) {
            return $questions;
        }

        // Pre-compute which indexes are jump targets (reached by logic rules)
        $jumpTargets = [];
        for ($j = 0; $j < $count; $j++) {
            foreach ($questions[$j]['logic'] ?? [] as $r) {
                $next = $r['next'] ?? null;
                if ($next !== null) {
                    $jumpTargets[(int) $next] = true;
                }
            }
        }

        for ($i = 0; $i < $count - 1; $i++) {
            $q = $questions[$i];
            $nextQ = $questions[$i + 1];

            // Only target non-required questions with no options and no logic (endpoint/statement screens)
            $isEndpoint = empty($q['options'])
                && empty($q['logic'])
                && ! ($q['is_required'] ?? false)
                && in_array($q['type'] ?? 'text', ['text', 'email', 'number'], true);

            if (! $isEndpoint) {
                continue;
            }

            // Check if the next question is also an endpoint-like question
            $nextIsEndpoint = empty($nextQ['options'])
                && ! ($nextQ['is_required'] ?? false)
                && in_array($nextQ['type'] ?? 'text', ['text', 'email', 'number'], true);

            if (! $nextIsEndpoint) {
                continue;
            }

            // Q[i+1] must be a jump target from some logic rule. This confirms the
            // dual-endpoint pattern: Q[i] and Q[i+1] are alternative endpoints reached
            // by different paths, not consecutive questions in the same branch path.
            if (empty($jumpTargets[$i + 1])) {
                continue;
            }

            // Auto-add terminal rule: this endpoint should end the form
            $questions[$i]['logic'] = [['operator' => 'always', 'value' => '', 'next' => null]];
            $num = $i + 1;
            $notes[] = "Auto-added terminal rule on Q{$num}: endpoint question now ends the form instead of flowing to the next question.";
        }

        return $questions;
    }

    private function formatError(array $issues, string $tip): string
    {
        $output = "VALIDATION_ERROR:\n";
        foreach (array_slice($issues, 0, 5) as $issue) {
            $output .= "- {$issue}\n";
        }
        $output .= $tip;

        return $output;
    }

    /**
     * Return indexes of questions not reachable from Q0 via BFS.
     */
    private function findUnreachableIndexes(array $questions): array
    {
        $questionCount = count($questions);
        if ($questionCount === 0) {
            return [];
        }

        $adj = [];
        for ($i = 0; $i < $questionCount; $i++) {
            $adj[$i] = [];
            $qData = $questions[$i];
            $rules = $qData['logic'] ?? [];
            $defaultNext = ($i + 1 < $questionCount) ? $i + 1 : null;

            if ($rules === []) {
                if ($defaultNext !== null) {
                    $adj[$i][] = $defaultNext;
                }
                continue;
            }

            $hasAlways = false;
            foreach ($rules as $r) {
                if (($r['operator'] ?? '') === 'always') {
                    $hasAlways = true;
                    if ($r['next'] !== null) {
                        $adj[$i][] = (int) $r['next'];
                    }
                    break;
                }
            }

            if ($hasAlways) {
                continue;
            }

            foreach ($rules as $r) {
                if (($r['next'] ?? null) !== null) {
                    $adj[$i][] = (int) $r['next'];
                }
            }

            if ($defaultNext !== null) {
                $adj[$i][] = $defaultNext;
            }

            $adj[$i] = array_values(array_unique($adj[$i]));
        }

        $visited = [];
        $queue = [0];
        while ($queue !== []) {
            $current = array_shift($queue);
            if (isset($visited[$current])) {
                continue;
            }
            $visited[$current] = true;
            foreach ($adj[$current] ?? [] as $nxt) {
                if (!isset($visited[$nxt])) {
                    $queue[] = $nxt;
                }
            }
        }

        $unreachable = [];
        for ($i = 0; $i < $questionCount; $i++) {
            if (!isset($visited[$i])) {
                $unreachable[] = $i;
            }
        }

        return $unreachable;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->description('The form title.')->required(),
            'description' => $schema->string()->description('The form description. Start with "User request: <verbatim user message>".'),
            'questions' => $schema->array()->description('Array of questions in display order.')->items(
                $schema->object([
                    'key' => $schema->string()->description('Optional stable question identifier (recommended). If you use `next_key` in logic, every referenced question must define a unique `key` (letters/numbers/`_`/`-`).'),
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
                    'options' => $schema->array()->description('Option labels for radio/select/checkbox/picture_choice types. Simple string array like ["Yes", "No"], or objects with label+image_url for picture_choice.')->items(
                        $schema->string()
                    ),
                    'logic' => $schema->array()->description('Conditional branching rules for this question. Evaluated after the question is answered.')->items(
                        $schema->object([
                            'operator' => $schema->string()->enum([
                                'equals', 'not_equals', 'is', 'is_not',
                                'greater_than', 'greater_equal_than',
                                'less_than', 'less_equal_than',
                                'lower_than', 'lower_equal_than',
                                'contains', 'not_contains',
                                'begins_with', 'ends_with',
                                'always',
                            ])->description('The comparison operator.')->required(),
                            'value' => $schema->string()->description('The value to compare against. Use option labels (auto-slugged). Empty string for "always".'),
                            'next' => $schema->integer()->description('0-based index of the question to jump to if the rule matches. Provide either `next` or `next_key`.'),
                            'next_key' => $schema->string()->description('Stable question key of the jump target. Preferred over numeric indexes. Provide either `next_key` or `next`.'),
                            'end' => $schema->boolean()->description('Set to true to end the form when this rule matches (no more questions). Mutually exclusive with next/next_key.'),
                        ])
                    ),
                ])
            )->required(),
        ];
    }

}

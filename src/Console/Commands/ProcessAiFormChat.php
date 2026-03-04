<?php

namespace Logicoforms\Forms\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Ai\Tools\Request as AiRequest;
use Logicoforms\Forms\Ai\Agents\FormBuilderAgent;
use Logicoforms\Forms\Ai\ToolStatusReporter;
use Logicoforms\Forms\Ai\Tools\CreateForm;
use Logicoforms\Forms\Models\Form;
use Logicoforms\Forms\Models\QuestionLogic;

class ProcessAiFormChat extends Command
{
    protected $signature = 'forms:ai-process {jobId}';

    protected $description = 'Process an AI form builder chat request in the background';

    protected $hidden = true;

    public function handle(): int
    {
        $jobId = $this->argument('jobId');
        $jobDir = storage_path('app/ai-jobs');
        $inputFile = "{$jobDir}/{$jobId}.input.json";
        $outputFile = "{$jobDir}/{$jobId}.jsonl";

        // Outermost safety net — catch absolutely everything
        try {
            return $this->process($inputFile, $outputFile);
        } catch (\Throwable $e) {
            logger()->error('AI builder worker crashed.', [
                'job_id' => $jobId,
                'exception' => $e,
            ]);
            $this->writeLine($outputFile, [
                'type' => 'error',
                'message' => 'Something went wrong while generating your form. Please try again.',
            ]);

            return 1;
        }
    }

    private function process(string $inputFile, string $outputFile): int
    {
        if (! file_exists($inputFile)) {
            $this->writeLine($outputFile, ['type' => 'error', 'message' => 'Something went wrong while generating your form. Please try again.']);

            return 1;
        }

        $input = json_decode(file_get_contents($inputFile), true);
        @unlink($inputFile);

        $message = $input['message'] ?? '';
        $conversationId = $input['conversation_id'] ?? null;
        $userId = $input['user_id'] ?? null;

        // Tool status events are written directly to the output file
        $reporter = app(ToolStatusReporter::class);
        $reporter->listen(function (string $status) use ($outputFile) {
            $this->writeLine($outputFile, ['type' => 'status', 'message' => $status]);
        });

        $this->writeLine($outputFile, ['type' => 'status', 'message' => 'Thinking...']);

        try {
            $startedAt = now();

            $ownerModel = config('forms.owner_model');
            $user = $userId
                ? $ownerModel::findOrFail($userId)
                : $ownerModel::firstOrCreate(
                    ['email' => 'admin@local'],
                    ['name' => 'Admin', 'password' => bcrypt(Str::random(32))]
                );

            Auth::setUser($user);

            $agent = FormBuilderAgent::make();

            if ($conversationId) {
                $agent->continue($conversationId, $user);
            } else {
                $agent->forUser($user);
            }

            $response = $agent->prompt($message, timeout: 120);

            $text = trim($response->text ?? '');
            $conversationIdOut = $response->conversationId;

            if ($text !== '' && $this->looksLikeBuilderFailure($text)) {
                $text = '';
            }

            // Fallback summary when the model exhausted its turns on tool calls
            // Only look for forms created during THIS run — not old ones
            $noCharge = false;

            if ($text === '') {
                $form = Form::where('created_at', '>=', $startedAt)->latest()->first();
                if ($form) {
                    $questionCount = $form->questions()->count();
                    $ruleCount = QuestionLogic::where('form_id', $form->id)->count();
                    $editUrl = url(str_replace('{id}', $form->id, config('forms.urls.edit', '/forms/{id}/edit')));
                    $text = "**{$form->title}** has been created with {$questionCount} questions and {$ruleCount} logic rules.\n\n"
                        ."[Edit the form]({$editUrl})\n\n"
                        ."It's in draft mode — say \"publish it\" to go live.";
                } else {
                    // AI failed — don't charge for fallback draft
                    $noCharge = true;

                    // Last resort: create a simple draft so the user isn't left with "nothing happened".
                    (new CreateForm)->handle(new AiRequest([
                        'title' => Str::limit(trim($message), 60, '…') ?: 'Untitled Form',
                        'description' => 'User request: '.$message,
                        'questions' => [
                            ['type' => 'text', 'question_text' => 'What is your name?', 'is_required' => true],
                            ['type' => 'email', 'question_text' => 'What is your email address?'],
                            [
                                'type' => 'radio',
                                'question_text' => 'What is this mainly about?',
                                'options' => ['A quick check-in', 'A decision / assessment', 'Feedback'],
                                'is_required' => true,
                            ],
                            ['type' => 'text', 'question_text' => 'Tell me a bit more (optional).'],
                            ['type' => 'rating', 'question_text' => 'How do you feel right now?', 'settings' => ['max' => 10]],
                        ],
                    ]));

                    $form = Form::where('created_at', '>=', $startedAt)->latest()->first();
                    if ($form) {
                        $editUrl = url(str_replace('{id}', $form->id, config('forms.urls.edit', '/forms/{id}/edit')));
                        $text = "I couldn't generate the full form automatically this time, so I created a simple draft you can edit.\n\n"
                            ."[Edit the form]({$editUrl})\n\n"
                            ."Try again in a new chat with a shorter prompt (one goal + one branching question).";
                    } else {
                        $text = "I couldn't create a form right now. Please try again.";
                    }

                    // Reset the conversation so the next user prompt doesn't get stuck trying to "fix" a failed attempt.
                    $conversationIdOut = null;
                }
            }

            // Publish-only responses don't cost credits
            if (! $noCharge) {
                $createdFormDuringRun = Form::where('created_at', '>=', $startedAt)->exists();
                $noCharge = ! $createdFormDuringRun;
            }

            $this->writeLine($outputFile, array_filter([
                'type' => 'response',
                'message' => $text,
                'conversation_id' => $conversationIdOut,
                'no_charge' => $noCharge ?: null,
            ]));
        } catch (\Throwable $e) {
            logger()->error('AI builder job failed.', [
                'input_file' => $inputFile,
                'exception' => $e,
            ]);
            $this->writeLine($outputFile, [
                'type' => 'error',
                'message' => 'Something went wrong while generating your form. Please try again.',
            ]);
        } finally {
            $reporter->clear();
        }

        return 0;
    }

    private function writeLine(string $file, array $data): void
    {
        file_put_contents($file, json_encode($data)."\n", FILE_APPEND | LOCK_EX);
    }

    private function looksLikeBuilderFailure(string $text): bool
    {
        $t = mb_strtolower($text);

        foreach ([
            'ran out of attempts',
            'validation retries',
            'rephrase your request',
            'simpler prompt',
            'couldn\'t create a form right now',
            'ai job did not start',
        ] as $needle) {
            if (str_contains($t, $needle)) {
                return true;
            }
        }

        return false;
    }
}

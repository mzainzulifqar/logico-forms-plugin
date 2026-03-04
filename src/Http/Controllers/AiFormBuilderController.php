<?php

namespace Logicoforms\Forms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Logicoforms\Forms\Contracts\FormLimiter;
use Logicoforms\Forms\Events\AiBuilderUsed;
use Logicoforms\Forms\Jobs\ProcessAiFormChatJob;

class AiFormBuilderController extends Controller
{
    public function __construct(private FormLimiter $limiter)
    {
    }

    public function index()
    {
        $user = auth()->user();
        $creditBalance = $this->limiter->getAiCreditBalance($user);

        $ghostSuggestion = 'Make sure based on selections we ask proper follow-up questions using branching logic';

        return view('forms::forms.ai-builder', compact('creditBalance', 'ghostSuggestion'));
    }

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:5000',
            'conversation_id' => 'nullable|uuid',
        ]);

        $user = auth()->user();

        if (! $this->limiter->hasAiCredits($user)) {
            return response()->json(['error' => 'No AI credits remaining.'], 403);
        }

        $jobId = Str::uuid()->toString();
        $jobDir = storage_path('app/ai-jobs');

        if (! is_dir($jobDir)) {
            mkdir($jobDir, 0755, true);
        }

        file_put_contents("{$jobDir}/{$jobId}.input.json", json_encode([
            'message' => $request->input('message'),
            'conversation_id' => $request->input('conversation_id'),
            'user_id' => auth()->id(),
        ]));

        ProcessAiFormChatJob::dispatch($jobId);

        AiBuilderUsed::dispatch(['message' => mb_substr($request->input('message'), 0, 100)]);

        return response()->json(['job_id' => $jobId], 202);
    }

    public function pollEvents(string $jobId): JsonResponse
    {
        if (! Str::isUuid($jobId)) {
            return response()->json(['events' => [], 'offset' => 0, 'done' => true], 400);
        }

        $outputFile = storage_path("app/ai-jobs/{$jobId}.jsonl");
        $inputFile = storage_path("app/ai-jobs/{$jobId}.input.json");
        $offset = (int) request()->query('offset', 0);
        $events = [];
        $done = false;

        clearstatcache(true, $outputFile);

        if (file_exists($outputFile) && filesize($outputFile) > $offset) {
            $fp = fopen($outputFile, 'r');
            fseek($fp, $offset);

            while (($line = fgets($fp)) !== false) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $data = json_decode($line, true);
                if (! $data) {
                    continue;
                }

                $events[] = $data;

                if (in_array($data['type'] ?? '', ['response', 'error'])) {
                    $done = true;
                }
            }

            $offset = ftell($fp);
            fclose($fp);

            if ($done) {
                $hasChargeableSuccess = false;
                foreach ($events as $evt) {
                    if (($evt['type'] ?? '') === 'response' && empty($evt['no_charge'])) {
                        $hasChargeableSuccess = true;
                        break;
                    }
                }

                if ($hasChargeableSuccess) {
                    $this->deductCreditForJob($inputFile);
                }

                @unlink($outputFile);
            }
        }

        if (! $done) {
            $now = time();
            $inputAge = file_exists($inputFile) ? ($now - (int) filemtime($inputFile)) : null;
            $hasOutput = file_exists($outputFile) && filesize($outputFile) > 0;

            if (! $hasOutput && $inputAge !== null && $inputAge > 60) {
                logger()->warning('AI builder job produced no output after grace period.', [
                    'job_id' => $jobId,
                    'input_age_seconds' => $inputAge,
                ]);
                $events[] = [
                    'type' => 'error',
                    'message' => 'Something went wrong starting the AI builder. Please try again in a moment.',
                ];
                $done = true;
                @unlink($outputFile);
            }
        }

        return response()->json([
            'events' => $events,
            'offset' => $offset,
            'done' => $done,
        ]);
    }

    private function deductCreditForJob(string $inputFile): void
    {
        if (! file_exists($inputFile)) {
            $user = auth()->user();
        } else {
            $input = json_decode(file_get_contents($inputFile), true);
            $ownerModel = config('forms.owner_model', 'App\\Models\\User');
            $user = $ownerModel::find($input['user_id'] ?? auth()->id());
        }

        if (! $user) {
            return;
        }

        $this->limiter->deductAiCredit($user);
    }
}

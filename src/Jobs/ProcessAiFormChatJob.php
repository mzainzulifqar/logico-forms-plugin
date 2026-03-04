<?php

namespace Logicoforms\Forms\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Artisan;

class ProcessAiFormChatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $timeout;
    public int $tries = 1;

    public function __construct(public string $jobId)
    {
        $this->timeout = (int) config('forms.ai_builder.timeout', 180);
    }

    public function handle(): void
    {
        Artisan::call('forms:ai-process', ['jobId' => $this->jobId]);
    }
}

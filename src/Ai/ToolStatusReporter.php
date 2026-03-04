<?php

namespace Logicoforms\Forms\Ai;

use Closure;

class ToolStatusReporter
{
    protected ?Closure $callback = null;

    public function listen(Closure $callback): void
    {
        $this->callback = $callback;
    }

    public function report(string $message): void
    {
        if ($this->callback) {
            ($this->callback)($message);
        }
    }

    public function clear(): void
    {
        $this->callback = null;
    }
}

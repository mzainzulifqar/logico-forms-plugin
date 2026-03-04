<?php

namespace Logicoforms\Forms\Events;

use Illuminate\Foundation\Events\Dispatchable;

class AiBuilderUsed
{
    use Dispatchable;

    public function __construct(public array $metadata = [])
    {
    }
}

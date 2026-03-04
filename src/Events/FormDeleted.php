<?php

namespace Logicoforms\Forms\Events;

use Illuminate\Foundation\Events\Dispatchable;

class FormDeleted
{
    use Dispatchable;

    public function __construct(public int $formId, public string $formTitle, public array $metadata = [])
    {
    }
}

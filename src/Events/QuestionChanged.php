<?php

namespace Logicoforms\Forms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Logicoforms\Forms\Models\Form;

class QuestionChanged
{
    use Dispatchable;

    public function __construct(public Form $form, public string $action, public array $metadata = [])
    {
    }
}

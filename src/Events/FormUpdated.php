<?php

namespace Logicoforms\Forms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Logicoforms\Forms\Models\Form;

class FormUpdated
{
    use Dispatchable;

    public function __construct(public Form $form, public array $metadata = [])
    {
    }
}

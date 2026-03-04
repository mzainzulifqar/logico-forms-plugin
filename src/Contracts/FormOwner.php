<?php

namespace Logicoforms\Forms\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface FormOwner
{
    /**
     * Get the primary key for the owner.
     */
    public function getKey();

    /**
     * Get the forms owned by this user.
     */
    public function forms(): HasMany;
}

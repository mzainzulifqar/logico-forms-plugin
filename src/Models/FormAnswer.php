<?php

namespace Logicoforms\Forms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormAnswer extends Model
{
    public $timestamps = false;

    protected $fillable = ['session_id', 'question_id', 'answer_value', 'answered_at'];

    protected function casts(): array
    {
        return [
            'answer_value' => 'array',
            'answered_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(FormSession::class, 'session_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(FormQuestion::class, 'question_id');
    }
}

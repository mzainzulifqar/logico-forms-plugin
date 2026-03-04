<?php

namespace Logicoforms\Forms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionOption extends Model
{
    protected $fillable = ['question_id', 'label', 'value', 'image_url', 'order_index'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(FormQuestion::class, 'question_id');
    }
}

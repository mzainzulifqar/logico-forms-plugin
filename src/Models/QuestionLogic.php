<?php

namespace Logicoforms\Forms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionLogic extends Model
{
    protected $table = 'question_logic';

    protected $fillable = ['form_id', 'question_id', 'operator', 'value', 'next_question_id'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(FormQuestion::class, 'question_id');
    }

    public function nextQuestion(): BelongsTo
    {
        return $this->belongsTo(FormQuestion::class, 'next_question_id');
    }
}

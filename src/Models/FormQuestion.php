<?php

namespace Logicoforms\Forms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormQuestion extends Model
{
    protected $fillable = ['form_id', 'type', 'question_text', 'help_text', 'is_required', 'order_index', 'settings'];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_required' => 'boolean',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class, 'question_id')->orderBy('order_index');
    }

    public function logicRules(): HasMany
    {
        return $this->hasMany(QuestionLogic::class, 'question_id');
    }
}

<?php

namespace Logicoforms\Forms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FormSession extends Model
{
    protected $fillable = ['form_id', 'session_uuid', 'current_question_id', 'is_completed'];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (FormSession $session) {
            if (empty($session->session_uuid)) {
                $session->session_uuid = (string) Str::uuid();
            }
        });
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function currentQuestion(): BelongsTo
    {
        return $this->belongsTo(FormQuestion::class, 'current_question_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(FormAnswer::class, 'session_id');
    }
}

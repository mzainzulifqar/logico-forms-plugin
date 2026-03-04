<?php

namespace Logicoforms\Forms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Form extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'description', 'slug', 'status', 'created_by', 'theme', 'end_screen_title', 'end_screen_message', 'end_screen_image_url'];

    protected $casts = [
        'theme' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Form $form) {
            if (empty($form->slug)) {
                $form->slug = Str::slug($form->title) . '-' . Str::random(6);
            }
            if (empty($form->created_by) && Auth::id()) {
                $form->created_by = Auth::id();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('forms.owner_model'), 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(FormQuestion::class)->orderBy('order_index');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(FormSession::class);
    }

    public function firstQuestion(): ?FormQuestion
    {
        return $this->questions()->first();
    }
}

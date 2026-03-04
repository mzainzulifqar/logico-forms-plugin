<?php

namespace Logicoforms\Forms\Models;

use Illuminate\Database\Eloquent\Model;

class FormThemePreset extends Model
{
    protected $fillable = [
        'name', 'slug', 'is_system',
        'background_color', 'question_color', 'answer_color',
        'button_color', 'button_text_color', 'font', 'border_radius',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function toThemeArray(): array
    {
        return [
            'background_color' => $this->background_color,
            'question_color'   => $this->question_color,
            'answer_color'     => $this->answer_color,
            'button_color'     => $this->button_color,
            'button_text_color'=> $this->button_text_color,
            'font'             => $this->font,
            'border_radius'    => $this->border_radius,
        ];
    }
}

<?php

namespace Logicoforms\Forms\Support;

use Illuminate\Support\Str;

final class OptionValue
{
    public static function fromLabel(string $label): string
    {
        $ascii = Str::ascii($label);
        $lower = Str::lower($ascii);

        $slug = preg_replace('/[^a-z0-9]+/', '_', $lower) ?? '';
        $slug = trim($slug, '_');
        $slug = preg_replace('/_+/', '_', $slug) ?? '';

        // Non-Latin labels (Japanese, Chinese, Arabic, etc.) produce empty slugs.
        // Fall back to the original label lowercased so each option keeps a unique value.
        if ($slug === '') {
            return mb_strtolower(trim($label));
        }

        return $slug;
    }
}

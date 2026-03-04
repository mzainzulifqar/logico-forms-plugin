<?php

namespace Logicoforms\Forms;

use Logicoforms\Forms\Contracts\FormLimiter;

class NullFormLimiter implements FormLimiter
{
    public function maxResponsesPerForm($owner): ?int
    {
        return null;
    }

    public function canCreateForm($owner): bool
    {
        return true;
    }

    public function canRemoveBranding($owner): bool
    {
        return true;
    }

    public function canUseCustomThemes($owner): bool
    {
        return true;
    }

    public function canAccessAiBuilder($owner): bool
    {
        return true;
    }

    public function hasAiCredits($owner): bool
    {
        return true;
    }

    public function deductAiCredit($owner): void
    {
        // No-op
    }

    public function getAiCreditBalance($owner): ?int
    {
        return null; // unlimited
    }

    public function formLimitRedirectUrl(): ?string
    {
        return null;
    }
}

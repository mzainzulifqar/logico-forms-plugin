<?php

namespace Logicoforms\Forms\Contracts;

interface FormLimiter
{
    /**
     * Get the maximum number of responses allowed per form per month.
     * Return null for unlimited.
     */
    public function maxResponsesPerForm($owner): ?int;

    /**
     * Whether the owner can create another form.
     */
    public function canCreateForm($owner): bool;

    /**
     * Whether the owner's plan allows hiding the "Powered by" branding badge.
     */
    public function canRemoveBranding($owner): bool;

    /**
     * Whether the owner's plan allows custom themes.
     */
    public function canUseCustomThemes($owner): bool;

    /**
     * Whether the owner can access the AI form builder.
     */
    public function canAccessAiBuilder($owner): bool;

    /**
     * Whether the owner has AI credits remaining.
     */
    public function hasAiCredits($owner): bool;

    /**
     * Deduct one AI credit from the owner. No-op if unlimited.
     */
    public function deductAiCredit($owner): void;

    /**
     * Get the owner's current AI credit balance. Return null for unlimited.
     */
    public function getAiCreditBalance($owner): ?int;

    /**
     * Get the URL to redirect to when form creation limit is reached.
     * Return null to show a generic error instead.
     */
    public function formLimitRedirectUrl(): ?string;
}

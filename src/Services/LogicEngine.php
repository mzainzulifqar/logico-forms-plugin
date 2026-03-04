<?php

namespace Logicoforms\Forms\Services;

use Logicoforms\Forms\Models\FormQuestion;
use Logicoforms\Forms\Models\FormSession;
use Logicoforms\Forms\Models\QuestionLogic;

class LogicEngine
{
    public static function getNextQuestion(FormSession $session, int $questionId, mixed $answerValue): ?FormQuestion
    {
        $rules = QuestionLogic::where('question_id', $questionId)
            ->where('form_id', $session->form_id)
            ->orderBy('id')
            ->get();

        foreach ($rules as $rule) {
            if (self::evaluate($rule->operator, $answerValue, $rule->value)) {
                return FormQuestion::find($rule->next_question_id);
            }
        }

        // Fall back to next question by order_index
        $currentQuestion = FormQuestion::find($questionId);

        return FormQuestion::where('form_id', $session->form_id)
            ->where('order_index', '>', $currentQuestion->order_index)
            ->orderBy('order_index')
            ->first();
    }

    public static function getProgress(FormSession $session): array
    {
        $total = FormQuestion::where('form_id', $session->form_id)->count();
        $current = $session->answers()->count();

        return [
            'current' => $current,
            'total' => $total,
        ];
    }

    private static function evaluate(string $operator, mixed $answerValue, string $ruleValue): bool
    {
        if ($operator === 'always') {
            return true;
        }

        if (is_array($answerValue)) {
            return match ($operator) {
                'is', 'equals'             => in_array($ruleValue, $answerValue),
                'is_not', 'not_equals'     => !in_array($ruleValue, $answerValue),
                'contains'                 => in_array($ruleValue, $answerValue),
                'not_contains'             => !in_array($ruleValue, $answerValue),
                default                    => false,
            };
        }

        $answer = (string) $answerValue;

        return match ($operator) {
            'is', 'equals'                           => $answer === $ruleValue,
            'is_not', 'not_equals'                   => $answer !== $ruleValue,
            'greater_than'                           => is_numeric($answer) && is_numeric($ruleValue) && (float)$answer > (float)$ruleValue,
            'greater_equal_than'                     => is_numeric($answer) && is_numeric($ruleValue) && (float)$answer >= (float)$ruleValue,
            'less_than', 'lower_than'                => is_numeric($answer) && is_numeric($ruleValue) && (float)$answer < (float)$ruleValue,
            'less_equal_than', 'lower_equal_than'    => is_numeric($answer) && is_numeric($ruleValue) && (float)$answer <= (float)$ruleValue,
            'contains'                               => str_contains(strtolower($answer), strtolower($ruleValue)),
            'not_contains'                           => !str_contains(strtolower($answer), strtolower($ruleValue)),
            'begins_with'                            => str_starts_with(strtolower($answer), strtolower($ruleValue)),
            'ends_with'                              => str_ends_with(strtolower($answer), strtolower($ruleValue)),
            default                                  => false,
        };
    }
}

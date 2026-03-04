<?php

namespace Logicoforms\Forms\Tests\Unit;

use Logicoforms\Forms\Models\Form;
use Logicoforms\Forms\Models\FormAnswer;
use Logicoforms\Forms\Models\FormQuestion;
use Logicoforms\Forms\Models\FormSession;
use Logicoforms\Forms\Models\QuestionLogic;
use Logicoforms\Forms\Services\LogicEngine;
use Logicoforms\Forms\Tests\TestCase;

class LogicEngineTest extends TestCase
{
    private Form $form;
    private FormSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->form = Form::create([
            'title' => 'Logic Test Form',
            'status' => 'published',
            'created_by' => 1,
        ]);

        $this->session = FormSession::create([
            'form_id' => $this->form->id,
        ]);
    }

    private function createQuestion(int $orderIndex, string $type = 'text'): FormQuestion
    {
        return FormQuestion::create([
            'form_id' => $this->form->id,
            'type' => $type,
            'question_text' => "Question {$orderIndex}",
            'order_index' => $orderIndex,
        ]);
    }

    private function addRule(int $questionId, string $operator, string $value, ?int $nextQuestionId): QuestionLogic
    {
        return QuestionLogic::create([
            'form_id' => $this->form->id,
            'question_id' => $questionId,
            'operator' => $operator,
            'value' => $value,
            'next_question_id' => $nextQuestionId,
        ]);
    }

    public function test_equals_operator_matches_exact_value(): void
    {
        $q1 = $this->createQuestion(0);
        $q2 = $this->createQuestion(1);
        $q3 = $this->createQuestion(2);

        $this->addRule($q1->id, 'equals', 'yes', $q3->id);

        $next = LogicEngine::getNextQuestion($this->session, $q1->id, 'yes');
        $this->assertEquals($q3->id, $next->id);
    }

    public function test_equals_operator_does_not_match_different_value(): void
    {
        $q1 = $this->createQuestion(0);
        $q2 = $this->createQuestion(1);
        $q3 = $this->createQuestion(2);

        $this->addRule($q1->id, 'equals', 'yes', $q3->id);

        $next = LogicEngine::getNextQuestion($this->session, $q1->id, 'no');
        $this->assertEquals($q2->id, $next->id); // Falls back to next by order
    }

    public function test_not_equals_operator(): void
    {
        $q1 = $this->createQuestion(0);
        $q2 = $this->createQuestion(1);
        $q3 = $this->createQuestion(2);

        $this->addRule($q1->id, 'not_equals', 'yes', $q3->id);

        $next = LogicEngine::getNextQuestion($this->session, $q1->id, 'no');
        $this->assertEquals($q3->id, $next->id);
    }

    public function test_greater_than_operator_with_numeric_values(): void
    {
        $q1 = $this->createQuestion(0);
        $q2 = $this->createQuestion(1);
        $q3 = $this->createQuestion(2);

        $this->addRule($q1->id, 'greater_than', '10', $q3->id);

        $this->assertEquals($q3->id, LogicEngine::getNextQuestion($this->session, $q1->id, '15')->id);
        $this->assertEquals($q2->id, LogicEngine::getNextQuestion($this->session, $q1->id, '5')->id);
    }

    public function test_less_than_operator_with_numeric_values(): void
    {
        $q1 = $this->createQuestion(0);
        $q2 = $this->createQuestion(1);
        $q3 = $this->createQuestion(2);

        $this->addRule($q1->id, 'less_than', '10', $q3->id);

        $this->assertEquals($q3->id, LogicEngine::getNextQuestion($this->session, $q1->id, '5')->id);
        $this->assertEquals($q2->id, LogicEngine::getNextQuestion($this->session, $q1->id, '15')->id);
    }

    public function test_contains_operator_substring_match(): void
    {
        $q1 = $this->createQuestion(0);
        $q2 = $this->createQuestion(1);
        $q3 = $this->createQuestion(2);

        $this->addRule($q1->id, 'contains', 'hello', $q3->id);

        $this->assertEquals($q3->id, LogicEngine::getNextQuestion($this->session, $q1->id, 'say hello world')->id);
        $this->assertEquals($q2->id, LogicEngine::getNextQuestion($this->session, $q1->id, 'goodbye')->id);
    }

    public function test_not_contains_operator(): void
    {
        $q1 = $this->createQuestion(0);
        $q2 = $this->createQuestion(1);
        $q3 = $this->createQuestion(2);

        $this->addRule($q1->id, 'not_contains', 'hello', $q3->id);

        $this->assertEquals($q3->id, LogicEngine::getNextQuestion($this->session, $q1->id, 'goodbye')->id);
        $this->assertEquals($q2->id, LogicEngine::getNextQuestion($this->session, $q1->id, 'say hello')->id);
    }

    public function test_begins_with_operator(): void
    {
        $q1 = $this->createQuestion(0);
        $q2 = $this->createQuestion(1);
        $q3 = $this->createQuestion(2);

        $this->addRule($q1->id, 'begins_with', 'hello', $q3->id);

        $this->assertEquals($q3->id, LogicEngine::getNextQuestion($this->session, $q1->id, 'hello world')->id);
        $this->assertEquals($q2->id, LogicEngine::getNextQuestion($this->session, $q1->id, 'world hello')->id);
    }

    public function test_ends_with_operator(): void
    {
        $q1 = $this->createQuestion(0);
        $q2 = $this->createQuestion(1);
        $q3 = $this->createQuestion(2);

        $this->addRule($q1->id, 'ends_with', 'world', $q3->id);

        $this->assertEquals($q3->id, LogicEngine::getNextQuestion($this->session, $q1->id, 'hello world')->id);
        $this->assertEquals($q2->id, LogicEngine::getNextQuestion($this->session, $q1->id, 'world hello')->id);
    }

    public function test_always_operator_matches_any_value(): void
    {
        $q1 = $this->createQuestion(0);
        $q2 = $this->createQuestion(1);
        $q3 = $this->createQuestion(2);

        $this->addRule($q1->id, 'always', '', $q3->id);

        $this->assertEquals($q3->id, LogicEngine::getNextQuestion($this->session, $q1->id, 'anything')->id);
        $this->assertEquals($q3->id, LogicEngine::getNextQuestion($this->session, $q1->id, '')->id);
    }

    public function test_array_answer_with_equals_uses_in_array(): void
    {
        $q1 = $this->createQuestion(0);
        $q2 = $this->createQuestion(1);
        $q3 = $this->createQuestion(2);

        $this->addRule($q1->id, 'equals', 'red', $q3->id);

        $this->assertEquals($q3->id, LogicEngine::getNextQuestion($this->session, $q1->id, ['blue', 'red'])->id);
        $this->assertEquals($q2->id, LogicEngine::getNextQuestion($this->session, $q1->id, ['blue', 'green'])->id);
    }

    public function test_fallback_to_next_question_by_order_index(): void
    {
        $q1 = $this->createQuestion(0);
        $q2 = $this->createQuestion(1);
        $q3 = $this->createQuestion(2);

        // No rules at all — should fall back to next by order_index
        $next = LogicEngine::getNextQuestion($this->session, $q1->id, 'any');
        $this->assertEquals($q2->id, $next->id);
    }

    public function test_returns_null_at_last_question(): void
    {
        $q1 = $this->createQuestion(0);

        $next = LogicEngine::getNextQuestion($this->session, $q1->id, 'any');
        $this->assertNull($next);
    }

    public function test_first_matching_rule_wins(): void
    {
        $q1 = $this->createQuestion(0);
        $q2 = $this->createQuestion(1);
        $q3 = $this->createQuestion(2);

        $this->addRule($q1->id, 'equals', 'yes', $q2->id);
        $this->addRule($q1->id, 'equals', 'yes', $q3->id);

        $next = LogicEngine::getNextQuestion($this->session, $q1->id, 'yes');
        $this->assertEquals($q2->id, $next->id); // First rule wins
    }

    public function test_get_progress_returns_correct_counts(): void
    {
        $q1 = $this->createQuestion(0);
        $q2 = $this->createQuestion(1);
        $q3 = $this->createQuestion(2);

        $progress = LogicEngine::getProgress($this->session);
        $this->assertEquals(['current' => 0, 'total' => 3], $progress);

        FormAnswer::create([
            'session_id' => $this->session->id,
            'question_id' => $q1->id,
            'answer_value' => 'test',
            'answered_at' => now(),
        ]);

        $progress = LogicEngine::getProgress($this->session);
        $this->assertEquals(['current' => 1, 'total' => 3], $progress);
    }
}

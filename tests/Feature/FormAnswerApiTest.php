<?php

namespace Logicoforms\Forms\Tests\Feature;

use Logicoforms\Forms\Models\Form;
use Logicoforms\Forms\Models\FormQuestion;
use Logicoforms\Forms\Models\FormSession;
use Logicoforms\Forms\Models\QuestionLogic;
use Logicoforms\Forms\Models\QuestionOption;
use Logicoforms\Forms\Tests\TestCase;

class FormAnswerApiTest extends TestCase
{
    private function createFormWithLinearQuestions(int $count = 3): array
    {
        $form = Form::create([
            'title' => 'Test Form',
            'status' => 'published',
            'created_by' => 1,
        ]);

        $questions = [];
        for ($i = 0; $i < $count; $i++) {
            $questions[] = FormQuestion::create([
                'form_id' => $form->id,
                'type' => 'text',
                'question_text' => "Question {$i}",
                'order_index' => $i,
            ]);
        }

        $session = FormSession::create([
            'form_id' => $form->id,
            'current_question_id' => $questions[0]->id,
        ]);

        return [$form, $questions, $session];
    }

    public function test_submit_answer_returns_next_question(): void
    {
        [$form, $questions, $session] = $this->createFormWithLinearQuestions();

        $response = $this->postJson("/api/forms/sessions/{$session->session_uuid}/answers", [
            'question_id' => $questions[0]->id,
            'answer' => 'My answer',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.completed', false);
        $response->assertJsonPath('data.question.id', $questions[1]->id);
    }

    public function test_logic_branching_routes_correctly(): void
    {
        $form = Form::create([
            'title' => 'Branch Form',
            'status' => 'published',
            'created_by' => 1,
        ]);

        $q1 = FormQuestion::create(['form_id' => $form->id, 'type' => 'radio', 'question_text' => 'Choose', 'order_index' => 0]);
        $q2 = FormQuestion::create(['form_id' => $form->id, 'type' => 'text', 'question_text' => 'Path A', 'order_index' => 1]);
        $q3 = FormQuestion::create(['form_id' => $form->id, 'type' => 'text', 'question_text' => 'Path B', 'order_index' => 2]);

        QuestionOption::create(['question_id' => $q1->id, 'label' => 'A', 'value' => 'a', 'order_index' => 0]);
        QuestionOption::create(['question_id' => $q1->id, 'label' => 'B', 'value' => 'b', 'order_index' => 1]);

        QuestionLogic::create([
            'form_id' => $form->id,
            'question_id' => $q1->id,
            'operator' => 'equals',
            'value' => 'b',
            'next_question_id' => $q3->id,
        ]);

        $session = FormSession::create(['form_id' => $form->id, 'current_question_id' => $q1->id]);

        $response = $this->postJson("/api/forms/sessions/{$session->session_uuid}/answers", [
            'question_id' => $q1->id,
            'answer' => 'b',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.question.id', $q3->id);
    }

    public function test_default_fallback_when_no_logic_match(): void
    {
        $form = Form::create([
            'title' => 'Fallback Form',
            'status' => 'published',
            'created_by' => 1,
        ]);

        $q1 = FormQuestion::create(['form_id' => $form->id, 'type' => 'radio', 'question_text' => 'Choose', 'order_index' => 0]);
        $q2 = FormQuestion::create(['form_id' => $form->id, 'type' => 'text', 'question_text' => 'Default', 'order_index' => 1]);
        $q3 = FormQuestion::create(['form_id' => $form->id, 'type' => 'text', 'question_text' => 'Branch', 'order_index' => 2]);

        QuestionLogic::create([
            'form_id' => $form->id,
            'question_id' => $q1->id,
            'operator' => 'equals',
            'value' => 'special',
            'next_question_id' => $q3->id,
        ]);

        $session = FormSession::create(['form_id' => $form->id, 'current_question_id' => $q1->id]);

        $response = $this->postJson("/api/forms/sessions/{$session->session_uuid}/answers", [
            'question_id' => $q1->id,
            'answer' => 'normal',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.question.id', $q2->id);
    }

    public function test_array_answer_with_logic(): void
    {
        $form = Form::create([
            'title' => 'Checkbox Form',
            'status' => 'published',
            'created_by' => 1,
        ]);

        $q1 = FormQuestion::create(['form_id' => $form->id, 'type' => 'checkbox', 'question_text' => 'Select all', 'order_index' => 0]);
        $q2 = FormQuestion::create(['form_id' => $form->id, 'type' => 'text', 'question_text' => 'Default', 'order_index' => 1]);
        $q3 = FormQuestion::create(['form_id' => $form->id, 'type' => 'text', 'question_text' => 'Special', 'order_index' => 2]);

        QuestionLogic::create([
            'form_id' => $form->id,
            'question_id' => $q1->id,
            'operator' => 'equals',
            'value' => 'vip',
            'next_question_id' => $q3->id,
        ]);

        $session = FormSession::create(['form_id' => $form->id, 'current_question_id' => $q1->id]);

        $response = $this->postJson("/api/forms/sessions/{$session->session_uuid}/answers", [
            'question_id' => $q1->id,
            'answer' => ['basic', 'vip'],
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.question.id', $q3->id);
    }

    public function test_last_question_completes_session(): void
    {
        [$form, $questions, $session] = $this->createFormWithLinearQuestions(1);

        $response = $this->postJson("/api/forms/sessions/{$session->session_uuid}/answers", [
            'question_id' => $questions[0]->id,
            'answer' => 'done',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.completed', true);
        $response->assertJsonPath('data.question', null);
        $this->assertDatabaseHas('form_sessions', [
            'id' => $session->id,
            'is_completed' => true,
        ]);
    }

    public function test_cannot_answer_on_completed_session(): void
    {
        [$form, $questions, $session] = $this->createFormWithLinearQuestions();

        $session->update(['is_completed' => true]);

        $response = $this->postJson("/api/forms/sessions/{$session->session_uuid}/answers", [
            'question_id' => $questions[0]->id,
            'answer' => 'test',
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_answer_with_invalid_session_uuid(): void
    {
        $response = $this->postJson('/api/forms/sessions/invalid-uuid-12345/answers', [
            'question_id' => 1,
            'answer' => 'test',
        ]);

        $response->assertStatus(404);
    }

    public function test_progress_updates_after_each_answer(): void
    {
        [$form, $questions, $session] = $this->createFormWithLinearQuestions(3);

        $response = $this->postJson("/api/forms/sessions/{$session->session_uuid}/answers", [
            'question_id' => $questions[0]->id,
            'answer' => 'first',
        ]);

        $response->assertJsonPath('data.progress.current', 1);
        $response->assertJsonPath('data.progress.total', 3);

        $response = $this->postJson("/api/forms/sessions/{$session->session_uuid}/answers", [
            'question_id' => $questions[1]->id,
            'answer' => 'second',
        ]);

        $response->assertJsonPath('data.progress.current', 2);
    }

    public function test_end_screen_returned_on_completion(): void
    {
        $form = Form::create([
            'title' => 'Test Form',
            'status' => 'published',
            'created_by' => 1,
            'end_screen_title' => 'Thank you!',
            'end_screen_message' => 'Your response has been recorded.',
        ]);

        $q = FormQuestion::create([
            'form_id' => $form->id,
            'type' => 'text',
            'question_text' => 'Only question',
            'order_index' => 0,
        ]);

        $session = FormSession::create(['form_id' => $form->id, 'current_question_id' => $q->id]);

        $response = $this->postJson("/api/forms/sessions/{$session->session_uuid}/answers", [
            'question_id' => $q->id,
            'answer' => 'done',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.completed', true);
        $response->assertJsonPath('data.end_screen.title', 'Thank you!');
        $response->assertJsonPath('data.end_screen.message', 'Your response has been recorded.');
    }
}

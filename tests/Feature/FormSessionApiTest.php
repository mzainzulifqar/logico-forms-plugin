<?php

namespace Logicoforms\Forms\Tests\Feature;

use Logicoforms\Forms\Models\Form;
use Logicoforms\Forms\Models\FormQuestion;
use Logicoforms\Forms\Models\QuestionOption;
use Logicoforms\Forms\Tests\TestCase;

class FormSessionApiTest extends TestCase
{
    private function createFormWithQuestions(string $status = 'published', int $questionCount = 3): Form
    {
        $form = Form::create([
            'title' => 'Test Form',
            'status' => $status,
            'created_by' => 1,
        ]);

        for ($i = 0; $i < $questionCount; $i++) {
            $q = FormQuestion::create([
                'form_id' => $form->id,
                'type' => 'text',
                'question_text' => "Question {$i}",
                'order_index' => $i,
            ]);
        }

        return $form;
    }

    public function test_start_session_on_published_form(): void
    {
        $form = $this->createFormWithQuestions('published');

        $response = $this->postJson("/api/forms/{$form->id}/sessions");

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'session_uuid',
                'completed',
                'question' => ['id', 'type', 'question_text'],
                'progress' => ['current', 'total'],
            ],
        ]);
        $response->assertJsonPath('data.completed', false);
    }

    public function test_404_on_draft_form(): void
    {
        $form = $this->createFormWithQuestions('draft');

        $response = $this->postJson("/api/forms/{$form->id}/sessions");

        $response->assertStatus(404);
    }

    public function test_404_on_closed_form(): void
    {
        $form = $this->createFormWithQuestions('closed');

        $response = $this->postJson("/api/forms/{$form->id}/sessions");

        $response->assertStatus(404);
    }

    public function test_returns_first_question_with_options(): void
    {
        $form = Form::create([
            'title' => 'Test Form',
            'status' => 'published',
            'created_by' => 1,
        ]);

        $q = FormQuestion::create([
            'form_id' => $form->id,
            'type' => 'radio',
            'question_text' => 'Pick one',
            'order_index' => 0,
        ]);

        QuestionOption::create(['question_id' => $q->id, 'label' => 'Option A', 'value' => 'option_a', 'order_index' => 0]);
        QuestionOption::create(['question_id' => $q->id, 'label' => 'Option B', 'value' => 'option_b', 'order_index' => 1]);

        $response = $this->postJson("/api/forms/{$form->id}/sessions");

        $response->assertOk();
        $response->assertJsonCount(2, 'data.question.options');
        $response->assertJsonPath('data.question.options.0.label', 'Option A');
    }

    public function test_returns_progress(): void
    {
        $form = $this->createFormWithQuestions('published', 5);

        $response = $this->postJson("/api/forms/{$form->id}/sessions");

        $response->assertOk();
        $response->assertJsonPath('data.progress.current', 0);
        $response->assertJsonPath('data.progress.total', 5);
    }

    public function test_response_limit_enforcement(): void
    {
        $user = $this->createUser();
        $form = Form::create([
            'title' => 'Limited Form',
            'status' => 'published',
            'created_by' => $user->getKey(),
        ]);

        FormQuestion::create([
            'form_id' => $form->id,
            'type' => 'text',
            'question_text' => 'Q1',
            'order_index' => 0,
        ]);

        // Bind a limiter that restricts to 1 response per form
        $this->app->singleton(\Logicoforms\Forms\Contracts\FormLimiter::class, function () {
            return new class implements \Logicoforms\Forms\Contracts\FormLimiter {
                public function maxResponsesPerForm($owner): ?int { return 1; }
                public function canCreateForm($owner): bool { return true; }
                public function canRemoveBranding($owner): bool { return true; }
                public function canUseCustomThemes($owner): bool { return true; }
                public function canAccessAiBuilder($owner): bool { return true; }
                public function hasAiCredits($owner): bool { return true; }
                public function deductAiCredit($owner): void {}
                public function getAiCreditBalance($owner): ?int { return null; }
                public function formLimitRedirectUrl(): ?string { return null; }
            };
        });

        // First session should work
        $this->postJson("/api/forms/{$form->id}/sessions")->assertOk();

        // Second session should be blocked
        $this->postJson("/api/forms/{$form->id}/sessions")->assertStatus(403);
    }

    public function test_unique_session_uuids(): void
    {
        $form = $this->createFormWithQuestions('published');

        $response1 = $this->postJson("/api/forms/{$form->id}/sessions");
        $response2 = $this->postJson("/api/forms/{$form->id}/sessions");

        $uuid1 = $response1->json('data.session_uuid');
        $uuid2 = $response2->json('data.session_uuid');

        $this->assertNotEquals($uuid1, $uuid2);
    }

    public function test_422_on_form_with_no_questions(): void
    {
        $form = Form::create([
            'title' => 'Empty Form',
            'status' => 'published',
            'created_by' => 1,
        ]);

        $response = $this->postJson("/api/forms/{$form->id}/sessions");
        $response->assertStatus(422);
    }
}

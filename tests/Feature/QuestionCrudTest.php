<?php

namespace Logicoforms\Forms\Tests\Feature;

use Logicoforms\Forms\Models\Form;
use Logicoforms\Forms\Models\FormQuestion;
use Logicoforms\Forms\Tests\TestCase;

class QuestionCrudTest extends TestCase
{
    public function test_add_question_with_options(): void
    {
        $user = $this->actingAsUser();
        $form = Form::create(['title' => 'Test', 'created_by' => $user->getKey()]);

        $response = $this->postJson(route('forms.questions.store', $form), [
            'type' => 'radio',
            'question_text' => 'Pick one',
            'options' => [
                ['label' => 'Option A', 'value' => ''],
                ['label' => 'Option B', 'value' => ''],
            ],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('form_questions', ['form_id' => $form->id, 'question_text' => 'Pick one']);
        $this->assertDatabaseHas('question_options', ['label' => 'Option A']);
        $this->assertDatabaseHas('question_options', ['label' => 'Option B']);
    }

    public function test_update_question_with_logic_rules(): void
    {
        $user = $this->actingAsUser();
        $form = Form::create(['title' => 'Test', 'created_by' => $user->getKey()]);

        $q1 = FormQuestion::create(['form_id' => $form->id, 'type' => 'radio', 'question_text' => 'Q1', 'order_index' => 0]);
        $q2 = FormQuestion::create(['form_id' => $form->id, 'type' => 'text', 'question_text' => 'Q2', 'order_index' => 1]);

        $response = $this->putJson(route('forms.questions.update', [$form, $q1]), [
            'type' => 'radio',
            'question_text' => 'Updated Q1',
            'options' => [
                ['label' => 'Yes', 'value' => ''],
                ['label' => 'No', 'value' => ''],
            ],
            'logic' => [
                [
                    'operator' => 'equals',
                    'value' => 'yes',
                    'next_question_id' => $q2->id,
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('form_questions', ['id' => $q1->id, 'question_text' => 'Updated Q1']);
        $this->assertDatabaseHas('question_logic', ['question_id' => $q1->id, 'operator' => 'equals']);
    }

    public function test_delete_question(): void
    {
        $user = $this->actingAsUser();
        $form = Form::create(['title' => 'Test', 'created_by' => $user->getKey()]);
        $q = FormQuestion::create(['form_id' => $form->id, 'type' => 'text', 'question_text' => 'Delete me', 'order_index' => 0]);

        $response = $this->deleteJson(route('forms.questions.destroy', [$form, $q]));

        $response->assertOk();
        $this->assertDatabaseMissing('form_questions', ['id' => $q->id]);
    }

    public function test_reorder_questions(): void
    {
        $user = $this->actingAsUser();
        $form = Form::create(['title' => 'Test', 'created_by' => $user->getKey()]);

        $q1 = FormQuestion::create(['form_id' => $form->id, 'type' => 'text', 'question_text' => 'Q1', 'order_index' => 0]);
        $q2 = FormQuestion::create(['form_id' => $form->id, 'type' => 'text', 'question_text' => 'Q2', 'order_index' => 1]);

        $response = $this->putJson(route('questions.reorder', $form), [
            'order' => [$q2->id, $q1->id],
        ]);

        $response->assertOk();
        $this->assertEquals(1, $q1->fresh()->order_index);
        $this->assertEquals(0, $q2->fresh()->order_index);
    }

    public function test_cannot_manage_other_users_questions(): void
    {
        $user = $this->actingAsUser();
        $other = $this->createUser(['email' => 'other@example.com']);
        $form = Form::create(['title' => 'Not Mine', 'created_by' => $other->getKey()]);

        $response = $this->postJson(route('forms.questions.store', $form), [
            'type' => 'text',
            'question_text' => 'Sneaky question',
        ]);

        $response->assertStatus(404);
    }

    public function test_validates_question_type(): void
    {
        $user = $this->actingAsUser();
        $form = Form::create(['title' => 'Test', 'created_by' => $user->getKey()]);

        $response = $this->postJson(route('forms.questions.store', $form), [
            'type' => 'invalid_type',
            'question_text' => 'Bad type',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('type');
    }

    public function test_add_question_auto_sets_order_index(): void
    {
        $user = $this->actingAsUser();
        $form = Form::create(['title' => 'Test', 'created_by' => $user->getKey()]);

        FormQuestion::create(['form_id' => $form->id, 'type' => 'text', 'question_text' => 'Q1', 'order_index' => 0]);

        $response = $this->postJson(route('forms.questions.store', $form), [
            'type' => 'text',
            'question_text' => 'Q2',
        ]);

        $response->assertStatus(201);
        $newQuestion = FormQuestion::where('question_text', 'Q2')->first();
        $this->assertEquals(1, $newQuestion->order_index);
    }

    public function test_option_values_auto_generated_from_label(): void
    {
        $user = $this->actingAsUser();
        $form = Form::create(['title' => 'Test', 'created_by' => $user->getKey()]);

        $response = $this->postJson(route('forms.questions.store', $form), [
            'type' => 'radio',
            'question_text' => 'Pick',
            'options' => [
                ['label' => 'Very likely', 'value' => ''],
                ['label' => 'Not at all', 'value' => ''],
            ],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('question_options', ['label' => 'Very likely', 'value' => 'very_likely']);
        $this->assertDatabaseHas('question_options', ['label' => 'Not at all', 'value' => 'not_at_all']);
    }
}

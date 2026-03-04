<?php

namespace Logicoforms\Forms\Tests\Feature;

use Logicoforms\Forms\Ai\Tools\CreateFormWithQuestions;
use Logicoforms\Forms\Models\Form;
use Logicoforms\Forms\Models\FormQuestion;
use Logicoforms\Forms\Models\QuestionOption;
use Logicoforms\Forms\Tests\TestCase;
use Laravel\Ai\Tools\Request as AiRequest;

class CreateFormToolTest extends TestCase
{
    private CreateFormWithQuestions $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tool = new CreateFormWithQuestions();
    }

    public function test_rejects_missing_title(): void
    {
        $result = $this->tool->handle(new AiRequest([
            'title' => '',
            'questions' => [
                ['type' => 'radio', 'question_text' => 'Pick one', 'options' => [['label' => 'A'], ['label' => 'B']]],
            ],
        ]));

        $this->assertStringContains('VALIDATION_ERROR', (string) $result);
        $this->assertStringContains('title', (string) $result);
    }

    public function test_rejects_empty_questions(): void
    {
        $result = $this->tool->handle(new AiRequest([
            'title' => 'My Form',
            'questions' => [],
        ]));

        $this->assertStringContains('VALIDATION_ERROR', (string) $result);
        $this->assertStringContains('No questions', (string) $result);
    }

    public function test_creates_simple_form(): void
    {
        $this->actingAsUser();

        $result = $this->tool->handle(new AiRequest([
            'title' => 'Simple Survey',
            'questions' => [
                ['type' => 'text', 'question_text' => 'What is your name?'],
                ['type' => 'radio', 'question_text' => 'How are you?', 'options' => [['label' => 'Good'], ['label' => 'Bad']]],
            ],
        ]));

        $this->assertStringContains('Form created', (string) $result);
        $this->assertDatabaseHas('forms', ['title' => 'Simple Survey']);
        $this->assertEquals(2, FormQuestion::where('form_id', Form::first()->id)->count());
    }

    public function test_rejects_radio_with_fewer_than_2_options(): void
    {
        $result = $this->tool->handle(new AiRequest([
            'title' => 'Bad Form',
            'questions' => [
                ['type' => 'radio', 'question_text' => 'Pick one', 'options' => [['label' => 'Only one']]],
            ],
        ]));

        $this->assertStringContains('VALIDATION_ERROR', (string) $result);
        $this->assertStringContains('fewer than 2 options', (string) $result);
    }

    public function test_rejects_question_without_text(): void
    {
        $result = $this->tool->handle(new AiRequest([
            'title' => 'Bad Form',
            'questions' => [
                ['type' => 'text', 'question_text' => ''],
                ['type' => 'radio', 'question_text' => 'Pick one', 'options' => [['label' => 'A'], ['label' => 'B']]],
            ],
        ]));

        $this->assertStringContains('VALIDATION_ERROR', (string) $result);
        $this->assertStringContains('question_text', (string) $result);
    }

    public function test_creates_options_with_auto_generated_values(): void
    {
        $this->actingAsUser();

        $this->tool->handle(new AiRequest([
            'title' => 'Options Form',
            'questions' => [
                [
                    'type' => 'radio',
                    'question_text' => 'Pick color',
                    'options' => [['label' => 'Bright Red'], ['label' => 'Dark Blue']],
                ],
            ],
        ]));

        $this->assertDatabaseHas('question_options', ['label' => 'Bright Red', 'value' => 'bright_red']);
        $this->assertDatabaseHas('question_options', ['label' => 'Dark Blue', 'value' => 'dark_blue']);
    }

    public function test_rejects_options_on_non_choice_types(): void
    {
        $result = $this->tool->handle(new AiRequest([
            'title' => 'Bad Form',
            'questions' => [
                ['type' => 'text', 'question_text' => 'Name?', 'options' => [['label' => 'A'], ['label' => 'B']]],
                ['type' => 'radio', 'question_text' => 'Pick', 'options' => [['label' => 'X'], ['label' => 'Y']]],
            ],
        ]));

        $this->assertStringContains('VALIDATION_ERROR', (string) $result);
        $this->assertStringContains('options are only valid', (string) $result);
    }

    public function test_enforces_max_questions_limit(): void
    {
        $questions = [];
        for ($i = 0; $i < 21; $i++) {
            $questions[] = ['type' => 'text', 'question_text' => "Q{$i}"];
        }
        // Add a radio to satisfy branch requirement
        $questions[0] = ['type' => 'radio', 'question_text' => 'Pick', 'options' => [['label' => 'A'], ['label' => 'B']]];

        $result = $this->tool->handle(new AiRequest([
            'title' => 'Too Many',
            'questions' => $questions,
        ]));

        $this->assertStringContains('VALIDATION_ERROR', (string) $result);
        $this->assertStringContains('max is 20', (string) $result);
    }

    public function test_requires_at_least_one_branch_question(): void
    {
        $result = $this->tool->handle(new AiRequest([
            'title' => 'No Branch',
            'questions' => [
                ['type' => 'text', 'question_text' => 'Q1'],
                ['type' => 'text', 'question_text' => 'Q2'],
            ],
        ]));

        $this->assertStringContains('VALIDATION_ERROR', (string) $result);
        $this->assertStringContains('radio', (string) $result);
    }

    public function test_creates_form_with_rating_settings(): void
    {
        $this->actingAsUser();

        $this->tool->handle(new AiRequest([
            'title' => 'Rating Form',
            'questions' => [
                ['type' => 'rating', 'question_text' => 'Rate us', 'settings' => ['max' => 10]],
                ['type' => 'radio', 'question_text' => 'Pick', 'options' => [['label' => 'A'], ['label' => 'B']]],
            ],
        ]));

        $q = FormQuestion::where('question_text', 'Rate us')->first();
        $this->assertNotNull($q);
        $this->assertEquals(['max' => 10], $q->settings);
    }

    /**
     * Custom assertion for string contains (works with Stringable).
     */
    private function assertStringContains(string $needle, string $haystack): void
    {
        $this->assertStringContainsString($needle, $haystack);
    }
}

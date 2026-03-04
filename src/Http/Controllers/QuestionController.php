<?php

namespace Logicoforms\Forms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Logicoforms\Forms\Events\QuestionChanged;
use Logicoforms\Forms\Models\Form;
use Logicoforms\Forms\Models\FormQuestion;
use Logicoforms\Forms\Support\OptionValue;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QuestionController extends Controller
{
    private function authorizeOwner(Form $form): void
    {
        if ((int) $form->created_by !== (int) auth()->id()) {
            throw new NotFoundHttpException;
        }
    }

    public function store(Request $request, Form $form): JsonResponse
    {
        $this->authorizeOwner($form);
        $validated = $request->validate([
            'type' => 'required|in:text,email,number,select,radio,checkbox,rating,picture_choice,opinion_scale',
            'question_text' => 'required|string',
            'help_text' => 'nullable|string',
            'is_required' => 'boolean',
            'settings' => 'nullable|array',
            'options' => 'nullable|array',
            'options.*.label' => 'required_with:options|string',
            'options.*.value' => 'nullable|string',
            'options.*.image_url' => 'nullable|string',
        ]);

        $maxOrder = $form->questions()->max('order_index') ?? -1;

        $question = $form->questions()->create([
            'type' => $validated['type'],
            'question_text' => $validated['question_text'],
            'help_text' => $validated['help_text'] ?? null,
            'is_required' => $validated['is_required'] ?? false,
            'order_index' => $maxOrder + 1,
            'settings' => $validated['settings'] ?? null,
        ]);

        if (!empty($validated['options'])) {
            foreach ($validated['options'] as $i => $opt) {
                $question->options()->create([
                    'label' => $opt['label'],
                    'value' => $opt['value'] ?: OptionValue::fromLabel($opt['label']),
                    'image_url' => $opt['image_url'] ?? null,
                    'order_index' => $i,
                ]);
            }
        }

        $question->load('options');

        QuestionChanged::dispatch($form, 'added', ['question_text' => $question->question_text, 'type' => $question->type]);

        return response()->json(['data' => $question], 201);
    }

    public function update(Request $request, Form $form, FormQuestion $question): JsonResponse
    {
        $this->authorizeOwner($form);
        $validated = $request->validate([
            'type' => 'required|in:text,email,number,select,radio,checkbox,rating,picture_choice,opinion_scale',
            'question_text' => 'required|string',
            'help_text' => 'nullable|string',
            'is_required' => 'boolean',
            'settings' => 'nullable|array',
            'options' => 'nullable|array',
            'options.*.label' => 'required_with:options|string',
            'options.*.value' => 'nullable|string',
            'options.*.image_url' => 'nullable|string',
            'logic' => 'nullable|array',
            'logic.*.operator' => 'required_with:logic|in:equals,not_equals,is,is_not,greater_than,greater_equal_than,less_than,less_equal_than,lower_than,lower_equal_than,contains,not_contains,begins_with,ends_with,always',
            'logic.*.value' => 'nullable|string',
            'logic.*.next_question_id' => 'required_with:logic|exists:form_questions,id,form_id,'.$form->id,
        ]);

        $question->update([
            'type' => $validated['type'],
            'question_text' => $validated['question_text'],
            'help_text' => $validated['help_text'] ?? null,
            'is_required' => $validated['is_required'] ?? false,
            'settings' => $validated['settings'] ?? null,
        ]);

        // Sync options
        $question->options()->delete();
        if (!empty($validated['options'])) {
            foreach ($validated['options'] as $i => $opt) {
                $question->options()->create([
                    'label' => $opt['label'],
                    'value' => $opt['value'] ?: OptionValue::fromLabel($opt['label']),
                    'image_url' => $opt['image_url'] ?? null,
                    'order_index' => $i,
                ]);
            }
        }

        // Sync logic rules
        $question->logicRules()->delete();
        if (!empty($validated['logic'])) {
            foreach ($validated['logic'] as $rule) {
                $question->logicRules()->create([
                    'form_id' => $form->id,
                    'operator' => $rule['operator'],
                    'value' => $rule['value'] ?? '',
                    'next_question_id' => $rule['next_question_id'],
                ]);
            }
        }

        $question->load(['options', 'logicRules']);

        QuestionChanged::dispatch($form, 'updated', ['question_text' => $question->question_text, 'type' => $question->type]);

        return response()->json(['data' => $question]);
    }

    public function destroy(Form $form, FormQuestion $question): JsonResponse
    {
        $this->authorizeOwner($form);

        QuestionChanged::dispatch($form, 'deleted', ['question_text' => $question->question_text]);

        $question->delete();

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, Form $form): JsonResponse
    {
        $this->authorizeOwner($form);
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:form_questions,id',
        ]);

        foreach ($validated['order'] as $index => $questionId) {
            FormQuestion::where('id', $questionId)->where('form_id', $form->id)->update(['order_index' => $index]);
        }

        return response()->json(['success' => true]);
    }
}

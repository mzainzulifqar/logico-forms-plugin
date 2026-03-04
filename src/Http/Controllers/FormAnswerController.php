<?php

namespace Logicoforms\Forms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Logicoforms\Forms\Models\FormAnswer;
use Logicoforms\Forms\Models\FormSession;
use Logicoforms\Forms\Services\LogicEngine;

class FormAnswerController extends Controller
{
    public function store(string $uuid, Request $request): JsonResponse
    {
        $session = FormSession::where('session_uuid', $uuid)->firstOrFail();

        if ($session->is_completed) {
            return response()->json(['error' => 'Session is already completed.'], 422);
        }

        $request->validate([
            'question_id' => 'required|integer|exists:form_questions,id',
            'answer'      => 'present',
        ]);

        $questionId  = $request->input('question_id');
        $answerValue = $request->input('answer');

        FormAnswer::create([
            'session_id'  => $session->id,
            'question_id' => $questionId,
            'answer_value'=> $answerValue,
            'answered_at' => now(),
        ]);

        $nextQuestion = LogicEngine::getNextQuestion($session, $questionId, $answerValue);

        if (!$nextQuestion) {
            $session->update(['is_completed' => true, 'current_question_id' => null]);

            return response()->json([
                'data' => [
                    'session_uuid' => $session->session_uuid,
                    'completed'    => true,
                    'question'     => null,
                    'progress'     => LogicEngine::getProgress($session),
                    'end_screen'   => [
                        'title'     => $session->form->end_screen_title,
                        'message'   => $session->form->end_screen_message,
                        'image_url' => $session->form->end_screen_image_url,
                    ],
                ],
            ]);
        }

        $session->update(['current_question_id' => $nextQuestion->id]);

        return response()->json([
            'data' => [
                'session_uuid' => $session->session_uuid,
                'completed'    => false,
                'question'     => [
                    'id'            => $nextQuestion->id,
                    'type'          => $nextQuestion->type,
                    'question_text' => $nextQuestion->question_text,
                    'help_text'     => $nextQuestion->help_text,
                    'is_required'   => $nextQuestion->is_required,
                    'settings'      => $nextQuestion->settings,
                    'options'       => $nextQuestion->options->map(fn($o) => [
                        'id'        => $o->id,
                        'label'     => $o->label,
                        'value'     => $o->value,
                        'image_url' => $o->image_url,
                    ])->values(),
                ],
                'progress' => LogicEngine::getProgress($session),
            ],
        ]);
    }
}

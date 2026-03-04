<?php

namespace Logicoforms\Forms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Logicoforms\Forms\Contracts\FormLimiter;
use Logicoforms\Forms\Models\Form;
use Logicoforms\Forms\Models\FormSession;
use Logicoforms\Forms\Services\LogicEngine;

class FormSessionController extends Controller
{
    public function store(Form $form, FormLimiter $limiter): JsonResponse
    {
        if ($form->status !== 'published') {
            return response()->json(['error' => 'Form is not available.'], 404);
        }

        // Enforce response-per-form limit (current month only)
        $owner = $form->creator;
        if ($owner) {
            $limit = $limiter->maxResponsesPerForm($owner);
            if ($limit !== null) {
                $monthlyCount = $form->sessions()
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->count();
                if ($monthlyCount >= $limit) {
                    return response()->json(['error' => 'This form has reached its monthly response limit.'], 403);
                }
            }
        }

        $firstQuestion = $form->firstQuestion();

        if (!$firstQuestion) {
            return response()->json(['error' => 'Form has no questions.'], 422);
        }

        $session = FormSession::create([
            'form_id'             => $form->id,
            'current_question_id' => $firstQuestion->id,
        ]);

        $progress = LogicEngine::getProgress($session);

        return response()->json([
            'data' => [
                'session_uuid' => $session->session_uuid,
                'completed'    => false,
                'question'     => [
                    'id'            => $firstQuestion->id,
                    'type'          => $firstQuestion->type,
                    'question_text' => $firstQuestion->question_text,
                    'help_text'     => $firstQuestion->help_text,
                    'is_required'   => $firstQuestion->is_required,
                    'settings'      => $firstQuestion->settings,
                    'options'       => $firstQuestion->options->map(fn($o) => [
                        'id'        => $o->id,
                        'label'     => $o->label,
                        'value'     => $o->value,
                        'image_url' => $o->image_url,
                    ])->values(),
                ],
                'progress' => $progress,
            ],
        ]);
    }
}

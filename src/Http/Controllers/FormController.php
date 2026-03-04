<?php

namespace Logicoforms\Forms\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Logicoforms\Forms\Ai\Tools\CreateForm;
use Logicoforms\Forms\Contracts\FormLimiter;
use Logicoforms\Forms\Events\FormCreated;
use Logicoforms\Forms\Events\FormDeleted;
use Logicoforms\Forms\Events\FormUpdated;
use Logicoforms\Forms\Models\Form;
use Logicoforms\Forms\Models\FormSession;
use Logicoforms\Forms\Services\FormTemplateService;
use Laravel\Ai\Tools\Request as AiRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FormController extends Controller
{
    public function __construct(private FormLimiter $limiter)
    {
    }

    private function authorizeOwner(Form $form): void
    {
        if ((int) $form->created_by !== (int) auth()->id()) {
            throw new NotFoundHttpException;
        }
    }

    private function limitRedirect(): \Illuminate\Http\RedirectResponse
    {
        $url = $this->limiter->formLimitRedirectUrl() ?? route('forms.index');

        return redirect($url)
            ->with('error', 'You\'ve reached the form limit for your current plan.');
    }

    // ── Public ───────────────────────────────────────────────────

    public function showPublic(string $slug)
    {
        $form = Form::where('slug', $slug)->where('status', 'published')->firstOrFail();

        $defaultTheme = [
            'background_color' => '#FFFFFF',
            'question_color' => '#191919',
            'answer_color' => '#0445AF',
            'button_color' => '#0445AF',
            'button_text_color' => '#FFFFFF',
            'font' => 'Inter',
            'border_radius' => 'medium',
        ];

        $theme = array_merge($defaultTheme, $form->theme ?? []);

        $owner = $form->creator;
        $showBranding = $owner ? ! $this->limiter->canRemoveBranding($owner) : true;

        return view('forms::forms.show-public', [
            'formId' => $form->id,
            'slug' => $form->slug,
            'title' => $form->title,
            'description' => $form->description,
            'theme' => $theme,
            'showBranding' => $showBranding,
            'endScreen' => [
                'title' => $form->end_screen_title,
                'message' => $form->end_screen_message,
                'image_url' => $form->end_screen_image_url,
            ],
        ]);
    }

    public function ogImage(string $slug)
    {
        $form = Form::where('slug', $slug)->where('status', 'published')->firstOrFail();

        $width = 1200;
        $height = 630;
        $img = imagecreatetruecolor($width, $height);
        imagesavealpha($img, true);
        imagealphablending($img, true);

        $theme = $form->theme ?? [];
        $bgHex = $theme['background_color'] ?? '#FFFFFF';
        $textHex = $theme['question_color'] ?? '#191919';
        $accentHex = $theme['button_color'] ?? '#0445AF';

        $bg = $this->hexToRgb($bgHex);
        $text = $this->hexToRgb($textHex);
        $accent = $this->hexToRgb($accentHex);

        $bgLuminance = (0.299 * $bg[0] + 0.587 * $bg[1] + 0.114 * $bg[2]) / 255;
        $isDark = $bgLuminance < 0.5;

        $bgColor = imagecolorallocate($img, $bg[0], $bg[1], $bg[2]);
        $textColor = imagecolorallocate($img, $text[0], $text[1], $text[2]);
        $accentColor = imagecolorallocate($img, $accent[0], $accent[1], $accent[2]);
        $mutedColor = imagecolorallocate(
            $img,
            (int) ($text[0] * 0.55 + $bg[0] * 0.45),
            (int) ($text[1] * 0.55 + $bg[1] * 0.45),
            (int) ($text[2] * 0.55 + $bg[2] * 0.45),
        );
        $separatorColor = imagecolorallocate(
            $img,
            (int) ($text[0] * 0.12 + $bg[0] * 0.88),
            (int) ($text[1] * 0.12 + $bg[1] * 0.88),
            (int) ($text[2] * 0.12 + $bg[2] * 0.88),
        );

        imagefill($img, 0, 0, $bgColor);
        imagefilledrectangle($img, 0, 0, $width, 5, $accentColor);

        $fontDir = __DIR__ . '/../../../resources/fonts';
        $fontBold = "{$fontDir}/Inter-Bold.ttf";
        $fontSemiBold = "{$fontDir}/Inter-SemiBold.ttf";
        $fontMedium = "{$fontDir}/Inter-Medium.ttf";
        $fontRegular = "{$fontDir}/Inter-Regular.ttf";

        $hasFonts = is_file($fontBold) && is_file($fontRegular);
        $px = 80;

        $brandName = config('forms.brand.name', 'Logicoforms');
        $brandDomain = config('forms.brand.domain', 'logicoforms.com');
        $brandTagline = config('forms.brand.tagline', 'AI-powered forms  ·  Logic branching  ·  Smart workflows');

        if ($hasFonts) {
            $logoColoredPath = __DIR__ . '/../../../resources/images/logo-80.png';
            $logoWhitePath = __DIR__ . '/../../../resources/images/logo-80-white.png';
            $logoPath = $isDark ? $logoWhitePath : $logoColoredPath;
            $logoX = $px;
            $logoY = 48;
            $targetH = 40;

            if (is_file($logoPath)) {
                $logo = imagecreatefrompng($logoPath);
                if ($logo) {
                    imagealphablending($logo, true);
                    $srcW = imagesx($logo);
                    $srcH = imagesy($logo);
                    $targetW = (int) ($srcW * ($targetH / $srcH));
                    imagecopyresampled($img, $logo, $logoX, $logoY, 0, 0, $targetW, $targetH, $srcW, $srcH);
                    imagedestroy($logo);

                    $brandFont = is_file($fontSemiBold) ? $fontSemiBold : $fontBold;
                    imagettftext($img, 16, 0, $logoX + $targetW + 14, $logoY + 28, $textColor, $brandFont, $brandName);
                }
            } else {
                $brandFont = is_file($fontSemiBold) ? $fontSemiBold : $fontBold;
                imagettftext($img, 16, 0, $logoX, $logoY + 28, $textColor, $brandFont, $brandName);
            }

            $title = mb_substr($form->title, 0, 90);
            $titleSize = 48;
            $titleY = 180;
            $maxW = $width - $px * 2;
            $titleLineH = 62;
            $this->renderWrappedText($img, $titleSize, $fontBold, $textColor, $px, $titleY, $maxW, $title, $titleLineH);

            $titleLines = $this->countWrappedLines($titleSize, $fontBold, $maxW, $title);
            $titleEndY = $titleY + ($titleLines - 1) * $titleLineH;

            if ($form->description) {
                $desc = mb_substr(strip_tags($form->description), 0, 160);
                if (mb_strlen($form->description) > 160) {
                    $desc .= '...';
                }
                $descFont = is_file($fontRegular) ? $fontRegular : $fontMedium;
                $descY = $titleEndY + 40;
                $this->renderWrappedText($img, 22, $descFont, $mutedColor, $px, $descY, $maxW, $desc, 34);
            }

            $separatorY = $height - 80;
            imagefilledrectangle($img, $px, $separatorY, $width - $px, $separatorY + 1, $separatorColor);

            $domainFont = is_file($fontMedium) ? $fontMedium : $fontRegular;
            $taglineFont = is_file($fontRegular) ? $fontRegular : $fontMedium;

            imagettftext($img, 14, 0, $px, $separatorY + 32, $mutedColor, $domainFont, $brandDomain);

            $tagBbox = imagettfbbox(13, 0, $taglineFont, $brandTagline);
            $tagWidth = abs($tagBbox[2] - $tagBbox[0]);
            imagettftext($img, 13, 0, $width - $px - $tagWidth, $separatorY + 32, $mutedColor, $taglineFont, $brandTagline);
        } else {
            imagestring($img, 5, $px, 60, $brandName, $textColor);
            imagestring($img, 5, $px, 160, mb_substr($form->title, 0, 60), $textColor);
            if ($form->description) {
                imagestring($img, 4, $px, 220, mb_substr($form->description, 0, 100), $mutedColor);
            }
            imagestring($img, 3, $px, $height - 50, $brandDomain, $mutedColor);
        }

        ob_start();
        imagepng($img, null, 6);
        $imageData = ob_get_clean();
        imagedestroy($img);

        return response($imageData, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private function renderWrappedText($img, int $size, string $font, $color, int $x, int $y, int $maxWidth, string $text, int $lineHeight): void
    {
        $words = explode(' ', $text);
        $line = '';
        $currentY = $y;

        foreach ($words as $word) {
            $testLine = $line ? "{$line} {$word}" : $word;
            $bbox = imagettfbbox($size, 0, $font, $testLine);
            $lineWidth = abs($bbox[2] - $bbox[0]);

            if ($lineWidth > $maxWidth && $line !== '') {
                imagettftext($img, $size, 0, $x, $currentY, $color, $font, $line);
                $line = $word;
                $currentY += $lineHeight;
            } else {
                $line = $testLine;
            }
        }

        if ($line !== '') {
            imagettftext($img, $size, 0, $x, $currentY, $color, $font, $line);
        }
    }

    private function countWrappedLines(int $size, string $font, int $maxWidth, string $text): int
    {
        $words = explode(' ', $text);
        $line = '';
        $lines = 1;

        foreach ($words as $word) {
            $testLine = $line ? "{$line} {$word}" : $word;
            $bbox = imagettfbbox($size, 0, $font, $testLine);
            $lineWidth = abs($bbox[2] - $bbox[0]);

            if ($lineWidth > $maxWidth && $line !== '') {
                $line = $word;
                $lines++;
            } else {
                $line = $testLine;
            }
        }

        return $lines;
    }

    // ── Admin CRUD ──────────────────────────────────────────────

    public function index()
    {
        $user = auth()->user();
        $forms = Form::where('created_by', $user->getKey())
            ->withCount(['questions', 'sessions'])
            ->latest()
            ->get();

        $atLimit = ! $this->limiter->canCreateForm($user);

        return view('forms::forms.index', compact('forms', 'atLimit'));
    }

    public function templates()
    {
        $user = auth()->user();
        if (! $this->limiter->canCreateForm($user)) {
            return $this->limitRedirect();
        }

        $templates = FormTemplateService::all();
        $categories = FormTemplateService::categories();

        return view('forms::forms.templates', compact('templates', 'categories'));
    }

    public function createFromTemplate(string $slug)
    {
        $user = auth()->user();
        if (! $this->limiter->canCreateForm($user)) {
            return $this->limitRedirect();
        }

        $template = FormTemplateService::find($slug);
        if (! $template) {
            abort(404);
        }

        $maxIdBefore = Form::where('created_by', $user->getKey())->max('id') ?? 0;

        (new CreateForm)->handle(new AiRequest([
            'title' => $template['title'],
            'description' => $template['description'],
            'questions' => $template['questions'],
        ]));

        $form = Form::where('created_by', $user->getKey())
            ->where('id', '>', $maxIdBefore)
            ->latest('id')
            ->first();

        if (! $form) {
            return redirect()->route('forms.templates')
                ->with('error', 'Failed to create form from template. Please try again.');
        }

        if (! empty($template['theme'])) {
            $form->update(['theme' => $template['theme']]);
        }

        FormCreated::dispatch($form, ['template' => $slug]);

        return redirect()->route('forms.edit', $form)->with('success', 'Form created from template.');
    }

    public function create()
    {
        $user = auth()->user();
        if (! $this->limiter->canCreateForm($user)) {
            return $this->limitRedirect();
        }

        return view('forms::forms.create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (! $this->limiter->canCreateForm($user)) {
            return $this->limitRedirect();
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $form = Form::create($validated);

        FormCreated::dispatch($form);

        return redirect()->route('forms.edit', $form)->with('success', 'Form created.');
    }

    public function show(Form $form)
    {
        $this->authorizeOwner($form);
        $form->load('questions');
        $questions = $form->questions;

        $totalSessions = $form->sessions()->count();
        $completedSessions = $form->sessions()->where('is_completed', true)->count();
        $completionRate = $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100) : 0;

        $sessionsPaginator = FormSession::where('form_id', $form->id)
            ->where('is_completed', true)
            ->with('answers')
            ->latest()
            ->paginate(50);

        $sessions = $sessionsPaginator->getCollection()
            ->map(function ($session) use ($questions) {
                $answers = [];
                foreach ($questions as $q) {
                    $answer = $session->answers->firstWhere('question_id', $q->id);
                    $val = $answer ? $answer->answer_value : null;
                    if (is_array($val)) {
                        $val = implode(', ', $val);
                    }
                    $answers[$q->id] = $val;
                }
                return [
                    'uuid' => $session->session_uuid,
                    'completed_at' => $session->updated_at->format('M j, Y g:ia'),
                    'answers' => $answers,
                ];
            });

        $questionStats = $questions->map(function ($q) {
            $allAnswers = $q->form->sessions()
                ->where('is_completed', true)
                ->join('form_answers', 'form_sessions.id', '=', 'form_answers.session_id')
                ->where('form_answers.question_id', $q->id)
                ->pluck('form_answers.answer_value')
                ->map(fn ($v) => json_decode($v, true));

            $stats = ['question' => $q, 'count' => $allAnswers->count(), 'breakdown' => null];

            if (in_array($q->type, ['radio', 'select', 'checkbox', 'picture_choice'])) {
                $breakdown = [];
                foreach ($allAnswers as $val) {
                    if (is_array($val)) {
                        foreach ($val as $v) {
                            $breakdown[$v] = ($breakdown[$v] ?? 0) + 1;
                        }
                    } else {
                        $breakdown[(string) $val] = ($breakdown[(string) $val] ?? 0) + 1;
                    }
                }
                arsort($breakdown);
                $stats['breakdown'] = $breakdown;
            } elseif ($q->type === 'rating' || $q->type === 'number') {
                $nums = $allAnswers->filter(fn ($v) => is_numeric($v))->map(fn ($v) => (float) $v);
                if ($nums->isNotEmpty()) {
                    $stats['avg'] = round($nums->avg(), 1);
                    $stats['min'] = $nums->min();
                    $stats['max'] = $nums->max();
                }
            }

            return $stats;
        });

        return view('forms::forms.responses', compact(
            'form', 'questions', 'sessions', 'sessionsPaginator', 'totalSessions', 'completedSessions', 'completionRate', 'questionStats'
        ));
    }

    public function edit(Form $form)
    {
        $this->authorizeOwner($form);
        $form->load(['questions.options', 'questions.logicRules']);

        $questionsJson = $form->questions->map(function ($q) {
            return [
                'id' => $q->id,
                'type' => $q->type,
                'question_text' => $q->question_text,
                'help_text' => $q->help_text,
                'is_required' => $q->is_required,
                'order_index' => $q->order_index,
                'settings' => $q->settings ?? [],
                'options' => $q->options->map(fn ($o) => ['label' => $o->label, 'value' => $o->value, 'image_url' => $o->image_url])->values(),
                'logic_rules' => $q->logicRules->map(fn ($r) => ['operator' => $r->operator, 'value' => $r->value, 'next_question_id' => (string) $r->next_question_id])->values(),
            ];
        })->values();

        $defaultTheme = [
            'background_color' => '#FFFFFF',
            'question_color' => '#191919',
            'answer_color' => '#0445AF',
            'button_color' => '#0445AF',
            'button_text_color' => '#FFFFFF',
            'font' => 'Inter',
            'border_radius' => 'medium',
        ];
        $theme = array_merge($defaultTheme, $form->theme ?? []);
        $canCustomizeDesign = $this->limiter->canUseCustomThemes(auth()->user());

        return view('forms::forms.edit', compact('form', 'questionsJson', 'theme', 'canCustomizeDesign'));
    }

    public function update(Request $request, Form $form)
    {
        $this->authorizeOwner($form);
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'status' => 'sometimes|required|in:draft,published,closed',
            'theme' => 'sometimes|array',
            'theme.background_color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme.question_color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme.answer_color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme.button_color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme.button_text_color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme.font' => 'sometimes|string|max:100',
            'theme.border_radius' => 'sometimes|string|in:none,small,medium,large',
            'end_screen_title' => 'sometimes|nullable|string|max:255',
            'end_screen_message' => 'sometimes|nullable|string',
            'end_screen_image_url' => 'sometimes|nullable|string|max:2048',
        ]);

        $form->update($validated);

        FormUpdated::dispatch($form);

        if ($request->wantsJson()) {
            return response()->json(['data' => $form]);
        }

        return redirect()->route('forms.edit', $form)->with('success', 'Form updated.');
    }

    public function destroy(Form $form)
    {
        $this->authorizeOwner($form);

        FormDeleted::dispatch($form->id, $form->title);

        $form->delete();

        return redirect()->route('forms.index')->with('success', 'Form deleted.');
    }

    // ── Logic Tree ───────────────────────────────────────────────

    public function logicTree(Form $form)
    {
        $this->authorizeOwner($form);
        $form->load(['questions.options', 'questions.logicRules']);
        $questions = $form->questions;

        $mermaid = $this->buildMermaidDiagram($form, $questions);

        $tree = $questions->map(function ($q, $idx) use ($questions) {
            $rules = $q->logicRules->map(fn ($r) => [
                'operator' => $r->operator,
                'value' => $r->value,
                'next_question_id' => $r->next_question_id,
            ])->values();

            $nextIdx = $idx + 1;
            $defaultNext = $nextIdx < $questions->count() ? $questions[$nextIdx]->id : null;

            return [
                'id' => $q->id,
                'number' => $idx + 1,
                'text' => $q->question_text,
                'type' => $q->type,
                'options' => $q->options->map(fn ($o) => ['label' => $o->label, 'value' => $o->value, 'image_url' => $o->image_url])->values(),
                'rules' => $rules,
                'default_next' => $defaultNext,
            ];
        })->values();

        return view('forms::forms.logic-tree', compact('form', 'mermaid', 'tree'));
    }

    private function buildMermaidDiagram(Form $form, $questions): string
    {
        $lines = ['graph TD'];

        $idMap = [];
        foreach ($questions as $idx => $q) {
            $idMap[$q->id] = 'Q' . ($idx + 1);
        }

        foreach ($questions as $idx => $q) {
            $nodeId = $idMap[$q->id];
            $label = 'Q' . ($idx + 1) . '. ' . addcslashes(mb_substr($q->question_text, 0, 50), '"');
            $hasRules = $q->logicRules->isNotEmpty();

            if ($hasRules) {
                $lines[] = "    {$nodeId}" . '{{"' . $label . '"}}';
            } else {
                $lines[] = "    {$nodeId}" . '["' . $label . '"]';
            }
        }

        $lines[] = '    DONE([" Done"])';
        $lines[] = '';

        foreach ($questions as $idx => $q) {
            $nodeId = $idMap[$q->id];
            $rules = $q->logicRules;
            $nextIdx = $idx + 1;
            $defaultTargetId = $nextIdx < $questions->count() ? $idMap[$questions[$nextIdx]->id] : 'DONE';

            if ($rules->isEmpty()) {
                $lines[] = "    {$nodeId} --> {$defaultTargetId}";
                continue;
            }

            $hasAlways = $rules->contains(fn ($r) => $r->operator === 'always');

            if ($hasAlways) {
                $alwaysRule = $rules->first(fn ($r) => $r->operator === 'always');
                $target = isset($idMap[$alwaysRule->next_question_id]) ? $idMap[$alwaysRule->next_question_id] : 'DONE';
                $lines[] = "    {$nodeId} --> {$target}";
            } else {
                foreach ($rules as $rule) {
                    $target = isset($idMap[$rule->next_question_id]) ? $idMap[$rule->next_question_id] : 'DONE';
                    $val = addcslashes($rule->value, '"');
                    $lines[] = "    {$nodeId} -->|\"" . $val . "\"| {$target}";
                }

                $optionValues = $q->options->pluck('value')->toArray();
                $coveredValues = $rules->pluck('value')->toArray();
                $uncovered = array_diff($optionValues, $coveredValues);

                if (! empty($uncovered)) {
                    $defaultLabel = count($uncovered) === 1 ? addcslashes(reset($uncovered), '"') : 'default';
                    $lines[] = "    {$nodeId} -->|\"" . $defaultLabel . "\"| {$defaultTargetId}";
                }
            }
        }

        $lines[] = '';
        $lines[] = '    classDef branch fill:#EEF2FF,stroke:#6366F1,stroke-width:2px,color:#3730A3';
        $lines[] = '    classDef normal fill:#F9FAFB,stroke:#D1D5DB,stroke-width:1px,color:#374151';
        $lines[] = '    classDef done fill:#ECFDF5,stroke:#10B981,stroke-width:2px,color:#065F46';

        $branchNodes = [];
        $normalNodes = [];
        foreach ($questions as $idx => $q) {
            $nodeId = $idMap[$q->id];
            if ($q->logicRules->isNotEmpty()) {
                $branchNodes[] = $nodeId;
            } else {
                $normalNodes[] = $nodeId;
            }
        }

        if (! empty($branchNodes)) {
            $lines[] = '    class ' . implode(',', $branchNodes) . ' branch';
        }
        if (! empty($normalNodes)) {
            $lines[] = '    class ' . implode(',', $normalNodes) . ' normal';
        }
        $lines[] = '    class DONE done';

        return implode("\n", $lines);
    }
}

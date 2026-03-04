<?php

namespace Logicoforms\Forms\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Logicoforms\Forms\Ai\ToolStatusReporter;
use Logicoforms\Forms\Models\Form;
use Stringable;

class PublishForm implements Tool
{
    public function description(): Stringable|string
    {
        return 'Publish a draft form so respondents can fill it out. Returns the public URL.';
    }

    public function handle(Request $request): Stringable|string
    {
        app(ToolStatusReporter::class)->report('Publishing…');

        $form = Form::find($request['form_id']);

        if (!$form) {
            return "Error: Form with ID {$request['form_id']} not found.";
        }

        $publicUrl = route('forms.public', $form->slug);
        $editUrl   = route('forms.edit', $form);

        if ($form->status === 'published') {
            return "Form \"{$form->title}\" is already published.\nPublic URL: {$publicUrl}";
        }

        $form->update(['status' => 'published']);

        return "Form \"{$form->title}\" is now published!\nPublic URL: {$publicUrl}\nEdit URL: {$editUrl}";
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'form_id' => $schema->integer()->description('The ID of the form to publish.')->required(),
        ];
    }
}

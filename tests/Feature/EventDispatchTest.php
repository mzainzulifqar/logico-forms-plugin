<?php

namespace Logicoforms\Forms\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Logicoforms\Forms\Events\FormCreated;
use Logicoforms\Forms\Events\FormDeleted;
use Logicoforms\Forms\Events\FormUpdated;
use Logicoforms\Forms\Events\QuestionChanged;
use Logicoforms\Forms\Models\Form;
use Logicoforms\Forms\Models\FormQuestion;
use Logicoforms\Forms\Tests\TestCase;

class EventDispatchTest extends TestCase
{
    public function test_form_created_event_on_store(): void
    {
        Event::fake([FormCreated::class]);

        $user = $this->actingAsUser();

        $this->post(route('forms.store'), [
            'title' => 'Event Test Form',
        ]);

        Event::assertDispatched(FormCreated::class, function ($event) {
            return $event->form->title === 'Event Test Form';
        });
    }

    public function test_form_updated_event_on_update(): void
    {
        Event::fake([FormUpdated::class]);

        $user = $this->actingAsUser();
        $form = Form::create(['title' => 'Original', 'created_by' => $user->getKey()]);

        $this->put(route('forms.update', $form), [
            'title' => 'Updated Title',
        ]);

        Event::assertDispatched(FormUpdated::class, function ($event) {
            return $event->form->title === 'Updated Title';
        });
    }

    public function test_form_deleted_event_on_delete(): void
    {
        Event::fake([FormDeleted::class]);

        $user = $this->actingAsUser();
        $form = Form::create(['title' => 'To Delete', 'created_by' => $user->getKey()]);

        $this->delete(route('forms.destroy', $form));

        Event::assertDispatched(FormDeleted::class, function ($event) use ($form) {
            return $event->formId === $form->id && $event->formTitle === 'To Delete';
        });
    }

    public function test_question_changed_event_on_add(): void
    {
        Event::fake([QuestionChanged::class]);

        $user = $this->actingAsUser();
        $form = Form::create(['title' => 'Test', 'created_by' => $user->getKey()]);

        $this->postJson(route('forms.questions.store', $form), [
            'type' => 'text',
            'question_text' => 'New question',
        ]);

        Event::assertDispatched(QuestionChanged::class, function ($event) {
            return $event->action === 'added';
        });
    }

    public function test_question_changed_event_on_delete(): void
    {
        Event::fake([QuestionChanged::class]);

        $user = $this->actingAsUser();
        $form = Form::create(['title' => 'Test', 'created_by' => $user->getKey()]);
        $q = FormQuestion::create([
            'form_id' => $form->id,
            'type' => 'text',
            'question_text' => 'Delete me',
            'order_index' => 0,
        ]);

        $this->deleteJson(route('forms.questions.destroy', [$form, $q]));

        Event::assertDispatched(QuestionChanged::class, function ($event) {
            return $event->action === 'deleted';
        });
    }
}

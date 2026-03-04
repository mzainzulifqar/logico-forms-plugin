<?php

namespace Logicoforms\Forms\Tests\Feature;

use Logicoforms\Forms\Models\Form;
use Logicoforms\Forms\Models\FormQuestion;
use Logicoforms\Forms\Tests\TestCase;

class FormCrudTest extends TestCase
{
    public function test_list_own_forms(): void
    {
        $user = $this->actingAsUser();
        Form::create(['title' => 'My Form', 'created_by' => $user->getKey()]);

        $response = $this->get(route('forms.index'));

        $response->assertOk();
        $response->assertSee('My Form');
    }

    public function test_cannot_see_other_users_forms_in_list(): void
    {
        $user = $this->actingAsUser();
        $other = $this->createUser(['email' => 'other@example.com']);
        Form::create(['title' => 'Other Form', 'created_by' => $other->getKey()]);

        $response = $this->get(route('forms.index'));

        $response->assertOk();
        $response->assertDontSee('Other Form');
    }

    public function test_create_form(): void
    {
        $user = $this->actingAsUser();

        $response = $this->post(route('forms.store'), [
            'title' => 'New Form',
            'description' => 'A test form',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('forms', [
            'title' => 'New Form',
            'created_by' => $user->getKey(),
        ]);
    }

    public function test_update_form_title(): void
    {
        $user = $this->actingAsUser();
        $form = Form::create(['title' => 'Old Title', 'created_by' => $user->getKey()]);

        $response = $this->put(route('forms.update', $form), [
            'title' => 'New Title',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('forms', ['id' => $form->id, 'title' => 'New Title']);
    }

    public function test_update_form_status(): void
    {
        $user = $this->actingAsUser();
        $form = Form::create(['title' => 'Test', 'created_by' => $user->getKey()]);

        $this->put(route('forms.update', $form), ['status' => 'published']);

        $this->assertDatabaseHas('forms', ['id' => $form->id, 'status' => 'published']);
    }

    public function test_update_form_theme(): void
    {
        $user = $this->actingAsUser();
        $form = Form::create(['title' => 'Test', 'created_by' => $user->getKey()]);

        $this->put(route('forms.update', $form), [
            'theme' => [
                'background_color' => '#FF0000',
                'question_color' => '#000000',
                'button_color' => '#00FF00',
                'button_text_color' => '#FFFFFF',
                'answer_color' => '#0000FF',
                'font' => 'Arial',
                'border_radius' => 'large',
            ],
        ]);

        $form->refresh();
        $this->assertEquals('#FF0000', $form->theme['background_color']);
    }

    public function test_delete_form(): void
    {
        $user = $this->actingAsUser();
        $form = Form::create(['title' => 'To Delete', 'created_by' => $user->getKey()]);

        $response = $this->delete(route('forms.destroy', $form));

        $response->assertRedirect(route('forms.index'));
        $this->assertSoftDeleted('forms', ['id' => $form->id]);
    }

    public function test_view_responses_page(): void
    {
        $user = $this->actingAsUser();
        $form = Form::create(['title' => 'Responses Test', 'created_by' => $user->getKey()]);
        FormQuestion::create([
            'form_id' => $form->id,
            'type' => 'text',
            'question_text' => 'Q1',
            'order_index' => 0,
        ]);

        $response = $this->get(route('forms.show', $form));

        $response->assertOk();
    }

    public function test_unauthenticated_gets_error(): void
    {
        // Without auth middleware configured in test env, unauthenticated
        // users get an error (500/redirect depending on middleware setup)
        $response = $this->get(route('forms.index'));

        // Should not be 200 (must not show forms to unauthenticated users)
        $this->assertNotEquals(200, $response->status());
    }

    public function test_show_public_works_for_published_form(): void
    {
        $form = Form::create([
            'title' => 'Public Form',
            'status' => 'published',
            'created_by' => 1,
        ]);

        $response = $this->get("/f/{$form->slug}");

        $response->assertOk();
    }

    public function test_show_public_404s_for_draft_form(): void
    {
        $form = Form::create([
            'title' => 'Draft Form',
            'status' => 'draft',
            'created_by' => 1,
        ]);

        $response = $this->get("/f/{$form->slug}");

        $response->assertStatus(404);
    }

    public function test_cannot_edit_other_users_form(): void
    {
        $user = $this->actingAsUser();
        $other = $this->createUser(['email' => 'other@example.com']);
        $form = Form::create(['title' => 'Not Mine', 'created_by' => $other->getKey()]);

        $response = $this->get(route('forms.edit', $form));
        $response->assertStatus(404);
    }
}

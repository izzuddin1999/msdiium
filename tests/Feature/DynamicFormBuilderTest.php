<?php

namespace Tests\Feature;

use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicFormBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['features.form_builder' => true]);
    }

    public function test_manager_can_create_template_and_configure_field(): void
    {
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'kcdiom', 'is_active' => true]);
        $response = $this->actingAs($manager)->post(route('form-templates.store'), [
            'name' => 'KCDIOM Policy Review', 'description' => 'Operational review data', 'document_type' => 'policy',
            'owner_unit' => 'kcdiom', 'columns' => 3, 'is_active' => 1,
        ]);
        $template = FormTemplate::firstOrFail();
        $response->assertRedirect(route('form-templates.edit', $template));

        $this->post(route('form-templates.fields.store', $template), [
            'label' => 'Risk Level', 'name' => 'risk_level', 'type' => 'select', 'section' => 'Risk assessment',
            'width' => 1, 'is_required' => 1, 'options_text' => "low|Low\nhigh|High", 'sort_order' => 10,
        ])->assertRedirect();

        $this->assertDatabaseHas('form_fields', ['form_template_id' => $template->id, 'name' => 'risk_level', 'is_required' => true]);
        $this->get(route('form-templates.edit', $template))->assertOk()->assertSee('Live form preview')->assertSee('Risk Level');
    }

    public function test_dynamic_values_are_validated_and_saved_with_document(): void
    {
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'kcdiom', 'is_active' => true]);
        $template = FormTemplate::create(['name' => 'Circular Approval', 'slug' => 'circular-approval', 'document_type' => 'circular', 'owner_unit' => 'kcdiom', 'columns' => 3, 'is_active' => true, 'created_by' => $manager->id]);
        FormField::create(['form_template_id' => $template->id, 'label' => 'Approval Code', 'name' => 'approval_code', 'type' => 'text', 'section' => 'Approval', 'width' => 1, 'is_required' => true, 'validation' => ['max' => 20], 'sort_order' => 10]);

        $payload = ['title' => 'Dynamic Circular', 'document_type' => 'circular', 'content' => 'Circular content', 'access_scope' => 'kcdiom', 'owner_unit' => 'kcdiom', 'status' => 'draft', 'created_by' => $manager->id, 'form_template_id' => $template->id];
        $this->actingAs($manager)->post(route('policy-documents.store'), $payload)->assertSessionHasErrors('dynamic.approval_code');
        $this->post(route('policy-documents.store'), $payload + ['dynamic' => ['approval_code' => 'KCD-APP-01']])->assertRedirect(route('policy-documents.index'));

        $this->assertDatabaseHas('document_form_responses', ['form_template_id' => $template->id]);
        $this->get(route('policy-documents.show', 1))->assertOk()->assertSee('Circular Approval')->assertSee('KCD-APP-01');
    }

    public function test_staff_cannot_access_form_builder(): void
    {
        $staff = User::factory()->create(['role' => 'staff_user', 'unit' => 'kcdiom', 'is_active' => true]);
        $this->actingAs($staff)->get(route('form-templates.index'))->assertForbidden();
    }

    public function test_manager_can_reorder_fields_from_design_canvas(): void
    {
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'kcdiom', 'is_active' => true]);
        $template = FormTemplate::create(['name' => 'Sortable Form', 'slug' => 'sortable-form', 'owner_unit' => 'kcdiom', 'columns' => 3, 'is_active' => true, 'created_by' => $manager->id]);
        $first = FormField::create(['form_template_id' => $template->id, 'label' => 'First', 'name' => 'first', 'type' => 'text', 'section' => 'Details', 'width' => 1, 'sort_order' => 10]);
        $second = FormField::create(['form_template_id' => $template->id, 'label' => 'Second', 'name' => 'second', 'type' => 'text', 'section' => 'Details', 'width' => 1, 'sort_order' => 20]);

        $this->actingAs($manager)->postJson(route('form-templates.fields.reorder', $template), ['field_ids' => [$second->id, $first->id]])
            ->assertOk()->assertJson(['message' => 'Field order saved.']);

        $this->assertSame([$second->id, $first->id], $template->fields()->pluck('id')->all());
    }

    public function test_new_template_controls_complete_document_registration_form(): void
    {
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'kcdiom', 'is_active' => true]);
        $this->actingAs($manager)->post(route('form-templates.store'), [
            'name' => 'Complete Policy Form', 'document_type' => 'policy', 'owner_unit' => 'kcdiom', 'columns' => 3, 'is_active' => 1,
        ]);
        $template = FormTemplate::firstOrFail();

        $this->assertTrue($template->fields()->where('binding', 'title')->exists());
        $this->assertTrue($template->fields()->where('binding', 'content')->exists());
        $this->actingAs($manager)->get(route('policy-documents.create'))
            ->assertOk()->assertSee('Complete Policy Form')->assertSee('name="title"', false)->assertSee('name="file"', false);

        $this->post(route('policy-documents.store'), [
            'form_template_id' => $template->id, 'title' => 'Template Managed Policy', 'document_type' => 'policy',
            'content' => 'All document data came from the configured template.', 'access_scope' => 'kcdiom',
            'owner_unit' => 'kcdiom', 'status' => 'draft', 'created_by' => $manager->id,
        ])->assertRedirect(route('policy-documents.index'));

        $this->assertDatabaseHas('policy_documents', ['title' => 'Template Managed Policy', 'owner_unit' => 'kcdiom']);
        $this->assertDatabaseHas('document_form_responses', ['form_template_id' => $template->id]);
    }

    public function test_component_can_be_dropped_on_canvas_without_configuration(): void
    {
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'kcdiom', 'is_active' => true]);
        $template = FormTemplate::create(['name' => 'Visual Builder', 'slug' => 'visual-builder', 'owner_unit' => 'kcdiom', 'columns' => 3, 'is_active' => true, 'created_by' => $manager->id]);

        $this->actingAs($manager)->postJson(route('form-templates.components.store', $template), ['type' => 'select'])
            ->assertOk()->assertJsonFragment(['message' => 'Dropdown added.']);

        $field = $template->fields()->firstOrFail();
        $this->assertSame('Dropdown', $field->label);
        $this->assertSame('dropdown', $field->name);
        $this->assertCount(2, $field->options);
    }

    public function test_visual_builder_field_update_and_delete_crud_returns_json(): void
    {
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'kcdiom', 'is_active' => true]);
        $template = FormTemplate::create(['name' => 'CRUD Form', 'slug' => 'crud-form', 'owner_unit' => 'kcdiom', 'columns' => 3, 'is_active' => true]);
        $field = FormField::create(['form_template_id'=>$template->id,'label'=>'Old Label','name'=>'old_label','type'=>'text','section'=>'Details','width'=>1,'sort_order'=>10]);

        $this->actingAs($manager)->putJson(route('form-templates.fields.update', [$template,$field]), [
            'label'=>'New Label','name'=>'old_label','type'=>'text','section'=>'Details','width'=>1,'sort_order'=>10,
        ])->assertOk()->assertJson(['message'=>'Field updated.']);
        $this->assertSame('New Label', $field->fresh()->label);

        $this->deleteJson(route('form-templates.fields.destroy', [$template,$field]))
            ->assertOk()->assertJson(['message'=>'Field removed.']);
        $this->assertDatabaseMissing('form_fields', ['id'=>$field->id]);
    }
}

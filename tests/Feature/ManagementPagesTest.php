<?php

namespace Tests\Feature;

use App\Models\PolicyDocument;
use App\Models\DocumentAttachment;
use App\Models\DocumentHistory;
use App\Models\LookupValue;
use App\Models\TopicCategory;
use App\Models\TopicSubtopic;
use App\Models\User;
use App\Notifications\CircularPublishedNotification;
use App\Notifications\DocumentPublishedNotification;
use App\Notifications\DocumentExpiryReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManagementPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_main_topic_returns_validation_error_instead_of_server_error(): void
    {
        $manager = User::factory()->create(['role' => 'msd_admin', 'unit' => 'msd', 'is_active' => true]);
        $category = TopicCategory::create(['name' => 'Service Matters', 'slug' => 'service-matters', 'is_active' => true]);
        TopicSubtopic::create(['topic_category_id' => $category->id, 'name' => 'SM.1 Appointment', 'slug' => 'sm1-appointment', 'is_active' => true]);

        $this->actingAs($manager)
            ->from(route('topic-categories.index').'#main')
            ->post(route('topic-subtopics.store'), ['topic_category_id' => $category->id, 'name' => ' SM.1 Appointment ', 'is_active' => 1])
            ->assertRedirect(route('topic-categories.index').'#main')
            ->assertSessionHasErrors(['name']);

        $this->assertDatabaseCount('topic_subtopics', 1);
    }

    public function test_form_builder_module_is_disabled(): void
    {
        config(['features.form_builder' => false]);
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'kcdiom', 'is_active' => true]);

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Form Builder');

        $this->get(route('form-templates.index'))->assertNotFound();
        $this->get(route('policy-documents.create'))
            ->assertOk()
            ->assertDontSee('Document template')
            ->assertDontSee('Form Template');
    }

    public function test_management_pages_render_successfully_for_system_admin(): void
    {
        $creator = User::factory()->create([
            'role' => 'system_admin',
            'unit' => 'all',
            'is_active' => true,
        ]);

        $document = PolicyDocument::create([
            'title' => 'Staff Leave Policy',
            'document_type' => 'policy',
            'content' => 'Initial version content.',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'published',
            'is_circular' => false,
            'version_number' => 1,
            'created_by' => $creator->id,
            'published_at' => now(),
        ]);

        PolicyDocument::create([
            'title' => $document->title,
            'document_type' => $document->document_type,
            'content' => 'Second version content.',
            'access_scope' => $document->access_scope,
            'owner_unit' => $document->owner_unit,
            'status' => 'draft',
            'is_circular' => false,
            'version_number' => 2,
            'parent_document_id' => $document->id,
            'created_by' => $creator->id,
        ]);

        $this->actingAs($creator);

        $this->get(route('policy-documents.index'))->assertOk();
        $this->get(route('policy-documents.create'))->assertOk();
        $this->get(route('policy-documents.show', $document))->assertOk();
        $this->get(route('policy-documents.edit', $document))->assertOk();
        $this->get(route('reports.circulars'))->assertOk();
        $this->get(route('reports.versions'))->assertOk();
        $this->get(route('roles.index'))->assertOk();
    }

    public function test_policy_manager_sees_all_documents_regardless_of_unit(): void
    {
        // Both MSD and KCDIOM are one unified Policy Manager actor with full visibility.
        $liaison = User::factory()->create([
            'role' => 'policy_manager',
            'unit' => 'kcdiom',
            'is_active' => true,
        ]);

        $kcdiomDocument = PolicyDocument::create([
            'title' => 'KCDIOM Circular',
            'document_type' => 'circular',
            'access_scope' => 'kcdiom',
            'owner_unit' => 'kcdiom',
            'status' => 'draft',
            'is_circular' => true,
            'version_number' => 1,
            'created_by' => $liaison->id,
        ]);

        $msdDocument = PolicyDocument::create([
            'title' => 'MSD Internal Policy',
            'document_type' => 'policy',
            'access_scope' => 'msd',
            'owner_unit' => 'msd',
            'status' => 'published',
            'is_circular' => false,
            'version_number' => 1,
            'created_by' => $liaison->id,
            'published_at' => now(),
        ]);

        // A KCDIOM policy_manager sees both KCDIOM AND MSD documents (unified actor).
        $this->actingAs($liaison)
            ->get(route('policy-documents.index'))
            ->assertOk()
            ->assertSee('KCDIOM Circular')
            ->assertSee('MSD Internal Policy');

        $this->actingAs($liaison)
            ->get(route('policy-documents.show', $kcdiomDocument))
            ->assertOk();

        $this->actingAs($liaison)
            ->get(route('policy-documents.show', $msdDocument))
            ->assertOk();
    }

    public function test_staff_user_only_sees_published_documents_with_matching_scope(): void
    {
        $staffUser = User::factory()->create([
            'role' => 'staff_user',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        PolicyDocument::create([
            'title' => 'All Staff Circular',
            'document_type' => 'circular',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'published',
            'is_circular' => true,
            'version_number' => 1,
            'published_at' => now(),
        ]);

        PolicyDocument::create([
            'title' => 'MSD Published Policy',
            'document_type' => 'policy',
            'access_scope' => 'msd',
            'owner_unit' => 'msd',
            'status' => 'published',
            'is_circular' => false,
            'version_number' => 1,
            'published_at' => now(),
        ]);

        $hiddenDraft = PolicyDocument::create([
            'title' => 'Draft Circular',
            'document_type' => 'circular',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'draft',
            'is_circular' => true,
            'version_number' => 1,
        ]);

        $hiddenScope = PolicyDocument::create([
            'title' => 'KCDIOM Only Guideline',
            'document_type' => 'guideline',
            'access_scope' => 'kcdiom',
            'owner_unit' => 'kcdiom',
            'status' => 'published',
            'is_circular' => false,
            'version_number' => 1,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($staffUser)->get(route('policy-documents.index'));

        $response->assertOk()
            ->assertSee('All Staff Circular')
            ->assertSee('MSD Published Policy')
            ->assertDontSee('Draft Circular')
            ->assertDontSee('KCDIOM Only Guideline')
            ->assertDontSee('Drafts in progress')
            ->assertDontSee('value="draft"', false);

        $this->actingAs($staffUser)
            ->get(route('policy-documents.index', ['status' => 'draft']))
            ->assertOk()
            ->assertDontSee('Draft Circular')
            ->assertDontSee('value="draft"', false);

        $this->actingAs($staffUser)
            ->get(route('policy-documents.create'))
            ->assertForbidden();

        $this->actingAs($staffUser)
            ->get(route('policy-documents.show', $hiddenDraft))
            ->assertNotFound();

        $this->actingAs($staffUser)
            ->get(route('policy-documents.show', $hiddenScope))
            ->assertNotFound();
    }

    public function test_viewer_session_switcher_changes_browser_access_context(): void
    {
        $msdAdmin = User::factory()->create([
            'name' => 'MSD Admin',
            'role' => 'policy_manager',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $liaison = User::factory()->create([
            'name' => 'KCDIOM Liaison',
            'role' => 'policy_manager',
            'unit' => 'kcdiom',
            'is_active' => true,
        ]);

        PolicyDocument::create([
            'title' => 'MSD Draft Policy',
            'document_type' => 'policy',
            'access_scope' => 'msd',
            'owner_unit' => 'msd',
            'status' => 'draft',
            'is_circular' => false,
            'version_number' => 1,
            'created_by' => $msdAdmin->id,
        ]);

        PolicyDocument::create([
            'title' => 'KCDIOM Draft Circular',
            'document_type' => 'circular',
            'access_scope' => 'kcdiom',
            'owner_unit' => 'kcdiom',
            'status' => 'draft',
            'is_circular' => true,
            'version_number' => 1,
            'created_by' => $liaison->id,
        ]);

        PolicyDocument::create([
            'title' => 'Public Published Circular',
            'document_type' => 'circular',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'published',
            'is_circular' => true,
            'version_number' => 1,
            'created_by' => $msdAdmin->id,
            'published_at' => now(),
        ]);

        // KCDIOM policy_manager is the same unified actor as MSD — sees all documents.
        $this->post(route('viewer-session.store'), ['user_id' => $liaison->id])
            ->assertRedirect();

        $this->get(route('policy-documents.index'))
            ->assertOk()
            ->assertSee('KCDIOM Draft Circular')
            ->assertSee('MSD Draft Policy');

        $this->delete(route('viewer-session.destroy'))
            ->assertRedirect();

        // Guest/public only sees published docs with access_scope=all.
        $this->get(route('policy-documents.index'))
            ->assertOk()
            ->assertSee('Public Published Circular')
            ->assertDontSee('KCDIOM Draft Circular')
            ->assertDontSee('MSD Draft Policy');
    }

    public function test_create_page_surfaces_historical_records_for_version_workflow(): void
    {
        $manager = User::factory()->create([
            'role' => 'policy_manager',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $document = PolicyDocument::create([
            'title' => 'Leave Policy',
            'document_type' => 'policy',
            'content' => 'Root version content.',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'published',
            'is_circular' => false,
            'version_number' => 1,
            'created_by' => $manager->id,
            'published_at' => now(),
        ]);

        PolicyDocument::create([
            'title' => 'Leave Policy',
            'document_type' => 'policy',
            'content' => 'Second version content.',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'draft',
            'is_circular' => false,
            'version_number' => 2,
            'parent_document_id' => $document->id,
            'created_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->get(route('policy-documents.create', ['title_lookup' => 'Leave']))
            ->assertOk()
            ->assertSee('Historical Records for "Leave"', false)
            ->assertSee('Leave Policy')
            ->assertSee('Create New Version');
    }

    public function test_duplicate_root_title_redirects_back_to_guided_version_search(): void
    {
        $manager = User::factory()->create([
            'role' => 'policy_manager',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        PolicyDocument::create([
            'title' => 'Leave Policy',
            'document_type' => 'policy',
            'content' => 'Root version content.',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'published',
            'is_circular' => false,
            'version_number' => 1,
            'created_by' => $manager->id,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($manager)->from(route('policy-documents.create'))->post(route('policy-documents.store'), [
            'title' => 'Leave Policy',
            'document_type' => 'policy',
            'content' => 'Attempted duplicate root document.',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'draft',
        ]);

        $response
            ->assertRedirect(route('policy-documents.create', ['title_lookup' => 'Leave Policy']))
            ->assertSessionHasErrors('title');

        $this->followRedirects($response)
            ->assertOk()
            ->assertSee('A root document with this title already exists.')
            ->assertSee('Leave Policy')
            ->assertSee('Create New Version');
    }

    public function test_visible_user_can_download_attached_document(): void
    {
        Storage::fake('public');

        $manager = User::factory()->create([
            'role' => 'policy_manager',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $path = 'policy-documents/policy.pdf';
        Storage::disk('public')->put($path, 'test policy content');

        $document = PolicyDocument::create([
            'title' => 'Downloadable Policy',
            'document_type' => 'policy',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'published',
            'is_circular' => false,
            'version_number' => 1,
            'created_by' => $manager->id,
            'file_path' => $path,
            'file_original_name' => 'policy.pdf',
            'published_at' => now(),
        ]);

        $this->actingAs($manager)
            ->get(route('policy-documents.download', $document))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename=policy.pdf');
    }

    public function test_staff_can_preview_visible_pdf_inline(): void
    {
        Storage::fake('public');
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'msd', 'is_active' => true]);
        $staff = User::factory()->create(['role' => 'staff_user', 'unit' => 'msd', 'is_active' => true]);
        $path = 'policy-documents/preview.pdf';
        Storage::disk('public')->put($path, '%PDF-1.4 preview');
        $document = PolicyDocument::create([
            'title' => 'Previewable Policy', 'document_type' => 'policy', 'content' => 'Preview',
            'access_scope' => 'all', 'owner_unit' => 'msd', 'status' => 'published',
            'version_number' => 1, 'created_by' => $manager->id, 'file_path' => $path,
            'file_original_name' => 'preview.pdf', 'published_at' => now(),
        ]);

        $this->actingAs($staff)->get(route('policy-documents.show', $document))
            ->assertOk()->assertSee('Document preview')->assertSee(route('policy-documents.preview', $document));

        $this->get(route('policy-documents.preview', $document))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'inline; filename=preview.pdf');
    }

    public function test_manager_can_upload_and_staff_can_switch_between_multiple_pdfs(): void
    {
        Storage::fake('public');
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'msd', 'is_active' => true]);
        $staff = User::factory()->create(['role' => 'staff_user', 'unit' => 'msd', 'is_active' => true]);

        $this->actingAs($manager)->post(route('policy-documents.store'), [
            'title' => 'Multiple PDF Policy',
            'document_type' => 'policy',
            'content' => 'Policy with supporting PDF documents.',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'published',
            'created_by' => $manager->id,
            'files' => [
                UploadedFile::fake()->create('policy.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('appendix.pdf', 80, 'application/pdf'),
            ],
        ])->assertRedirect(route('policy-documents.index'));

        $document = PolicyDocument::where('title', 'Multiple PDF Policy')->firstOrFail();
        $attachments = DocumentAttachment::where('policy_document_id', $document->id)->orderBy('id')->get();

        $this->assertCount(2, $attachments);
        $this->assertSame('policy.pdf', $document->file_original_name);
        $this->actingAs($staff)->get(route('policy-documents.show', $document))
            ->assertOk()
            ->assertSee('Choose PDF')
            ->assertSee('policy.pdf')
            ->assertSee('appendix.pdf')
            ->assertSee(route('document-attachments.preview', $attachments->last()));

        $this->get(route('document-attachments.preview', $attachments->last()))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_version_flow_can_retain_exclude_and_delete_draft_pdfs(): void
    {
        Storage::fake('public');
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'msd', 'is_active' => true]);
        $document = PolicyDocument::create([
            'title' => 'Attachment Lifecycle Policy', 'document_type' => 'policy', 'content' => 'Original content',
            'access_scope' => 'all', 'owner_unit' => 'msd', 'status' => 'published',
            'version_number' => 1, 'created_by' => $manager->id, 'published_at' => now(),
        ]);
        $history = $document->histories()->firstOrFail();
        Storage::disk('public')->put('policy-documents/keep.pdf', '%PDF keep');
        Storage::disk('public')->put('policy-documents/exclude.pdf', '%PDF exclude');
        $keep = DocumentAttachment::create(['policy_document_id' => $document->id, 'document_history_id' => $history->id, 'file_name' => 'keep.pdf', 'file_path' => 'policy-documents/keep.pdf', 'file_type' => 'application/pdf', 'security_status' => 'validated', 'uploaded_by' => $manager->id]);
        DocumentAttachment::create(['policy_document_id' => $document->id, 'document_history_id' => $history->id, 'file_name' => 'exclude.pdf', 'file_path' => 'policy-documents/exclude.pdf', 'file_type' => 'application/pdf', 'security_status' => 'validated', 'uploaded_by' => $manager->id]);

        $this->actingAs($manager)->post(route('policy-documents.versions.store', $document), [
            'status' => 'draft', 'attachments_reviewed' => 1, 'retain_attachment_ids' => [$keep->id],
        ])->assertRedirect(route('policy-documents.show', $document));

        $newVersion = PolicyDocument::where('parent_document_id', $document->id)->firstOrFail();
        $newHistory = DocumentHistory::where('policy_document_id', $document->id)->where('version_number', 2)->firstOrFail();
        $copied = DocumentAttachment::where('document_history_id', $newHistory->id)->sole();
        $this->assertSame('keep.pdf', $copied->file_name);
        $this->assertSame('policy-documents/keep.pdf', $newVersion->file_path);

        $this->delete(route('document-attachments.destroy', $copied))
            ->assertRedirect();
        $this->assertDatabaseMissing('document_attachments', ['id' => $copied->id]);
        $this->assertNull($newVersion->fresh()->file_path);
        Storage::disk('public')->assertExists('policy-documents/keep.pdf');
    }

    public function test_manager_can_publish_draft_circular_with_explicit_action(): void
    {
        $manager = User::factory()->create([
            'name' => 'Publishing Manager',
            'role' => 'policy_manager',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $document = PolicyDocument::create([
            'title' => 'Draft Circular',
            'document_type' => 'circular',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'draft',
            'is_circular' => true,
            'version_number' => 1,
            'created_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->post(route('policy-documents.publish', $document))
            ->assertRedirect(route('policy-documents.show', $document))
            ->assertSessionHas('status', 'Circular published and marked for Staff/Public circulation.');

        $document->refresh();

        $this->assertSame('published', $document->status);
        $this->assertNotNull($document->published_at);
        $this->assertSame($manager->id, $document->published_by);
    }

    public function test_staff_cannot_publish_or_download_hidden_documents(): void
    {
        Storage::fake('public');

        $manager = User::factory()->create([
            'role' => 'policy_manager',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $staff = User::factory()->create([
            'role' => 'staff_user',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $path = 'policy-documents/restricted.pdf';
        Storage::disk('public')->put($path, 'restricted policy content');

        $document = PolicyDocument::create([
            'title' => 'Restricted Draft',
            'document_type' => 'policy',
            'access_scope' => 'msd',
            'owner_unit' => 'msd',
            'status' => 'draft',
            'is_circular' => false,
            'version_number' => 1,
            'created_by' => $manager->id,
            'file_path' => $path,
            'file_original_name' => 'restricted.pdf',
        ]);

        $this->actingAs($staff)
            ->post(route('policy-documents.publish', $document))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('policy-documents.download', $document))
            ->assertNotFound();
    }

    public function test_reports_surface_effective_published_version_and_publisher(): void
    {
        $manager = User::factory()->create([
            'name' => 'Report Publisher',
            'role' => 'policy_manager',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $root = PolicyDocument::create([
            'title' => 'Travel Policy',
            'document_type' => 'policy',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'published',
            'is_circular' => false,
            'version_number' => 1,
            'created_by' => $manager->id,
            'published_at' => now()->subDay(),
            'published_by' => $manager->id,
        ]);

        $latest = PolicyDocument::create([
            'title' => 'Travel Policy',
            'document_type' => 'policy',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'published',
            'is_circular' => false,
            'version_number' => 2,
            'parent_document_id' => $root->id,
            'created_by' => $manager->id,
            'published_at' => now(),
            'published_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->get(route('policy-documents.index'))
            ->assertOk()
            ->assertSee('Travel Policy')
            ->assertSee('Effective Published')
            ->assertSee('v2');

        $this->actingAs($manager)
            ->get(route('reports.versions'))
            ->assertOk()
            ->assertSee('Report Publisher')
            ->assertSee('Effective Published')
            ->assertSee('v2');

        $this->actingAs($manager)
            ->get(route('policy-documents.show', $latest))
            ->assertOk()
            ->assertSee('Published By')
            ->assertSee('Report Publisher')
            ->assertSee('Effective Published Version')
            ->assertSee('v2');
    }

    public function test_publishing_circular_notifies_matching_staff_users(): void
    {
        Notification::fake();

        $manager = User::factory()->create([
            'role' => 'policy_manager',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $staffAll = User::factory()->create([
            'role' => 'staff_user',
            'unit' => 'all',
            'is_active' => true,
        ]);

        $staffMsd = User::factory()->create([
            'role' => 'staff_user',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $staffKcdiom = User::factory()->create([
            'role' => 'staff_user',
            'unit' => 'kcdiom',
            'is_active' => true,
        ]);

        $document = PolicyDocument::create([
            'title' => 'Campus Circular',
            'document_type' => 'circular',
            'access_scope' => 'msd',
            'owner_unit' => 'msd',
            'status' => 'draft',
            'is_circular' => true,
            'version_number' => 1,
            'created_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->post(route('policy-documents.publish', $document))
            ->assertRedirect(route('policy-documents.show', $document));

        Notification::assertSentTo($staffMsd, CircularPublishedNotification::class);
        Notification::assertNotSentTo($staffAll, CircularPublishedNotification::class);
        Notification::assertNotSentTo($staffKcdiom, CircularPublishedNotification::class);
        Notification::assertNotSentTo($manager, CircularPublishedNotification::class);
    }

    public function test_viewer_can_mark_notification_as_read(): void
    {
        $manager = User::factory()->create([
            'role' => 'policy_manager',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $staff = User::factory()->create([
            'role' => 'staff_user',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $document = PolicyDocument::create([
            'title' => 'Notification Circular',
            'document_type' => 'circular',
            'access_scope' => 'msd',
            'owner_unit' => 'msd',
            'status' => 'published',
            'is_circular' => true,
            'version_number' => 1,
            'created_by' => $manager->id,
            'published_at' => now(),
            'published_by' => $manager->id,
        ]);

        $staff->notify(new CircularPublishedNotification($document));
        $notification = $staff->notifications()->firstOrFail();

        $this->actingAs($staff)
            ->patch(route('notifications.update', $notification->id))
            ->assertRedirect();

        $this->assertNotNull($staff->fresh()->notifications()->first()->read_at);
    }

    public function test_viewer_can_mark_all_notifications_as_read(): void
    {
        $manager = User::factory()->create([
            'role' => 'policy_manager',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $staff = User::factory()->create([
            'role' => 'staff_user',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $firstDocument = PolicyDocument::create([
            'title' => 'First Circular',
            'document_type' => 'circular',
            'access_scope' => 'msd',
            'owner_unit' => 'msd',
            'status' => 'published',
            'is_circular' => true,
            'version_number' => 1,
            'created_by' => $manager->id,
            'published_at' => now(),
            'published_by' => $manager->id,
        ]);

        $secondDocument = PolicyDocument::create([
            'title' => 'Second Circular',
            'document_type' => 'circular',
            'access_scope' => 'msd',
            'owner_unit' => 'msd',
            'status' => 'published',
            'is_circular' => true,
            'version_number' => 1,
            'created_by' => $manager->id,
            'published_at' => now(),
            'published_by' => $manager->id,
        ]);

        $staff->notify(new CircularPublishedNotification($firstDocument));
        $staff->notify(new CircularPublishedNotification($secondDocument));

        $this->actingAs($staff)
            ->post(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $staff->fresh()->unreadNotifications()->count());
    }

    public function test_viewer_can_browse_notifications_page_with_status_filter(): void
    {
        $manager = User::factory()->create([
            'role' => 'policy_manager',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $staff = User::factory()->create([
            'role' => 'staff_user',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $firstDocument = PolicyDocument::create([
            'title' => 'Unread Circular',
            'document_type' => 'circular',
            'content' => 'This circular updates the staff attendance flow and introduces revised internal submission timing.',
            'revision_summary' => 'Attendance submission timing has been revised for all MSD staff.',
            'access_scope' => 'msd',
            'owner_unit' => 'msd',
            'status' => 'published',
            'is_circular' => true,
            'version_number' => 2,
            'created_by' => $manager->id,
            'published_at' => now(),
            'published_by' => $manager->id,
        ]);

        $secondDocument = PolicyDocument::create([
            'title' => 'Read Circular',
            'document_type' => 'circular',
            'access_scope' => 'msd',
            'owner_unit' => 'msd',
            'status' => 'published',
            'is_circular' => true,
            'version_number' => 1,
            'created_by' => $manager->id,
            'published_at' => now(),
            'published_by' => $manager->id,
        ]);

        $staff->notify(new CircularPublishedNotification($firstDocument));
        $staff->notify(new CircularPublishedNotification($secondDocument));
        $staff->notifications()->where('data->title', 'Read Circular')->update(['read_at' => now()]);

        $this->actingAs($staff)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Unread Circular')
            ->assertSee('Read Circular');

        $this->actingAs($staff)
            ->get(route('notifications.index', ['status' => 'unread']))
            ->assertOk()
            ->assertSee('Unread Circular')
            ->assertDontSee('Read Circular');

        $this->actingAs($staff)
            ->get(route('notifications.index', ['category' => 'circular-publication']))
            ->assertOk()
            ->assertSee('Circular Publication')
            ->assertSee('Release summary: Attendance submission timing has been revised for all MSD staff.')
            ->assertSee('This circular updates the staff attendance flow')
            ->assertSee('notifications_active');
    }

    public function test_published_version_can_store_editor_revision_summary(): void
    {
        $manager = User::factory()->create([
            'role' => 'policy_manager',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $document = PolicyDocument::create([
            'title' => 'Leave Policy',
            'document_type' => 'policy',
            'content' => 'Existing policy content.',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'published',
            'is_circular' => false,
            'version_number' => 1,
            'created_by' => $manager->id,
            'published_at' => now(),
            'published_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->post(route('policy-documents.versions.store', $document), [
                'content' => 'Updated policy content for a new revision.',
                'revision_summary' => 'Clarified leave entitlement rules for contract staff.',
                'status' => 'published',
            ])
            ->assertRedirect(route('policy-documents.show', $document));

        $newVersion = PolicyDocument::query()->where('parent_document_id', $document->id)->latest('id')->firstOrFail();

        $this->assertSame('Clarified leave entitlement rules for contract staff.', $newVersion->revision_summary);

        $this->actingAs($manager)
            ->get(route('policy-documents.show', $newVersion))
            ->assertOk()
            ->assertSee('Revision Summary')
            ->assertSee('Clarified leave entitlement rules for contract staff.');
    }

    public function test_publishing_policy_notifies_matching_staff_with_document_category(): void
    {
        Notification::fake();

        $manager = User::factory()->create([
            'role' => 'policy_manager',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $staffMsd = User::factory()->create([
            'role' => 'staff_user',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $staffKcdiom = User::factory()->create([
            'role' => 'staff_user',
            'unit' => 'kcdiom',
            'is_active' => true,
        ]);

        $document = PolicyDocument::create([
            'title' => 'Updated Policy Guide',
            'document_type' => 'policy',
            'access_scope' => 'msd',
            'owner_unit' => 'msd',
            'status' => 'draft',
            'is_circular' => false,
            'version_number' => 1,
            'created_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->post(route('policy-documents.publish', $document))
            ->assertRedirect(route('policy-documents.show', $document))
            ->assertSessionHas('status', 'Document published successfully.');

        Notification::assertSentTo($staffMsd, DocumentPublishedNotification::class);
        Notification::assertNotSentTo($staffKcdiom, DocumentPublishedNotification::class);
        Notification::assertNotSentTo($staffMsd, CircularPublishedNotification::class);
    }

    public function test_repository_groups_versions_and_links_each_historical_version(): void
    {
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'msd', 'is_active' => true]);
        $root = PolicyDocument::create([
            'title' => 'Grouped Version Policy', 'document_type' => 'policy', 'content' => 'Version one',
            'access_scope' => 'all', 'owner_unit' => 'msd', 'status' => 'superseded',
            'version_number' => 1, 'created_by' => $manager->id,
        ]);
        $current = PolicyDocument::create([
            'title' => 'Grouped Version Policy', 'document_type' => 'policy', 'content' => 'Version two',
            'access_scope' => 'all', 'owner_unit' => 'msd', 'status' => 'published',
            'version_number' => 2, 'parent_document_id' => $root->id, 'created_by' => $manager->id, 'published_at' => now(),
        ]);

        $this->actingAs($manager)->get(route('policy-documents.index'))
            ->assertOk()
            ->assertSee('Grouped Version Policy', false)
            ->assertSee('Version 2')
            ->assertSee(route('policy-documents.show', $root))
            ->assertSee(route('policy-documents.show', $current));
    }

    public function test_topic_category_shows_related_documents_before_delete(): void
    {
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'msd', 'is_active' => true]);
        $category = TopicCategory::create(['name' => 'Service Matters', 'slug' => 'service-matters', 'is_active' => true]);
        $document = PolicyDocument::create([
            'title' => 'Appointment Circular', 'document_type' => 'circular', 'topic_category' => $category->slug,
            'content' => 'Circular content', 'access_scope' => 'all', 'owner_unit' => 'msd',
            'status' => 'published', 'version_number' => 1, 'created_by' => $manager->id, 'published_at' => now(),
        ]);

        $this->actingAs($manager)
            ->get(route('topic-categories.index'))
            ->assertOk()
            ->assertSee('Used by 1')
            ->assertSee('Show 1 related document before deleting')
            ->assertSee('Appointment Circular')
            ->assertSee(route('policy-documents.show', $document), false);
    }

    public function test_registration_asks_whether_to_modify_a_version_one_document(): void
    {
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'msd', 'is_active' => true]);
        $root = PolicyDocument::create([
            'title' => 'Versioned Governance Policy', 'document_type' => 'policy', 'content' => 'Version one',
            'access_scope' => 'all', 'owner_unit' => 'msd', 'status' => 'published', 'version_number' => 1,
            'created_by' => $manager->id, 'published_at' => now(),
        ]);
        PolicyDocument::create([
            'title' => $root->title, 'document_type' => 'policy', 'content' => 'Version two',
            'access_scope' => 'all', 'owner_unit' => 'msd', 'status' => 'draft', 'version_number' => 2,
            'parent_document_id' => $root->id, 'created_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->get(route('policy-documents.create'))
            ->assertOk()
            ->assertSee('What would you like to do?')
            ->assertSee('Modify an existing document')
            ->assertSee('Search by title or reference number')
            ->assertSee('Versioned Governance Policy')
            ->assertDontSee('Versioned Governance Policy — v2');
    }

    public function test_staff_dashboard_does_not_show_draft_metric(): void
    {
        $staff = User::factory()->create(['role' => 'staff_user', 'unit' => 'msd', 'is_active' => true]);

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Visible records')
            ->assertSee('Active')
            ->assertSee('Circulars')
            ->assertDontSee('Drafts');
    }

    public function test_document_registration_only_offers_requested_statuses(): void
    {
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'msd', 'is_active' => true]);

        $this->actingAs($manager)
            ->get(route('policy-documents.create'))
            ->assertOk()
            ->assertSee('Draft')
            ->assertSee('Active')
            ->assertSee('Superceded')
            ->assertDontSee('>Inactive<', false)
            ->assertDontSee('>Archived<', false);
    }

    public function test_manager_can_create_document_with_governance_metadata_and_audit_log(): void
    {
        $manager = User::factory()->create([
            'role' => 'policy_manager',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $this->actingAs($manager)->post(route('policy-documents.store'), [
            'title' => 'Governed Policy',
            'reference_number' => 'IIUM/MSD/2026/001',
            'document_type' => 'policy',
            'content' => 'Approved policy content.',
            'effective_date' => '2026-08-01',
            'expiry_date' => '2027-07-31',
            'remarks' => 'Annual review required.',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'draft',
            'created_by' => $manager->id,
        ])->assertRedirect(route('policy-documents.index'));

        $document = PolicyDocument::where('reference_number', 'IIUM/MSD/2026/001')->firstOrFail();

        $this->assertSame('2026-08-01', $document->effective_date->format('Y-m-d'));
        $this->assertDatabaseHas('document_activity_logs', [
            'policy_document_id' => $document->id,
            'user_id' => $manager->id,
            'action' => 'created',
        ]);
        $this->assertDatabaseHas('document_histories', [
            'policy_document_id' => $document->id,
            'version_number' => 1,
            'status' => 'draft',
            'created_by' => $manager->id,
        ]);
    }

    public function test_archived_document_cannot_return_to_draft(): void
    {
        $manager = User::factory()->create([
            'role' => 'policy_manager',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $document = PolicyDocument::create([
            'title' => 'Archived Policy',
            'document_type' => 'policy',
            'content' => 'Historical content.',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'archived',
            'is_circular' => false,
            'version_number' => 1,
            'created_by' => $manager->id,
        ]);

        $this->actingAs($manager)->put(route('policy-documents.update', $document), [
            'title' => $document->title,
            'document_type' => $document->document_type,
            'content' => $document->content,
            'access_scope' => $document->access_scope,
            'owner_unit' => $document->owner_unit,
            'status' => 'draft',
            'created_by' => $manager->id,
        ])->assertSessionHasErrors('status');

        $this->assertSame('archived', $document->fresh()->status);
    }

    public function test_policy_manager_can_view_reporting_dashboard(): void
    {
        $manager = User::factory()->create([
            'role' => 'policy_manager',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        PolicyDocument::create([
            'title' => 'Dashboard Circular',
            'document_type' => 'circular',
            'content' => 'Reporting dashboard data.',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'published',
            'is_circular' => true,
            'version_number' => 1,
            'created_by' => $manager->id,
            'published_at' => now(),
        ]);

        $this->actingAs($manager)
            ->get(route('reports.dashboard'))
            ->assertOk()
            ->assertSee('Reporting dashboard')
            ->assertSee('Dashboard Circular')
            ->assertSee('Submissions by unit');
    }

    public function test_staff_cannot_view_management_reporting_dashboard(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff_user',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $this->actingAs($staff)
            ->get(route('reports.dashboard'))
            ->assertForbidden();
    }

    public function test_manager_can_configure_document_type_lookup_and_use_it_in_create_form(): void
    {
        $manager = User::factory()->create([
            'role' => 'policy_manager',
            'unit' => 'msd',
            'is_active' => true,
        ]);

        $this->actingAs($manager)->post(route('lookup-values.store'), [
            'type' => 'DOCUMENT_TYPE',
            'code' => 'procedure',
            'description' => 'Procedure',
            'sort_order' => 4,
            'is_active' => 1,
        ])->assertRedirect(route('lookup-values.index'));

        $this->assertDatabaseHas('lookup_values', [
            'type' => 'DOCUMENT_TYPE',
            'code' => 'procedure',
            'description' => 'Procedure',
            'is_active' => true,
        ]);

        $this->actingAs($manager)
            ->get(route('policy-documents.create'))
            ->assertOk()
            ->assertSee('Procedure');
    }

    public function test_staff_cannot_manage_lookup_values(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff_user',
            'unit' => 'msd',
            'is_active' => true,
        ]);
        $lookup = LookupValue::create([
            'type' => 'DOCUMENT_TYPE', 'code' => 'restricted', 'description' => 'Restricted',
            'sort_order' => 50, 'is_active' => true,
        ]);

        $this->actingAs($staff)
            ->get(route('lookup-values.index'))
            ->assertForbidden();
        $this->delete(route('lookup-values.destroy', $lookup))->assertForbidden();
        $this->assertDatabaseHas('lookup_values', ['id' => $lookup->id]);
    }

    public function test_manager_can_delete_unused_lookup_value(): void
    {
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'msd', 'is_active' => true]);
        $lookup = LookupValue::create([
            'type' => 'DOCUMENT_TYPE',
            'code' => 'unused_procedure',
            'description' => 'Unused Procedure',
            'sort_order' => 20,
            'is_active' => true,
        ]);

        $this->actingAs($manager)
            ->delete(route('lookup-values.destroy', $lookup))
            ->assertRedirect(route('lookup-values.index'))
            ->assertSessionHas('status', 'Lookup value deleted successfully.');

        $this->assertDatabaseMissing('lookup_values', ['id' => $lookup->id]);
    }

    public function test_common_superseded_misspelling_is_normalized(): void
    {
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'msd', 'is_active' => true]);
        $lookup = LookupValue::firstOrCreate(
            ['type' => 'DOCUMENT_STATUS', 'code' => 'superseded'],
            ['description' => 'Superseded', 'sort_order' => 4, 'is_active' => true],
        );

        $this->actingAs($manager)->put(route('lookup-values.update', $lookup), [
            'type' => 'DOCUMENT_STATUS',
            'code' => 'superceded',
            'description' => 'Superseded',
            'sort_order' => 4,
            'is_active' => 1,
        ])->assertRedirect(route('lookup-values.index'));

        $this->assertDatabaseHas('lookup_values', [
            'type' => 'DOCUMENT_STATUS',
            'code' => 'superseded',
            'description' => 'Superseded',
        ]);
    }

    public function test_used_lookup_value_must_be_deactivated_instead_of_deleted(): void
    {
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'msd', 'is_active' => true]);
        $lookup = LookupValue::create([
            'type' => 'DOCUMENT_TYPE',
            'code' => 'policy',
            'description' => 'Policy Document',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        PolicyDocument::create([
            'title' => 'Protected Lookup Policy',
            'document_type' => 'policy',
            'content' => 'Content',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'draft',
            'version_number' => 1,
            'created_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->delete(route('lookup-values.destroy', $lookup))
            ->assertRedirect(route('lookup-values.index'))
            ->assertSessionHasErrors('lookup_value');

        $this->assertDatabaseHas('lookup_values', ['id' => $lookup->id]);
    }

    public function test_authorized_viewer_can_download_historical_attachment(): void
    {
        Storage::fake('public');
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'msd', 'is_active' => true]);
        $document = PolicyDocument::create([
            'title' => 'Historical Attachment Policy',
            'document_type' => 'policy',
            'content' => 'Published content.',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'published',
            'version_number' => 1,
            'created_by' => $manager->id,
            'published_at' => now(),
        ]);
        Storage::disk('public')->put('policy-documents/history.pdf', 'historical file');
        $attachment = DocumentAttachment::create([
            'policy_document_id' => $document->id,
            'file_name' => 'history.pdf',
            'file_path' => 'policy-documents/history.pdf',
            'file_size' => 15,
            'file_type' => 'application/pdf',
            'uploaded_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->get(route('document-attachments.download', $attachment))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename=history.pdf');
    }

    public function test_staff_cannot_download_attachment_for_hidden_document(): void
    {
        Storage::fake('public');
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'msd', 'is_active' => true]);
        $staff = User::factory()->create(['role' => 'staff_user', 'unit' => 'msd', 'is_active' => true]);
        $document = PolicyDocument::create([
            'title' => 'Hidden Attachment Policy',
            'document_type' => 'policy',
            'content' => 'Draft content.',
            'access_scope' => 'msd',
            'owner_unit' => 'msd',
            'status' => 'draft',
            'version_number' => 1,
            'created_by' => $manager->id,
        ]);
        Storage::disk('public')->put('policy-documents/hidden.pdf', 'hidden file');
        $attachment = DocumentAttachment::create([
            'policy_document_id' => $document->id,
            'file_name' => 'hidden.pdf',
            'file_path' => 'policy-documents/hidden.pdf',
            'uploaded_by' => $manager->id,
        ]);

        $this->actingAs($staff)
            ->get(route('document-attachments.download', $attachment))
            ->assertNotFound();
    }

    public function test_manager_can_filter_document_audit_log(): void
    {
        $manager = User::factory()->create(['role' => 'system_admin', 'unit' => 'all', 'is_active' => true]);
        $this->actingAs($manager);
        PolicyDocument::create([
            'title' => 'Audited Governance Policy',
            'reference_number' => 'AUDIT-001',
            'document_type' => 'policy',
            'content' => 'Audited content.',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'draft',
            'version_number' => 1,
            'created_by' => $manager->id,
        ]);

        $this->get(route('document-activity-logs.index', ['action' => 'created', 'q' => 'AUDIT-001']))
            ->assertOk()
            ->assertSee('Audited Governance Policy')
            ->assertSee('Created')
            ->assertSee($manager->name);
    }

    public function test_staff_cannot_view_document_audit_log(): void
    {
        $staff = User::factory()->create(['role' => 'staff_user', 'unit' => 'msd', 'is_active' => true]);

        $this->actingAs($staff)
            ->get(route('document-activity-logs.index'))
            ->assertForbidden();
    }

    public function test_manager_can_filter_and_export_user_access_report(): void
    {
        $manager = User::factory()->create([
            'name' => 'Access Report Manager',
            'staff_id' => 'MSD9001',
            'cas_username' => 'access.manager',
            'role' => 'system_admin',
            'unit' => 'all',
            'is_active' => true,
        ]);
        User::factory()->create([
            'name' => 'KCD Report Staff',
            'staff_id' => 'KCD9001',
            'cas_username' => 'kcd.report',
            'role' => 'staff_user',
            'unit' => 'kcdiom',
            'is_active' => true,
        ]);

        $this->actingAs($manager)
            ->get(route('reports.user-access', ['unit' => 'kcdiom']))
            ->assertOk()
            ->assertSee('KCD Report Staff')
            ->assertDontSee($manager->email);

        $this->actingAs($manager)
            ->get(route('reports.user-access.export', ['unit' => 'kcdiom']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertDownload();
    }

    public function test_staff_cannot_view_or_export_user_access_report(): void
    {
        $staff = User::factory()->create(['role' => 'staff_user', 'unit' => 'msd', 'is_active' => true]);

        $this->actingAs($staff)->get(route('reports.user-access'))->assertForbidden();
        $this->actingAs($staff)->get(route('reports.user-access.export'))->assertForbidden();
    }

    public function test_attachment_download_rejects_checksum_mismatch(): void
    {
        Storage::fake('public');
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'msd', 'is_active' => true]);
        $document = PolicyDocument::create([
            'title' => 'Integrity Policy',
            'document_type' => 'policy',
            'content' => 'Integrity-controlled content.',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'published',
            'version_number' => 1,
            'created_by' => $manager->id,
            'published_at' => now(),
        ]);
        Storage::disk('public')->put('policy-documents/integrity.pdf', 'tampered content');
        $attachment = DocumentAttachment::create([
            'policy_document_id' => $document->id,
            'file_name' => 'integrity.pdf',
            'file_path' => 'policy-documents/integrity.pdf',
            'checksum_sha256' => hash('sha256', 'original content'),
            'security_status' => 'validated',
            'uploaded_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->get(route('document-attachments.download', $attachment))
            ->assertStatus(409);
    }

    public function test_expiry_reminder_command_notifies_managers_once(): void
    {
        Notification::fake();
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'msd', 'is_active' => true]);
        $document = PolicyDocument::create([
            'title' => 'Expiring Governance Policy',
            'document_type' => 'policy',
            'content' => 'Expiring content.',
            'access_scope' => 'all',
            'owner_unit' => 'msd',
            'status' => 'published',
            'version_number' => 1,
            'created_by' => $manager->id,
            'published_at' => now(),
            'expiry_date' => today()->addDays(10),
        ]);

        $this->artisan('documents:send-expiry-reminders', ['--days' => 30])
            ->expectsOutput('Dispatched 1 expiry reminder(s).')
            ->assertSuccessful();
        $this->artisan('documents:send-expiry-reminders', ['--days' => 30])
            ->expectsOutput('Dispatched 0 expiry reminder(s).')
            ->assertSuccessful();

        Notification::assertSentToTimes($manager, DocumentExpiryReminderNotification::class, 1);
        $this->assertDatabaseHas('expiry_reminder_dispatches', [
            'policy_document_id' => $document->id,
            'user_id' => $manager->id,
            'reminder_days' => 30,
        ]);
    }

    public function test_manager_can_synchronize_huris_directory_data_without_overwriting_roles(): void
    {
        $manager = User::factory()->create([
            'staff_id' => 'MSD-SYNC-1',
            'cas_username' => 'sync.manager',
            'role' => 'system_admin',
            'unit' => 'all',
            'is_active' => true,
        ]);
        $existingManager = User::factory()->create([
            'staff_id' => 'MSD-SYNC-2',
            'cas_username' => 'existing.manager',
            'role' => 'policy_manager',
            'unit' => 'msd',
            'is_active' => true,
        ]);
        $csv = "staff_id,cas_username,name,email,unit\n"
            ."MSD-SYNC-2,existing.manager,Updated Manager,updated.manager@iium.edu.my,msd\n"
            ."KCD-SYNC-3,new.staff,New Staff,new.staff@iium.edu.my,kcdiom";

        $this->actingAs($manager)
            ->post(route('directory-sync.store'), ['csv_data' => $csv])
            ->assertRedirect(route('directory-sync.index'))
            ->assertSessionHas('status', 'Directory sync completed: 1 created, 1 updated, 0 rejected.');

        $this->assertSame('policy_manager', $existingManager->fresh()->role);
        $this->assertDatabaseHas('users', ['staff_id' => 'KCD-SYNC-3', 'role' => 'staff_user', 'unit' => 'kcdiom']);
        $this->assertDatabaseHas('directory_sync_runs', [
            'initiated_by' => $manager->id,
            'rows_received' => 2,
            'rows_created' => 1,
            'rows_updated' => 1,
            'rows_rejected' => 0,
        ]);
    }

    public function test_staff_cannot_run_directory_synchronization(): void
    {
        $staff = User::factory()->create(['role' => 'staff_user', 'unit' => 'msd', 'is_active' => true]);

        $this->actingAs($staff)->get(route('directory-sync.index'))->assertForbidden();
        $this->actingAs($staff)->post(route('directory-sync.store'), ['csv_data' => 'test'])->assertForbidden();
    }

    public function test_kcdiom_and_msd_policy_managers_cannot_access_system_administration_modules(): void
    {
        foreach (['kcdiom', 'msd'] as $unit) {
            $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => $unit, 'is_active' => true]);

            $this->actingAs($manager)->get(route('roles.index'))->assertForbidden();
            $this->get(route('reports.user-access'))->assertForbidden();
            $this->get(route('document-activity-logs.index'))->assertForbidden();
            $this->get(route('directory-sync.index'))->assertForbidden();

            $this->get(route('policy-documents.index'))->assertOk()
                ->assertDontSee('User Roles')
                ->assertDontSee('User Access Report')
                ->assertDontSee('Document Audit Log')
                ->assertDontSee('CAS/HURIS Sync');
        }
    }

    public function test_manager_can_delete_document_without_newer_versions(): void
    {
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'msd', 'is_active' => true]);
        $document = PolicyDocument::create([
            'title' => 'Obsolete Draft', 'document_type' => 'policy', 'content' => 'Delete me',
            'access_scope' => 'msd', 'owner_unit' => 'msd', 'status' => 'draft',
            'version_number' => 1, 'created_by' => $manager->id,
        ]);

        $this->actingAs($manager)->delete(route('policy-documents.destroy', $document))
            ->assertRedirect(route('policy-documents.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('policy_documents', ['id' => $document->id]);
    }

    public function test_original_document_with_newer_versions_cannot_be_deleted(): void
    {
        $manager = User::factory()->create(['role' => 'policy_manager', 'unit' => 'msd', 'is_active' => true]);
        $root = PolicyDocument::create([
            'title' => 'Versioned Policy', 'document_type' => 'policy', 'content' => 'v1',
            'access_scope' => 'msd', 'owner_unit' => 'msd', 'status' => 'published',
            'version_number' => 1, 'created_by' => $manager->id,
        ]);
        PolicyDocument::create([
            'title' => 'Versioned Policy', 'document_type' => 'policy', 'content' => 'v2',
            'access_scope' => 'msd', 'owner_unit' => 'msd', 'status' => 'draft',
            'version_number' => 2, 'parent_document_id' => $root->id, 'created_by' => $manager->id,
        ]);

        $this->actingAs($manager)->delete(route('policy-documents.destroy', $root))
            ->assertRedirect(route('policy-documents.show', $root))
            ->assertSessionHasErrors('document');

        $this->assertDatabaseHas('policy_documents', ['id' => $root->id]);
    }
}

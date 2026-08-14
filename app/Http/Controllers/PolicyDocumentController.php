<?php

namespace App\Http\Controllers;

use App\Models\PolicyDocument;
use App\Models\DocumentAttachment;
use App\Models\DocumentHistory;
use App\Models\LookupValue;
use App\Models\Organization;
use App\Models\TopicCategory;
use App\Models\TopicSubtopic;
use App\Models\TopicDetail;
use App\Models\User;
use App\Models\FormTemplate;
use App\Models\DocumentFormResponse;
use App\Notifications\CircularPublishedNotification;
use App\Notifications\DocumentPublishedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PolicyDocumentController extends Controller
{
    private const STATUS_TRANSITIONS = [
        'draft' => ['draft', 'published', 'superseded', 'archived'],
        'published' => ['published', 'inactive', 'superseded', 'archived'],
        'inactive' => ['inactive', 'published', 'superseded', 'archived'],
        'superseded' => ['superseded', 'archived'],
        'archived' => ['archived'],
    ];

    public function index(Request $request): View
    {
        $viewer = $request->user();
        $canManageDocuments = $this->canManageDocuments($viewer);
        $latestInFamily = function ($query) use ($canManageDocuments): void {
            $query->whereNotExists(function ($newer) use ($canManageDocuments): void {
                $newer->selectRaw('1')
                    ->from((new PolicyDocument)->getTable().' as newer')
                    ->whereRaw('COALESCE(newer.parent_document_id, newer.id) = COALESCE(policy_documents.parent_document_id, policy_documents.id)')
                    ->whereColumn('newer.version_number', '>', 'policy_documents.version_number');

                if (! $canManageDocuments) {
                    $newer->whereIn('newer.status', ['published', 'superseded']);
                }
            });
        };
        $relations = ['creator', 'subtopic.mainTopic', 'topicDetail'];

        if (config('features.form_builder')) {
            $relations[] = 'formResponses.template';
        }

        $showSupersededHistory = $request->string('status')->toString() === 'superseded';
        $query = PolicyDocument::with($relations)->visibleTo($viewer);
        if (! $showSupersededHistory) {
            $query->tap($latestInFamily);
        }
        $visibleDocuments = PolicyDocument::query()->visibleTo($viewer)->tap($latestInFamily);
        $visibleHistory = PolicyDocument::query()->visibleTo($viewer);
        $unitVisibleDocuments = PolicyDocument::query()->visibleTo($viewer)->tap($latestInFamily);

        if ($request->boolean('public')) {
            $unitVisibleDocuments->where('public_flag', true);
        }

        $unitStats = [
            'all' => (clone $unitVisibleDocuments)->count(),
            'msd' => (clone $unitVisibleDocuments)->where('owner_unit', 'msd')->count(),
            'kcdiom' => (clone $unitVisibleDocuments)->where('owner_unit', 'kcdiom')->count(),
        ];

        if ($request->filled('unit') && in_array($request->string('unit')->toString(), ['msd', 'kcdiom'], true)) {
            $unit = $request->string('unit')->toString();
            $query->where('owner_unit', $unit);
            $visibleDocuments->where('owner_unit', $unit);
            $visibleHistory->where('owner_unit', $unit);
        }

        if ($request->boolean('public')) {
            $query->where('public_flag', true);
            $visibleDocuments->where('public_flag', true);
            $visibleHistory->where('public_flag', true);
        }

        $repositoryStats = [
            'total' => (clone $visibleDocuments)->count(),
            'published' => (clone $visibleDocuments)->where('status', 'published')->count(),
            'draft' => (clone $visibleDocuments)->where('status', 'draft')->count(),
            'superseded' => (clone $visibleHistory)->where('status', 'superseded')->count(),
            'expiring' => (clone $visibleDocuments)->whereNotNull('expiry_date')->whereBetween('expiry_date', [today(), today()->addDays(30)])->count(),
        ];

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('document_type')) {
            $query->where('document_type', $request->string('document_type'));
        }

        if ($request->filled('topic_category')) {
            $query->where('topic_category', $request->string('topic_category'));
        }

        if ($request->filled('subtopic_id')) {
            $query->where('subtopic_id', (int) $request->input('subtopic_id'));
        }

        if ($request->filled('topic_detail_id')) {
            $query->where('topic_detail_id', (int) $request->input('topic_detail_id'));
        }

        if ($request->filled('q')) {
            $search = '%' . $request->string('q') . '%';
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', $search)
                    ->orWhere('reference_number', 'like', $search);
            });
        }

        if (config('features.form_builder') && $request->filled('form_template_id')) {
            $query->whereHas('formResponses', fn ($builder) => $builder->where('form_template_id', (int) $request->input('form_template_id')));
        }

        match ($request->string('sort')->toString()) {
            'title' => $query->orderBy('title'),
            'oldest' => $query->oldest('updated_at'),
            'effective' => $query->orderByDesc('effective_date'),
            default => $query->latest('updated_at'),
        };

        $documents = $this->decoratePaginator($query->paginate(12)->withQueryString());
        $this->attachVersionFamilies($documents->getCollection(), $viewer);
        $managedTopicUnit = $request->filled('unit') && in_array($request->string('unit')->toString(), ['msd', 'kcdiom'], true)
            ? $request->string('unit')->toString()
            : ($canManageDocuments ? $this->organizationUnit($viewer) : null);
        $managedOrganizationId = $canManageDocuments && ! $viewer?->isSystemAdmin() ? $viewer?->organization_id : null;
        $managedTopics = TopicCategory::query()
            ->where('is_active', true)
            ->when($managedOrganizationId,
                fn ($topicQuery) => $topicQuery->where('organization_id', $managedOrganizationId),
                fn ($topicQuery) => $topicQuery->when($managedTopicUnit, fn ($legacyQuery) => $legacyQuery->where('owner_unit', $managedTopicUnit)))
            ->with(['subtopics' => fn ($mainTopicQuery) => $mainTopicQuery
                ->where('is_active', true)
                ->with(['details' => fn ($detailQuery) => $detailQuery->where('is_active', true)->orderBy('sort_order')->orderBy('name')])
                ->orderBy('sort_order')
                ->orderBy('name')])
            ->orderBy('owner_unit')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $showMsdDashboard = $request->string('unit')->toString() === 'msd' && ! $canManageDocuments;
        $msdTopicDashboard = $showMsdDashboard
            ? TopicCategory::query()
                // SQLite cannot compile a schema-qualified `table.*` select.
                // Listing the columns explicitly keeps the shared HR schema
                // compatible while retaining relationship and count loading.
                ->select(['id', 'name', 'slug', 'owner_unit', 'organization_id', 'is_active', 'sort_order'])
                ->where('is_active', true)
                ->with([
                    'subtopics' => fn ($topicQuery) => $topicQuery
                        ->select(['id', 'topic_category_id', 'name', 'slug', 'is_active', 'sort_order'])
                        ->where('is_active', true)
                        ->withCount(['details' => fn ($detailQuery) => $detailQuery->where('is_active', true)])
                        ->orderBy('name'),
                ])
                ->withCount([
                    'documents as accessible_documents_count' => fn ($documentQuery) => $documentQuery
                        ->visibleTo($viewer)
                        ->where('owner_unit', 'msd')
                        ->where('status', 'published')
                        ->tap($latestInFamily),
                ])
                ->orderBy('name')
                ->get()
            : collect();

        return view('policy_documents.index', [
            'documents' => $documents,
            'canManageDocuments' => $canManageDocuments,
            'viewer' => $viewer,
            'topicCategories' => $this->topicCategoryOptions(),
            'subtopics' => $this->subtopicOptions(),
            'documentTypes' => $this->lookupOptions('DOCUMENT_TYPE', ['policy' => 'Policy', 'guideline' => 'Guideline', 'circular' => 'Circular']),
            'documentStatuses' => $this->lookupOptions('DOCUMENT_STATUS', ['draft' => 'Draft', 'published' => 'Active', 'superseded' => 'Superceded']),
            'repositoryStats' => $repositoryStats,
            'unitStats' => $unitStats,
            'selectedUnit' => in_array($request->string('unit')->toString(), ['msd', 'kcdiom'], true)
                ? $request->string('unit')->toString()
                : null,
            'showMsdDashboard' => $showMsdDashboard,
            'msdTopicDashboard' => $msdTopicDashboard,
            'managedTopics' => $managedTopics,
            'formTemplates' => config('features.form_builder') ? FormTemplate::where('is_active', true)->orderBy('name')->get(['id', 'name']) : collect(),
        ]);
    }

    public function create(Request $request): View
    {
        $viewer = $request->user();
        $this->ensureCanManageDocuments($viewer);

        $lookupTerm = trim((string) $request->input('title_lookup', old('title', '')));

        return view('policy_documents.create', [
            'managementUnit' => $this->organizationUnit($viewer),
            'users' => $this->editableCreators(),
            'document' => new PolicyDocument(),
            'lookupTerm' => $lookupTerm,
            'matchingDocuments' => $this->findHistoricalRecords($viewer, $lookupTerm),
            'topicCategories' => $this->topicCategoryOptions(),
            'subtopics' => $this->subtopicOptions(),
            'topicDetails' => $this->topicDetailOptions(),
            'documentTypes' => $this->lookupOptions('DOCUMENT_TYPE', ['policy' => 'Policy', 'guideline' => 'Guideline', 'circular' => 'Circular']),
            'documentStatuses' => $this->lookupOptions('DOCUMENT_STATUS', ['draft' => 'Draft', 'published' => 'Active', 'superseded' => 'Superceded']),
            'formTemplates' => $this->availableFormTemplates($viewer),
            'rootDocuments' => PolicyDocument::query()->visibleTo($viewer)->whereNull('parent_document_id')->orderBy('title')->get(['id', 'title', 'reference_number', 'version_number', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $viewer = $request->user();
        $this->ensureCanManageDocuments($viewer);

        $submittedTitle = preg_replace('/\s+/u', ' ', trim((string) $request->input('title')));
        $ownerUnit = $this->organizationUnit($viewer);
        $organizationId = $viewer?->organization_id;
        $exists = $submittedTitle !== '' && PolicyDocument::query()
            ->whereNull('parent_document_id')
            ->where('owner_unit', $ownerUnit)
            ->whereRaw('LOWER(TRIM(title)) = ?', [mb_strtolower($submittedTitle)])
            ->exists();

        if ($exists) {
            return redirect()
                ->route('policy-documents.create', ['title_lookup' => $submittedTitle])
                ->withInput()
                ->withErrors([
                    'title' => 'A root document with this title already exists. Use "Create New Version" from the existing record.',
                ]);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:100', Rule::unique(PolicyDocument::class, 'reference_number')],
            'document_type' => ['required', Rule::in($this->lookupCodes('DOCUMENT_TYPE', ['policy', 'guideline', 'circular']))],
            'topic_category' => ['nullable', Rule::exists('topic_categories', 'slug')->where(fn ($query) => $query->where('is_active', true)->when($organizationId, fn ($q) => $q->where('organization_id', $organizationId), fn ($q) => $q->where('owner_unit', $ownerUnit)))],
            'subtopic_id' => ['nullable', Rule::exists('topic_subtopics', 'id')->where(fn ($query) => $query->where('is_active', true)->whereIn('topic_category_id', TopicCategory::query()->when($organizationId, fn ($q) => $q->where('organization_id', $organizationId), fn ($q) => $q->where('owner_unit', $ownerUnit))->select('id')))],
            'topic_detail_id' => ['nullable', Rule::exists('topic_details', 'id')->where(fn ($query) => $query->where('is_active', true)->whereIn('main_topic_id', TopicSubtopic::whereIn('topic_category_id', TopicCategory::where('owner_unit', $ownerUnit)->select('id'))->select('id')))],
            'content' => ['required', 'string'],
            'revision_summary' => ['nullable', 'string', 'max:1000'],
            'effective_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:effective_date'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'access_scope' => ['required', Rule::in(['all', $ownerUnit])],
            'public_flag' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in($this->lookupCodes('DOCUMENT_STATUS', ['draft', 'published', 'inactive', 'superseded', 'archived']))],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:3072'],
            'files' => ['nullable', 'array', 'max:10'],
            'files.*' => ['file', 'mimes:pdf', 'max:3072'],
            'form_template_id' => [Rule::prohibitedIf(! config('features.form_builder')), 'nullable', 'integer', 'exists:form_templates,id'],
        ]);
        $data['title'] = $submittedTitle;

        [$template, $dynamicValues] = $this->validateDynamicForm($request, $viewer, $data['form_template_id'] ?? null);
        unset($data['form_template_id']);

        // Governance ownership is derived from the authenticated manager rather
        // than being manually selected during document registration.
        $data['owner_unit'] = $ownerUnit;
        $data['organization_id'] = $viewer?->organization_id
            ?: Organization::idForLegacyUnit($data['owner_unit']);
        $data['owner_report'] = null;
        $data['created_by'] = $viewer?->id;

        $this->ensureOwnerUnitAccess($viewer, $data['owner_unit']);
        $this->ensureSubtopicMatchesMainTopic($data['topic_category'] ?? null, $data['subtopic_id'] ?? null);
        $this->ensureTopicDetailMatchesMainTopic($data['subtopic_id'] ?? null, $data['topic_detail_id'] ?? null);

        $uploadedFiles = $this->storePdfUploads($request);
        if ($uploadedFiles !== []) {
            $data['file_path'] = $uploadedFiles[0]['path'];
            $data['file_original_name'] = $uploadedFiles[0]['file']->getClientOriginalName();
        }

        $data['is_circular'] = $data['document_type'] === 'circular';
        $data['public_flag'] = $request->boolean('public_flag');
        $data['version_number'] = 1;
        $data['published_at'] = $data['status'] === 'published' ? now() : null;
        $data['published_by'] = $data['status'] === 'published' ? $viewer?->id : null;

        $document = PolicyDocument::create($data);
        if ($template) {
            DocumentFormResponse::create(['policy_document_id' => $document->id, 'form_template_id' => $template->id, 'values' => $dynamicValues, 'submitted_by' => $viewer?->id]);
        }
        $this->recordAttachments($document, $uploadedFiles);

        return redirect()->route('policy-documents.index')->with('status', 'Policy/Circular record created successfully.');
    }

    public function show(PolicyDocument $policyDocument): View
    {
        $viewer = request()->user();

        abort_unless($this->canViewDocument($viewer, $policyDocument), 404);

        $rootDocumentId = $policyDocument->parent_document_id ?: $policyDocument->id;
        $versions = PolicyDocument::with(['creator', 'publisher'])
            ->where(function ($query) use ($rootDocumentId): void {
                $query->where('id', $rootDocumentId)
                    ->orWhere('parent_document_id', $rootDocumentId);
            })
            ->visibleTo($viewer)
            ->orderByDesc('version_number')
            ->get();

        $rootId = $policyDocument->parent_document_id ?: $policyDocument->id;
        $relations = ['creator', 'publisher', 'updater', 'subtopic.mainTopic', 'activityLogs.user'];

        if (config('features.form_builder')) {
            $relations[] = 'formResponses.template.fields';
        }

        $policyDocument->load($relations);
        $currentHistory = DocumentHistory::query()
            ->where('policy_document_id', $rootId)
            ->where('version_number', $policyDocument->version_number)
            ->first();
        $currentAttachments = DocumentAttachment::with(['uploader', 'history'])
            ->where('policy_document_id', $rootId)
            ->when($currentHistory, fn ($query) => $query->where('document_history_id', $currentHistory->id))
            ->when(! ($viewer?->canManagePolicies() ?? false), fn ($query) => $query->where('is_public', true))
            ->orderBy('id')
            ->get();
        $primaryAttachment = $policyDocument->file_path
            ? DocumentAttachment::query()
                ->where('policy_document_id', $rootId)
                ->where('file_path', $policyDocument->file_path)
                ->latest('id')
                ->first()
            : null;
        $versions = $this->decorateCollection($versions);
        $policyDocument = $this->decorateDocument($policyDocument);
        $activeVersion = $versions->firstWhere('status', 'published');

        return view('policy_documents.show', [
            'document' => $policyDocument,
            'versions' => $versions,
            'activeVersion' => $activeVersion,
            'users' => $this->editableCreators(),
            'canManageDocuments' => $this->canManageDocuments($viewer),
            'canPublishDocument' => $this->canPublishDocument($viewer, $policyDocument),
            'topicCategories' => $this->topicCategoryOptions(),
            'subtopics' => $this->subtopicOptions(),
            'normalizedHistories' => DocumentHistory::with([
                'creator',
                'attachments' => fn ($query) => $query->when(
                    ! ($viewer?->canManagePolicies() ?? false),
                    fn ($attachmentQuery) => $attachmentQuery->where('is_public', true)
                ),
            ])->where('policy_document_id', $rootId)->orderByDesc('version_number')->get(),
            'normalizedAttachments' => DocumentAttachment::with(['uploader', 'history'])
                ->where('policy_document_id', $rootId)
                ->when(! ($viewer?->canManagePolicies() ?? false), fn ($query) => $query->where('is_public', true))
                ->latest()
                ->get(),
            'currentAttachments' => $currentAttachments,
            'legacyPdfAllowed' => ! $primaryAttachment || $primaryAttachment->is_public || ($viewer?->canManagePolicies() ?? false),
            'documentStatuses' => $this->lookupOptions('DOCUMENT_STATUS', ['draft' => 'Draft', 'published' => 'Active', 'superseded' => 'Superceded']),
        ]);
    }

    public function download(PolicyDocument $policyDocument)
    {
        $viewer = request()->user();

        abort_unless($this->canViewDocument($viewer, $policyDocument), 404);
        abort_unless($policyDocument->file_path, 404);
        $primaryAttachment = DocumentAttachment::query()
            ->where('policy_document_id', $policyDocument->parent_document_id ?: $policyDocument->id)
            ->where('file_path', $policyDocument->file_path)
            ->latest('id')
            ->first();
        abort_unless(! $primaryAttachment || $primaryAttachment->is_public || ($viewer?->canManagePolicies() ?? false), 404);
        abort_unless(Storage::disk('public')->exists($policyDocument->file_path), 404);

        return Storage::disk('public')->download(
            $policyDocument->file_path,
            $policyDocument->file_original_name ?: basename($policyDocument->file_path)
        );
    }

    public function preview(PolicyDocument $policyDocument)
    {
        $viewer = request()->user();

        abort_unless($this->canViewDocument($viewer, $policyDocument), 404);
        abort_unless($policyDocument->file_path, 404);
        $primaryAttachment = DocumentAttachment::query()
            ->where('policy_document_id', $policyDocument->parent_document_id ?: $policyDocument->id)
            ->where('file_path', $policyDocument->file_path)
            ->latest('id')
            ->first();
        abort_unless(! $primaryAttachment || $primaryAttachment->is_public || ($viewer?->canManagePolicies() ?? false), 404);
        abort_unless(Storage::disk('public')->exists($policyDocument->file_path), 404);

        $fileName = $policyDocument->file_original_name ?: basename($policyDocument->file_path);
        abort_unless(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) === 'pdf', 415, 'Only PDF documents can be previewed.');

        return Storage::disk('public')->response(
            $policyDocument->file_path,
            $fileName,
            ['Content-Type' => 'application/pdf'],
            'inline'
        );
    }

    public function downloadAttachment(DocumentAttachment $documentAttachment)
    {
        $viewer = request()->user();
        $document = PolicyDocument::findOrFail($documentAttachment->policy_document_id);
        $documentAttachment->loadMissing('history');

        abort_unless($this->canViewDocument($viewer, $document), 404);
        abort_unless($documentAttachment->is_public || ($viewer?->canManagePolicies() ?? false), 404);
        if (! ($viewer?->canManagePolicies() ?? false) && $documentAttachment->history) {
            abort_unless(in_array($documentAttachment->history->status, ['published', 'superseded'], true), 404);
        }
        abort_unless(Storage::disk('public')->exists($documentAttachment->file_path), 404);

        if ($documentAttachment->checksum_sha256) {
            $actualChecksum = hash('sha256', Storage::disk('public')->get($documentAttachment->file_path));
            abort_unless(hash_equals($documentAttachment->checksum_sha256, $actualChecksum), 409, 'Attachment integrity verification failed.');
            $documentAttachment->forceFill(['integrity_verified_at' => now()])->saveQuietly();
        }

        return Storage::disk('public')->download(
            $documentAttachment->file_path,
            $documentAttachment->file_name
        );
    }

    public function previewAttachment(DocumentAttachment $documentAttachment)
    {
        $viewer = request()->user();
        $document = PolicyDocument::findOrFail($documentAttachment->policy_document_id);
        $documentAttachment->loadMissing('history');

        abort_unless($this->canViewDocument($viewer, $document), 404);
        abort_unless($documentAttachment->is_public || ($viewer?->canManagePolicies() ?? false), 404);
        if (! ($viewer?->canManagePolicies() ?? false) && $documentAttachment->history) {
            abort_unless(in_array($documentAttachment->history->status, ['published', 'superseded'], true), 404);
        }
        abort_unless(strtolower(pathinfo($documentAttachment->file_name, PATHINFO_EXTENSION)) === 'pdf', 415, 'Only PDF documents can be previewed.');
        abort_unless(Storage::disk('public')->exists($documentAttachment->file_path), 404);

        return Storage::disk('public')->response(
            $documentAttachment->file_path,
            $documentAttachment->file_name,
            ['Content-Type' => 'application/pdf'],
            'inline'
        );
    }

    public function destroyAttachment(Request $request, DocumentAttachment $documentAttachment): RedirectResponse
    {
        $viewer = $request->user();
        $this->ensureCanManageDocuments($viewer);
        $rootDocument = PolicyDocument::findOrFail($documentAttachment->policy_document_id);
        abort_unless($this->canViewDocument($viewer, $rootDocument), 404);

        $documentAttachment->loadMissing('history');
        abort_unless($documentAttachment->history && $documentAttachment->history->status === 'draft', 409, 'Files from an active or superceded version are protected. Create a new version to exclude them.');

        $versionDocument = PolicyDocument::query()
            ->where(function ($query) use ($rootDocument): void {
                $query->whereKey($rootDocument->id)->orWhere('parent_document_id', $rootDocument->id);
            })
            ->where('version_number', $documentAttachment->history->version_number)
            ->first();
        $path = $documentAttachment->file_path;
        $name = $documentAttachment->file_name;
        $historyId = $documentAttachment->document_history_id;
        $documentAttachment->delete();

        if ($versionDocument && $versionDocument->file_path === $path) {
            $replacement = DocumentAttachment::query()->where('document_history_id', $historyId)->orderBy('id')->first();
            $versionDocument->update([
                'file_path' => $replacement?->file_path,
                'file_original_name' => $replacement?->file_name,
                'updated_by' => $viewer?->id,
            ]);
        }

        $stillReferenced = DocumentAttachment::query()->where('file_path', $path)->exists()
            || PolicyDocument::query()->where('file_path', $path)->exists();
        if (! $stillReferenced) {
            Storage::disk('public')->delete($path);
        }

        return back()->with('status', 'PDF “'.$name.'” deleted from this draft version.');
    }

    public function publish(Request $request, PolicyDocument $policyDocument): RedirectResponse
    {
        $viewer = $request->user();
        $this->ensureCanManageDocuments($viewer);
        abort_unless($this->canViewDocument($viewer, $policyDocument), 404);
        $this->ensureValidStatusTransition($policyDocument->status, 'published');

        $rootId = $policyDocument->parent_document_id ?: $policyDocument->id;
        PolicyDocument::query()
            ->where(function ($query) use ($rootId): void {
                $query->whereKey($rootId)->orWhere('parent_document_id', $rootId);
            })
            ->where('id', '!=', $policyDocument->id)
            ->where('status', 'published')
            ->get()
            ->each(fn (PolicyDocument $version) => $version->update([
                'status' => 'superseded',
                'updated_by' => $viewer?->id,
            ]));

        $policyDocument->update([
            'status' => 'published',
            'published_at' => now(),
            'published_by' => $viewer?->id,
            'updated_by' => $viewer?->id,
        ]);

        $this->notifyPublishedDocumentRecipients($policyDocument, $viewer?->id);

        $message = $policyDocument->is_circular
            ? 'Circular published and marked for Staff/Public circulation.'
            : 'Document published successfully.';

        return redirect()->route('policy-documents.show', $policyDocument)->with('status', $message);
    }

    public function edit(Request $request, PolicyDocument $policyDocument): View
    {
        $viewer = $request->user();
        $this->ensureCanManageDocuments($viewer);
        abort_unless($this->canViewDocument($viewer, $policyDocument), 404);

        $rootId = $policyDocument->parent_document_id ?: $policyDocument->id;
        $historyId = DocumentHistory::query()
            ->where('policy_document_id', $rootId)
            ->where('version_number', $policyDocument->version_number)
            ->value('id');

        return view('policy_documents.edit', [
            'managementUnit' => $this->organizationUnit($viewer),
            'document' => $policyDocument,
            'currentAttachments' => DocumentAttachment::query()
                ->where('policy_document_id', $rootId)
                ->when($historyId, fn ($query) => $query->where('document_history_id', $historyId))
                ->orderBy('file_name')
                ->get(),
            'users' => $this->editableCreators(),
            'topicCategories' => $this->topicCategoryOptions(),
            'subtopics' => $this->subtopicOptions(),
            'topicDetails' => $this->topicDetailOptions(),
            'documentTypes' => $this->lookupOptions('DOCUMENT_TYPE', ['policy' => 'Policy', 'guideline' => 'Guideline', 'circular' => 'Circular']),
            'documentStatuses' => $this->lookupOptions('DOCUMENT_STATUS', ['draft' => 'Draft', 'published' => 'Active', 'superseded' => 'Superceded']),
        ]);
    }

    public function update(Request $request, PolicyDocument $policyDocument): RedirectResponse
    {
        $viewer = $request->user();
        $this->ensureCanManageDocuments($viewer);
        abort_unless($this->canViewDocument($viewer, $policyDocument), 404);
        $ownerUnit = $this->organizationUnit($viewer);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:100', Rule::unique(PolicyDocument::class, 'reference_number')->ignore($policyDocument)],
            'document_type' => ['required', Rule::in($this->lookupCodes('DOCUMENT_TYPE', ['policy', 'guideline', 'circular']))],
            'topic_category' => ['nullable', Rule::exists('topic_categories', 'slug')->where(fn ($query) => $query->where('is_active', true)->where('owner_unit', $ownerUnit))],
            'subtopic_id' => ['nullable', Rule::exists('topic_subtopics', 'id')->where(fn ($query) => $query->where('is_active', true)->whereIn('topic_category_id', TopicCategory::where('owner_unit', $ownerUnit)->select('id')))],
            'topic_detail_id' => ['nullable', Rule::exists('topic_details', 'id')->where(fn ($query) => $query->where('is_active', true)->whereIn('main_topic_id', TopicSubtopic::whereIn('topic_category_id', TopicCategory::where('owner_unit', $ownerUnit)->select('id'))->select('id')))],
            'content' => ['required', 'string'],
            'revision_summary' => ['nullable', 'string', 'max:1000'],
            'effective_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:effective_date'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'access_scope' => ['required', Rule::in(['all', $ownerUnit])],
            'public_flag' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in($this->lookupCodes('DOCUMENT_STATUS', ['draft', 'published', 'inactive', 'superseded', 'archived']))],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:3072'],
            'files' => ['nullable', 'array', 'max:10'],
            'files.*' => ['file', 'mimes:pdf', 'max:3072'],
            'new_attachment_visibility' => ['nullable', Rule::in(['public', 'internal'])],
            'attachment_visibility' => ['nullable', 'array'],
            'attachment_visibility.*' => [Rule::in(['public', 'internal'])],
        ]);

        $this->ensureOwnerUnitAccess($viewer, $policyDocument->owner_unit);
        $this->ensureSubtopicMatchesMainTopic($data['topic_category'] ?? null, $data['subtopic_id'] ?? null);
        $this->ensureTopicDetailMatchesMainTopic($data['subtopic_id'] ?? null, $data['topic_detail_id'] ?? null);
        $this->ensureValidStatusTransition($policyDocument->status, $data['status']);

        $uploadedFiles = $this->storePdfUploads($request);
        if ($uploadedFiles !== []) {
            $data['file_path'] = $uploadedFiles[0]['path'];
            $data['file_original_name'] = $uploadedFiles[0]['file']->getClientOriginalName();
        }

        $data['is_circular'] = $data['document_type'] === 'circular';
        $data['public_flag'] = $request->boolean('public_flag');
        $data['updated_by'] = $viewer?->id;
        $data['published_at'] = $data['status'] === 'published' ? ($policyDocument->published_at ?? now()) : null;
        $data['published_by'] = $data['status'] === 'published' ? ($policyDocument->published_by ?? $viewer?->id) : null;

        $policyDocument->update($data);
        $this->recordAttachments(
            $policyDocument,
            $uploadedFiles,
            $request->input('new_attachment_visibility', 'internal') === 'public'
        );
        $this->updateAttachmentVisibility($policyDocument, $request->input('attachment_visibility', []));

        return redirect()->route('policy-documents.show', $policyDocument)->with('status', 'Record updated successfully.');
    }

    public function destroy(Request $request, PolicyDocument $policyDocument): RedirectResponse
    {
        $viewer = $request->user();
        $this->ensureCanManageDocuments($viewer);
        abort_unless($this->canViewDocument($viewer, $policyDocument), 404);

        if ($policyDocument->versions()->exists()) {
            return redirect()->route('policy-documents.show', $policyDocument)->withErrors([
                'document' => 'Delete the newer versions before deleting this original record.',
            ]);
        }

        $paths = $policyDocument->attachments()->pluck('file_path')
            ->push($policyDocument->file_path)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $title = $policyDocument->title;
        $policyDocument->delete();

        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }

        return redirect()->route('policy-documents.index')->with('status', 'Document “'.$title.'” deleted successfully.');
    }

    public function storeVersion(Request $request, PolicyDocument $policyDocument): RedirectResponse
    {
        $viewer = $request->user();
        $this->ensureCanManageDocuments($viewer);
        abort_unless($this->canViewDocument($viewer, $policyDocument), 404);

        $data = $request->validate([
            'content' => ['required', 'string'],
            'revision_summary' => ['nullable', 'string', 'max:1000'],
            'effective_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:effective_date'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'public_flag' => ['nullable', 'boolean'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:3072'],
            'files' => ['nullable', 'array', 'max:10'],
            'files.*' => ['file', 'mimes:pdf', 'max:3072'],
            'attachments_reviewed' => ['nullable', 'boolean'],
            'retain_attachment_ids' => ['nullable', 'array'],
            'retain_attachment_ids.*' => ['integer'],
        ]);

        // A new version inherits the document family's title; the version form
        // intentionally does not ask users to submit the same title again.
        $normalizedTitle = preg_replace('/\s+/u', ' ', trim($policyDocument->title));
        $rootId = $policyDocument->parent_document_id ?: $policyDocument->id;
        $duplicateFamily = PolicyDocument::query()
            ->whereNull('parent_document_id')
            ->where('owner_unit', $policyDocument->owner_unit)
            ->whereKeyNot($rootId)
            ->whereRaw('LOWER(TRIM(title)) = ?', [mb_strtolower($normalizedTitle)])
            ->exists();

        if ($duplicateFamily) {
            return back()->withInput()->withErrors([
                'title' => 'Another document family in this unit already uses this title. Open that record to create a new version.',
            ]);
        }

        $rootId = $policyDocument->parent_document_id ?: $policyDocument->id;
        $selectedMainTopic = $policyDocument->topic_category;
        $selectedSubtopic = $policyDocument->subtopic_id;
        $this->ensureSubtopicMatchesMainTopic($selectedMainTopic, $selectedSubtopic);

        $latestVersion = PolicyDocument::where(function ($q) use ($rootId): void {
            $q->where('id', $rootId)->orWhere('parent_document_id', $rootId);
        })->max('version_number');

        $sourceHistory = DocumentHistory::query()
            ->where('policy_document_id', $rootId)
            ->where('version_number', $policyDocument->version_number)
            ->first();
        $availableAttachments = DocumentAttachment::query()
            ->where('policy_document_id', $rootId)
            ->when($sourceHistory, fn ($query) => $query->where('document_history_id', $sourceHistory->id))
            ->orderBy('id')
            ->get();
        $selectedAttachmentIds = collect($data['retain_attachment_ids'] ?? [])->map(fn ($id) => (int) $id);
        $retainedAttachments = $request->boolean('attachments_reviewed')
            ? $availableAttachments->whereIn('id', $selectedAttachmentIds)->values()
            : $availableAttachments;

        $newVersionData = [
            'title' => $policyDocument->title,
            'document_type' => $policyDocument->document_type,
            'topic_category' => $selectedMainTopic,
            'subtopic_id' => $selectedSubtopic,
            'topic_detail_id' => $policyDocument->topic_detail_id,
            'content' => $data['content'],
            'revision_summary' => $data['revision_summary'] ?? null,
            'reference_number' => null,
            'effective_date' => $data['effective_date'] ?? null,
            'expiry_date' => $data['expiry_date'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'access_scope' => $policyDocument->access_scope,
            'public_flag' => $request->boolean('public_flag'),
            'owner_unit' => $policyDocument->owner_unit,
            'organization_id' => $policyDocument->organization_id
                ?: Organization::idForLegacyUnit($policyDocument->owner_unit),
            'owner_report' => $policyDocument->owner_report,
            'status' => 'draft',
            'is_circular' => $policyDocument->document_type === 'circular',
            'version_number' => ($latestVersion ?? 1) + 1,
            'parent_document_id' => $rootId,
            'created_by' => $policyDocument->created_by,
            'published_at' => null,
            'published_by' => null,
        ];

        $uploadedFiles = $this->storePdfUploads($request);
        if ($uploadedFiles !== []) {
            $newVersionData['file_path'] = $uploadedFiles[0]['path'];
            $newVersionData['file_original_name'] = $uploadedFiles[0]['file']->getClientOriginalName();
        } elseif ($retainedAttachments->isNotEmpty()) {
            $newVersionData['file_path'] = $retainedAttachments->first()->file_path;
            $newVersionData['file_original_name'] = $retainedAttachments->first()->file_name;
        } elseif ($request->boolean('attachments_reviewed')) {
            $newVersionData['file_path'] = null;
            $newVersionData['file_original_name'] = null;
        } else {
            $newVersionData['file_path'] = $policyDocument->file_path;
            $newVersionData['file_original_name'] = $policyDocument->file_original_name;
        }

        $newVersion = PolicyDocument::create($newVersionData);
        $this->copyAttachmentsToVersion($newVersion, $retainedAttachments);
        $this->recordAttachments($newVersion, $uploadedFiles);

        return redirect()->route('policy-documents.show', $newVersion)->with('status', 'New version created successfully.');
    }

    public function reportCirculars(): View
    {
        $viewer = request()->user();

        return view('policy_documents.report_circulars', [
            'documents' => $this->decorateCollection(
                PolicyDocument::with(['publisher', 'subtopic', 'topicDetail'])
                    ->where('document_type', 'circular')
                    ->visibleTo($viewer)
                    ->latest()
                    ->get()
            ),
            'topicCategories' => $this->topicCategoryOptions(),
        ]);
    }

    public function reportVersions(): View
    {
        $viewer = request()->user();
        $documents = $this->decorateCollection(
            PolicyDocument::with('publisher')->visibleTo($viewer)->orderBy('title')->orderByDesc('version_number')->get()
        );

        return view('policy_documents.report_versions', [
            'documents' => $documents,
            'versionStats' => [
                'records' => $documents->count(),
                'roots' => $documents->whereNull('parent_document_id')->count(),
                'derived' => $documents->whereNotNull('parent_document_id')->count(),
                'effective' => $documents->where('is_effective_published_version', true)->count(),
            ],
        ]);
    }

    private function canManageDocuments(?User $user): bool
    {
        return $user?->canManagePolicies() ?? false;
    }

    private function editableCreators()
    {
        $unit = $this->organizationUnit(request()->user());

        $unitScope = function ($query) use ($unit): void {
            $query->where('unit', $unit);

            if (Schema::hasTable('organizations')) {
                $query->orWhereHas('organization', fn ($organization) => $organization->where('code', strtoupper($unit)));
            }
        };

        return User::query()
            ->where('is_active', true)
            ->whereIn('role', ['system_admin', 'msd_admin', 'kcdiom_liaison'])
            ->where($unitScope)
            ->orderBy('name')
            ->get();
    }

    private function creatorValidationRules(bool $nullable = true): array
    {
        return array_filter([
            $nullable ? 'nullable' : null,
            Rule::exists('users', 'id')->where(function ($query): void {
                $query->where('is_active', true)
                    ->whereIn('role', ['system_admin', 'msd_admin', 'kcdiom_liaison']);
            }),
        ]);
    }

    private function ensureValidStatusTransition(string $from, string $to): void
    {
        if (! in_array($to, self::STATUS_TRANSITIONS[$from] ?? [], true)) {
            throw ValidationException::withMessages([
                'status' => sprintf('A document cannot move from %s to %s.', $from, $to),
            ]);
        }
    }

    private function topicCategoryOptions()
    {
        return TopicCategory::query()
            ->when(request()->user()?->organization_id,
                fn ($query, $organizationId) => $query->where('organization_id', $organizationId),
                fn ($query) => $query->where('owner_unit', $this->organizationUnit(request()->user())))
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'slug');
    }

    private function subtopicOptions()
    {
        return TopicSubtopic::query()
            ->select(['id', 'topic_category_id', 'name'])
            ->with('mainTopic:id,slug,name')
            ->where('is_active', true)
            ->whereHas('mainTopic', fn ($query) => $query->where('is_active', true)->when(request()->user()?->organization_id,
                fn ($query, $organizationId) => $query->where('organization_id', $organizationId),
                fn ($query) => $query->where('owner_unit', $this->organizationUnit(request()->user()))))
            ->orderBy('name')
            ->get();
    }

    private function topicDetailOptions()
    {
        return TopicDetail::query()
            ->select(['id', 'main_topic_id', 'name'])
            ->where('is_active', true)
            ->whereHas('mainTopic.mainTopic', fn ($query) => $query->when(request()->user()?->organization_id,
                fn ($query, $organizationId) => $query->where('organization_id', $organizationId),
                fn ($query) => $query->where('owner_unit', $this->organizationUnit(request()->user()))))
            ->orderBy('name')
            ->get();
    }

    private function lookupOptions(string $type, array $fallback)
    {
        $options = LookupValue::query()
            ->where('type', $type)
            ->where('owner_unit', $this->organizationUnit(request()->user()))
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('description', 'code');

        return $options->isEmpty() ? collect($fallback) : $options;
    }

    private function lookupCodes(string $type, array $fallback): array
    {
        $codes = LookupValue::query()->where('type', $type)->where('owner_unit', $this->organizationUnit(request()->user()))->where('is_active', true)->pluck('code')->all();

        return $codes === [] ? $fallback : $codes;
    }

    private function availableFormTemplates(?User $viewer)
    {
        if (! config('features.form_builder')) {
            return collect();
        }

        return FormTemplate::with('fields')->where('is_active', true)
            ->where('owner_unit', $this->organizationUnit($viewer))
            ->orderBy('name')->get()->map(function (FormTemplate $template) {
                $template->fields->each(function ($field): void {
                    $field->setAttribute('resolved_options', $this->dynamicFieldOptions($field->data_source, $field->options ?? []));
                });
                return $template;
            });
    }

    private function dynamicFieldOptions(?string $source, array $manual): array
    {
        $unit = $this->organizationUnit(request()->user());

        return match ($source) {
            'users' => User::where('is_active', true)->where('unit', $unit)->orderBy('name')->get()->map(fn ($u) => ['value' => (string) $u->id, 'label' => $u->name])->all(),
            'main_topics' => TopicCategory::where('is_active', true)->where('owner_unit', $unit)->orderBy('name')->get()->map(fn ($v) => ['value' => $v->slug, 'label' => $v->name])->all(),
            'subtopics' => TopicSubtopic::where('is_active', true)->whereHas('mainTopic', fn ($query) => $query->where('owner_unit', $unit))->orderBy('name')->get()->map(fn ($v) => ['value' => (string) $v->id, 'label' => $v->name])->all(),
            'departments' => $this->masterOptions('DEPARTMENT', [$unit]),
            'user_roles' => $this->masterOptions('USER_ROLE', User::where('is_active', true)->whereNotNull('role')->distinct()->orderBy('role')->pluck('role')->all()),
            'access_scopes' => [['value' => 'all', 'label' => 'ALL'], ['value' => $unit, 'label' => strtoupper($unit)]],
            'document_types' => $this->lookupOptions('DOCUMENT_TYPE', [])->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values()->all(),
            'document_statuses' => $this->lookupOptions('DOCUMENT_STATUS', [])->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values()->all(),
            default => str_starts_with((string) $source, 'lov:')
                ? $this->lookupOptions(substr($source, 4), [])->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values()->all()
                : $manual,
        };
    }

    private function masterOptions(string $lookupType, array $fallback): array
    {
        $options = $this->lookupOptions($lookupType, array_combine($fallback, array_map(fn ($value) => strtoupper(str_replace('_', ' ', $value)), $fallback)) ?: []);
        return $options->map(fn ($label, $value) => ['value' => (string) $value, 'label' => $label])->values()->all();
    }

    private function validateDynamicForm(Request $request, ?User $viewer, ?int $templateId): array
    {
        if (! $templateId) return [null, []];
        $template = FormTemplate::with('fields')->whereKey($templateId)->where('is_active', true)
            ->where('owner_unit', $this->organizationUnit($viewer))->firstOrFail();
        $rules = [];
        foreach ($template->fields as $field) {
            if ($field->binding || in_array($field->type, ['heading', 'paragraph'], true)) continue;
            $rule = [$field->is_required ? 'required' : 'nullable'];
            $rule[] = match ($field->type) { 'number' => 'numeric', 'date' => 'date', 'email' => 'email', 'checkbox' => 'boolean', 'multi_select' => 'array', default => 'string' };
            $validation = $field->validation ?? [];
            if (isset($validation['min'])) $rule[] = 'min:'.$validation['min'];
            if (isset($validation['max'])) $rule[] = 'max:'.$validation['max'];
            if (! empty($validation['pattern'])) $rule[] = 'regex:'.$validation['pattern'];
            if (in_array($field->type, ['select', 'radio', 'multi_select'], true)) {
                $allowed = collect($this->dynamicFieldOptions($field->data_source, $field->options ?? []))->pluck('value')->map(fn ($v) => (string) $v)->all();
                if ($allowed && $field->type !== 'multi_select') $rule[] = Rule::in($allowed);
            }
            $rules['dynamic.'.$field->name] = $rule;
            if ($field->type === 'multi_select') {
                $allowed = collect($this->dynamicFieldOptions($field->data_source, $field->options ?? []))->pluck('value')->map(fn ($v) => (string) $v)->all();
                if ($allowed) $rules['dynamic.'.$field->name.'.*'] = [Rule::in($allowed)];
            }
        }
        return [$template, $request->validate($rules)['dynamic'] ?? []];
    }

    private function ensureSubtopicMatchesMainTopic(?string $mainTopicSlug, ?int $subtopicId): void
    {
        if (! $subtopicId) {
            return;
        }

        if (! $mainTopicSlug) {
            throw ValidationException::withMessages([
                'topic_category' => 'Please select a main topic when a subtopic is selected.',
            ]);
        }

        $isMatching = TopicSubtopic::query()
            ->whereKey($subtopicId)
            ->whereHas('mainTopic', fn ($query) => $query
                ->where('slug', $mainTopicSlug)
                ->where('owner_unit', $this->organizationUnit(request()->user())))
            ->exists();

        if (! $isMatching) {
            throw ValidationException::withMessages([
                'subtopic_id' => 'Selected subtopic does not belong to the selected main topic.',
            ]);
        }
    }

    private function ensureTopicDetailMatchesMainTopic(?int $mainTopicId, ?int $topicDetailId): void
    {
        if (! $topicDetailId) {
            return;
        }

        if (! $mainTopicId || ! TopicDetail::query()
            ->whereKey($topicDetailId)
            ->where('main_topic_id', $mainTopicId)
            ->whereHas('mainTopic.mainTopic', fn ($query) => $query->where('owner_unit', $this->organizationUnit(request()->user())))
            ->exists()) {
            throw ValidationException::withMessages([
                'topic_detail_id' => 'Selected subtopic does not belong to the selected main topic.',
            ]);
        }
    }

    private function canViewDocument(?User $user, PolicyDocument $policyDocument): bool
    {
        return PolicyDocument::query()
            ->whereKey($policyDocument->id)
            ->visibleTo($user)
            ->exists();
    }

    private function ensureCanManageDocuments(?User $user): void
    {
        abort_unless($this->canManageDocuments($user), 403);
    }

    private function ensureOwnerUnitAccess(?User $user, string $ownerUnit): void
    {
        abort_unless($user?->canManagePolicies() && ($user->isSystemAdmin() || $ownerUnit === $this->organizationUnit($user)), 403);
    }

    private function organizationUnit(?User $user): string
    {
        if ($user?->organization && strtoupper($user->organization->code) !== 'MSD') {
            return 'kcdiom';
        }

        return $user?->unit === 'kcdiom' ? 'kcdiom' : 'msd';
    }

    private function canPublishDocument(?User $user, PolicyDocument $policyDocument): bool
    {
        return $this->canManageDocuments($user) && $policyDocument->status !== 'published';
    }

    private function findHistoricalRecords(?User $viewer, string $lookupTerm)
    {
        if ($lookupTerm === '') {
            return collect();
        }

        return PolicyDocument::query()
            ->with('creator')
            ->withCount('versions')
            ->visibleTo($viewer)
            ->whereNull('parent_document_id')
            ->where(function ($query) use ($lookupTerm): void {
                $query->where('title', 'like', '%'.$lookupTerm.'%');

                if (is_numeric($lookupTerm)) {
                    $query->orWhereKey((int) $lookupTerm);
                }
            })
            ->latest()
            ->limit(10)
            ->get();
    }

    private function decoratePaginator($documents)
    {
        $documents->setCollection($this->decorateCollection($documents->getCollection()));

        return $documents;
    }

    private function attachVersionFamilies($documents, ?User $viewer): void
    {
        $rootIds = $documents
            ->map(fn (PolicyDocument $document) => $document->parent_document_id ?: $document->id)
            ->unique()
            ->values();

        if ($rootIds->isEmpty()) {
            return;
        }

        $families = PolicyDocument::query()
            ->visibleTo($viewer)
            ->where(function ($query) use ($rootIds): void {
                $query->whereIn('id', $rootIds)->orWhereIn('parent_document_id', $rootIds);
            })
            ->orderByDesc('version_number')
            ->get()
            ->groupBy(fn (PolicyDocument $document) => $document->parent_document_id ?: $document->id);

        $documents->each(function (PolicyDocument $document) use ($families): void {
            $rootId = $document->parent_document_id ?: $document->id;
            $document->setRelation('versionFamily', $families->get($rootId, collect()));
        });
    }

    private function decorateCollection($documents)
    {
        return $documents->map(fn (PolicyDocument $document) => $this->decorateDocument($document));
    }

    private function decorateDocument(PolicyDocument $document): PolicyDocument
    {
        $rootId = $document->parent_document_id ?: $document->id;

        $effectiveVersionNumber = PolicyDocument::query()
            ->where(function ($query) use ($rootId): void {
                $query->where('id', $rootId)
                    ->orWhere('parent_document_id', $rootId);
            })
            ->where('status', 'published')
            ->max('version_number');

        $document->setAttribute('effective_version_number', $effectiveVersionNumber);
        $document->setAttribute(
            'is_effective_published_version',
            $effectiveVersionNumber !== null && (int) $effectiveVersionNumber === (int) $document->version_number
        );

        return $document;
    }

    private function notifyPublishedDocumentRecipients(PolicyDocument $policyDocument, ?int $publisherId): void
    {
        $notificationClass = $policyDocument->is_circular
            ? CircularPublishedNotification::class
            : DocumentPublishedNotification::class;

        User::query()
            ->where('is_active', true)
            ->where('id', '!=', $publisherId)
            ->get()
            ->filter(fn (User $user) => $user->canReceiveCircularNotificationFor($policyDocument))
            ->each(fn (User $user) => $user->notify(new $notificationClass($policyDocument)));
    }

    private function storePdfUploads(Request $request): array
    {
        $files = collect($request->file('files', []))->filter();

        // Continue accepting the previous single-file field for existing clients.
        if ($request->hasFile('file')) {
            $files->prepend($request->file('file'));
        }

        return $files->map(fn (UploadedFile $file): array => [
            'file' => $file,
            'path' => $file->store('policy-documents', 'public'),
        ])->values()->all();
    }

    private function recordAttachments(PolicyDocument $document, array $uploads, ?bool $isPublic = null): void
    {
        if ($uploads === []) {
            return;
        }

        $isPublic ??= (bool) $document->public_flag;
        $rootId = $document->parent_document_id ?: $document->id;
        $history = DocumentHistory::where('policy_document_id', $rootId)
            ->where('version_number', $document->version_number)
            ->first();

        foreach ($uploads as $upload) {
            /** @var UploadedFile $file */
            $file = $upload['file'];
            $storedPath = $upload['path'];

            DocumentAttachment::create([
                'policy_document_id' => $rootId,
                'document_history_id' => $history?->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $storedPath,
                'file_size' => $file->getSize(),
                'file_type' => $file->getClientMimeType(),
                'checksum_sha256' => hash('sha256', Storage::disk('public')->get($storedPath)),
                'security_status' => 'validated',
                'is_public' => $isPublic,
                'uploaded_by' => auth()->id(),
            ]);
        }
    }

    private function copyAttachmentsToVersion(PolicyDocument $document, $attachments): void
    {
        if ($attachments->isEmpty()) {
            return;
        }

        $rootId = $document->parent_document_id ?: $document->id;
        $history = DocumentHistory::query()
            ->where('policy_document_id', $rootId)
            ->where('version_number', $document->version_number)
            ->first();

        foreach ($attachments as $attachment) {
            DocumentAttachment::create([
                'policy_document_id' => $rootId,
                'document_history_id' => $history?->id,
                'file_name' => $attachment->file_name,
                'file_path' => $attachment->file_path,
                'file_size' => $attachment->file_size,
                'file_type' => $attachment->file_type,
                'checksum_sha256' => $attachment->checksum_sha256,
                'security_status' => $attachment->security_status,
                'is_public' => $attachment->is_public,
                'integrity_verified_at' => $attachment->integrity_verified_at,
                'uploaded_by' => $attachment->uploaded_by,
            ]);
        }
    }

    private function updateAttachmentVisibility(PolicyDocument $document, array $visibility): void
    {
        if ($visibility === []) {
            return;
        }

        $rootId = $document->parent_document_id ?: $document->id;
        $historyId = DocumentHistory::query()
            ->where('policy_document_id', $rootId)
            ->where('version_number', $document->version_number)
            ->value('id');

        DocumentAttachment::query()
            ->where('policy_document_id', $rootId)
            ->when($historyId, fn ($query) => $query->where('document_history_id', $historyId))
            ->whereIn('id', array_keys($visibility))
            ->get()
            ->each(function (DocumentAttachment $attachment) use ($visibility): void {
                $attachment->update([
                    'is_public' => ($visibility[$attachment->id] ?? 'internal') === 'public',
                ]);
            });
    }
}

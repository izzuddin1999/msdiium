<?php

namespace App\Http\Controllers;

use App\Models\DocumentAttachment;
use App\Models\DocumentHistory;
use App\Models\PolicyDocument;
use App\Models\TopicCategory;
use App\Models\TopicSubtopic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PublicPortalController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $viewer = $request->user();
        $requestedUnit = $request->route('unit') ?: $request->query('unit');
        $selectedUnit = in_array($requestedUnit, ['msd', 'kcdiom'], true) ? $requestedUnit : null;

        // Unit managers work in the governed repository, not in the public
        // presentation. The repository scopes itself to their assigned unit,
        // so even a manually entered cross-unit URL returns them to their own
        // management workspace.
        if ($selectedUnit && $viewer?->canManagePolicies()) {
            $managementUnit = $viewer->unit === 'kcdiom' ? 'kcdiom' : 'msd';

            return redirect()->route('policy-documents.index', ['unit' => $managementUnit]);
        }

        $allEligible = $this->eligibleDocuments();
        $scopedEligible = clone $allEligible;

        if ($selectedUnit) {
            $scopedEligible->where('public_documents.owner_unit', $selectedUnit);
        }

        $query = (clone $scopedEligible)->with(['subtopic.mainTopic', 'topicDetail', 'organization']);

        if ($search = trim((string) $request->query('q'))) {
            $needle = '%'.mb_strtolower($search).'%';
            $query->where(function (Builder $builder) use ($needle): void {
                $builder->whereRaw('LOWER(public_documents.title) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(public_documents.reference_number, \'\')) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(public_documents.content, \'\')) LIKE ?', [$needle]);
            });
        }

        if ($request->filled('year')) {
            $yearExpression = config('database.default') === 'sqlite'
                ? "CAST(strftime('%Y', COALESCE(public_documents.effective_date, public_documents.published_at, public_documents.created_at)) AS INTEGER)"
                : 'EXTRACT(YEAR FROM COALESCE(public_documents.effective_date, public_documents.published_at, public_documents.created_at))';
            $query->whereRaw($yearExpression.' = ?', [(int) $request->query('year')]);
        }
        if ($request->filled('category')) {
            $query->where('public_documents.topic_category', $request->query('category'));
        }
        if ($request->filled('type')) {
            $query->where('public_documents.document_type', $request->query('type'));
        }

        $documents = $query
            ->orderByRaw('COALESCE(public_documents.effective_date, public_documents.published_at, public_documents.created_at) DESC')
            ->paginate(12)
            ->withQueryString();

        $yearQuery = clone $scopedEligible;
        $yearQuery->getQuery()->columns = null;
        $yearExpression = config('database.default') === 'sqlite'
            ? "CAST(strftime('%Y', COALESCE(public_documents.effective_date, public_documents.published_at, public_documents.created_at)) AS INTEGER)"
            : 'EXTRACT(YEAR FROM COALESCE(public_documents.effective_date, public_documents.published_at, public_documents.created_at))::int';
        $years = $yearQuery
            ->selectRaw('DISTINCT '.$yearExpression.' AS year')
            ->orderByDesc('year')
            ->pluck('year');
        $categoryQuery = clone $scopedEligible;
        $categoryQuery->getQuery()->columns = null;
        $categories = $categoryQuery
            ->select('public_documents.topic_category')
            ->whereNotNull('public_documents.topic_category')
            ->distinct()
            ->orderBy('public_documents.topic_category')
            ->pluck('topic_category');

        $typeQuery = clone $scopedEligible;
        $typeQuery->getQuery()->columns = null;
        $types = $typeQuery
            ->select('public_documents.document_type')
            ->whereNotNull('public_documents.document_type')
            ->distinct()
            ->orderBy('public_documents.document_type')
            ->pluck('document_type');

        $staffVisible = $viewer
            ? PolicyDocument::query()->visibleTo($viewer)->latestInFamily($viewer->canManagePolicies() ? null : ['published', 'superseded'])
            : null;
        $staffHistory = $viewer
            ? PolicyDocument::query()->visibleTo($viewer)
            : null;
        $staffMetrics = $staffVisible ? [
            'total' => (clone $staffVisible)->count(),
            'published' => (clone $staffVisible)->where('status', 'published')->count(),
            'draft' => $viewer->canManagePolicies() ? (clone $staffVisible)->where('status', 'draft')->count() : null,
            // Superseded records are historical versions, so they are
            // intentionally excluded by latestInFamily() above. Count them
            // from the full visible history instead.
            'superseded' => (clone $staffHistory)->where('status', 'superseded')->count(),
            'expiring' => (clone $staffVisible)->whereBetween('expiry_date', [now()->startOfDay(), now()->addDays(30)->endOfDay()])->count(),
            'policies' => (clone $staffVisible)->where('document_type', 'policy')->count(),
            'circulars' => (clone $staffVisible)->where('is_circular', true)->count(),
            'guidelines' => (clone $staffVisible)->where('document_type', 'guideline')->count(),
        ] : null;

        $pageMeta = match ($selectedUnit) {
            'msd' => [
                'title' => 'MSD Public Directory',
                'eyebrow' => 'MANAGEMENT SERVICES DIVISION',
                'heading' => 'MSD policies, guidelines and circulars',
                'description' => 'Browse current public documents issued by the Management Services Division.',
            ],
            'kcdiom' => [
                'title' => 'KCDIOM Public Directory',
                'eyebrow' => 'KULLIYYAH, CENTRES, DIVISIONS, INSTITUTES & OFFICES',
                'heading' => 'KCDIOM policies, guidelines and circulars',
                'description' => 'Browse current public documents issued by IIUM Kulliyyah, Centres, Divisions, Institutes and Offices.',
            ],
            default => [
                'title' => 'Public Directory',
                'eyebrow' => 'IIUM PUBLIC DOCUMENT PORTAL',
                'heading' => 'Policies, guidelines and circulars in one trusted directory',
                'description' => 'Search current public documents issued by IIUM. Browse by year, topic or document type, then preview the official PDF online.',
            ],
        };

        $unitCardDocuments = $staffVisible ?: $allEligible;
        $unitCardTable = $staffVisible ? (new PolicyDocument)->getTable() : 'public_documents';

        return view('public_portal.index', [
            'documents' => $documents,
            'years' => $years,
            'categories' => $categories,
            'types' => $types,
            'totalDocuments' => (clone $scopedEligible)->count(),
            'allDocumentCount' => (clone $allEligible)->count(),
            'latestYear' => $years->first(),
            'categoryCount' => $categories->count(),
            'selectedUnit' => $selectedUnit,
            'pageMeta' => $pageMeta,
            'unitCards' => collect([
                ['code' => 'MSD', 'name' => 'Management Services Division', 'unit' => 'msd', 'icon' => 'account_balance'],
                ['code' => 'OTHER UNITS', 'name' => 'Other Kulliyyah, Centres, Divisions, Institutes & Offices', 'unit' => 'kcdiom', 'icon' => 'domain'],
            ])->map(function (array $unit) use ($unitCardDocuments, $unitCardTable): array {
                $documents = (clone $unitCardDocuments)->where($unitCardTable.'.owner_unit', $unit['unit']);

                return $unit + [
                    'documents' => (clone $documents)->count(),
                    'circulars' => (clone $documents)->where($unitCardTable.'.is_circular', true)->count(),
                    'latest' => (clone $documents)->latest($unitCardTable.'.published_at')->value('title'),
                ];
            }),
            'viewer' => $viewer,
            'staffMetrics' => $staffMetrics,
            'recentStaffDocuments' => $staffVisible
                ? (clone $staffVisible)->with('organization')->latest('updated_at')->limit(5)->get()
                : collect(),
            'topicHierarchy' => ($selectedUnit === 'msd' || ($viewer?->canManagePolicies() && $viewer->unit !== 'kcdiom'))
                ? TopicCategory::query()
                    ->where('owner_unit', 'msd')
                    ->where('is_active', true)
                    ->with(['subtopics' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('name')])
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get()
                    ->each(fn (TopicCategory $category) => $category->setAttribute(
                        'documents_count',
                        PolicyDocument::query()->where('owner_unit', 'msd')->where('topic_category', $category->slug)->count()
                    ))
                    ->sortByDesc('documents_count')
                    ->values()
                : collect(),
        ]);
    }

    public function show(PolicyDocument $policyDocument): View
    {
        $document = $this->eligibleDocuments()
            ->where('public_documents.id', $policyDocument->getKey())
            ->with(['subtopic.mainTopic', 'topicDetail', 'organization'])
            ->firstOrFail();

        return view('public_portal.show', [
            'document' => $document,
            'attachments' => $this->publicAttachments($document),
            'directoryRoute' => $document->owner_unit === 'msd' ? 'public.msd' : 'public.kcdiom',
        ]);
    }

    public function topic(Request $request, TopicSubtopic $topicSubtopic): View
    {
        $topicSubtopic->load([
            'mainTopic',
            'details' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('name'),
        ]);

        abort_unless(
            $topicSubtopic->is_active
            && $topicSubtopic->mainTopic?->is_active
            && $topicSubtopic->mainTopic?->owner_unit === 'msd',
            404
        );

        $documents = $this->eligibleDocuments()
            ->where('public_documents.owner_unit', 'msd')
            ->where('public_documents.subtopic_id', $topicSubtopic->id)
            ->with(['topicDetail', 'organization'])
            ->orderByRaw('COALESCE(public_documents.effective_date, public_documents.published_at, public_documents.created_at) DESC')
            ->paginate(10)
            ->withQueryString();

        return view('public_portal.topic', [
            'viewer' => $request->user(),
            'mainTopic' => $topicSubtopic,
            'category' => $topicSubtopic->mainTopic,
            'documents' => $documents,
            'pageMeta' => ['title' => $topicSubtopic->name],
        ]);
    }

    public function download(DocumentAttachment $documentAttachment)
    {
        $attachment = $this->authorizePublicAttachment($documentAttachment);
        abort_unless(Storage::disk('public')->exists($attachment->file_path), 404);

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }

    public function preview(DocumentAttachment $documentAttachment)
    {
        $attachment = $this->authorizePublicAttachment($documentAttachment);
        abort_unless(Storage::disk('public')->exists($attachment->file_path), 404);

        return Storage::disk('public')->response($attachment->file_path, $attachment->file_name, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.str_replace('"', '', $attachment->file_name).'"',
        ]);
    }

    private function eligibleDocuments(): Builder
    {
        return PolicyDocument::query()
            ->from('hr_intern.policy_documents as public_documents')
            ->select('public_documents.*')
            ->where('public_documents.status', 'published')
            ->where('public_documents.public_flag', true)
            ->whereRaw('LOWER(public_documents.access_scope) = ?', ['all'])
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('hr_intern.policy_documents as newer')
                    ->whereRaw('COALESCE(newer.parent_document_id, newer.id) = COALESCE(public_documents.parent_document_id, public_documents.id)')
                    ->whereColumn('newer.version_number', '>', 'public_documents.version_number')
                    ->where('newer.status', 'published')
                    ->where('newer.public_flag', true)
                    ->whereRaw('LOWER(newer.access_scope) = ?', ['all']);
            });
    }

    private function publicAttachments(PolicyDocument $document)
    {
        $rootId = $document->parent_document_id ?: $document->id;
        $history = DocumentHistory::query()
            ->where('policy_document_id', $rootId)
            ->where('version_number', $document->version_number)
            ->first();

        return DocumentAttachment::query()
            ->where('is_public', true)
            ->where(function (Builder $query) use ($document, $rootId, $history): void {
                if ($history) {
                    $query->where('policy_document_id', $rootId)->where('document_history_id', $history->id);
                } else {
                    $query->where('policy_document_id', $document->id)->whereNull('document_history_id');
                }
            })
            ->where(function (Builder $query): void {
                $query->where('file_type', 'application/pdf')->orWhereRaw('LOWER(file_name) LIKE ?', ['%.pdf']);
            })
            ->orderBy('file_name')
            ->get();
    }

    private function authorizePublicAttachment(DocumentAttachment $attachment): DocumentAttachment
    {
        abort_unless($attachment->is_public, 404);
        abort_unless($attachment->file_type === 'application/pdf' || str_ends_with(strtolower($attachment->file_name), '.pdf'), 404);

        $attachment->loadMissing(['document', 'history']);
        abort_unless($attachment->document, 404);
        $familyId = $attachment->document->parent_document_id ?: $attachment->document->id;
        $document = $this->eligibleDocuments()
            ->whereRaw('COALESCE(public_documents.parent_document_id, public_documents.id) = ?', [$familyId])
            ->firstOrFail();

        if ($attachment->document_history_id) {
            abort_unless((int) optional($attachment->history)->version_number === (int) $document->version_number, 404);
        } else {
            abort_unless((int) $attachment->policy_document_id === (int) $document->id, 404);
        }

        return $attachment;
    }
}

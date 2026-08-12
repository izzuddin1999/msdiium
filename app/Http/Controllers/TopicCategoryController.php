<?php

namespace App\Http\Controllers;

use App\Models\PolicyDocument;
use App\Models\Organization;
use App\Models\TopicCategory;
use App\Models\TopicDetail;
use App\Models\TopicSubtopic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TopicCategoryController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canManagePolicies(), 403);
        $organization = $this->organization($request);
        $isLegacySqlite = ! Schema::hasColumn('topic_categories', 'owner_unit') || ! Schema::hasColumn('topic_categories', 'sort_order');
        $organizationScope = fn ($query) => $query->where('owner_unit', $organization);

        $categories = TopicCategory::query()
            ->select($isLegacySqlite
                ? ['id', 'name', 'slug', 'is_active', 'created_at', 'updated_at']
                : ['id', 'name', 'slug', 'owner_unit', 'organization_id', 'is_active', 'sort_order'])
            ->when(! $isLegacySqlite, $organizationScope)
            ->with([
                'documents' => fn ($query) => $query->select(['id', 'title', 'topic_category', 'version_number', 'status', 'reference_number'])->where('owner_unit', $organization)->orderBy('title')->orderByDesc('version_number'),
                'subtopics' => fn ($query) => $query
                    ->with(['details' => fn ($detailQuery) => $detailQuery->when(! $isLegacySqlite, fn ($ordered) => $ordered->orderBy('sort_order'))->orderBy('name')])
                    ->when(! $isLegacySqlite, fn ($ordered) => $ordered->orderBy('sort_order'))
                    ->orderBy('name'),
            ])
            ->withCount(['documents' => fn ($query) => $query->where('owner_unit', $organization)])
            ->when(! $isLegacySqlite, fn ($query) => $query->orderBy('sort_order'))
            ->orderBy('name')
            ->get();

        $allMainTopics = TopicCategory::query()
            ->when(! $isLegacySqlite, $organizationScope)
            ->orderBy('name')
            ->get();

        $subtopicsQuery = TopicSubtopic::query()
            ->with('mainTopic:id,name')
            ->when(! $isLegacySqlite, fn ($query) => $query->whereHas('mainTopic', $organizationScope))
            ->orderBy('name');

        $detailsQuery = TopicDetail::query()
            ->with('mainTopic.mainTopic:id,name')
            ->when(! $isLegacySqlite, fn ($query) => $query->whereHas('mainTopic.mainTopic', $organizationScope))
            ->orderBy('name');

        return view('topic_categories.index', [
            'categories' => $categories,
            'allMainTopics' => $allMainTopics,
            'subtopics' => (clone $subtopicsQuery)->paginate(15, ['*'], 'subtopics_page'),
            'allMainTopicRecords' => (clone $subtopicsQuery)->get(),
            'topicDetails' => $detailsQuery->paginate(15, ['*'], 'details_page'),
            'organization' => $organization,
        ]);
    }

    public function reorder(Request $request): JsonResponse
    {
        abort_unless($request->user()?->canManagePolicies(), 403);
        $organization = $this->organization($request);
        $data = $request->validate([
            'categories' => ['required', 'array'],
            'categories.*' => ['integer'],
            'main_topics' => ['required', 'array'],
            'main_topics.*.id' => ['required', 'integer'],
            'main_topics.*.category_id' => ['required', 'integer'],
            'subtopics' => ['required', 'array'],
            'subtopics.*.id' => ['required', 'integer'],
            'subtopics.*.main_topic_id' => ['required', 'integer'],
        ]);

        $categoryIds = TopicCategory::where('owner_unit', $organization)->pluck('id')->map(fn ($id) => (int) $id);
        abort_unless(collect($data['categories'])->diff($categoryIds)->isEmpty(), 422);

        DB::transaction(function () use ($data, $categoryIds): void {
            foreach ($data['categories'] as $position => $id) {
                TopicCategory::whereKey($id)->update(['sort_order' => $position]);
            }
            foreach ($data['main_topics'] as $position => $item) {
                abort_unless($categoryIds->contains((int) $item['category_id']), 422);
                TopicSubtopic::whereKey($item['id'])->whereIn('topic_category_id', $categoryIds)
                    ->update(['topic_category_id' => $item['category_id'], 'sort_order' => $position]);
            }
            foreach ($data['subtopics'] as $position => $item) {
                $allowedMain = TopicSubtopic::whereKey($item['main_topic_id'])->whereIn('topic_category_id', $categoryIds)->exists();
                abort_unless($allowedMain, 422);
                TopicDetail::whereKey($item['id'])->whereHas('mainTopic', fn ($query) => $query->whereIn('topic_category_id', $categoryIds))
                    ->update(['main_topic_id' => $item['main_topic_id'], 'sort_order' => $position]);
            }
        });

        return response()->json(['message' => 'Topic hierarchy saved.']);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canManagePolicies(), 403);
        $organization = $this->organization($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('topic_categories', 'name')->where(fn ($query) => $query->where('owner_unit', $organization))],
            'is_active' => ['nullable', 'boolean'],
        ]);

        TopicCategory::create([
            'name' => trim($data['name']),
            'slug' => $this->uniqueSlug(trim($data['name']), $organization),
            'owner_unit' => $organization,
            'organization_id' => $request->user()?->organization_id
                ?: (Schema::hasTable('organizations') ? Organization::idForLegacyUnit($organization) : null),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return redirect()->route('topic-categories.index')->with('status', 'Topic category added successfully.');
    }

    public function storeSubtopic(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canManagePolicies(), 403);
        $organization = $this->organization($request);

        $request->merge(['name' => trim((string) $request->input('name'))]);

        $data = $request->validate([
            'topic_category_id' => ['required', Rule::exists('topic_categories', 'id')->where(fn ($query) => $query->where('is_active', true)->where('owner_unit', $organization))],
            'name' => ['required', 'string', 'max:80', Rule::unique('topic_subtopics', 'name')->where(fn ($query) => $query->where('topic_category_id', $request->input('topic_category_id')))],
            'is_active' => ['nullable', 'boolean'],
        ], ['name.unique' => 'This main topic already exists in the selected category.']);

        TopicSubtopic::create([
            'topic_category_id' => (int) $data['topic_category_id'],
            'name' => trim($data['name']),
            'slug' => $this->uniqueSubtopicSlug(trim($data['name'])),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return redirect()->route('topic-categories.index')->with('status', 'Main topic added successfully.');
    }

    public function update(Request $request, TopicCategory $topicCategory): RedirectResponse
    {
        abort_unless($request->user()?->canManagePolicies(), 403);
        $this->ensureOrganizationAccess($request, $topicCategory);
        $organization = $this->organization($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('topic_categories', 'name')->where(fn ($query) => $query->where('owner_unit', $organization))->ignore($topicCategory->id)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $newName = trim($data['name']);
        $newSlug = $topicCategory->slug;

        if ($topicCategory->name !== $newName) {
            $newSlug = $this->uniqueSlug($newName, $organization, $topicCategory->id);
        }

        DB::transaction(function () use ($topicCategory, $newName, $newSlug, $data): void {
            if ($newSlug !== $topicCategory->slug) {
                PolicyDocument::query()
                    ->where('topic_category', $topicCategory->slug)
                    ->update(['topic_category' => $newSlug]);
            }

            $topicCategory->update([
                'name' => $newName,
                'slug' => $newSlug,
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]);
        });

        return redirect()->route('topic-categories.index')->with('status', 'Topic category updated successfully.');
    }

    public function storeDetail(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canManagePolicies(), 403);
        $organization = $this->organization($request);

        $request->merge(['name' => trim((string) $request->input('name'))]);

        $data = $request->validate([
            'main_topic_id' => ['required', Rule::exists('topic_subtopics', 'id')->where(fn ($query) => $query->where('is_active', true)->whereIn('topic_category_id', TopicCategory::where('owner_unit', $organization)->select('id')))],
            'name' => ['required', 'string', 'max:100', Rule::unique('topic_details', 'name')->where(fn ($query) => $query->where('main_topic_id', $request->input('main_topic_id')))],
            'is_active' => ['nullable', 'boolean'],
        ], ['name.unique' => 'This subtopic already exists under the selected main topic.']);

        $name = trim($data['name']);
        TopicDetail::create([
            'main_topic_id' => (int) $data['main_topic_id'],
            'name' => $name,
            'slug' => $this->uniqueDetailSlug($name),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return redirect()->route('topic-categories.index')->with('status', 'Subtopic added successfully.');
    }

    public function updateDetail(Request $request, TopicDetail $topicDetail): RedirectResponse
    {
        abort_unless($request->user()?->canManagePolicies(), 403);
        $this->ensureOrganizationAccess($request, $topicDetail->mainTopic?->mainTopic);
        $organization = $this->organization($request);

        $request->merge(['name' => trim((string) $request->input('name'))]);

        $data = $request->validate([
            'main_topic_id' => ['required', Rule::exists('topic_subtopics', 'id')->where(fn ($query) => $query->where('is_active', true)->whereIn('topic_category_id', TopicCategory::where('owner_unit', $organization)->select('id')))],
            'name' => ['required', 'string', 'max:100', Rule::unique('topic_details', 'name')->ignore($topicDetail->id)->where(fn ($query) => $query->where('main_topic_id', $request->input('main_topic_id')))],
            'is_active' => ['nullable', 'boolean'],
        ], ['name.unique' => 'This subtopic already exists under the selected main topic.']);
        $name = trim($data['name']);
        $topicDetail->update([
            'main_topic_id' => (int) $data['main_topic_id'],
            'name' => $name,
            'slug' => $topicDetail->name === $name ? $topicDetail->slug : $this->uniqueDetailSlug($name, $topicDetail->id),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('topic-categories.index')->with('status', 'Subtopic updated successfully.');
    }

    public function destroyDetail(Request $request, TopicDetail $topicDetail): RedirectResponse
    {
        abort_unless($request->user()?->canManagePolicies(), 403);
        $this->ensureOrganizationAccess($request, $topicDetail->mainTopic?->mainTopic);
        $usageCount = PolicyDocument::query()->where('topic_detail_id', $topicDetail->id)->count();
        if ($usageCount > 0) {
            return redirect()->route('topic-categories.index')->withErrors(['detail' => 'This subtopic is used by '.$usageCount.' document(s) and cannot be deleted.']);
        }
        $topicDetail->delete();

        return redirect()->route('topic-categories.index')->with('status', 'Subtopic deleted successfully.');
    }

    public function updateSubtopic(Request $request, TopicSubtopic $topicSubtopic): RedirectResponse
    {
        abort_unless($request->user()?->canManagePolicies(), 403);
        $this->ensureOrganizationAccess($request, $topicSubtopic->mainTopic);
        $organization = $this->organization($request);

        $request->merge(['name' => trim((string) $request->input('name'))]);

        $data = $request->validate([
            'topic_category_id' => ['required', Rule::exists('topic_categories', 'id')->where(fn ($query) => $query->where('is_active', true)->where('owner_unit', $organization))],
            'name' => ['required', 'string', 'max:80', Rule::unique('topic_subtopics', 'name')->ignore($topicSubtopic->id)->where(fn ($query) => $query->where('topic_category_id', $request->input('topic_category_id')))],
            'is_active' => ['nullable', 'boolean'],
        ], ['name.unique' => 'This main topic already exists in the selected category.']);

        $newName = trim($data['name']);
        $newSlug = $topicSubtopic->slug;

        if ($topicSubtopic->name !== $newName) {
            $newSlug = $this->uniqueSubtopicSlug($newName, $topicSubtopic->id);
        }

        $topicSubtopic->update([
            'topic_category_id' => (int) $data['topic_category_id'],
            'name' => $newName,
            'slug' => $newSlug,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('topic-categories.index')->with('status', 'Main topic updated successfully.');
    }

    public function destroy(Request $request, TopicCategory $topicCategory): RedirectResponse
    {
        abort_unless($request->user()?->canManagePolicies(), 403);
        $this->ensureOrganizationAccess($request, $topicCategory);

        $usageCount = PolicyDocument::query()->where('topic_category', $topicCategory->slug)->count();

        if ($usageCount > 0) {
            return redirect()
                ->route('topic-categories.index')
                ->withErrors(['category' => 'This topic category is used by '.$usageCount.' document(s) and cannot be deleted.']);
        }

        $topicCategory->delete();

        return redirect()->route('topic-categories.index')->with('status', 'Topic category deleted successfully.');
    }

    public function destroySubtopic(Request $request, TopicSubtopic $topicSubtopic): RedirectResponse
    {
        abort_unless($request->user()?->canManagePolicies(), 403);
        $this->ensureOrganizationAccess($request, $topicSubtopic->mainTopic);

        $usageCount = PolicyDocument::query()->where('subtopic_id', $topicSubtopic->id)->count();

        if ($usageCount > 0) {
            return redirect()
                ->route('topic-categories.index')
                ->withErrors(['subtopic' => 'This main topic is used by '.$usageCount.' document(s) and cannot be deleted.']);
        }

        $topicSubtopic->delete();

        return redirect()->route('topic-categories.index')->with('status', 'Main topic deleted successfully.');
    }

    private function uniqueSlug(string $name, string $organization, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'topic';
        $slug = $base;
        $suffix = 2;

        while (
            TopicCategory::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('owner_unit', $organization)
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function uniqueSubtopicSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'subtopic';
        $slug = $base;
        $suffix = 2;

        while (
            TopicSubtopic::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function organization(Request $request): string
    {
        return $request->user()?->unit === 'kcdiom' ? 'kcdiom' : 'msd';
    }

    private function ensureOrganizationAccess(Request $request, ?TopicCategory $category): void
    {
        abort_unless($category && $category->owner_unit === $this->organization($request), 404);
    }

    private function uniqueDetailSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'subtopic';
        $slug = $base;
        $suffix = 2;
        while (TopicDetail::query()->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}

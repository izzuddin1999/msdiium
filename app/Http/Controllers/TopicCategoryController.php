<?php

namespace App\Http\Controllers;

use App\Models\PolicyDocument;
use App\Models\TopicCategory;
use App\Models\TopicDetail;
use App\Models\TopicSubtopic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TopicCategoryController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canManagePolicies(), 403);

        return view('topic_categories.index', [
            'categories' => TopicCategory::query()
                ->with(['documents' => fn ($query) => $query->select(['id', 'title', 'topic_category', 'version_number', 'status', 'reference_number'])->orderBy('title')->orderByDesc('version_number')])
                ->withCount('documents')
                ->orderBy('name')->paginate(15),
            'allMainTopics' => TopicCategory::query()->orderBy('name')->get(),
            'subtopics' => TopicSubtopic::query()
                ->with('mainTopic:id,name')
                ->orderBy('name')
                ->paginate(15, ['*'], 'subtopics_page'),
            'allMainTopicRecords' => TopicSubtopic::query()->with('mainTopic:id,name')->orderBy('name')->get(),
            'topicDetails' => TopicDetail::query()
                ->with('mainTopic.mainTopic:id,name')
                ->orderBy('name')
                ->paginate(15, ['*'], 'details_page'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canManagePolicies(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:topic_categories,name'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        TopicCategory::create([
            'name' => trim($data['name']),
            'slug' => $this->uniqueSlug(trim($data['name'])),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return redirect()->route('topic-categories.index')->with('status', 'Topic category added successfully.');
    }

    public function storeSubtopic(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canManagePolicies(), 403);

        $request->merge(['name' => trim((string) $request->input('name'))]);

        $data = $request->validate([
            'topic_category_id' => ['required', Rule::exists('topic_categories', 'id')->where(fn ($query) => $query->where('is_active', true))],
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

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('topic_categories', 'name')->ignore($topicCategory->id)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $newName = trim($data['name']);
        $newSlug = $topicCategory->slug;

        if ($topicCategory->name !== $newName) {
            $newSlug = $this->uniqueSlug($newName, $topicCategory->id);
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

        $request->merge(['name' => trim((string) $request->input('name'))]);

        $data = $request->validate([
            'main_topic_id' => ['required', Rule::exists('topic_subtopics', 'id')->where(fn ($query) => $query->where('is_active', true))],
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

        $request->merge(['name' => trim((string) $request->input('name'))]);

        $data = $request->validate([
            'main_topic_id' => ['required', Rule::exists('topic_subtopics', 'id')->where(fn ($query) => $query->where('is_active', true))],
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

        $request->merge(['name' => trim((string) $request->input('name'))]);

        $data = $request->validate([
            'topic_category_id' => ['required', Rule::exists('topic_categories', 'id')->where(fn ($query) => $query->where('is_active', true))],
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

        $usageCount = PolicyDocument::query()->where('subtopic_id', $topicSubtopic->id)->count();

        if ($usageCount > 0) {
            return redirect()
                ->route('topic-categories.index')
                ->withErrors(['subtopic' => 'This main topic is used by '.$usageCount.' document(s) and cannot be deleted.']);
        }

        $topicSubtopic->delete();

        return redirect()->route('topic-categories.index')->with('status', 'Main topic deleted successfully.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'topic';
        $slug = $base;
        $suffix = 2;

        while (
            TopicCategory::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
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

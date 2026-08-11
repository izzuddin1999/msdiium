@extends('layouts.app')

@section('content')
<style>
    .dependency-row td{padding:0!important;background:#fffaf0}.dependency-details{padding:0 14px 14px}.dependency-details summary{display:inline-flex;align-items:center;gap:6px;margin:10px 0 0;padding:7px 10px;border-radius:8px;background:#fff1cf;color:#76540c;font-size:12px;font-weight:700;cursor:pointer;list-style:none}.dependency-details summary::-webkit-details-marker{display:none}.dependency-list{margin-top:10px;border:1px solid #ecdcae;border-radius:9px;overflow:hidden;background:#fff}.dependency-document{display:grid;grid-template-columns:minmax(0,1fr) auto auto auto;align-items:center;gap:12px;padding:10px 12px;border-bottom:1px solid #f0e7d0}.dependency-document:last-child{border-bottom:0}.dependency-document strong,.dependency-document small{display:block}.dependency-document small{color:#8a8170}.usage-badge{display:inline-flex;align-items:center;gap:4px;padding:5px 8px;border-radius:20px;background:#fff0cc;color:#815e12;font-size:11px;font-weight:750}.delete-protected{opacity:.55;cursor:not-allowed}
    .category-table td{vertical-align:middle;padding:15px 12px}.category-name{display:flex;align-items:center;gap:11px}.category-icon{display:grid;place-items:center;flex:0 0 38px;height:38px;border-radius:10px;background:#e9f7f4;color:#009b90}.category-icon .material-icons{font-size:20px}.category-name strong,.category-name small{display:block}.category-name small{margin-top:2px;color:#7b8c87}.category-actions{display:flex;justify-content:flex-end;align-items:center;gap:7px}.icon-action{width:36px;height:36px;display:grid;place-items:center;padding:0;border-radius:9px}.icon-action .material-icons{font-size:18px}.category-edit-row{display:none}.category-edit-row.open{display:table-row}.category-edit-row>td{padding:0 12px 14px!important;background:#f5fbf9}.category-editor{display:grid;grid-template-columns:minmax(200px,1fr) minmax(180px,1fr) auto auto;align-items:end;gap:12px;padding:14px;border:1px solid #d6e9e4;border-radius:11px;background:#fff;box-shadow:0 7px 18px rgba(25,72,62,.07)}.category-editor-actions{display:flex;gap:7px}.category-editor .form-check{white-space:nowrap;margin:0 0 10px}.category-edit-toggle[aria-expanded="true"],.main-topic-edit-toggle[aria-expanded="true"]{background:#e8f7f4;border-color:#00a094;color:#00867c}
    @media(max-width:767px){.dependency-document{grid-template-columns:1fr auto}.dependency-document .usage-meta{display:none}.category-table thead{display:none}.category-table tr:not(.category-edit-row):not(.dependency-row){display:grid;grid-template-columns:1fr auto}.category-table tr:not(.category-edit-row):not(.dependency-row) td:nth-child(2){display:none}.category-actions{justify-content:flex-start}.category-editor{grid-template-columns:1fr}.category-editor-actions{justify-content:flex-end}}
    .hierarchy-flow{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin:0 0 20px;padding:10px;border:1px solid #d9e8e4;border-radius:14px;background:#fff;box-shadow:0 7px 20px rgba(25,72,62,.05)}.hierarchy-step{display:flex;align-items:center;gap:11px;padding:12px 14px;border:1px solid transparent;border-radius:10px;background:#f7faf9;color:#61756f;text-align:left;cursor:pointer;transition:.18s}.hierarchy-step:hover{background:#eef8f5;color:#087f74}.hierarchy-step.active{border-color:#78cec4;background:linear-gradient(135deg,#e7f8f4,#f5fcfa);color:#075e55;box-shadow:0 5px 14px rgba(0,145,132,.10)}.hierarchy-step-number{display:grid;place-items:center;flex:0 0 34px;height:34px;border-radius:10px;background:#dceae6;color:#54716a;font-weight:800}.hierarchy-step.active .hierarchy-step-number{background:#009b90;color:#fff}.hierarchy-step strong,.hierarchy-step small{display:block}.hierarchy-step small{margin-top:2px;color:#82918d}.topic-layer-panel{display:none}.topic-layer-panel.active{display:block}@media(max-width:767px){.hierarchy-flow{grid-template-columns:1fr}.hierarchy-step small{display:none}}
    .topic-management-page>.row{--bs-gutter-x:16px}.topic-management-page>.row>.col-lg-4{flex:0 0 29%;max-width:29%}.topic-management-page>.row>.col-lg-8{flex:0 0 71%;max-width:71%}
    .layer-main .card-header{padding:15px 18px}.layer-main .card-body{padding:16px 18px}.layer-main .card-footer{padding:13px 18px}.layer-main .form-control{min-height:42px}.layer-main .form-label{margin-bottom:5px;font-size:11px}.layer-main .form-check-label{font-size:12px}
    .main-topic-table{width:100%!important;min-width:0!important;table-layout:fixed}.main-topic-table th:nth-child(1){width:36%}.main-topic-table th:nth-child(2){width:26%}.main-topic-table th:nth-child(3){width:17%}.main-topic-table th:nth-child(4){width:9%}.main-topic-table th:nth-child(5){width:12%}
    .main-topic-table td{padding:11px 10px}.main-topic-table .category-icon{flex-basis:34px;height:34px}.main-topic-table .category-name{gap:9px;min-width:0}.main-topic-table .category-name>span:last-child{min-width:0}.main-topic-table .category-name strong{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:11px}.main-topic-table .category-name small{font-size:10px}.main-topic-table code{display:block;overflow:hidden;color:#e84c4c;font-size:9px;text-overflow:ellipsis;white-space:nowrap}.main-topic-table .status-pill{white-space:nowrap;font-size:9px}.main-topic-table .category-actions{gap:4px}.main-topic-table .icon-action{width:30px;height:30px;border-radius:7px}.main-topic-table .icon-action .material-icons{font-size:16px}
    @media(max-width:1199px){.topic-management-page>.row>.col-lg-4{flex-basis:34%;max-width:34%}.topic-management-page>.row>.col-lg-8{flex-basis:66%;max-width:66%}.main-topic-table th:nth-child(3),.main-topic-table td:nth-child(3){display:none}.main-topic-table th:nth-child(1){width:42%}.main-topic-table th:nth-child(2){width:33%}.main-topic-table th:nth-child(4){width:10%}.main-topic-table th:nth-child(5){width:15%}}
    @media(max-width:991px){.topic-management-page>.row>.col-lg-4,.topic-management-page>.row>.col-lg-8{flex:0 0 100%;max-width:100%}.topic-management-page>.row>.col-lg-8{margin-top:16px}}
</style>
<div class="topic-management-page">
<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Dashboard</a><span class="material-icons">chevron_right</span><span>Topics</span></div>
<div class="page-heading"><div><span class="eyebrow">Classification governance</span><h2>Topic categories, main topics & subtopics</h2><p>Maintain the Topic Category → Main Topic → Subtopic hierarchy used across the document repository.</p></div></div>
<div class="hierarchy-flow" role="tablist" aria-label="Topic hierarchy levels">
    <button type="button" class="hierarchy-step active" data-topic-layer="category" role="tab" aria-selected="true"><span class="hierarchy-step-number">1</span><span><strong>Category Name</strong><small>Top-level policy grouping</small></span></button>
    <button type="button" class="hierarchy-step" data-topic-layer="main" role="tab" aria-selected="false"><span class="hierarchy-step-number">2</span><span><strong>Main Topic</strong><small>Topic within a category</small></span></button>
    <button type="button" class="hierarchy-step" data-topic-layer="detail" role="tab" aria-selected="false"><span class="hierarchy-step-number">3</span><span><strong>Subtopic</strong><small>Detailed document classification</small></span></button>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="card mb-3 topic-layer-panel layer-category active">
            <div class="card-header">
                <h5 class="mb-0">Add Topic Category</h5>
            </div>
            <form action="{{ route('topic-categories.store') }}" method="POST">
                @csrf
                <div class="card-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Topic Category Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Employee Relations" required>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" value="1" id="topicActive" @checked(old('is_active', true))>
                            <label class="form-check-label" for="topicActive">Active category</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary w-100">Add Topic Category</button>
                </div>
            </form>
        </div>

        <div class="card topic-layer-panel layer-main">
            <div class="card-header">
                <h5 class="mb-0">Add Main Topic</h5>
            </div>
            <form action="{{ route('topic-subtopics.store') }}" method="POST">
                @csrf
                <div class="card-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Topic Category</label>
                        <select name="topic_category_id" class="form-control" required>
                            <option value="">Select topic category</option>
                            @foreach($allMainTopics as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Main Topic Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. SM.9 Recruitment Process" required>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" value="1" id="subtopicActive" @checked(true)>
                            <label class="form-check-label" for="subtopicActive">Active main topic</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-info w-100">Add Main Topic</button>
                </div>
            </form>
        </div>

        <div class="card topic-layer-panel layer-detail">
            <div class="card-header"><h5 class="mb-0">Add Subtopic</h5></div>
            <form action="{{ route('topic-details.store') }}" method="POST">
                @csrf
                <div class="card-body row g-3">
                    <div class="col-12"><label class="form-label">Category Name</label><select id="detailCategorySelect" class="form-control" required><option value="">Select category name</option>@foreach($allMainTopics as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
                    <div class="col-12"><label class="form-label">Main Topic</label><select id="detailMainTopicSelect" name="main_topic_id" class="form-control" required disabled><option value="">Select category name first</option>@foreach($allMainTopicRecords as $mainTopic)<option value="{{ $mainTopic->id }}" data-category-id="{{ $mainTopic->topic_category_id }}" @selected((int) old('main_topic_id') === $mainTopic->id)>{{ $mainTopic->name }}</option>@endforeach</select></div>
                    <div class="col-12"><label class="form-label">Subtopic Name</label><input type="text" name="name" class="form-control" placeholder="e.g. Salary Determination" required></div>
                    <div class="col-12"><div class="form-check"><input type="checkbox" class="form-check-input" name="is_active" value="1" id="detailActive" checked><label class="form-check-label" for="detailActive">Active subtopic</label></div></div>
                </div>
                <div class="card-footer"><button class="btn btn-success w-100">Add Subtopic</button></div>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card topic-layer-panel layer-category active">
            <div class="card-header">
                <h5 class="mb-0">Manage Topic Categories</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table flow-table category-table mb-0">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td><div class="category-name"><span class="category-icon"><span class="material-icons">account_tree</span></span><span><strong>{{ $category->name }}</strong><small>{{ $category->documents_count ? $category->documents_count.' related document'.($category->documents_count === 1 ? '' : 's') : 'Not used by any document' }}</small></span></div></td>
                                <td><code>{{ $category->slug }}</code></td>
                                <td><span class="status-pill {{ $category->is_active ? 'status-published' : 'status-inactive' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td><div class="category-actions">
                                    @if($category->documents_count)<span class="usage-badge"><span class="material-icons" style="font-size:14px">link</span>Used by {{ $category->documents_count }}</span>@endif
                                    <button type="button" class="btn btn-sm btn-outline-primary icon-action category-edit-toggle" data-edit-category="{{ $category->id }}" aria-expanded="false" title="Edit {{ $category->name }}"><span class="material-icons">edit</span></button>
                                    <form action="{{ route('topic-categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete this topic category? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger icon-action {{ $category->documents_count ? 'delete-protected' : '' }}" @disabled($category->documents_count) title="{{ $category->documents_count ? 'View and reassign related documents before deleting' : 'Delete topic category' }}"><span class="material-icons">delete</span></button>
                                    </form>
                                </div></td>
                            </tr>
                            <tr class="category-edit-row" id="category-editor-{{ $category->id }}">
                                <td colspan="4">
                                    <form action="{{ route('topic-categories.update', $category) }}" method="POST" class="category-editor">
                                        @csrf
                                        @method('PUT')
                                        <div><label class="form-label" for="category-name-{{ $category->id }}">Category name</label><input id="category-name-{{ $category->id }}" type="text" name="name" class="form-control" value="{{ $category->name }}" required></div>
                                        <div class="form-check"><input type="checkbox" class="form-check-input" name="is_active" value="1" id="cat-active-{{ $category->id }}" @checked($category->is_active)><label class="form-check-label" for="cat-active-{{ $category->id }}">Active category</label></div>
                                        <div class="category-editor-actions"><button type="button" class="btn btn-sm btn-light category-edit-cancel" data-edit-category="{{ $category->id }}">Cancel</button><button class="btn btn-sm btn-primary"><span class="material-icons" style="font-size:17px;vertical-align:middle">save</span> Save changes</button></div>
                                    </form>
                                </td>
                            </tr>
                            @if($category->documents_count)
                                <tr class="dependency-row"><td colspan="4"><details class="dependency-details"><summary><span class="material-icons" style="font-size:16px">visibility</span>Show {{ $category->documents_count }} related document{{ $category->documents_count === 1 ? '' : 's' }} before deleting</summary><div class="dependency-list">@foreach($category->documents as $relatedDocument)<div class="dependency-document"><div><strong>{{ $relatedDocument->title }}</strong><small>{{ $relatedDocument->reference_number ?: 'No official reference' }}</small></div><span class="usage-meta">v{{ $relatedDocument->version_number }}</span><span class="status-pill status-{{ $relatedDocument->status }}">{{ $relatedDocument->statusLabel() }}</span><a class="btn btn-sm btn-info" href="{{ route('policy-documents.show', $relatedDocument) }}">View</a></div>@endforeach</div></details></td></tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">No topic categories found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end">
                {{ $categories->links() }}
            </div>
        </div>

        <div class="card topic-layer-panel layer-main">
            <div class="card-header">
                <h5 class="mb-0">Manage Main Topics</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table flow-table category-table main-topic-table mb-0">
                        <thead>
                        <tr>
                            <th>Main Topic</th>
                            <th>Topic Category</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($subtopics as $subtopic)
                            <tr>
                                <td><div class="category-name"><span class="category-icon"><span class="material-icons">segment</span></span><span><strong>{{ $subtopic->name }}</strong><small>Main topic</small></span></div></td>
                                <td><span class="status-pill status-inactive">{{ $subtopic->mainTopic?->name ?? '-' }}</span></td>
                                <td><code>{{ $subtopic->slug }}</code></td>
                                <td><span class="status-pill {{ $subtopic->is_active ? 'status-published' : 'status-inactive' }}">{{ $subtopic->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td><div class="category-actions"><button type="button" class="btn btn-sm btn-outline-primary icon-action main-topic-edit-toggle" data-edit-main-topic="{{ $subtopic->id }}" aria-expanded="false" title="Edit main topic"><span class="material-icons">edit</span></button><form action="{{ route('topic-subtopics.destroy', $subtopic) }}" method="POST" onsubmit="return confirm('Delete this main topic?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger icon-action" title="Delete main topic"><span class="material-icons">delete</span></button></form></div></td>
                            </tr>
                            <tr class="category-edit-row main-topic-edit-row" id="main-topic-editor-{{ $subtopic->id }}"><td colspan="5"><form action="{{ route('topic-subtopics.update', $subtopic) }}" method="POST" class="category-editor">@csrf @method('PUT')<div><label class="form-label">Main Topic Name</label><input type="text" name="name" class="form-control" value="{{ $subtopic->name }}" required></div><div><label class="form-label">Category Name</label><select name="topic_category_id" class="form-control" required>@foreach($allMainTopics as $category)<option value="{{ $category->id }}" @selected($subtopic->topic_category_id === $category->id)>{{ $category->name }}</option>@endforeach</select></div><div class="form-check"><input type="checkbox" class="form-check-input" name="is_active" value="1" id="subtopic-active-{{ $subtopic->id }}" @checked($subtopic->is_active)><label class="form-check-label" for="subtopic-active-{{ $subtopic->id }}">Active</label></div><div class="category-editor-actions"><button type="button" class="btn btn-sm btn-light main-topic-edit-cancel" data-edit-main-topic="{{ $subtopic->id }}">Cancel</button><button class="btn btn-sm btn-primary"><span class="material-icons" style="font-size:17px;vertical-align:middle">save</span> Save changes</button></div></form></td></tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No main topics found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end">
                {{ $subtopics->links() }}
            </div>
        </div>

        <div class="card topic-layer-panel layer-detail">
            <div class="card-header"><h5 class="mb-0">Manage Subtopics</h5></div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table flow-table mb-0">
                <thead><tr><th>Category Name</th><th>Main Topic</th><th>Subtopic Name</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($topicDetails as $detail)
                    <tr>
                        <td>{{ $detail->mainTopic?->mainTopic?->name ?? '-' }}</td>
                        <td>{{ $detail->mainTopic?->name ?? '-' }}</td>
                        <td><strong>{{ $detail->name }}</strong><br><code>{{ $detail->slug }}</code></td>
                        <td><span class="status-pill {{ $detail->is_active ? 'status-published' : 'status-inactive' }}">{{ $detail->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <details><summary class="btn btn-sm btn-outline-primary">Edit</summary><form action="{{ route('topic-details.update', $detail) }}" method="POST" class="mt-2 d-grid gap-2">@csrf @method('PUT')<input name="name" class="form-control form-control-sm" value="{{ $detail->name }}" required><select name="main_topic_id" class="form-control form-control-sm" required>@foreach($allMainTopicRecords as $mainTopic)<option value="{{ $mainTopic->id }}" @selected($detail->main_topic_id === $mainTopic->id)>{{ $mainTopic->name }}</option>@endforeach</select><div class="form-check"><input type="checkbox" class="form-check-input" name="is_active" value="1" id="detail-active-{{ $detail->id }}" @checked($detail->is_active)><label class="form-check-label" for="detail-active-{{ $detail->id }}">Active</label></div><button class="btn btn-sm btn-primary">Save</button></form></details>
                            <form action="{{ route('topic-details.destroy', $detail) }}" method="POST" class="mt-2" onsubmit="return confirm('Delete this subtopic?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4">No subtopics found.</td></tr>
                @endforelse
                </tbody>
            </table></div></div>
            <div class="card-footer d-flex justify-content-end">{{ $topicDetails->links() }}</div>
        </div>
    </div>
</div>
</div>
<script>
    (() => {
        const hierarchySteps = document.querySelectorAll('[data-topic-layer]');
        const showLayer = layer => {
            hierarchySteps.forEach(step => {
                const selected = step.dataset.topicLayer === layer;
                step.classList.toggle('active', selected);
                step.setAttribute('aria-selected', selected ? 'true' : 'false');
            });
            document.querySelectorAll('.topic-layer-panel').forEach(panel => panel.classList.toggle('active', panel.classList.contains(`layer-${layer}`)));
            history.replaceState(null, '', `#${layer}`);
        };
        hierarchySteps.forEach(step => step.addEventListener('click', () => showLayer(step.dataset.topicLayer)));
        const initialLayer = ['category', 'main', 'detail'].includes(location.hash.slice(1)) ? location.hash.slice(1) : 'category';
        showLayer(initialLayer);

        const detailCategorySelect = document.getElementById('detailCategorySelect');
        const detailMainTopicSelect = document.getElementById('detailMainTopicSelect');
        const filterDetailMainTopics = (preserveSelection = false) => {
            if (!detailCategorySelect || !detailMainTopicSelect) return;
            const categoryId = detailCategorySelect.value;
            const previousValue = detailMainTopicSelect.value;
            let available = 0;
            Array.from(detailMainTopicSelect.options).forEach((option, index) => {
                if (index === 0) return;
                option.hidden = option.dataset.categoryId !== categoryId;
                if (!option.hidden) available++;
            });
            const previousOption = Array.from(detailMainTopicSelect.options).find(option => option.value === previousValue);
            if (!preserveSelection || !previousOption || previousOption.hidden) detailMainTopicSelect.value = '';
            detailMainTopicSelect.disabled = categoryId === '' || available === 0;
            detailMainTopicSelect.options[0].textContent = categoryId === '' ? 'Select category name first' : (available ? 'Select main topic' : 'No main topics available in this category');
            if (preserveSelection && previousValue && previousOption && !previousOption.hidden) detailMainTopicSelect.value = previousValue;
        };
        detailCategorySelect?.addEventListener('change', () => filterDetailMainTopics(false));
        const selectedDetailMainTopic = detailMainTopicSelect?.selectedOptions[0];
        if (selectedDetailMainTopic?.value) detailCategorySelect.value = selectedDetailMainTopic.dataset.categoryId;
        filterDetailMainTopics(true);

        const closeCategoryEditors = exceptId => {
            document.querySelectorAll('.category-edit-row.open').forEach(row => {
                if (row.id !== `category-editor-${exceptId}`) row.classList.remove('open');
            });
            document.querySelectorAll('.category-edit-toggle[aria-expanded="true"]').forEach(button => {
                if (button.dataset.editCategory !== String(exceptId)) button.setAttribute('aria-expanded', 'false');
            });
        };

        document.querySelectorAll('.category-edit-toggle').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.editCategory;
                const editor = document.getElementById(`category-editor-${id}`);
                const opening = !editor.classList.contains('open');
                closeCategoryEditors(opening ? id : null);
                editor.classList.toggle('open', opening);
                button.setAttribute('aria-expanded', opening ? 'true' : 'false');
                if (opening) {
                    editor.querySelector('input[name="name"]')?.focus();
                    editor.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
        });

        document.querySelectorAll('.category-edit-cancel').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.editCategory;
                document.getElementById(`category-editor-${id}`)?.classList.remove('open');
                document.querySelector(`.category-edit-toggle[data-edit-category="${id}"]`)?.setAttribute('aria-expanded', 'false');
            });
        });

        document.querySelectorAll('.main-topic-edit-toggle').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.editMainTopic;
                const editor = document.getElementById(`main-topic-editor-${id}`);
                const opening = !editor.classList.contains('open');
                document.querySelectorAll('.main-topic-edit-row.open').forEach(row => row.classList.remove('open'));
                document.querySelectorAll('.main-topic-edit-toggle[aria-expanded="true"]').forEach(item => item.setAttribute('aria-expanded', 'false'));
                editor.classList.toggle('open', opening);
                button.setAttribute('aria-expanded', opening ? 'true' : 'false');
                if (opening) {
                    editor.querySelector('input[name="name"]')?.focus();
                    editor.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
        });
        document.querySelectorAll('.main-topic-edit-cancel').forEach(button => button.addEventListener('click', () => {
            const id = button.dataset.editMainTopic;
            document.getElementById(`main-topic-editor-${id}`)?.classList.remove('open');
            document.querySelector(`.main-topic-edit-toggle[data-edit-main-topic="${id}"]`)?.setAttribute('aria-expanded', 'false');
        }));
    })();
</script>
@endsection

@extends('layouts.app')

@section('content')
<style>
    .policy-index{--pi-teal:#006d70;--pi-border:#dfe7ea;--pi-text:#172b4d;--pi-muted:#667085;padding-top:2px}
    .policy-unit-view{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:16px;padding:13px 16px;border:1px solid #dce7e8;border-radius:11px;background:linear-gradient(90deg,#f4fbfa,#fff);box-shadow:0 4px 14px rgba(22,50,70,.05)}
    .policy-unit-heading{display:flex;align-items:center;gap:10px;color:#075f64;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.04em}.policy-unit-heading .material-icons{font-size:22px}
    .policy-unit-options{display:flex;align-items:center;gap:8px}.policy-unit-option{display:flex;align-items:center;gap:9px;min-height:40px;padding:8px 14px;border:1px solid #cadcde;border-radius:8px;background:#fff;color:#344054;font-size:11px;font-weight:750}.policy-unit-option:hover{color:#006d70;border-color:#7dbfc0;text-decoration:none}.policy-unit-option.active{border-color:#007b7d;background:#007b7d;color:#fff;box-shadow:0 4px 10px rgba(0,109,112,.18)}.policy-unit-option b{display:grid;place-items:center;min-width:23px;height:23px;padding:0 6px;border-radius:12px;background:#e8f5f4;color:#087179;font-size:10px}.policy-unit-option.active b{background:rgba(255,255,255,.2);color:#fff}
    .policy-kpis{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:16px;margin-bottom:16px}
    .policy-kpi{display:flex;align-items:center;gap:16px;min-height:92px;padding:16px 18px;border:1px solid #e3e8ec;border-radius:11px;background:#fff;box-shadow:0 4px 14px rgba(22,50,70,.06)}
    .policy-kpi-icon{display:grid;place-items:center;flex:0 0 56px;height:56px;border-radius:12px;background:#e8f5f4;color:#007477}.policy-kpi-icon .material-icons{font-size:31px}
    .policy-kpi:nth-child(2) .policy-kpi-icon{background:#e9f7ef;color:#008f48}.policy-kpi:nth-child(3) .policy-kpi-icon{background:#fff3df;color:#f2a000}.policy-kpi:nth-child(4) .policy-kpi-icon{background:#f2eaff;color:#7b35e4}.policy-kpi:nth-child(5) .policy-kpi-icon{background:#ffe9eb;color:#f13345}
    .policy-kpi strong,.policy-kpi small{display:block}.policy-kpi strong{font-size:28px;line-height:1;color:var(--pi-text)}.policy-kpi small{margin-top:9px;color:#344054;font-size:13px}
    .policy-filter{display:grid;grid-template-columns:minmax(270px,1.8fr) repeat(4,minmax(145px,1fr)) auto auto;gap:16px;align-items:end;margin-bottom:14px;padding:16px 18px 18px;border:1px solid #e0e7eb;border-radius:11px;background:#fff;box-shadow:0 4px 14px rgba(22,50,70,.05)}
    .policy-field label{display:block;margin:0 0 6px;color:#152746;font-size:11px;font-weight:750}.policy-field .form-control{height:44px;border:1px solid #d6e0e5;border-radius:7px;background-color:#fff;color:#344054;font-size:12px}
    .policy-search{position:relative}.policy-search .material-icons{position:absolute;left:13px;top:12px;color:#344767;font-size:21px}.policy-search .form-control{padding-left:43px}
    .policy-filter .btn{height:44px;min-width:76px;border-radius:7px;font-size:12px;font-weight:750}.policy-filter .btn-outline-primary{border-color:#00828a;color:#006b73;background:#fff}
    .policy-repository{overflow:hidden;border:1px solid #dce5e8;border-radius:11px;background:#fff;box-shadow:0 4px 16px rgba(22,50,70,.06)}
    .policy-repository-head{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:10px 18px;border-bottom:1px solid var(--pi-border)}
    .policy-repository-title{display:flex;align-items:center;gap:13px;color:#006b73;font-size:17px;font-weight:800}.policy-count{padding:5px 12px;border-radius:18px;background:#e8f5f4;color:#086b70;font-size:10px}
    .policy-view-tools{display:flex;align-items:center;gap:9px;color:#667085;font-size:11px}.policy-view-tools select{width:168px;height:38px;border:1px solid #d7e0e5;border-radius:7px;padding:0 12px;color:#344054;background:#fff;font-size:11px}.repository-toggle{display:inline-flex;align-items:center;gap:6px;height:38px;padding:0 11px;border:1px solid #b9d4d5;border-radius:7px;background:#fff;color:#006b73;font:700 10px/1 Poppins,Arial,sans-serif;white-space:nowrap}.repository-toggle:hover{border-color:#007477;background:#edf8f7}.repository-toggle .material-icons{font-size:17px}.view-toggle{display:grid;place-items:center;width:38px;height:38px;border:1px solid #dce4e8;border-radius:7px;background:#fff;color:#344054}.view-toggle.active{background:#007477;color:#fff;border-color:#007477}.view-toggle .material-icons{font-size:19px}
    .policy-groups{background:#fbfcfd}.policy-category{border-bottom:1px solid #dfe7ea}.policy-category:last-child{border-bottom:0}.policy-category>summary,.policy-topic>summary{list-style:none;cursor:pointer}.policy-category>summary::-webkit-details-marker,.policy-topic>summary::-webkit-details-marker{display:none}
    .policy-category-head{display:flex;align-items:center;justify-content:space-between;min-height:42px;padding:8px 18px;background:linear-gradient(90deg,#edf7f7,#fbfdfd);color:#00676c;font-size:12px;font-weight:800}.policy-category-name,.policy-topic-name{display:flex;align-items:center;gap:12px}.policy-category-name .material-icons{font-size:22px}.group-actions{display:flex;align-items:center;gap:20px}.group-count{padding:5px 12px;border-radius:15px;background:#e5f3f3;color:#086b70;font-size:10px}.group-actions>.material-icons{font-size:18px;transition:.2s}.policy-category[open]>.policy-category-head .group-actions>.material-icons,.policy-topic[open]>.policy-topic-head .group-actions>.material-icons{transform:rotate(180deg)}
    .policy-unit-badge{padding:4px 8px;border-radius:5px;background:#007b7d;color:#fff;font-size:9px;letter-spacing:.04em}
    .policy-topic{margin:0 12px 10px;border:1px solid #e1e8eb;border-radius:8px;background:#fff;overflow:hidden}.policy-topic-head{display:flex;align-items:center;justify-content:space-between;min-height:38px;padding:7px 14px;background:#f7fafb;color:#08636a;font-size:11px;font-weight:800}
    .policy-subtopic-head{display:flex;align-items:center;justify-content:space-between;padding:7px 14px;border-top:1px solid #e3eaed;border-bottom:1px solid #e3eaed;background:#f8fafc;color:#344054;font-size:10px;font-weight:750}.policy-subtopic-head span:first-child:before{content:'';display:inline-block;width:6px;height:6px;margin-right:8px;border-radius:50%;background:#159cf5}
    .policy-table-head,.policy-document-row{display:grid;grid-template-columns:110px 155px minmax(250px,1.7fr) 110px 155px 135px 130px 90px;align-items:center}.policy-table-head{min-height:31px;padding:0 14px;border-bottom:1px solid #e4eaed;color:#475467;font-size:9px;font-weight:800;text-transform:uppercase}.policy-document-row{min-height:63px;padding:8px 14px;border-bottom:1px solid #edf1f3;color:#344054;font-size:11px}.policy-document-row:last-child{border-bottom:0}.policy-document-row:hover{background:#f8fcfc}
    .type-pill,.status-pill{justify-self:start;padding:5px 10px;border-radius:6px;background:#e7f3f2;color:#087179;font-size:9px;font-weight:800;text-transform:uppercase}.type-circular{background:#eaf2ff;color:#175fc4}.type-guideline{background:#fff4e4;color:#dc6803}.status-pill{border-radius:7px;background:#e7f6ec;color:#067435}.status-superseded{background:#f0ebff;color:#6938ca}
    .doc-title{min-width:0;color:#172b4d;font-weight:700;line-height:1.35}.doc-title a{color:inherit}.doc-title small{display:block;margin-top:2px;color:#007477;font-size:9px;font-weight:750}.doc-date{display:flex;align-items:center;gap:7px;white-space:nowrap}.doc-date .material-icons{font-size:15px;color:#344767}.doc-actions{display:flex;justify-content:flex-end;gap:8px}.doc-action{display:grid;place-items:center;width:36px;height:36px;border:1px solid #dce5e9;border-radius:7px;background:#fff;color:#233b61}.doc-action .material-icons{font-size:18px}.doc-action:hover{background:#eef8f8;color:#007477}
    .policy-footer{display:flex;justify-content:space-between;align-items:center;padding:10px 18px;color:#475467;font-size:10px}.policy-footer .pagination{margin:0}
    .empty-policy{padding:50px 20px;text-align:center;color:#667085}.empty-policy .material-icons{font-size:44px;color:#98a2b3}
    @media(max-width:1300px){.policy-kpis{grid-template-columns:repeat(3,1fr)}.policy-filter{grid-template-columns:repeat(3,1fr)}.policy-table-scroll{overflow-x:auto}.policy-table-head,.policy-document-row{min-width:1100px}}
    @media(max-width:767px){.policy-unit-view{align-items:flex-start;flex-direction:column}.policy-unit-options{width:100%;overflow-x:auto}.policy-unit-option{white-space:nowrap}.policy-kpis,.policy-filter{grid-template-columns:1fr}.policy-repository-head{align-items:flex-start;flex-direction:column}.policy-view-tools{width:100%;flex-wrap:wrap}.policy-view-tools select{flex:1}}
</style>

<div class="policy-index">
    @php
        $unitQuery = fn (?string $unit) => route('policy-documents.index', array_filter(array_merge(request()->except('unit', 'page'), ['unit' => $unit]), fn ($value) => $value !== null && $value !== ''));
    @endphp
    <nav class="policy-unit-view" aria-label="View documents by KCD or organizational unit">
        <div class="policy-unit-heading"><span class="material-icons">account_balance</span><span>View documents by KCD / organizational unit</span></div>
        <div class="policy-unit-options">
            <a class="policy-unit-option {{ $selectedUnit === null ? 'active' : '' }}" href="{{ $unitQuery(null) }}">All accessible <b>{{ $unitStats['all'] }}</b></a>
            <a class="policy-unit-option {{ $selectedUnit === 'msd' ? 'active' : '' }}" href="{{ $unitQuery('msd') }}">MSD <b>{{ $unitStats['msd'] }}</b></a>
            <a class="policy-unit-option {{ $selectedUnit === 'kcdiom' ? 'active' : '' }}" href="{{ $unitQuery('kcdiom') }}">Other KCDIOM units <b>{{ $unitStats['kcdiom'] }}</b></a>
        </div>
    </nav>
    <section class="policy-kpis" aria-label="Document statistics">
        <div class="policy-kpi"><span class="policy-kpi-icon"><span class="material-icons">description</span></span><div><strong>{{ $repositoryStats['total'] }}</strong><small>Total Documents</small></div></div>
        <div class="policy-kpi"><span class="policy-kpi-icon"><span class="material-icons">check_circle</span></span><div><strong>{{ $repositoryStats['published'] }}</strong><small>Active Documents</small></div></div>
        <div class="policy-kpi"><span class="policy-kpi-icon"><span class="material-icons">edit</span></span><div><strong>{{ $repositoryStats['draft'] }}</strong><small>Drafts</small></div></div>
        <div class="policy-kpi"><span class="policy-kpi-icon"><span class="material-icons">history_toggle_off</span></span><div><strong>{{ $repositoryStats['superseded'] }}</strong><small>Superseded</small></div></div>
        <div class="policy-kpi"><span class="policy-kpi-icon"><span class="material-icons">event</span></span><div><strong>{{ $repositoryStats['expiring'] }}</strong><small>Expiring in 30 days</small></div></div>
    </section>

    <form method="GET" class="policy-filter">
        @if(request('unit'))<input type="hidden" name="unit" value="{{ request('unit') }}">@endif
        <div class="policy-field policy-search"><label>&nbsp;</label><span class="material-icons">search</span><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Search policy, circular or reference number..."></div>
        <div class="policy-field"><label>Document Type</label><select class="form-control" name="document_type"><option value="">All Types</option>@foreach($documentTypes as $type=>$label)<option value="{{ $type }}" @selected(request('document_type')===$type)>{{ $label }}</option>@endforeach</select></div>
        <div class="policy-field"><label>Category of Topic</label><select class="form-control" name="topic_category"><option value="">All Categories</option>@foreach($topicCategories as $slug=>$label)<option value="{{ $slug }}" @selected(request('topic_category')===$slug)>{{ $label }}</option>@endforeach</select></div>
        <div class="policy-field"><label>Main Topic</label><select class="form-control" name="subtopic_id"><option value="">All Topics</option>@foreach($subtopics as $id=>$label)<option value="{{ $id }}" @selected((string)request('subtopic_id')===(string)$id)>{{ $label }}</option>@endforeach</select></div>
        <div class="policy-field"><label>Status</label><select class="form-control" name="status"><option value="">All Statuses</option>@foreach($documentStatuses as $status=>$label)@if($canManageDocuments || $status !== 'draft')<option value="{{ $status }}" @selected(request('status')===$status)>{{ $label }}</option>@endif @endforeach</select></div>
        <button class="btn btn-primary">Apply</button>
        <a class="btn btn-outline-primary" href="{{ route('policy-documents.index', request('unit') ? ['unit'=>request('unit')] : []) }}">Clear</a>
    </form>

    <section class="policy-repository">
        <header class="policy-repository-head">
            <div class="policy-repository-title">DOCUMENT REPOSITORY <span class="policy-count">{{ $documents->total() }} {{ Str::plural('document',$documents->total()) }}</span></div>
            <form method="GET" class="policy-view-tools">
                @foreach(request()->except('sort','page') as $key=>$value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach
                <button type="button" class="repository-toggle" data-expand-all><span class="material-icons">unfold_more</span>Expand all</button>
                <button type="button" class="repository-toggle" data-collapse-all><span class="material-icons">unfold_less</span>Collapse all</button>
                <span>Sort by:</span><select name="sort" onchange="this.form.submit()"><option value="" @selected(!request('sort'))>Latest Updated</option><option value="title" @selected(request('sort')==='title')>Title A–Z</option><option value="oldest" @selected(request('sort')==='oldest')>Oldest Updated</option><option value="effective" @selected(request('sort')==='effective')>Effective Date</option></select>
                <button type="button" class="view-toggle active" aria-label="List view"><span class="material-icons">view_list</span></button><button type="button" class="view-toggle" aria-label="Grid view"><span class="material-icons">grid_view</span></button>
            </form>
        </header>
        @php
            $categorizedDocuments = $documents->getCollection()->groupBy(function ($document) use ($topicCategories) {
                $category = $document->topic_category ? ($topicCategories[$document->topic_category] ?? ucfirst($document->topic_category)) : 'Uncategorized';
                return strtoupper($document->owner_unit ?: 'unassigned').'|'.$category;
            })->sortBy(function ($documents, $key) {
                [$unit, $category] = explode('|', $key, 2);
                $unitOrder = ['MSD' => 0, 'KCDIOM' => 1, 'UNASSIGNED' => 2];
                return sprintf('%02d-%s', $unitOrder[$unit] ?? 9, $category);
            });
        @endphp
        <div class="policy-groups">
        @forelse($categorizedDocuments as $unitCategory=>$categoryDocuments)
            @php([$ownerUnit, $categoryName] = explode('|', $unitCategory, 2))
            <details class="policy-category" open>
                <summary class="policy-category-head"><span class="policy-category-name"><span class="material-icons">folder</span><span class="policy-unit-badge">{{ $ownerUnit }}</span>CATEGORY OF TOPIC: {{ $categoryName }}</span><span class="group-actions"><span class="group-count">{{ $categoryDocuments->count() }} {{ Str::plural('document',$categoryDocuments->count()) }}</span><span class="material-icons">expand_more</span></span></summary>
                @foreach($categoryDocuments->groupBy(fn($document) => $document->subtopic?->name ?: 'Main topic not assigned') as $mainTopicName=>$mainTopicDocuments)
                    <details class="policy-topic" open>
                        <summary class="policy-topic-head"><span class="policy-topic-name">MAIN TOPIC: {{ $mainTopicName }}</span><span class="group-actions"><span class="group-count">{{ $mainTopicDocuments->count() }} {{ Str::plural('document',$mainTopicDocuments->count()) }}</span><span class="material-icons">expand_more</span></span></summary>
                        @foreach($mainTopicDocuments->groupBy(fn($document) => $document->topicDetail?->name ?: 'Subtopic not assigned') as $subtopicName=>$subtopicDocuments)
                            <div class="policy-subtopic-head"><span>SUBTOPIC: {{ $subtopicName }}</span><span>{{ $subtopicDocuments->count() }} {{ Str::plural('record',$subtopicDocuments->count()) }}</span></div>
                            <div class="policy-table-scroll"><div class="policy-table-head"><span>Type</span><span>Reference No.</span><span>Title</span><span>Version</span><span>Effective Date</span><span>Updated</span><span>Status</span><span>Actions</span></div>
                            @foreach($subtopicDocuments as $doc)
                                <article class="policy-document-row">
                                    <span class="type-pill type-{{ $doc->document_type }}">{{ $documentTypes[$doc->document_type] ?? ucfirst($doc->document_type) }}</span>
                                    <span>{{ $doc->reference_number ?: '—' }}</span>
                                    <span class="doc-title"><a href="{{ route('policy-documents.show',$doc) }}">{{ trim($doc->title," -\t\n\r\0\x0B") ?: 'Untitled document' }}</a><small>Show more</small></span>
                                    <span>{{ $doc->version_number }}</span>
                                    <span class="doc-date"><span class="material-icons">event</span>{{ $doc->effective_date?->format('d M Y') ?? '—' }}</span>
                                    <span>{{ $doc->updated_at?->format('d M Y') ?? '—' }}</span>
                                    <span class="status-pill status-{{ $doc->status }}">{{ $documentStatuses[$doc->status] ?? ucfirst($doc->status) }}</span>
                                    <span class="doc-actions"><a class="doc-action" href="{{ route('policy-documents.show',$doc) }}" title="View"><span class="material-icons">visibility</span></a>@if($canManageDocuments)<a class="doc-action" href="{{ route('policy-documents.edit',$doc) }}" title="Edit"><span class="material-icons">more_vert</span></a>@endif</span>
                                </article>
                            @endforeach</div>
                        @endforeach
                    </details>
                @endforeach
            </details>
        @empty
            <div class="empty-policy"><span class="material-icons">folder_off</span><h5>No documents found</h5><p>Try changing the filters.</p></div>
        @endforelse
        </div>
        <footer class="policy-footer"><span>Showing {{ $documents->firstItem() ?? 0 }} to {{ $documents->lastItem() ?? 0 }} of {{ $documents->total() }} records</span>@if($documents->hasPages()){{ $documents->links() }}@endif</footer>
    </section>
</div>
<script>
    (() => {
        const repository = document.querySelector('.policy-repository');
        const groups = () => repository?.querySelectorAll('details.policy-category, details.policy-topic') ?? [];

        repository?.querySelector('[data-expand-all]')?.addEventListener('click', () => {
            groups().forEach((group) => { group.open = true; });
        });

        repository?.querySelector('[data-collapse-all]')?.addEventListener('click', () => {
            groups().forEach((group) => { group.open = false; });
        });
    })();
</script>
@endsection

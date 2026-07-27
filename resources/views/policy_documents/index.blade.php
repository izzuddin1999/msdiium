@extends('layouts.app')

@section('content')
<style>
    .repository-hero{display:flex;justify-content:space-between;align-items:end;gap:20px;margin-bottom:22px}.repository-hero h2{font-weight:800;margin:4px 0}.repository-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}.repository-stat{display:flex;align-items:center;gap:13px;padding:17px 18px;background:#fff;border:1px solid #e0ebe8;border-radius:12px;box-shadow:0 4px 16px rgba(25,70,64,.05)}.repository-stat .material-icons{display:grid;place-items:center;width:42px;height:42px;border-radius:10px;background:#e9f7f4;color:#008f85}.repository-stat strong{display:block;font-size:23px;line-height:1;color:#133d37}.repository-stat small{color:#72837f}.repository-filter{padding:18px;background:#fff;border:1px solid #dfeae7;border-radius:12px;box-shadow:0 4px 16px rgba(25,70,64,.04);margin-bottom:20px}.repository-filter .form-control{height:46px;border-color:#d7e3e0;border-radius:8px}.repository-table-card{border:0;border-radius:12px;overflow:hidden;box-shadow:0 6px 24px rgba(25,70,64,.07)}.repository-table thead th{padding:14px 16px;background:#edf6f4;color:#496c65;font-size:11px;text-transform:uppercase;letter-spacing:.06em;border:0;white-space:nowrap}.repository-table tbody td{padding:17px 16px;vertical-align:middle;border-color:#e9efed}.repository-table tbody tr{transition:.18s}.repository-table tbody tr:hover{background:#f8fcfb}.document-name{display:flex;gap:11px;align-items:flex-start;min-width:230px}.document-icon{display:grid;place-items:center;flex:0 0 38px;height:38px;border-radius:9px;background:#e8f6f3;color:#008e84}.document-name strong{display:block;color:#123a35}.meta-line{font-size:12px;color:#85938f}.soft-badge{display:inline-flex;align-items:center;padding:5px 9px;border-radius:20px;background:#edf5f3;color:#39665f;font-size:12px;font-weight:650}.template-badge{background:#eef0ff;color:#5058a7}.action-menu{display:flex;gap:6px}.action-menu .btn{display:inline-flex;align-items:center;justify-content:center;width:35px;height:35px;padding:0;border-radius:8px}.action-menu .material-icons{font-size:18px}.empty-repository{text-align:center;padding:65px 20px}.empty-repository .material-icons{font-size:48px;color:#91aaa5}.filter-count{font-size:12px;color:#6f8580}@media(max-width:991px){.repository-stats{grid-template-columns:repeat(2,1fr)}.repository-hero{align-items:flex-start;flex-direction:column}}@media(max-width:575px){.repository-stats{grid-template-columns:1fr}.repository-hero .btn{width:100%}}
    .repository-stats.repository-stats-3{grid-template-columns:repeat(3,1fr)}
    .version-picker{min-width:185px}.version-picker summary{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:8px 10px;border:1px solid #dce8e4;border-radius:9px;background:#f7fbfa;color:#173d37;font-weight:700;cursor:pointer;list-style:none}.version-picker summary::-webkit-details-marker{display:none}.version-picker summary .version-current{display:flex;align-items:center;gap:5px}.version-picker summary .material-icons{font-size:17px;color:#63827b}.version-picker[open] summary{border-color:#00a094;box-shadow:0 0 0 3px rgba(0,160,148,.09)}.version-family-menu{width:285px;max-width:100%;margin-top:7px;padding:7px;border:1px solid #d8e6e2;border-radius:10px;background:#fff;box-shadow:0 8px 20px rgba(17,58,51,.12)}.version-family-link{display:grid;grid-template-columns:42px minmax(0,1fr) auto;gap:8px;align-items:center;padding:9px;border-radius:7px;color:#254941}.version-family-link:hover{background:#edf8f5;color:#007d73}.version-family-link+.version-family-link{border-top:1px solid #edf2f0}.version-number{font-weight:800}.version-family-meta{min-width:0}.version-family-meta strong,.version-family-meta small{display:block}.version-family-meta small{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#7a8d88}.version-current-pill{padding:3px 6px;border-radius:10px;background:#daf3ec;color:#08786d;font-size:9px;font-weight:800;text-transform:uppercase}.version-effective{margin-top:5px;color:#71847f;font-size:11px}
    .repository-results{border:1px solid #dce8e4;border-radius:16px;overflow:hidden;background:#fff;box-shadow:0 12px 34px rgba(18,64,55,.08)}.results-heading{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:17px 20px;border-bottom:1px solid #e2ece9;background:linear-gradient(90deg,#fff,#f5fbf9)}.results-heading strong{display:flex;align-items:center;gap:9px;color:#143f38}.results-heading .material-icons{width:34px;height:34px;display:grid;place-items:center;border-radius:9px;background:#e5f6f2;color:#008f85;font-size:19px}.results-heading small{color:#748680}.repository-table tbody tr:hover{background:#f4faf8;box-shadow:inset 4px 0 #00a094}.repository-table tbody td{padding-top:19px;padding-bottom:19px}.document-name{align-items:center}.document-icon{width:42px;height:42px;flex-basis:42px;border-radius:11px;box-shadow:inset 0 0 0 1px rgba(0,143,133,.08)}.document-name strong{font-size:14px;line-height:1.35}.reference-line{display:flex;align-items:center;gap:5px;margin-top:4px}.reference-line .material-icons{font-size:14px;color:#91a29d}.classification-path{display:flex;flex-wrap:wrap;align-items:center;gap:5px;margin-top:7px;color:#728580;font-size:11px}.classification-path .material-icons{font-size:14px;color:#a0afab}.governance-stack strong,.version-stack strong{display:flex;align-items:center;gap:5px;color:#173d37}.governance-stack .material-icons,.version-stack .material-icons{font-size:16px;color:#6d8780}.action-menu .btn{width:auto;min-width:38px;padding:0 10px;gap:5px;font-size:12px;font-weight:700}.action-menu .action-label{display:inline}.repository-pagination{padding:16px 20px;border-top:1px solid #e2ece9;background:#fbfdfc}@media(max-width:1199px){.action-menu .action-label{display:none}.action-menu .btn{width:36px;padding:0}}@media(max-width:767px){.results-heading{align-items:flex-start;flex-direction:column}.repository-table{min-width:850px}.repository-stats.repository-stats-3{grid-template-columns:1fr}.repository-filter .d-flex{align-items:flex-start!important;flex-direction:column}.repository-filter .col-xl-auto{width:100%}.repository-filter .col-xl-auto .btn-primary{flex:1}.version-family-menu{position:relative;width:240px}}
</style>
<style>
    .classified-repository{padding:16px;background:linear-gradient(180deg,#f5faf8,#eef6f4)}.classification-category{overflow:hidden;margin-bottom:14px;border:1px solid #cfe3df;border-radius:16px;background:#fff;box-shadow:0 8px 24px rgba(18,64,55,.07)}.classification-category:last-child{margin-bottom:0}.classification-category>summary{list-style:none;cursor:pointer}.classification-category>summary::-webkit-details-marker,.classification-main-topic>summary::-webkit-details-marker{display:none}.classification-category-header{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px;background:linear-gradient(110deg,#087a71,#00a094);color:#fff}.category-heading-main{display:flex;align-items:center;gap:12px}.category-monogram{display:grid;place-items:center;width:42px;height:42px;border-radius:12px;background:rgba(255,255,255,.16);font-size:13px;font-weight:900;letter-spacing:.03em}.classification-label{display:block;margin-bottom:2px;font-size:9px;font-weight:800;letter-spacing:.13em;text-transform:uppercase;opacity:.78}.classification-category-header h4{margin:0;color:#fff;font-size:18px;font-weight:800}.classification-summary-actions{display:flex;align-items:center;gap:9px}.classification-count{flex:0 0 auto;padding:5px 10px;border-radius:20px;background:rgba(255,255,255,.17);font-size:11px;font-weight:700}.classification-toggle{font-size:21px;transition:.2s}.classification-category[open]>.classification-category-header .classification-toggle,.classification-main-topic[open]>.classification-main-heading .classification-toggle{transform:rotate(180deg)}.classification-main-topic{margin:12px;border:1px solid #deebe8;border-radius:12px;background:#fbfdfc}.classification-main-topic>summary{list-style:none;cursor:pointer}.classification-main-heading,.classification-subtopic-heading{display:flex;align-items:center;justify-content:space-between;gap:12px}.classification-main-heading{padding:11px 13px}.classification-heading-title{display:flex;align-items:center;gap:9px;color:#173e38;font-weight:800}.classification-heading-title .material-icons{display:grid;place-items:center;width:30px;height:30px;border-radius:8px;background:#def3ee;color:#008f85;font-size:17px}.main-summary-actions{display:flex;align-items:center;gap:8px}.main-summary-actions .classification-count{background:#e8f4f1;color:#496b65}.classification-subtopic{margin:0 11px 11px;border:1px solid #e2ece9;border-radius:10px;background:#fff}.classification-subtopic-heading{padding:9px 12px;border-bottom:1px solid #e7efed;background:#f1f8f6;color:#365d56;font-size:12px;font-weight:750}.subtopic-marker{display:inline-block;width:6px;height:6px;margin-right:7px;border-radius:50%;background:#00a094}.classified-document-row{display:grid;grid-template-columns:minmax(260px,1.8fr) minmax(125px,.62fr) minmax(150px,.75fr) auto auto auto;gap:14px;align-items:center;padding:13px 14px;transition:.18s}.classified-document-row+.classified-document-row{border-top:1px solid #edf2f0}.classified-document-row:hover{background:#f6fbfa;box-shadow:inset 4px 0 #00a094}.classified-document-main{min-width:0}.classified-document-main .document-icon{width:38px;height:38px;flex-basis:38px}.classified-meta{font-size:10px;color:#7d8e8a}.classified-meta strong{display:block;color:#244a43;font-size:12px}.classified-version .version-picker{min-width:150px}.classified-document-actions{justify-content:flex-end}.uncategorized-heading{background:linear-gradient(110deg,#647a75,#82938f)}@media(max-width:1199px){.classified-document-row{grid-template-columns:minmax(230px,1.6fr) minmax(120px,.7fr) minmax(150px,.85fr) auto auto}.classified-document-row>.classified-meta:nth-of-type(2){display:none}}@media(max-width:991px){.classified-document-row{grid-template-columns:1fr 1fr;gap:10px}.classified-document-main{grid-column:1/-1}.classified-document-row>.classified-meta:nth-of-type(2){display:block}.classified-version .version-picker{min-width:0}.classified-document-actions{justify-content:flex-start}}@media(max-width:575px){.classified-repository{padding:9px}.classification-category-header{padding:13px}.category-monogram{width:36px;height:36px}.classification-category-header h4{font-size:15px}.classification-count{display:none}.classified-document-row{grid-template-columns:1fr;gap:9px}.classified-document-main{grid-column:auto}.classification-subtopic-heading{align-items:flex-start;flex-direction:column}.classified-document-actions{justify-content:flex-start}}
</style>

<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Dashboard</a><span class="material-icons">chevron_right</span><span>Documents</span></div>
<div class="repository-hero">
    <div><span class="eyebrow">Governed document repository</span><h2>{{ request('unit') ? strtoupper(request('unit')).' Public Documents' : 'Policies, Guidelines & Circulars' }}</h2><p class="text-muted mb-0">{{ request('unit') ? 'Published records available to staff from the selected document owner.' : 'Find, review, and maintain documents created from approved templates.' }}</p></div>
    @if($canManageDocuments)<a href="{{ route('policy-documents.create') }}" class="btn btn-primary action-primary"><span class="material-icons">add</span> Register document</a>@endif
</div>

@if($showMsdDashboard)
    <section class="topic-governance-panel staff-msd-dashboard repository-msd-dashboard" aria-label="MSD public document dashboard">
        <div class="topic-panel-heading">
            <div>
                <span class="admin-kicker">MSD public dashboard</span>
                <h4>Browse MSD documents by topic</h4>
                <p>Select a topic category to filter the MSD policies, guidelines and circulars listed below.</p>
            </div>
            @if(request('topic_category'))
                <a href="{{ route('policy-documents.index', ['unit' => 'msd']) }}">Show all MSD topics <span class="material-icons">restart_alt</span></a>
            @else
                <span class="staff-directory-total">{{ $repositoryStats['total'] }} accessible {{ Str::plural('record', $repositoryStats['total']) }}</span>
            @endif
        </div>
        <div class="topic-dashboard-grid">
            @forelse($msdTopicDashboard as $category)
                @php
                    preg_match('/^([A-Z]{2,3})\b/', $category->name, $msdCategoryCode);
                    $msdCode = $msdCategoryCode[1] ?? strtoupper(substr($category->name, 0, 2));
                    $msdCategoryName = trim((string) preg_replace('/^[A-Z]{2,3}\s*[—–-]\s*/u', '', $category->name));
                    $isSelectedCategory = request('topic_category') === $category->slug;
                @endphp
                <a class="topic-dashboard-card staff-topic-card {{ $isSelectedCategory ? 'selected' : '' }}" href="{{ route('policy-documents.index', ['unit' => 'msd', 'topic_category' => $category->slug]) }}">
                    <div class="topic-card-top">
                        <span class="topic-code">{{ $msdCode }}</span>
                        <div><h5>{{ $msdCategoryName }}</h5><small>{{ $category->accessible_documents_count }} accessible {{ Str::plural('document', $category->accessible_documents_count) }}</small></div>
                        <span class="material-icons staff-topic-arrow">{{ $isSelectedCategory ? 'check_circle' : 'arrow_forward' }}</span>
                    </div>
                    <ul>
                        @forelse($category->subtopics as $mainTopic)
                            <li><span>{{ $mainTopic->name }}</span><small>{{ $mainTopic->details_count }} {{ Str::plural('subtopic', $mainTopic->details_count) }}</small></li>
                        @empty
                            <li class="topic-empty">No main topics configured</li>
                        @endforelse
                    </ul>
                </a>
            @empty
                <div class="empty-state topic-empty-state"><span class="material-icons">account_tree</span><p>No MSD topic categories are available.</p></div>
            @endforelse
        </div>
    </section>
@endif

<div class="repository-stats {{ $canManageDocuments ? '' : 'repository-stats-3' }}">
    <div class="repository-stat"><span class="material-icons">description</span><div><strong>{{ $repositoryStats['total'] }}</strong><small>Total documents</small></div></div>
    <div class="repository-stat"><span class="material-icons">verified</span><div><strong>{{ $repositoryStats['published'] }}</strong><small>Active</small></div></div>
    @if($canManageDocuments)<div class="repository-stat"><span class="material-icons">edit_note</span><div><strong>{{ $repositoryStats['draft'] }}</strong><small>Drafts in progress</small></div></div>@endif
    <div class="repository-stat"><span class="material-icons">event_busy</span><div><strong>{{ $repositoryStats['expiring'] }}</strong><small>Expiring in 30 days</small></div></div>
</div>

<form method="GET" class="repository-filter">
    @if(request('unit'))<input type="hidden" name="unit" value="{{ request('unit') }}">@endif
    <div class="d-flex justify-content-between align-items-center mb-3"><strong>Search and filters</strong><span class="filter-count">{{ $documents->total() }} matching record{{ $documents->total() === 1 ? '' : 's' }}</span></div>
    <div class="row g-2">
        <div class="col-xl-3 col-md-6"><div class="input-group"><span class="input-group-text bg-white border-end-0"><span class="material-icons" style="font-size:19px">search</span></span><input type="text" name="q" class="form-control border-start-0" placeholder="Title or reference" value="{{ request('q') }}"></div></div>
        <div class="col-xl col-md-3"><select name="document_type" class="form-control"><option value="">All types</option>@foreach($documentTypes as $type=>$label)<option value="{{ $type }}" @selected(request('document_type')===$type)>{{ $label }}</option>@endforeach</select></div>
        @if(config('features.form_builder'))<div class="col-xl col-md-3"><select name="form_template_id" class="form-control"><option value="">All templates</option>@foreach($formTemplates as $template)<option value="{{ $template->id }}" @selected((int)request('form_template_id')===$template->id)>{{ $template->name }}</option>@endforeach</select></div>@endif
        <div class="col-xl col-md-4"><select name="topic_category" class="form-control"><option value="">All topics</option>@foreach($topicCategories as $slug=>$label)<option value="{{ $slug }}" @selected(request('topic_category')===$slug)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-xl col-md-4"><select name="status" class="form-control"><option value="">All statuses</option>@foreach($documentStatuses as $status=>$label)@if($canManageDocuments || $status !== 'draft')<option value="{{ $status }}" @selected(request('status')===$status)>{{ $label }}</option>@endif @endforeach</select></div>
        <div class="col-xl-auto col-md-4 d-flex gap-2"><button class="btn btn-primary px-4">Apply</button>@if(request()->query())<a href="{{ route('policy-documents.index') }}" class="btn btn-secondary" title="Clear filters"><span class="material-icons">restart_alt</span></a>@endif</div>
    </div>
</form>

<div class="repository-results">
<div class="results-heading"><strong><span class="material-icons">folder_open</span>Document repository</strong><small>Showing {{ $documents->firstItem() ?? 0 }} to {{ $documents->lastItem() ?? 0 }} of {{ $documents->total() }} governed records</small></div>
@php
    $categorizedDocuments = $documents->getCollection()->groupBy(
        fn ($document) => $document->topic_category
            ? ($topicCategories[$document->topic_category] ?? ucfirst($document->topic_category))
            : 'Uncategorized'
    );
@endphp
<div class="classified-repository">
@forelse($categorizedDocuments as $categoryName => $categoryDocuments)
    <details class="classification-category" open>
        <summary class="classification-category-header {{ $categoryName === 'Uncategorized' ? 'uncategorized-heading' : '' }}">
            <div class="category-heading-main"><span class="category-monogram">{{ strtoupper(substr(trim(strtok($categoryName, '—')), 0, 3)) }}</span><div><span class="classification-label">Category of Topic</span><h4>{{ $categoryName }}</h4></div></div>
            <span class="classification-summary-actions"><span class="classification-count">{{ $categoryDocuments->count() }} document{{ $categoryDocuments->count() === 1 ? '' : 's' }}</span><span class="material-icons classification-toggle">expand_more</span></span>
        </summary>
        @foreach($categoryDocuments->groupBy(fn ($document) => $document->subtopic?->name ?: 'Main topic not assigned') as $mainTopicName => $mainTopicDocuments)
            <details class="classification-main-topic" open>
                <summary class="classification-main-heading"><div class="classification-heading-title"><span class="material-icons">format_list_numbered</span><span><span class="classification-label">Main Topic</span>{{ $mainTopicName }}</span></div><span class="main-summary-actions"><span class="classification-count">{{ $mainTopicDocuments->count() }}</span><span class="material-icons classification-toggle">expand_more</span></span></summary>
                @foreach($mainTopicDocuments->groupBy(fn ($document) => $document->topicDetail?->name ?: 'Subtopic not assigned') as $subtopicName => $subtopicDocuments)
                    <div class="classification-subtopic">
                        <div class="classification-subtopic-heading"><span><span class="classification-label">Subtopic</span><span class="subtopic-marker"></span>{{ $subtopicName }}</span><span>{{ $subtopicDocuments->count() }} record{{ $subtopicDocuments->count() === 1 ? '' : 's' }}</span></div>
                        @foreach($subtopicDocuments as $doc)
                            <article class="classified-document-row">
                                <div class="classified-document-main"><div class="document-name"><span class="document-icon material-icons">{{ $doc->document_type === 'circular' ? 'campaign' : 'description' }}</span><div><strong>{{ trim($doc->title, " -\t\n\r\0\x0B") !== '' ? $doc->title : 'Untitled document' }}</strong><span class="meta-line reference-line"><span class="material-icons">tag</span>{{ $doc->reference_number ?: 'Reference not assigned' }}</span></div></div></div>
                                <div class="classified-meta"><span>Document type</span><strong>{{ $documentTypes[$doc->document_type] ?? ucfirst($doc->document_type) }}</strong></div>
                                <div class="classified-meta"><span>Governance</span><strong>{{ strtoupper($doc->owner_unit) }} · {{ strtoupper($doc->access_scope) }}</strong></div>
                                <div class="classified-version"><details class="version-picker"><summary><span class="version-current"><span class="material-icons">history</span>Version {{ $doc->version_number }}</span><span class="material-icons">expand_more</span></summary><div class="version-family-menu">@foreach($doc->versionFamily as $version)<a href="{{ route('policy-documents.show', $version) }}" class="version-family-link"><span class="version-number">v{{ $version->version_number }}</span><span class="version-family-meta"><strong>{{ $documentStatuses[$version->status] ?? $version->statusLabel() }}</strong><small>{{ $version->published_at?->format('d M Y H:i') ?? 'Not published' }}</small></span>@if($version->id === $doc->id)<span class="version-current-pill">Current</span>@else<span class="material-icons" style="font-size:16px">visibility</span>@endif</a>@endforeach</div></details></div>
                                <div><span class="status-pill status-{{ $doc->status }}">{{ $documentStatuses[$doc->status] ?? ucfirst($doc->status) }}</span></div>
                                <div class="action-menu classified-document-actions"><a href="{{ route('policy-documents.show',$doc) }}" class="btn btn-info" title="View"><span class="material-icons">visibility</span></a>@if($canManageDocuments)<a href="{{ route('policy-documents.edit',$doc) }}" class="btn btn-warning" title="Edit"><span class="material-icons">edit</span></a>@endif</div>
                            </article>
                        @endforeach
                    </div>
                @endforeach
            </details>
        @endforeach
    </details>
@empty
    <div class="empty-repository"><span class="material-icons">folder_off</span><h5>No documents found</h5><p class="text-muted">Try changing the filters or register a new document.</p></div>
@endforelse
</div>
@if(false)
<div class="table-responsive"><table class="table repository-table mb-0">
    <thead><tr><th>Document</th><th>Classification</th>@if(config('features.form_builder'))<th>Template</th>@endif<th>Governance</th><th>Version / Effective Published</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>@forelse($documents as $doc)
        @php
            $template = $doc->formResponses->first()?->template;
        @endphp
        <tr>
            <td><div class="document-name"><span class="document-icon material-icons">{{ $doc->document_type === 'circular' ? 'campaign' : 'description' }}</span><div><strong>{{ trim($doc->title, " -\t\n\r\0\x0B") !== '' ? $doc->title : 'Untitled document' }}</strong><span class="meta-line reference-line"><span class="material-icons">tag</span>{{ $doc->reference_number ?: 'Reference not assigned' }}</span></div></div></td>
            <td><span class="soft-badge">{{ $documentTypes[$doc->document_type] ?? ucfirst($doc->document_type) }}</span><div class="meta-line mt-1">{{ $doc->topic_category ? ($topicCategories[$doc->topic_category] ?? ucfirst($doc->topic_category)) : 'Uncategorized' }}{{ $doc->subtopic ? ' · '.$doc->subtopic->name : '' }}</div></td>
            @if(config('features.form_builder'))<td>@if($template)<span class="soft-badge template-badge"><span class="material-icons me-1" style="font-size:14px">dashboard_customize</span>{{ $template->name }}</span>@else<span class="meta-line">Legacy form</span>@endif</td>@endif
            <td><div class="governance-stack"><strong><span class="material-icons">apartment</span>{{ strtoupper($doc->owner_unit) }}</strong><div class="meta-line">Access · {{ strtoupper($doc->access_scope) }}</div></div></td>
            <td>
                <details class="version-picker">
                    <summary title="Open version history">
                        <span class="version-current"><span class="material-icons">history</span>Version {{ $doc->version_number }}</span>
                        <span class="material-icons">expand_more</span>
                    </summary>
                    <div class="version-family-menu">
                        @foreach($doc->versionFamily as $version)
                            <a href="{{ route('policy-documents.show', $version) }}" class="version-family-link">
                                <span class="version-number">v{{ $version->version_number }}</span>
                                <span class="version-family-meta"><strong>{{ $documentStatuses[$version->status] ?? $version->statusLabel() }}</strong><small>{{ $version->published_at?->format('d M Y H:i') ?? 'Not published' }}</small></span>
                                @if($version->id === $doc->id)<span class="version-current-pill">Current</span>@else<span class="material-icons" style="font-size:16px">visibility</span>@endif
                            </a>
                        @endforeach
                    </div>
                </details>
                <div class="version-effective">{{ $doc->effective_version_number ? 'Effective publication v'.$doc->effective_version_number : 'Not published' }}</div>
            </td>
            <td><span class="status-pill status-{{ $doc->status }}">{{ $documentStatuses[$doc->status] ?? ucfirst($doc->status) }}</span>@if($doc->expiry_date)<div class="meta-line mt-1">Expires {{ $doc->expiry_date->format('d M Y') }}</div>@endif</td>
            <td><div class="action-menu"><a href="{{ route('policy-documents.show',$doc) }}" class="btn btn-info" title="View document"><span class="material-icons">visibility</span><span class="action-label">View</span></a>@if($canManageDocuments)<a href="{{ route('policy-documents.edit',$doc) }}" class="btn btn-warning" title="Edit document"><span class="material-icons">edit</span><span class="action-label">Edit</span></a>@endif</div></td>
        </tr>
    @empty<tr><td colspan="{{ config('features.form_builder') ? 7 : 6 }}"><div class="empty-repository"><span class="material-icons">folder_off</span><h5>No documents found</h5><p class="text-muted">Try changing the filters or register a new document.</p>@if($canManageDocuments)<a href="{{ route('policy-documents.create') }}" class="btn btn-primary">Register document</a>@endif</div></td></tr>@endforelse</tbody>
</table></div>
@endif
@if($documents->hasPages())<div class="repository-pagination">{{ $documents->links() }}</div>@endif
</div>
@endsection

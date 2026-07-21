@extends('layouts.app')

@section('content')
<style>
    .repository-hero{display:flex;justify-content:space-between;align-items:end;gap:20px;margin-bottom:22px}.repository-hero h2{font-weight:800;margin:4px 0}.repository-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}.repository-stat{display:flex;align-items:center;gap:13px;padding:17px 18px;background:#fff;border:1px solid #e0ebe8;border-radius:12px;box-shadow:0 4px 16px rgba(25,70,64,.05)}.repository-stat .material-icons{display:grid;place-items:center;width:42px;height:42px;border-radius:10px;background:#e9f7f4;color:#008f85}.repository-stat strong{display:block;font-size:23px;line-height:1;color:#133d37}.repository-stat small{color:#72837f}.repository-filter{padding:18px;background:#fff;border:1px solid #dfeae7;border-radius:12px;box-shadow:0 4px 16px rgba(25,70,64,.04);margin-bottom:20px}.repository-filter .form-control{height:46px;border-color:#d7e3e0;border-radius:8px}.repository-table-card{border:0;border-radius:12px;overflow:hidden;box-shadow:0 6px 24px rgba(25,70,64,.07)}.repository-table thead th{padding:14px 16px;background:#edf6f4;color:#496c65;font-size:11px;text-transform:uppercase;letter-spacing:.06em;border:0;white-space:nowrap}.repository-table tbody td{padding:17px 16px;vertical-align:middle;border-color:#e9efed}.repository-table tbody tr{transition:.18s}.repository-table tbody tr:hover{background:#f8fcfb}.document-name{display:flex;gap:11px;align-items:flex-start;min-width:230px}.document-icon{display:grid;place-items:center;flex:0 0 38px;height:38px;border-radius:9px;background:#e8f6f3;color:#008e84}.document-name strong{display:block;color:#123a35}.meta-line{font-size:12px;color:#85938f}.soft-badge{display:inline-flex;align-items:center;padding:5px 9px;border-radius:20px;background:#edf5f3;color:#39665f;font-size:12px;font-weight:650}.template-badge{background:#eef0ff;color:#5058a7}.action-menu{display:flex;gap:6px}.action-menu .btn{display:inline-flex;align-items:center;justify-content:center;width:35px;height:35px;padding:0;border-radius:8px}.action-menu .material-icons{font-size:18px}.empty-repository{text-align:center;padding:65px 20px}.empty-repository .material-icons{font-size:48px;color:#91aaa5}.filter-count{font-size:12px;color:#6f8580}@media(max-width:991px){.repository-stats{grid-template-columns:repeat(2,1fr)}.repository-hero{align-items:flex-start;flex-direction:column}}@media(max-width:575px){.repository-stats{grid-template-columns:1fr}.repository-hero .btn{width:100%}}
    .repository-stats.repository-stats-3{grid-template-columns:repeat(3,1fr)}
    .version-picker{min-width:185px}.version-picker summary{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:8px 10px;border:1px solid #dce8e4;border-radius:9px;background:#f7fbfa;color:#173d37;font-weight:700;cursor:pointer;list-style:none}.version-picker summary::-webkit-details-marker{display:none}.version-picker summary .version-current{display:flex;align-items:center;gap:5px}.version-picker summary .material-icons{font-size:17px;color:#63827b}.version-picker[open] summary{border-color:#00a094;box-shadow:0 0 0 3px rgba(0,160,148,.09)}.version-family-menu{width:285px;max-width:100%;margin-top:7px;padding:7px;border:1px solid #d8e6e2;border-radius:10px;background:#fff;box-shadow:0 8px 20px rgba(17,58,51,.12)}.version-family-link{display:grid;grid-template-columns:42px minmax(0,1fr) auto;gap:8px;align-items:center;padding:9px;border-radius:7px;color:#254941}.version-family-link:hover{background:#edf8f5;color:#007d73}.version-family-link+.version-family-link{border-top:1px solid #edf2f0}.version-number{font-weight:800}.version-family-meta{min-width:0}.version-family-meta strong,.version-family-meta small{display:block}.version-family-meta small{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#7a8d88}.version-current-pill{padding:3px 6px;border-radius:10px;background:#daf3ec;color:#08786d;font-size:9px;font-weight:800;text-transform:uppercase}.version-effective{margin-top:5px;color:#71847f;font-size:11px}
    .repository-results{border:1px solid #dce8e4;border-radius:16px;overflow:hidden;background:#fff;box-shadow:0 12px 34px rgba(18,64,55,.08)}.results-heading{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:17px 20px;border-bottom:1px solid #e2ece9;background:linear-gradient(90deg,#fff,#f5fbf9)}.results-heading strong{display:flex;align-items:center;gap:9px;color:#143f38}.results-heading .material-icons{width:34px;height:34px;display:grid;place-items:center;border-radius:9px;background:#e5f6f2;color:#008f85;font-size:19px}.results-heading small{color:#748680}.repository-table tbody tr:hover{background:#f4faf8;box-shadow:inset 4px 0 #00a094}.repository-table tbody td{padding-top:19px;padding-bottom:19px}.document-name{align-items:center}.document-icon{width:42px;height:42px;flex-basis:42px;border-radius:11px;box-shadow:inset 0 0 0 1px rgba(0,143,133,.08)}.document-name strong{font-size:14px;line-height:1.35}.reference-line{display:flex;align-items:center;gap:5px;margin-top:4px}.reference-line .material-icons{font-size:14px;color:#91a29d}.classification-path{display:flex;flex-wrap:wrap;align-items:center;gap:5px;margin-top:7px;color:#728580;font-size:11px}.classification-path .material-icons{font-size:14px;color:#a0afab}.governance-stack strong,.version-stack strong{display:flex;align-items:center;gap:5px;color:#173d37}.governance-stack .material-icons,.version-stack .material-icons{font-size:16px;color:#6d8780}.action-menu .btn{width:auto;min-width:38px;padding:0 10px;gap:5px;font-size:12px;font-weight:700}.action-menu .action-label{display:inline}.repository-pagination{padding:16px 20px;border-top:1px solid #e2ece9;background:#fbfdfc}@media(max-width:1199px){.action-menu .action-label{display:none}.action-menu .btn{width:36px;padding:0}}@media(max-width:767px){.results-heading{align-items:flex-start;flex-direction:column}.repository-table{min-width:850px}.repository-stats.repository-stats-3{grid-template-columns:1fr}.repository-filter .d-flex{align-items:flex-start!important;flex-direction:column}.repository-filter .col-xl-auto{width:100%}.repository-filter .col-xl-auto .btn-primary{flex:1}.version-family-menu{position:relative;width:240px}}
</style>

<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Dashboard</a><span class="material-icons">chevron_right</span><span>Documents</span></div>
<div class="repository-hero">
    <div><span class="eyebrow">Governed document repository</span><h2>Policies, Guidelines & Circulars</h2><p class="text-muted mb-0">Find, review, and maintain documents created from approved templates.</p></div>
    @if($canManageDocuments)<a href="{{ route('policy-documents.create') }}" class="btn btn-primary action-primary"><span class="material-icons">add</span> Register document</a>@endif
</div>

<div class="repository-stats {{ $canManageDocuments ? '' : 'repository-stats-3' }}">
    <div class="repository-stat"><span class="material-icons">description</span><div><strong>{{ $repositoryStats['total'] }}</strong><small>Total documents</small></div></div>
    <div class="repository-stat"><span class="material-icons">verified</span><div><strong>{{ $repositoryStats['published'] }}</strong><small>Active</small></div></div>
    @if($canManageDocuments)<div class="repository-stat"><span class="material-icons">edit_note</span><div><strong>{{ $repositoryStats['draft'] }}</strong><small>Drafts in progress</small></div></div>@endif
    <div class="repository-stat"><span class="material-icons">event_busy</span><div><strong>{{ $repositoryStats['expiring'] }}</strong><small>Expiring in 30 days</small></div></div>
</div>

<form method="GET" class="repository-filter">
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
@if($documents->hasPages())<div class="repository-pagination">{{ $documents->links() }}</div>@endif
</div>
@endsection

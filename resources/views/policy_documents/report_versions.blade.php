@extends('layouts.app')

@section('content')
@php
    $versionFamilies = $documents
        ->groupBy(fn ($document) => $document->parent_document_id ?: $document->id)
        ->map(fn ($family) => $family->sortBy('version_number')->values())
        ->sortBy(fn ($family) => mb_strtolower($family->first()?->title ?: 'Untitled document'));
@endphp

<style>
    .version-report-hero{display:flex;justify-content:space-between;align-items:flex-end;gap:24px;margin-bottom:22px}.version-report-hero h2{margin:4px 0;font-weight:800;color:#123d37}.version-report-hero p{margin:0;color:#71837e}.version-report-hero .btn{display:inline-flex;align-items:center;gap:7px;min-height:44px;border-radius:9px;font-weight:700}
    .version-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:22px}.version-stat{display:flex;align-items:center;gap:13px;padding:18px;background:#fff;border:1px solid #dce9e5;border-radius:12px;box-shadow:0 5px 18px rgba(20,66,58,.05)}.version-stat-icon{display:grid;place-items:center;flex:0 0 44px;height:44px;border-radius:11px;background:#e8f6f3;color:#008f85}.version-stat strong{display:block;color:#113b35;font-size:24px;line-height:1}.version-stat small{display:block;margin-top:6px;color:#768782}.version-stat.derived .version-stat-icon{background:#eef0ff;color:#5964b3}.version-stat.effective .version-stat-icon{background:#e8f6eb;color:#328247}.version-stat.roots .version-stat-icon{background:#fff3df;color:#a66a08}
    .lifecycle-card{border:0;border-radius:15px;overflow:hidden;box-shadow:0 8px 26px rgba(20,66,58,.07)}.lifecycle-card>.card-header{display:flex;justify-content:space-between;align-items:center;padding:17px 20px;background:linear-gradient(90deg,#edf7f4,#fff);border-bottom:1px solid #dce8e4}.lifecycle-card>.card-header h5{margin:0;color:#123d37;font-weight:750}
    .family-list{display:grid;gap:16px;padding:18px;background:#f7faf9}.family-card{overflow:hidden;border:1px solid #d9e8e4;border-radius:14px;background:#fff;box-shadow:0 4px 14px rgba(20,66,58,.04)}.family-head{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:15px 17px;border-bottom:1px solid #e1ece9;background:#fff}.family-identity{display:flex;align-items:center;gap:12px;min-width:0}.family-icon{display:grid;place-items:center;flex:0 0 42px;height:42px;border-radius:11px;background:#e5f5f2;color:#008f85}.family-copy{min-width:0}.family-copy strong,.family-copy small{display:block}.family-copy strong{overflow:hidden;color:#123d37;font-size:13px;font-weight:800;text-overflow:ellipsis;white-space:nowrap}.family-copy small{margin-top:3px;color:#7b8d88}.family-summary{display:flex;align-items:center;gap:9px;white-space:nowrap}.family-count{padding:6px 10px;border-radius:20px;background:#e8f6f3;color:#087a71;font-size:10px;font-weight:800}
    .lifecycle-flow{display:flex;align-items:stretch;padding:18px;overflow-x:auto}.version-step{position:relative;display:flex;flex:1 0 230px;max-width:340px}.version-step:not(:last-child){padding-right:46px}.version-step:not(:last-child)::before{content:"";position:absolute;z-index:0;right:11px;top:25px;width:25px;height:2px;background:#9ccdc5}.version-step:not(:last-child)::after{content:"chevron_right";position:absolute;right:-1px;top:13px;color:#5ba99f;font-family:"Material Icons";font-size:24px}
    .release-card{position:relative;z-index:1;display:flex;flex-direction:column;width:100%;min-height:184px;padding:15px;border:1px solid #dbe9e5;border-radius:12px;background:#fbfdfc;transition:.18s}.release-card:hover{border-color:#78c8bd;box-shadow:0 7px 20px rgba(0,143,133,.1);transform:translateY(-2px)}.release-card.current{border:2px solid #00a095;background:linear-gradient(145deg,#f4fffc,#fff)}.release-top{display:flex;justify-content:space-between;gap:10px}.release-number{display:flex;align-items:center;gap:7px;color:#123d37;font-size:15px;font-weight:850}.release-kind{margin-top:5px;color:#768782;font-size:10px}.current-label{padding:5px 8px;border-radius:20px;background:#dff5ef;color:#08776e;font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.04em}.release-details{display:grid;gap:9px;margin:14px 0}.release-detail{display:flex;align-items:flex-start;gap:7px;color:#6e827c;font-size:10px}.release-detail .material-icons{color:#2c8d82;font-size:15px}.release-detail strong{display:block;color:#284f48;font-size:10px}.release-footer{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:auto;padding-top:11px;border-top:1px solid #e2ece9}.release-badges{display:flex;flex-wrap:wrap;gap:5px}.effective-badge,.lineage-badge{display:inline-flex;align-items:center;gap:3px;padding:5px 7px;border-radius:20px;font-size:9px;font-weight:750}.effective-badge{background:#e4f5e8;color:#28753c}.lineage-badge{background:#edf5f3;color:#3c685f}.lineage-badge.derived{background:#efeffd;color:#595da0}.release-action{display:grid;place-items:center;flex:0 0 34px;width:34px;height:34px;padding:0;border-radius:9px}
    .empty-version-report{text-align:center;padding:65px 20px;color:#7d908b}.empty-version-report .material-icons{font-size:48px;color:#9bb0aa}
    @media(max-width:991px){.version-stats{grid-template-columns:repeat(2,1fr)}.version-report-hero{align-items:flex-start;flex-direction:column}}
    @media(max-width:767px){.family-list{padding:12px}.family-head{align-items:flex-start}.lifecycle-flow{display:grid;overflow:visible;padding:15px}.version-step{max-width:none;min-width:0}.version-step:not(:last-child){padding:0 0 38px}.version-step:not(:last-child)::before{left:24px;right:auto;top:auto;bottom:8px;width:2px;height:22px}.version-step:not(:last-child)::after{content:"expand_more";left:13px;right:auto;top:auto;bottom:8px}.release-card{min-height:0}}
    @media(max-width:575px){.version-stats{grid-template-columns:1fr}.version-report-hero .btn{width:100%;justify-content:center}.family-head{flex-direction:column}.family-summary{width:100%;justify-content:space-between}.family-copy strong{white-space:normal}}
</style>

<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Dashboard</a><span class="material-icons">chevron_right</span><span>Versioning report</span></div>
<div class="version-report-hero">
    <div><span class="eyebrow">Document lifecycle intelligence</span><h2>Versioning Report</h2><p>Follow every document from its original record to the current effective release.</p></div>
    <a href="{{ route('policy-documents.index') }}" class="btn btn-secondary"><span class="material-icons">folder_open</span>Open document repository</a>
</div>

<div class="version-stats">
    <div class="version-stat"><span class="version-stat-icon material-icons">history</span><div><strong>{{ $versionStats['records'] }}</strong><small>Total version records</small></div></div>
    <div class="version-stat roots"><span class="version-stat-icon material-icons">account_tree</span><div><strong>{{ $versionStats['roots'] }}</strong><small>Document lifecycles</small></div></div>
    <div class="version-stat derived"><span class="version-stat-icon material-icons">difference</span><div><strong>{{ $versionStats['derived'] }}</strong><small>Revisions created</small></div></div>
    <div class="version-stat effective"><span class="version-stat-icon material-icons">verified</span><div><strong>{{ $versionStats['effective'] }}</strong><small>Effective releases</small></div></div>
</div>

<div class="card lifecycle-card">
    <div class="card-header"><h5>Document version lifecycles</h5><small class="text-muted">{{ $versionFamilies->count() }} document{{ $versionFamilies->count() === 1 ? '' : 's' }}</small></div>
    @if($versionFamilies->isEmpty())
        <div class="empty-version-report"><span class="material-icons">history_toggle_off</span><h5>No version data available</h5><p>No accessible document versions have been registered.</p></div>
    @else
        <div class="family-list">
            @foreach($versionFamilies as $family)
                @php
                    $root = $family->first();
                    $latest = $family->last();
                    $familyTitle = trim((string) $root->title) ?: 'Untitled document';
                @endphp
                <section class="family-card">
                    <header class="family-head">
                        <div class="family-identity">
                            <span class="family-icon material-icons">{{ $root->document_type === 'circular' ? 'campaign' : 'description' }}</span>
                            <span class="family-copy"><strong>{{ $familyTitle }}</strong><small>{{ $root->reference_number ?: 'No official reference' }} · {{ ucfirst($root->document_type) }}</small></span>
                        </div>
                        <div class="family-summary"><span class="family-count">{{ $family->count() }} version{{ $family->count() === 1 ? '' : 's' }}</span><span class="status-pill status-{{ $latest->status }}">{{ $latest->statusLabel() }}</span></div>
                    </header>
                    <div class="lifecycle-flow" aria-label="Version lifecycle for {{ $familyTitle }}">
                        @foreach($family as $document)
                            @php $isLatest = $document->is($latest); @endphp
                            <div class="version-step">
                                <article class="release-card {{ $isLatest ? 'current' : '' }}">
                                    <div class="release-top">
                                        <div><div class="release-number"><span class="material-icons" style="font-size:18px">history</span>Version {{ $document->version_number }}</div><div class="release-kind">{{ $document->parent_document_id ? 'Revision of the original document' : 'Original registered document' }}</div></div>
                                        @if($isLatest)<span class="current-label">Current</span>@endif
                                    </div>
                                    <div class="release-details">
                                        <div class="release-detail"><span class="material-icons">event</span><span><strong>{{ $document->published_at?->format('d M Y, H:i') ?? 'Not published' }}</strong>Publication date</span></div>
                                        <div class="release-detail"><span class="material-icons">person</span><span><strong>{{ $document->publisher?->name ?? 'Not assigned' }}</strong>Publisher</span></div>
                                    </div>
                                    <div class="release-footer">
                                        <div class="release-badges">
                                            <span class="status-pill status-{{ $document->status }}">{{ $document->statusLabel() }}</span>
                                            @if($document->is_effective_published_version)<span class="effective-badge"><span class="material-icons" style="font-size:11px">check_circle</span>Effective release</span>@endif
                                            <span class="lineage-badge {{ $document->parent_document_id ? 'derived' : '' }}">{{ $document->parent_document_id ? 'Revision' : 'Root' }}</span>
                                        </div>
                                        <a href="{{ route('policy-documents.show', $document) }}" class="btn btn-info release-action" title="View Version {{ $document->version_number }}" aria-label="View Version {{ $document->version_number }} of {{ $familyTitle }}"><span class="material-icons" style="font-size:18px">visibility</span></a>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @endif
</div>
@endsection

@extends('layouts.app')

@section('content')
<style>
    .version-report-hero{display:flex;justify-content:space-between;align-items:flex-end;gap:24px;margin-bottom:22px}.version-report-hero h2{margin:4px 0;font-weight:800;color:#123d37}.version-report-hero p{margin:0;color:#71837e}.version-report-hero .btn{display:inline-flex;align-items:center;gap:7px;min-height:44px;border-radius:9px;font-weight:700}
    .version-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:22px}.version-stat{display:flex;align-items:center;gap:13px;padding:18px;background:#fff;border:1px solid #dce9e5;border-radius:12px;box-shadow:0 5px 18px rgba(20,66,58,.05)}.version-stat-icon{display:grid;place-items:center;flex:0 0 44px;height:44px;border-radius:11px;background:#e8f6f3;color:#008f85}.version-stat strong{display:block;color:#113b35;font-size:24px;line-height:1}.version-stat small{display:block;margin-top:6px;color:#768782}.version-stat.derived .version-stat-icon{background:#eef0ff;color:#5964b3}.version-stat.effective .version-stat-icon{background:#e8f6eb;color:#328247}.version-stat.roots .version-stat-icon{background:#fff3df;color:#a66a08}
    .version-table-card{border:0;border-radius:13px;overflow:hidden;box-shadow:0 8px 26px rgba(20,66,58,.07)}.version-table-card .card-header{display:flex;justify-content:space-between;align-items:center;padding:17px 20px;background:linear-gradient(90deg,#edf7f4,#fff);border-bottom:1px solid #dce8e4}.version-table-card .card-header h5{margin:0;color:#123d37;font-weight:750}.version-table thead th{padding:14px 16px;background:#eaf4f1;color:#496a64;font-size:11px;text-transform:uppercase;letter-spacing:.06em;border:0;white-space:nowrap}.version-table tbody td{padding:16px;vertical-align:middle;border-color:#e7efec}.version-table tbody tr:hover{background:#f8fcfb}.version-title{display:flex;align-items:flex-start;gap:11px;min-width:240px}.version-title-icon{display:grid;place-items:center;flex:0 0 38px;height:38px;border-radius:9px;background:#e8f6f3;color:#008f85}.version-title strong,.version-title small{display:block}.version-title strong{color:#123d37}.version-title small{margin-top:3px;color:#85938f}.version-number{display:inline-flex;align-items:center;gap:6px;font-weight:800;color:#123d37}.effective-badge,.lineage-badge{display:inline-flex;align-items:center;gap:4px;padding:5px 9px;border-radius:20px;font-size:11px;font-weight:750}.effective-badge{margin-top:5px;background:#e4f5e8;color:#28753c}.lineage-badge{background:#edf5f3;color:#3c685f}.lineage-badge.derived{background:#efeffd;color:#595da0}.publisher-meta strong,.publisher-meta small{display:block}.publisher-meta small{margin-top:3px;color:#85938f}.empty-version-report{text-align:center;padding:65px 20px;color:#7d908b}.empty-version-report .material-icons{font-size:48px;color:#9bb0aa}
    @media(max-width:991px){.version-stats{grid-template-columns:repeat(2,1fr)}.version-report-hero{align-items:flex-start;flex-direction:column}}@media(max-width:575px){.version-stats{grid-template-columns:1fr}.version-report-hero .btn{width:100%;justify-content:center}}
</style>

<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Dashboard</a><span class="material-icons">chevron_right</span><span>Versioning report</span></div>
<div class="version-report-hero">
    <div><span class="eyebrow">Document lifecycle intelligence</span><h2>Versioning Report</h2><p>Trace root documents, derived revisions, publishers, and each effective release.</p></div>
    <a href="{{ route('policy-documents.index') }}" class="btn btn-secondary"><span class="material-icons">folder_open</span>Open document repository</a>
</div>

<div class="version-stats">
    <div class="version-stat"><span class="version-stat-icon material-icons">history</span><div><strong>{{ $versionStats['records'] }}</strong><small>Total version records</small></div></div>
    <div class="version-stat roots"><span class="version-stat-icon material-icons">account_tree</span><div><strong>{{ $versionStats['roots'] }}</strong><small>Root documents</small></div></div>
    <div class="version-stat derived"><span class="version-stat-icon material-icons">difference</span><div><strong>{{ $versionStats['derived'] }}</strong><small>Derived versions</small></div></div>
    <div class="version-stat effective"><span class="version-stat-icon material-icons">verified</span><div><strong>{{ $versionStats['effective'] }}</strong><small>Effective releases</small></div></div>
</div>

<div class="card version-table-card">
    <div class="card-header"><h5>Version register</h5><small class="text-muted">{{ $documents->count() }} accessible record{{ $documents->count() === 1 ? '' : 's' }}</small></div>
    <div class="table-responsive"><table class="table version-table mb-0">
        <thead><tr><th>Document</th><th>Version</th><th>Effective Published</th><th>Type</th><th>Status</th><th>Publisher</th><th>Lineage</th></tr></thead>
        <tbody>
        @forelse($documents as $document)
            <tr>
                <td><a class="version-title" href="{{ route('policy-documents.show', $document) }}"><span class="version-title-icon material-icons">{{ $document->document_type === 'circular' ? 'campaign' : 'description' }}</span><span><strong>{{ $document->title }}</strong><small>{{ $document->reference_number ?: 'No official reference' }}</small></span></a></td>
                <td><span class="version-number"><span class="material-icons" style="font-size:17px">history</span>v{{ $document->version_number }}</span>@if($document->is_effective_published_version)<div><span class="effective-badge"><span class="material-icons" style="font-size:13px">check_circle</span>Effective</span></div>@endif</td>
                <td>@if($document->effective_version_number)<strong>v{{ $document->effective_version_number }}</strong><div class="text-muted small">Current published</div>@else<span class="text-muted">Not published</span>@endif</td>
                <td><span class="soft-badge">{{ ucfirst($document->document_type) }}</span></td>
                <td><span class="status-pill status-{{ $document->status }}">{{ $document->statusLabel() }}</span></td>
                <td><div class="publisher-meta"><strong>{{ $document->publisher?->name ?? 'Not published' }}</strong><small>{{ $document->published_at?->format('d M Y H:i') ?? 'No publication date' }}</small></div></td>
                <td><span class="lineage-badge {{ $document->parent_document_id ? 'derived' : '' }}"><span class="material-icons" style="font-size:14px">{{ $document->parent_document_id ? 'call_split' : 'hub' }}</span>{{ $document->parent_document_id ? 'Derived version' : 'Root record' }}</span></td>
            </tr>
        @empty
            <tr><td colspan="7"><div class="empty-version-report"><span class="material-icons">history_toggle_off</span><h5>No version data available</h5><p>No accessible document versions have been registered.</p></div></td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection

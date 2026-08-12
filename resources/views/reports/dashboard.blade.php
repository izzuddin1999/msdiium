@extends('layouts.app')

@section('content')
<style>
    .report-flow{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px}.report-flow-step{position:relative;display:flex;align-items:center;gap:11px;padding:14px;border:1px solid #d8e7e3;border-radius:11px;background:#fff}.report-flow-step:not(:last-child):after{content:'arrow_forward';position:absolute;right:-21px;z-index:2;display:grid;place-items:center;width:28px;height:28px;border-radius:50%;background:#e8f6f2;color:#008f85;font-family:'Material Icons';font-size:17px}.report-flow-number{display:grid;place-items:center;flex:0 0 34px;height:34px;border-radius:9px;background:#e5f5f2;color:#087d72;font-weight:800}.report-flow-step strong,.report-flow-step small{display:block}.report-flow-step strong{color:#163f38}.report-flow-step small{margin-top:2px;color:#71847f;font-size:10px}
    .report-kpis{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px}.report-kpi{display:flex;align-items:center;gap:13px;padding:19px;border:1px solid #dbe8e5;border-radius:13px;background:#fff;box-shadow:0 6px 18px rgba(20,65,57,.06)}.report-kpi .material-icons{display:grid;place-items:center;width:44px;height:44px;border-radius:11px;background:#e7f7f3;color:#008f85}.report-kpi strong,.report-kpi small{display:block}.report-kpi strong{color:#153e37;font-size:25px;line-height:1}.report-kpi small{margin-top:4px;color:#71847f}.report-kpi.archived .material-icons{background:#f0edf7;color:#6b4ca3}
    .report-layout{display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:18px;align-items:start}.report-card{overflow:hidden;border:1px solid #dbe8e5;border-radius:13px;background:#fff;box-shadow:0 8px 24px rgba(20,65,57,.07)}.report-card-head{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:16px 18px;border-bottom:1px solid #e1ebe8;background:linear-gradient(90deg,#eef8f6,#fff)}.report-card-head h5,.report-card-head p{margin:0}.report-card-head h5{color:#153f38}.report-card-head p{margin-top:3px;color:#71847f;font-size:11px}.report-table{width:100%;margin:0;table-layout:fixed}.report-table thead th{padding:11px 13px;background:#f6faf9;color:#667e78;font-size:10px;text-transform:uppercase;letter-spacing:.055em;white-space:nowrap}.report-table thead th:first-child{width:48%}.report-table thead th:nth-child(2){width:15%}.report-table thead th:nth-child(3){width:14%}.report-table thead th:nth-child(4){width:16%}.report-table thead th:last-child{width:7%}.report-table tbody td{padding:12px 13px;vertical-align:middle;border-color:#e8efed;overflow-wrap:anywhere}.report-title strong,.report-title small{display:block}.report-title strong{display:-webkit-box;overflow:hidden;color:#173e38;-webkit-box-orient:vertical;-webkit-line-clamp:2}.report-title small{margin-top:2px;color:#81918d}.report-open{display:grid;place-items:center;width:32px;height:32px;padding:0}
    .kcdiom-report{padding:20px;text-align:center}.kcdiom-report .material-icons{display:grid;place-items:center;width:56px;height:56px;margin:0 auto 10px;border-radius:15px;background:#e5f6f2;color:#008f85;font-size:28px}.kcdiom-report strong{display:block;color:#123e37;font-size:38px;line-height:1}.kcdiom-report h5{margin:9px 0 4px;color:#173f38}.kcdiom-report p{margin:0 0 15px;color:#71847f;font-size:11px}.kcdiom-report .btn{width:100%}.report-pagination{padding:13px 16px;border-top:1px solid #e3ecea}
    @media(max-width:1350px){.report-layout,.report-flow,.report-kpis{grid-template-columns:1fr}.report-flow-step:after{display:none}}@media(max-width:767px){.report-card{overflow-x:auto}.report-table{min-width:720px}}
</style>

<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Dashboard</a><span class="material-icons">chevron_right</span><span>Reporting dashboard</span></div>
<div class="page-heading">
    <div><span class="eyebrow">{{ strtoupper($organization) }} · HR19-03 Reporting Management</span><h2>{{ strtoupper($organization) }} reporting dashboard</h2><p>View {{ strtoupper($organization) }} document counters, generate its document report, and monitor organizational submissions.</p></div>
</div>

<div class="report-flow" aria-label="Reporting process">
    <div class="report-flow-step"><span class="report-flow-number">1</span><span><strong>View dashboard</strong><small>Total, active and archived counters</small></span></div>
    <div class="report-flow-step"><span class="report-flow-number">2</span><span><strong>Review document report</strong><small>Title, type, status and current version</small></span></div>
    <div class="report-flow-step"><span class="report-flow-number">3</span><span><strong>Check {{ strtoupper($organization) }} report</strong><small>Submission count by {{ strtoupper($organization) }}</small></span></div>
</div>

<div class="report-kpis">
    <div class="report-kpi"><span class="material-icons">description</span><div><strong>{{ $metrics['total'] }}</strong><small>Total documents</small></div></div>
    <div class="report-kpi"><span class="material-icons">verified</span><div><strong>{{ $metrics['active'] }}</strong><small>Active documents</small></div></div>
    <div class="report-kpi archived"><span class="material-icons">inventory_2</span><div><strong>{{ $metrics['archived'] }}</strong><small>Archived documents</small></div></div>
</div>

<div class="report-layout">
    <section class="report-card">
        <div class="report-card-head"><div><h5>Document report</h5><p>Current record for every document family</p></div><span class="status-pill status-published">{{ $documentReport->total() }} records</span></div>
        <table class="table report-table">
            <thead><tr><th>Title</th><th>Type</th><th>Status</th><th>Current version</th><th>View</th></tr></thead>
            <tbody>
                @forelse($documentReport as $document)
                    <tr>
                        <td class="report-title"><strong>{{ trim($document->title) !== '' ? $document->title : 'Untitled document' }}</strong><small>{{ $document->reference_number ?: 'No reference number' }}</small></td>
                        <td>{{ ucwords(str_replace('_', ' ', $document->document_type)) }}</td>
                        <td><span class="status-pill status-{{ $document->status }}">{{ $document->statusLabel() }}</span></td>
                        <td><strong>Version {{ $document->version_number }}</strong></td>
                        <td><a href="{{ route('policy-documents.show', $document) }}" class="btn btn-info report-open" title="View {{ $document->title }}"><span class="material-icons">visibility</span></a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">No documents are available for reporting.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($documentReport->hasPages())<div class="report-pagination">{{ $documentReport->links() }}</div>@endif
    </section>

    <aside class="report-card">
        <div class="report-card-head"><div><h5>{{ strtoupper($organization) }} report</h5><p>Submissions by organization</p></div></div>
        <div class="kcdiom-report">
            <span class="material-icons">domain</span>
            <strong>{{ $organizationSubmissionCount }}</strong>
            <h5>{{ strtoupper($organization) }} submissions</h5>
            <p>Documents submitted under {{ strtoupper($organization) }} ownership.</p>
            <a href="{{ route('policy-documents.index', ['unit' => $organization]) }}" class="btn btn-primary">View {{ strtoupper($organization) }} documents</a>
        </div>
    </aside>
</div>
@endsection

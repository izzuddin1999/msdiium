@extends('layouts.app')

@section('content')
<style>
    .audit-v2{padding-bottom:24px}.audit-heading{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:20px}.audit-title{display:grid;grid-template-columns:48px 1fr;gap:14px;align-items:center}.audit-title-icon{display:grid;place-items:center;width:46px;height:46px;border:1px solid #dce5e8;border-radius:9px;background:rgba(255,255,255,.9);color:#08746e}.audit-title h2{margin:0;color:#152642;font-size:26px}.audit-title p{grid-column:1/-1;margin:0;color:#667085;font-size:12px}.audit-export{display:inline-flex;align-items:center;gap:8px;padding:10px 15px;border:1px solid #d3dfe3;border-radius:8px;background:rgba(255,255,255,.92);color:#086c67;font-size:10px;font-weight:800}.audit-export .material-icons{font-size:18px}.audit-filters{display:grid;grid-template-columns:1.35fr 1fr 1fr .95fr .95fr auto;gap:10px;align-items:end;margin-bottom:20px;padding:19px;border:1px solid #dfe5e8;border-radius:11px;background:rgba(255,255,255,.96);box-shadow:0 5px 14px rgba(31,42,55,.06)}.audit-filters label{display:block;margin:0;color:#243650;font-size:9px;font-weight:750}.audit-filters input,.audit-filters select{width:100%;height:40px;margin-top:7px;padding:0 11px;border:1px solid #d4dee2;border-radius:7px;background:#fff;color:#415168;font-size:9px}.audit-search{position:relative}.audit-search .material-icons{position:absolute;right:10px;bottom:10px;color:#2b4964;font-size:18px}.audit-apply{display:inline-flex;align-items:center;justify-content:center;gap:7px;height:40px;padding:0 17px;border:0;border-radius:7px;background:linear-gradient(135deg,#00645f,#00877f);color:#fff;font-size:9px;font-weight:800}.audit-apply .material-icons{font-size:16px}.audit-clear{align-self:center;margin-top:16px;color:#087b75;font-size:8px;font-weight:700}.audit-card{overflow:hidden;border:1px solid #dfe5e8;border-radius:11px;background:rgba(255,255,255,.97);box-shadow:0 5px 15px rgba(31,42,55,.06)}.audit-card-head{display:flex;align-items:center;gap:12px;min-height:58px;padding:0 20px;border-bottom:1px solid #e3e8ea}.audit-card-head h3{margin:0;color:#172b4d;font-size:14px}.audit-count{padding:5px 9px;border-radius:7px;background:#e7f5f1;color:#08756e;font-size:8px;font-weight:800}.audit-table{width:100%;border-collapse:collapse}.audit-table th{padding:12px 20px;background:#f6f8fb;color:#47566c;font-size:8px;text-align:left;white-space:nowrap}.audit-table td{padding:14px 20px;border-bottom:1px solid #e7ebed;color:#243650;font-size:9px;vertical-align:middle}.audit-table tr:last-child td{border:0}.audit-date strong,.audit-date small{display:block;white-space:nowrap}.audit-date strong{font-size:9px}.audit-date small{display:flex;align-items:center;gap:4px;margin-top:5px;color:#667085;font-size:8px}.audit-date .material-icons{font-size:13px}.audit-document{display:grid;grid-template-columns:30px minmax(240px,1fr);gap:10px;align-items:center}.audit-doc-icon{display:grid;place-items:center;width:29px;height:32px;border-radius:6px;background:#e7f2ff;color:#1685e4}.audit-doc-icon .material-icons{font-size:17px}.audit-document strong,.audit-document small{display:block}.audit-document strong{color:#172b4d;font-size:9px;line-height:1.45}.audit-document small{margin-top:2px;color:#667085;font-size:8px}.audit-action{display:inline-flex;align-items:center;gap:4px;padding:6px 9px;border-radius:7px;background:#e5f6ec;color:#087b4d;font-size:7px;font-weight:850;text-transform:uppercase}.audit-action:after{content:'';width:5px;height:5px;border-radius:50%;background:currentColor}.audit-action.updated{background:#e7f1ff;color:#1674d1}.audit-action.created{background:#fff0dd;color:#d77600}.audit-action.superseded{background:#ffe8ea;color:#e62939}.audit-action.deleted{background:#f1f2f4;color:#5e6671}.audit-actor{display:grid;grid-template-columns:29px 1fr;gap:9px;align-items:center}.audit-avatar{display:grid;place-items:center;width:29px;height:29px;border-radius:50%;background:#078c83;color:#fff;font-size:9px;font-weight:800}.audit-actor strong,.audit-actor small{display:block;white-space:nowrap}.audit-actor strong{font-size:8px}.audit-actor small{margin-top:2px;color:#667085;font-size:7px}.audit-details strong,.audit-details small{display:block}.audit-details strong{font-size:8px}.audit-details small{margin-top:3px;color:#667085;font-size:8px}.audit-arrow{color:#087c76}.audit-footer{display:flex;align-items:center;justify-content:space-between;gap:15px;min-height:58px;padding:10px 20px;border-top:1px solid #e5e9eb;color:#667085;font-size:9px}.audit-footer nav{margin-left:auto}.audit-empty{padding:45px!important;text-align:center;color:#667085!important}.audit-empty .material-icons{display:block;margin-bottom:8px;font-size:34px}
    @media(max-width:1150px){.audit-filters{grid-template-columns:repeat(3,1fr)}.audit-apply{width:100%}}@media(max-width:800px){.audit-heading{align-items:flex-start}.audit-title h2{font-size:22px}.audit-filters{grid-template-columns:1fr 1fr}.audit-table-wrap{overflow-x:auto}.audit-table{min-width:900px}}@media(max-width:520px){.audit-filters{grid-template-columns:1fr}.audit-heading{flex-direction:column}.audit-export{align-self:flex-end}}
</style>

<div class="audit-v2">
    <div class="audit-heading">
        <div class="audit-title"><span class="audit-title-icon"><span class="material-icons">fact_check</span></span><h2>Document audit log</h2><p>Trace document creation, publication, and metadata changes with actor and timestamp evidence.</p></div>
        <a class="audit-export" href="{{ route('document-activity-logs.export',request()->query()) }}"><span class="material-icons">download</span>Export</a>
    </div>

    <form method="GET" class="audit-filters">
        <label class="audit-search">Search document<input name="q" value="{{ request('q') }}" placeholder="Document title or reference..."><span class="material-icons">search</span></label>
        <label>Action<select name="action"><option value="">All actions</option>@foreach($actions as $action)<option value="{{ $action }}" @selected(request('action')===$action)>{{ ucfirst($action) }}</option>@endforeach</select></label>
        <label>User<select name="user_id"><option value="">All users</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((int)request('user_id')===$user->id)>{{ $user->name }}</option>@endforeach</select></label>
        <label>From date<input type="date" name="date_from" value="{{ request('date_from') }}"></label>
        <label>To date<input type="date" name="date_to" value="{{ request('date_to') }}"></label>
        <button class="audit-apply"><span class="material-icons">filter_alt</span>Apply</button>
        @if(request()->query())<a class="audit-clear" href="{{ route('document-activity-logs.index') }}">Clear filters</a>@endif
    </form>

    <section class="audit-card">
        <header class="audit-card-head"><h3>Audit events</h3><span class="audit-count">{{ $logs->total() }} matching event(s)</span></header>
        <div class="audit-table-wrap"><table class="audit-table"><thead><tr><th>WHEN</th><th>DOCUMENT</th><th>ACTION</th><th>BY</th><th>DETAILS</th><th></th></tr></thead><tbody>
        @forelse($logs as $log)
            @php
                $fields = array_keys($log->new_values ?? []);
                $detailLabel = match($log->action) {'published'=>'Document published','created'=>'Document created','updated'=>'Document updated','superseded'=>'Document superseded','deleted'=>'Document deleted',default=>ucfirst($log->action)};
                $detailSummary = count($fields) > 2
                    ? 'Metadata changed'
                    : ($fields ? str(implode(', ',$fields))->replace('_',' ')->title() : 'No metadata fields recorded');
            @endphp
            <tr>
                <td class="audit-date"><strong>{{ $log->created_at->format('d M Y') }}</strong><small><span class="material-icons">schedule</span>{{ $log->created_at->format('H:i:s') }}</small></td>
                <td><div class="audit-document"><span class="audit-doc-icon"><span class="material-icons">description</span></span><span>@if($log->document)<a href="{{ route('policy-documents.show',$log->document) }}"><strong>{{ $log->document->title }}</strong></a><small>{{ $log->document->reference_number ?: 'No reference' }}</small>@else<strong>Deleted document</strong><small>Record no longer available</small>@endif</span></div></td>
                <td><span class="audit-action {{ $log->action }}">{{ $log->action }}</span></td>
                <td><div class="audit-actor"><span class="audit-avatar">{{ strtoupper(substr($log->user?->name ?? 'S',0,1)) }}</span><span><strong>{{ $log->user?->name ?? 'System' }}</strong><small>{{ $log->user?->actorLabel() ?? 'Automated action' }}</small></span></div></td>
                <td class="audit-details"><strong>{{ $detailLabel }}</strong><small>{{ $detailSummary }}</small></td>
                <td>@if($log->document)<a class="audit-arrow material-icons" href="{{ route('policy-documents.show',$log->document) }}">chevron_right</a>@endif</td>
            </tr>
        @empty<tr><td colspan="6" class="audit-empty"><span class="material-icons">history_toggle_off</span>No audit events match the selected filters.</td></tr>@endforelse
        </tbody></table></div>
        <footer class="audit-footer"><span>Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} records</span><span>{{ $logs->links() }}</span></footer>
    </section>
</div>
@endsection

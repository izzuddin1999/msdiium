@extends('layouts.app')

@section('content')
<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Dashboard</a><span class="material-icons">chevron_right</span><span>Document audit log</span></div>
<div class="page-heading"><div><span class="eyebrow">DOCUMENT_LOG governance</span><h2>Document audit log</h2><p>Trace document creation, publication, and metadata changes with actor and timestamp evidence.</p></div></div>

<form method="GET" class="row g-2 flow-toolbar">
    <div class="col-lg-3"><input name="q" class="form-control" value="{{ request('q') }}" placeholder="Document title or reference"></div>
    <div class="col-lg-2"><select name="action" class="form-control"><option value="">All actions</option>@foreach($actions as $action)<option value="{{ $action }}" @selected(request('action') === $action)>{{ ucfirst($action) }}</option>@endforeach</select></div>
    <div class="col-lg-2"><select name="user_id" class="form-control"><option value="">All users</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((int)request('user_id') === $user->id)>{{ $user->name }}</option>@endforeach</select></div>
    <div class="col-lg-2"><input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" title="From date"></div>
    <div class="col-lg-2"><input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" title="To date"></div>
    <div class="col-lg-1"><button class="btn btn-secondary w-100">Apply</button></div>
</form>

<div class="card flow-card">
    <div class="card-header card-header-row"><div><h5>Audit events</h5><small>{{ $logs->total() }} matching event(s)</small></div>@if(request()->query())<a href="{{ route('document-activity-logs.index') }}">Clear filters</a>@endif</div>
    <div class="card-body p-0"><div class="table-responsive"><table class="table flow-table mb-0">
        <thead><tr><th>When</th><th>Document</th><th>Action</th><th>Actor</th><th>Changed fields</th><th>IP address</th></tr></thead>
        <tbody>
        @forelse($logs as $log)
            <tr>
                <td>{{ $log->created_at->format('d M Y H:i:s') }}</td>
                <td>@if($log->document)<a href="{{ route('policy-documents.show', $log->document) }}"><strong>{{ $log->document->title }}</strong></a><br><small class="text-muted">{{ $log->document->reference_number ?: 'No reference' }}</small>@else<span class="text-muted">Deleted document</span>@endif</td>
                <td><span class="status-pill {{ $log->action === 'published' ? 'status-published' : 'status-draft' }}">{{ ucfirst($log->action) }}</span></td>
                <td>{{ $log->user?->name ?? 'System' }}<br><small class="text-muted">{{ $log->user?->staff_id ?: 'No staff ID' }}</small></td>
                <td>{{ implode(', ', array_keys($log->new_values ?? [])) ?: 'No field list' }}</td>
                <td>{{ $log->ip_address ?: 'Not recorded' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center py-4">No audit events match the selected filters.</td></tr>
        @endforelse
        </tbody>
    </table></div></div>
    <div class="card-footer">{{ $logs->links() }}</div>
</div>
@endsection

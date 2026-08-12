@extends('layouts.app')

@section('content')
<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Dashboard</a><span class="material-icons">chevron_right</span><span>CAS/HURIS sync</span></div>
<div class="page-heading"><div><span class="eyebrow">Directory integration</span><h2>CAS/HURIS synchronization</h2><p>Import authoritative staff identities while preserving existing policy-management role assignments.</p></div></div>

<div class="row g-4">
    <div class="col-xl-5">
        <div class="card flow-card">
            <div class="card-header"><h5>Import directory export</h5><small>Paste comma-separated HURIS data using the required header.</small></div>
            <form action="{{ route('directory-sync.store') }}" method="POST">@csrf
                <div class="card-body">
                    <div class="alert alert-info"><strong>Required header</strong><br><code>staff_id,cas_username,name,email,unit</code><br><small>Optional: <code>organization_code</code> for a specific Kulliyyah/Centre/Division/Institute/Office. Without it, records use the MSD or KCDIOM root organization. Existing roles are never overwritten.</small></div>
                    <label class="form-label">CSV Data</label>
                    <textarea name="csv_data" class="form-control font-monospace" rows="13" required placeholder="staff_id,cas_username,name,email,unit,organization_code&#10;ST1001,ahmad.ali,Ahmad Ali,ahmad.ali@iium.edu.my,kcdiom,KCDIOM">{{ old('csv_data') }}</textarea>
                </div>
                <div class="card-footer"><button class="btn btn-primary w-100">Validate and synchronize</button></div>
            </form>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card flow-card">
            <div class="card-header"><h5>Synchronization history</h5><small>Recent imports and row-level outcomes.</small></div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table flow-table mb-0">
                <thead><tr><th>Completed</th><th>Initiated By</th><th>Received</th><th>Created</th><th>Updated</th><th>Rejected</th></tr></thead>
                <tbody>@forelse($runs as $run)<tr><td>{{ \Carbon\Carbon::parse($run->completed_at)->format('d M Y H:i') }}</td><td>{{ $run->initiator_name ?? 'System' }}</td><td>{{ $run->rows_received }}</td><td>{{ $run->rows_created }}</td><td>{{ $run->rows_updated }}</td><td><span class="status-pill {{ $run->rows_rejected ? 'status-inactive' : 'status-published' }}">{{ $run->rows_rejected }}</span></td></tr>@empty<tr><td colspan="6" class="text-center py-4">No synchronization runs recorded.</td></tr>@endforelse</tbody>
            </table></div></div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Dashboard</a><span class="material-icons">chevron_right</span><span>User access report</span></div>
<div class="page-heading">
    <div><span class="eyebrow">Access governance</span><h2>User access report</h2><p>Review CAS-linked identities, assigned roles, organizational units, and account status.</p></div>
    <a href="{{ route('reports.user-access.export', request()->query()) }}" class="btn btn-primary action-primary"><span class="material-icons">download</span> Export CSV</a>
</div>

<div class="metric-grid">
    @foreach([
        ['label' => 'Registered users', 'value' => $summary['total'], 'icon' => 'groups', 'tone' => 'teal'],
        ['label' => 'Active accounts', 'value' => $summary['active'], 'icon' => 'person_check', 'tone' => 'green'],
        ['label' => 'Policy managers', 'value' => $summary['managers'], 'icon' => 'admin_panel_settings', 'tone' => 'blue'],
        ['label' => 'Staff users', 'value' => $summary['staff'], 'icon' => 'badge', 'tone' => 'amber'],
    ] as $metric)
        <div class="metric-card {{ $metric['tone'] }}"><span class="material-icons">{{ $metric['icon'] }}</span><div><strong>{{ $metric['value'] }}</strong><small>{{ $metric['label'] }}</small></div></div>
    @endforeach
</div>

<form method="GET" class="row g-2 flow-toolbar">
    <div class="col-lg-4"><input name="q" class="form-control" value="{{ request('q') }}" placeholder="Name, email, staff ID, or CAS username"></div>
    <div class="col-lg-2"><select name="role" class="form-control"><option value="">All roles</option><option value="msd_admin" @selected(request('role') === 'msd_admin')>MSD Administrator</option><option value="kcdiom_liaison" @selected(request('role') === 'kcdiom_liaison')>AIKOL Liaison</option><option value="staff_user" @selected(request('role') === 'staff_user')>Staff User</option></select></div>
    <div class="col-lg-2"><select name="unit" class="form-control"><option value="">All units</option>@foreach(['all' => 'All', 'msd' => 'MSD', 'kcdiom' => 'KCDIOM'] as $value => $label)<option value="{{ $value }}" @selected(request('unit') === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-lg-2"><select name="status" class="form-control"><option value="">All statuses</option><option value="active" @selected(request('status') === 'active')>Active</option><option value="inactive" @selected(request('status') === 'inactive')>Inactive</option></select></div>
    <div class="col-lg-2 d-flex gap-2"><button class="btn btn-secondary flex-grow-1">Apply</button>@if(request()->query())<a href="{{ route('reports.user-access') }}" class="btn btn-light border">Clear</a>@endif</div>
</form>

<div class="card flow-card">
    <div class="card-header"><h5>Access register</h5><small>{{ $users->total() }} matching account(s)</small></div>
    <div class="card-body p-0"><div class="table-responsive"><table class="table flow-table mb-0">
        <thead><tr><th>CAS Identity</th><th>User</th><th>Role</th><th>Unit</th><th>Status</th><th>Last CAS Sync</th></tr></thead>
        <tbody>
        @forelse($users as $user)
            <tr>
                <td><strong>{{ $user->staff_id ?: 'No staff ID' }}</strong><br><small class="text-muted">{{ $user->cas_username ?: 'Not linked' }}</small></td>
                <td>{{ $user->name }}<br><small class="text-muted">{{ $user->email }}</small></td>
                <td>{{ ucwords(str_replace('_', ' ', $user->role)) }}</td>
                <td>{{ strtoupper($user->unit) }}</td>
                <td><span class="status-pill {{ $user->is_active ? 'status-published' : 'status-inactive' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td>{{ $user->last_cas_sync_at?->format('d M Y H:i') ?? 'Never synchronized' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center py-4">No access records match the selected filters.</td></tr>
        @endforelse
        </tbody>
    </table></div></div>
    <div class="card-footer">{{ $users->links() }}</div>
</div>
@endsection

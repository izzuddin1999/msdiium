@extends('layouts.app')

@section('content')
@php($roleOptions = [
    'system_admin' => 'System Administrator',
    'msd_admin' => 'MSD Administrator',
    'policy_manager' => 'Policy Manager',
    'kcdiom_liaison' => 'KCDIOM Liaison',
    'staff_user' => 'Staff User',
])
<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Dashboard</a><span class="material-icons">chevron_right</span><span>Access roles</span></div>
<div class="page-heading"><div><span class="eyebrow">Identity governance</span><h2>Access roles</h2><p>Link CAS identities to application roles, units, and active access.</p></div></div>
<div class="row">
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Register Access Role</h5>
            </div>
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                <div class="card-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Staff ID</label>
                        <input type="text" name="staff_id" class="form-control" value="{{ old('staff_id') }}" required maxlength="20">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">CAS Username</label>
                        <input type="text" name="cas_username" class="form-control" value="{{ old('cas_username') }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-control" required>
                            @foreach($roleOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Unit</label>
                        <select name="unit" class="form-control" required>
                            @foreach(['all' => 'All', 'msd' => 'MSD', 'kcdiom' => 'KCDIOM'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('unit') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" value="1" id="isActiveUser" @checked(old('is_active', true))>
                            <label class="form-check-label" for="isActiveUser">User is active</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary w-100">Create User Access</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Registered Users</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table flow-table mb-0">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>CAS Identity</th>
                            <th>Role</th>
                            <th>Unit</th>
                            <th>Status</th>
                            <th>Update Access</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->staff_id ?? '-' }}<br><small>{{ $user->cas_username ?? 'Not linked' }}</small></td>
                                <td>{{ $roleOptions[$user->role] ?? str($user->role)->replace('_', ' ')->title() }}</td>
                                <td>{{ strtoupper($user->unit) }}</td>
                                <td><span class="status-pill {{ $user->is_active ? 'status-published' : 'status-inactive' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td>
                                    <form action="{{ route('roles.update', $user) }}" method="POST" class="row g-2 align-items-end">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="staff_id" value="{{ $user->staff_id }}">
                                        <input type="hidden" name="cas_username" value="{{ $user->cas_username }}">
                                        <div class="col-md-4">
                                            <select name="role" class="form-control" required>
                                                @foreach($roleOptions as $value => $label)
                                                    <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select name="unit" class="form-control" required>
                                                @foreach(['all' => 'All', 'msd' => 'MSD', 'kcdiom' => 'KCDIOM'] as $value => $label)
                                                    <option value="{{ $value }}" @selected($user->unit === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check mt-2">
                                                <input type="checkbox" class="form-check-input" name="is_active" value="1" id="active-{{ $user->id }}" @checked($user->is_active)>
                                                <label class="form-check-label" for="active-{{ $user->id }}">Active</label>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <button class="btn btn-sm btn-warning w-100">Save</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">No users registered yet.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

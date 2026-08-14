@extends('layouts.app')

@section('content')
@php($roleOptions = [
    'system_admin' => 'System Administrator',
    'msd_admin' => 'MSD Administrator',
    'kcdiom_liaison' => 'Organization Liaison',
    'staff_user' => 'Staff User',
])
<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Dashboard</a><span class="material-icons">chevron_right</span><span>Access roles</span></div>
<div class="page-heading"><div><span class="eyebrow">Identity governance</span><h2>Organizations & access</h2><p>Add IIUM organizations, then assign every account to the organization whose documents it manages.</p></div></div>
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div><h5 class="mb-1">Organizations</h5><small class="text-muted">MSD, AIKOL and any additional Kulliyyah, centre, division, institute or office.</small></div>
        <span class="status-pill status-published">{{ $organizations->where('is_active', true)->count() }} active</span>
    </div>
    <div class="card-body">
        <form action="{{ route('organizations.store') }}" method="POST" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-2"><label class="form-label">Code</label><input name="code" class="form-control" placeholder="e.g. KICT" required maxlength="50"></div>
            <div class="col-md-4"><label class="form-label">Organization name</label><input name="name" class="form-control" placeholder="Full official name" required maxlength="180"></div>
            <div class="col-md-2"><label class="form-label">Type</label><select name="organization_type" class="form-control" required>@foreach(['kulliyyah','centre','division','institute','office','other'] as $type)<option value="{{ $type }}">{{ ucfirst($type) }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label">Parent (optional)</label><select name="parent_id" class="form-control"><option value="">None</option>@foreach($organizations as $organization)<option value="{{ $organization->id }}">{{ $organization->code }}</option>@endforeach</select></div>
            <div class="col-md-2"><button class="btn btn-primary w-100"><span class="material-icons me-1">add_business</span>Add organization</button></div>
        </form>
        <div class="table-responsive mt-4">
            <table class="table flow-table align-middle mb-0">
                <thead><tr><th>Code</th><th>Organization</th><th>Type</th><th>Parent</th><th>Status</th><th style="min-width:230px">Actions</th></tr></thead>
                <tbody>
                @forelse($organizations as $organization)
                    <tr>
                            <td><form id="org-update-{{ $organization->id }}" action="{{ route('organizations.update', $organization) }}" method="POST">@csrf @method('PUT')</form><input form="org-update-{{ $organization->id }}" name="code" class="form-control" value="{{ $organization->code }}" required maxlength="50"></td>
                            <td><input form="org-update-{{ $organization->id }}" name="name" class="form-control" value="{{ $organization->name }}" required maxlength="180"></td>
                            <td><select form="org-update-{{ $organization->id }}" name="organization_type" class="form-control">@foreach(['kulliyyah','centre','division','institute','office','other'] as $type)<option value="{{ $type }}" @selected($organization->organization_type === $type)>{{ ucfirst($type) }}</option>@endforeach</select></td>
                            <td><select form="org-update-{{ $organization->id }}" name="parent_id" class="form-control"><option value="">None</option>@foreach($organizations->where('id', '!=', $organization->id) as $parent)<option value="{{ $parent->id }}" @selected($organization->parent_id === $parent->id)>{{ $parent->code }}</option>@endforeach</select></td>
                            <td><div class="form-check"><input form="org-update-{{ $organization->id }}" type="checkbox" class="form-check-input" name="is_active" value="1" id="org-active-{{ $organization->id }}" @checked($organization->is_active)><label class="form-check-label" for="org-active-{{ $organization->id }}">Active</label></div></td>
                            <td><button form="org-update-{{ $organization->id }}" class="btn btn-sm btn-warning"><span class="material-icons align-middle" style="font-size:17px">save</span> Update</button>
                        <form action="{{ route('organizations.destroy', $organization) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete {{ $organization->code }}? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger ms-1" @disabled(in_array(strtoupper($organization->code), ['MSD','KCDIOM'], true))><span class="material-icons align-middle" style="font-size:17px">delete</span> Delete</button>
                        </form></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4">No organizations registered.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Register Access Role</h5>
            </div>
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Staff ID</label>
                        <input type="text" name="staff_id" class="form-control" value="{{ old('staff_id') }}" required maxlength="20">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">CAS Username</label>
                        <input type="text" name="cas_username" class="form-control" value="{{ old('cas_username') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-control" required>
                            @foreach($roleOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Organization</label>
                        <select name="organization_id" class="form-control">
                            <option value="">All organizations (system administrator only)</option>
                            @foreach($organizations->where('is_active', true) as $organization)
                                <option value="{{ $organization->id }}" @selected((string) old('organization_id') === (string) $organization->id)>{{ $organization->code }} — {{ $organization->name }}</option>
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
                    <button class="btn btn-primary px-4">Create User Access</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-12">
        <div class="card mt-3">
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
                            <th>Organization</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td><form id="user-update-{{ $user->id }}" action="{{ route('roles.update', $user) }}" method="POST">@csrf @method('PUT')</form><input form="user-update-{{ $user->id }}" name="name" class="form-control form-control-sm" value="{{ $user->name }}" required></td>
                                <td><input form="user-update-{{ $user->id }}" type="email" name="email" class="form-control form-control-sm" value="{{ $user->email }}" required></td>
                                <td><input form="user-update-{{ $user->id }}" name="staff_id" class="form-control form-control-sm mb-1" value="{{ $user->staff_id }}" placeholder="Staff ID" required><input form="user-update-{{ $user->id }}" name="cas_username" class="form-control form-control-sm" value="{{ $user->cas_username }}" placeholder="CAS username" required></td>
                                <td><select form="user-update-{{ $user->id }}" name="role" class="form-control form-control-sm" required>@foreach($roleOptions as $value => $label)<option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>@endforeach</select></td>
                                <td><select form="user-update-{{ $user->id }}" name="organization_id" class="form-control form-control-sm"><option value="">All</option>@foreach($organizations->where('is_active', true) as $organization)<option value="{{ $organization->id }}" @selected($user->organization_id === $organization->id)>{{ $organization->code }}</option>@endforeach</select></td>
                                <td><div class="form-check"><input form="user-update-{{ $user->id }}" type="checkbox" class="form-check-input" name="is_active" value="1" id="active-{{ $user->id }}" @checked($user->is_active)><label class="form-check-label" for="active-{{ $user->id }}">Active</label></div></td>
                                <td class="text-nowrap"><button form="user-update-{{ $user->id }}" class="btn btn-sm btn-warning"><span class="material-icons align-middle" style="font-size:17px">save</span> Save</button><form action="{{ route('roles.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete {{ $user->name }}? This cannot be undone.')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger ms-1" @disabled(auth()->id() === $user->id)><span class="material-icons align-middle" style="font-size:17px">delete</span></button></form></td>
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

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleManagementController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canAdministerAccess(), 403);

        return view('roles.index', [
            'users' => User::with('organization')->orderBy('name')->paginate(15),
            'organizations' => Organization::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canAdministerAccess(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'staff_id' => ['required', 'string', 'max:20', 'unique:users,staff_id'],
            'cas_username' => ['required', 'string', 'max:255', 'unique:users,cas_username'],
            'role' => ['required', 'in:system_admin,msd_admin,kcdiom_liaison,staff_user'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->applyOrganizationScope($data);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['password'] = 'Password123!';

        User::create($data);

        return redirect()->route('roles.index')->with('status', 'User registered with default password: Password123!');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->canAdministerAccess(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'staff_id' => ['required', 'string', 'max:20', Rule::unique('users', 'staff_id')->ignore($user->id)],
            'cas_username' => ['required', 'string', 'max:255', Rule::unique('users', 'cas_username')->ignore($user->id)],
            'role' => ['required', 'in:system_admin,msd_admin,kcdiom_liaison,staff_user'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->applyOrganizationScope($data);

        $willRemainActiveAdmin = $data['role'] === 'system_admin' && (bool) ($data['is_active'] ?? false);
        if ($user->role === 'system_admin' && $user->is_active && ! $willRemainActiveAdmin
            && ! User::query()->whereKeyNot($user->id)->where('role', 'system_admin')->where('is_active', true)->exists()) {
            return back()->withErrors(['role' => 'At least one active system administrator must remain.']);
        }

        if ($request->user()->is($user) && ! $willRemainActiveAdmin) {
            return back()->withErrors(['role' => 'You cannot remove your own active system administrator access.']);
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'staff_id' => $data['staff_id'],
            'cas_username' => $data['cas_username'],
            'role' => $data['role'],
            'unit' => $data['unit'],
            'organization_id' => $data['organization_id'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('roles.index')->with('status', 'User account and access updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->canAdministerAccess(), 403);

        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        if ($user->role === 'system_admin' && $user->is_active
            && ! User::query()->whereKeyNot($user->id)->where('role', 'system_admin')->where('is_active', true)->exists()) {
            return back()->withErrors(['user' => 'The last active system administrator cannot be deleted.']);
        }

        $name = $user->name;
        try {
            $user->delete();
        } catch (QueryException) {
            return back()->withErrors(['user' => 'This user is linked to governed records and cannot be deleted. Deactivate the account instead.']);
        }

        return redirect()->route('roles.index')->with('status', $name.' was deleted successfully.');
    }

    private function applyOrganizationScope(array &$data): void
    {
        if ($data['role'] === 'system_admin') {
            $data['unit'] = 'all';
            $data['organization_id'] = null;
            return;
        }

        $organization = Organization::query()->whereKey($data['organization_id'] ?? 0)->where('is_active', true)->firstOrFail();
        $data['unit'] = strtoupper($organization->code) === 'MSD' ? 'msd' : 'kcdiom';

        if ($data['role'] === 'msd_admin' && strtoupper($organization->code) !== 'MSD') {
            $data['role'] = 'kcdiom_liaison';
        }
    }
}

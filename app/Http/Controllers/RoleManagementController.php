<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleManagementController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canAdministerAccess(), 403);

        return view('roles.index', [
            'users' => User::orderBy('name')->paginate(15),
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
            'role' => ['required', 'in:system_admin,policy_manager,staff_user'],
            'unit' => ['required', 'in:all,msd,kcdiom'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($data['role'] === 'staff_user' || $data['role'] === 'system_admin') {
            $data['unit'] = 'all';
        }

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['password'] = 'Password123!';

        User::create($data);

        return redirect()->route('roles.index')->with('status', 'User registered with default password: Password123!');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->canAdministerAccess(), 403);

        $data = $request->validate([
            'role' => ['required', 'in:system_admin,policy_manager,staff_user'],
            'unit' => ['required', 'in:all,msd,kcdiom'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($data['role'] === 'staff_user' || $data['role'] === 'system_admin') {
            $data['unit'] = 'all';
        }

        $user->update([
            'role' => $data['role'],
            'unit' => $data['unit'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('roles.index')->with('status', 'User role updated successfully.');
    }
}

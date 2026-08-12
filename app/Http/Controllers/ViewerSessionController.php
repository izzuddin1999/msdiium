<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ViewerSessionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if ($request->input('user_id') === 'public') {
            $request->session()->forget('viewer_user_id');
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('public-portal');
        }

        $data = $request->validate([
            'user_id' => ['required', Rule::exists(User::class, 'id')],
            'redirect_to' => ['nullable', Rule::in(['dashboard', 'public'])],
        ]);

        $viewer = User::query()
            ->whereKey($data['user_id'])
            ->where('is_active', true)
            ->firstOrFail();

        Auth::guard('web')->login($viewer);
        $request->session()->regenerate();
        $request->session()->put('viewer_user_id', $viewer->id);

        if (($data['redirect_to'] ?? null) === 'dashboard') {
            return redirect()->route('public-portal')
                ->with('status', 'Welcome back, '.$viewer->name.'.');
        }

        if (($data['redirect_to'] ?? null) === 'public') {
            return redirect()->route('public-portal')
                ->with('status', 'Signed in as '.$viewer->name.'.');
        }

        return back()->with('status', 'Viewer switched to '.$viewer->name.'.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'redirect_to' => ['nullable', Rule::in(['dashboard', 'public'])],
        ]);

        $request->session()->forget('viewer_user_id');
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (($data['redirect_to'] ?? null) === 'public') {
            return redirect()->route('public-portal')
                ->with('status', 'You have signed out of the Staff Portal.');
        }

        return redirect()->route('public-portal')
            ->with('status', 'Viewer reset to guest mode.');
    }
}

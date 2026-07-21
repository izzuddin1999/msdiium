<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ViewerSessionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $viewer = User::query()
            ->whereKey($data['user_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $request->session()->put('viewer_user_id', $viewer->id);
        Auth::guard('web')->login($viewer);

        return back()->with('status', 'Viewer switched to '.$viewer->name.'.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget('viewer_user_id');
        Auth::guard('web')->logout();

        return back()->with('status', 'Viewer reset to guest mode.');
    }
}
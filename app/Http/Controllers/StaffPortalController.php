<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffPortalController extends Controller
{
    public function create(): RedirectResponse
    {
        return redirect()->route('public-portal');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('public-portal')->with('status', 'You have been logged out successfully.');
    }
}

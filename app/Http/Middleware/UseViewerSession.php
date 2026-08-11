<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UseViewerSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('viewer_user_id')) {
            return $next($request);
        }

        $viewerId = $request->session()->get('viewer_user_id');
        $viewer = User::query()
            ->whereKey($viewerId)
            ->where('is_active', true)
            ->first();

        if (! $viewer) {
            $request->session()->forget('viewer_user_id');
            Auth::guard('web')->logout();

            return $next($request);
        }

        Auth::guard('web')->login($viewer);

        return $next($request);
    }
}
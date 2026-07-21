<?php

namespace App\Http\Controllers;

use App\Models\PolicyDocument;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $viewer = $request->user();
        $visible = PolicyDocument::query()->visibleTo($viewer);

        return view('dashboard', [
            'viewer' => $viewer,
            'canManageDocuments' => $viewer?->canManagePolicies() ?? false,
            'metrics' => [
                'total' => (clone $visible)->count(),
                'published' => (clone $visible)->where('status', 'published')->count(),
                'draft' => (clone $visible)->where('status', 'draft')->count(),
                'circulars' => (clone $visible)->where('is_circular', true)->count(),
            ],
            'recentDocuments' => (clone $visible)->with(['creator', 'subtopic'])->latest()->limit(6)->get(),
            'expiringDocuments' => (clone $visible)
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', today())
                ->orderBy('expiry_date')
                ->limit(5)
                ->get(),
        ]);
    }
}

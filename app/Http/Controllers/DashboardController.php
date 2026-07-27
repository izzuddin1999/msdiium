<?php

namespace App\Http\Controllers;

use App\Models\DocumentActivityLog;
use App\Models\PolicyDocument;
use App\Models\TopicCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $viewer = $request->user();
        $visible = PolicyDocument::query()->visibleTo($viewer);
        $isSystemAdministrator = $viewer?->canAdministerAccess() ?? false;
        $isKcdiomLiaison = $viewer?->isKcdiomLiaison() ?? false;
        $isStaffViewer = ! ($viewer?->canManagePolicies() ?? false);
        $metrics = [
            'total' => (clone $visible)->count(),
            'published' => (clone $visible)->where('status', 'published')->count(),
            'draft' => (clone $visible)->where('status', 'draft')->count(),
            'circulars' => (clone $visible)->where('is_circular', true)->count(),
            'expiring' => (clone $visible)
                ->whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [today(), today()->addDays(30)])
                ->count(),
        ];

        return view('dashboard', [
            'viewer' => $viewer,
            'canManageDocuments' => $viewer?->canManagePolicies() ?? false,
            'isSystemAdministrator' => $isSystemAdministrator,
            'isKcdiomLiaison' => $isKcdiomLiaison,
            'isStaffViewer' => $isStaffViewer,
            'metrics' => $metrics,
            'recentDocuments' => (clone $visible)->with(['creator', 'subtopic.mainTopic'])->latest('updated_at')->limit(6)->get(),
            'expiringDocuments' => (clone $visible)
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', today())
                ->orderBy('expiry_date')
                ->limit(5)
                ->get(),
            'topicOverview' => $isSystemAdministrator
                ? TopicCategory::query()
                    ->where('is_active', true)
                    ->with([
                        'subtopics' => fn ($query) => $query
                            ->where('is_active', true)
                            ->withCount(['details' => fn ($detailQuery) => $detailQuery->where('is_active', true)])
                            ->orderBy('name'),
                    ])
                    ->withCount('documents')
                    ->orderBy('name')
                    ->get()
                : collect(),
            'recentActivity' => $isSystemAdministrator
                ? DocumentActivityLog::query()
                    ->with(['document:id,title', 'user:id,name'])
                    ->latest()
                    ->limit(5)
                    ->get()
                : collect(),
            'staffUnitCards' => $isStaffViewer
                ? collect([
                    ['code' => 'MSD', 'name' => 'Management Services Division', 'unit' => 'msd', 'icon' => 'account_balance'],
                    ['code' => 'OTHER UNITS', 'name' => 'Other Kulliyyah, Centres, Divisions, Institutes & Offices', 'unit' => 'kcdiom', 'icon' => 'domain'],
                ])->map(function (array $unit) use ($visible): array {
                    $publicDocuments = (clone $visible)
                        ->where('owner_unit', $unit['unit'])
                        ->where('status', 'published');

                    return $unit + [
                        'documents' => (clone $publicDocuments)->count(),
                        'circulars' => (clone $publicDocuments)->where('is_circular', true)->count(),
                        'latest' => (clone $publicDocuments)->latest('published_at')->value('title'),
                    ];
                })
                : collect(),
        ]);
    }
}

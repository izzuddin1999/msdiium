<?php

namespace App\Http\Controllers;

use App\Models\PolicyDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportingDashboardController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canManagePolicies(), 403);

        $base = PolicyDocument::query();
        $statusCounts = $this->countsBy(clone $base, 'status');
        $unitCounts = $this->countsBy(clone $base, 'owner_unit');
        $typeCounts = $this->countsBy(clone $base, 'document_type');
        $topicCounts = PolicyDocument::query()
            ->selectRaw("COALESCE(topic_category, 'uncategorized') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'label');

        return view('reports.dashboard', [
            'metrics' => [
                'documents' => (clone $base)->count(),
                'published' => (clone $base)->where('status', 'published')->count(),
                'versions' => (clone $base)->whereNotNull('parent_document_id')->count(),
                'expiring' => (clone $base)
                    ->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '>=', today())
                    ->whereDate('expiry_date', '<=', today()->addDays(90))
                    ->count(),
            ],
            'statusCounts' => $statusCounts,
            'unitCounts' => $unitCounts,
            'typeCounts' => $typeCounts,
            'topicCounts' => $topicCounts,
            'statusMaximum' => max(1, (int) $statusCounts->max()),
            'unitMaximum' => max(1, (int) $unitCounts->max()),
            'typeMaximum' => max(1, (int) $typeCounts->max()),
            'topicMaximum' => max(1, (int) $topicCounts->max()),
            'recentPublications' => PolicyDocument::with('publisher')
                ->where('status', 'published')
                ->latest('published_at')
                ->limit(6)
                ->get(),
        ]);
    }

    private function countsBy($query, string $column): Collection
    {
        return $query->selectRaw("{$column} as label, COUNT(*) as total")
            ->groupBy($column)
            ->orderByDesc('total')
            ->pluck('total', 'label');
    }
}

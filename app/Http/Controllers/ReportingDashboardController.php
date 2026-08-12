<?php

namespace App\Http\Controllers;

use App\Models\PolicyDocument;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportingDashboardController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canManagePolicies(), 403);
        $organization = $request->user()->unit === 'kcdiom' ? 'kcdiom' : 'msd';

        $base = PolicyDocument::query()->where('owner_unit', $organization);
        $documentReport = PolicyDocument::query()
            ->where('owner_unit', $organization)
            ->whereNotExists(function ($newer): void {
                $newer->selectRaw('1')
                    ->from((new PolicyDocument)->getTable().' as newer')
                    ->whereRaw('COALESCE(newer.parent_document_id, newer.id) = COALESCE(policy_documents.parent_document_id, policy_documents.id)')
                    ->whereColumn('newer.version_number', '>', 'policy_documents.version_number');
            })
            ->orderBy('title')
            ->paginate(10, ['*'], 'report_page')
            ->withQueryString();

        return view('reports.dashboard', [
            'metrics' => [
                'total' => (clone $base)->count(),
                'active' => (clone $base)->where('status', 'published')->count(),
                'archived' => (clone $base)->where('status', 'archived')->count(),
            ],
            'documentReport' => $documentReport,
            'organizationSubmissionCount' => (clone $base)->count(),
            'organization' => $organization,
        ]);
    }
}

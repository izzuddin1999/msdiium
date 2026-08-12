<?php

namespace App\Http\Controllers;

use App\Models\DocumentActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canAdministerAccess(), 403);

        $query = $this->filteredQuery($request);

        return view('document_activity_logs.index', [
            'logs' => $query->paginate(5)->withQueryString(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
            'actions' => DocumentActivityLog::query()->distinct()->orderBy('action')->pluck('action'),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->canAdministerAccess(), 403);
        $logs = $this->filteredQuery($request)->get();

        return response()->streamDownload(function () use ($logs): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, ['Date', 'Time', 'Document', 'Reference', 'Action', 'Actor', 'Role', 'Changed fields', 'IP address']);
            foreach ($logs as $log) {
                fputcsv($output, [
                    $log->created_at->format('d M Y'), $log->created_at->format('H:i:s'),
                    $log->document?->title ?? 'Deleted document', $log->document?->reference_number,
                    ucfirst($log->action), $log->user?->name ?? 'System', $log->user?->actorLabel() ?? 'System',
                    implode(', ', array_keys($log->new_values ?? [])), $log->ip_address,
                ]);
            }
            fclose($output);
        }, 'document-audit-log-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function filteredQuery(Request $request)
    {
        $query = DocumentActivityLog::with(['document', 'user'])->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->string('action'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->whereHas('document', fn ($documentQuery) => $documentQuery
                ->where('title', 'like', $term)
                ->orWhere('reference_number', 'like', $term));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        return $query;
    }
}

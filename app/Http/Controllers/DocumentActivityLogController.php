<?php

namespace App\Http\Controllers;

use App\Models\DocumentActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canAdministerAccess(), 403);

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

        return view('document_activity_logs.index', [
            'logs' => $query->paginate(30)->withQueryString(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
            'actions' => DocumentActivityLog::query()->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}

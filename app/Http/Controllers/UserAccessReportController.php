<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserAccessReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeManager($request);

        return view('reports.user_access', [
            'users' => $this->filteredQuery($request)->paginate(30)->withQueryString(),
            'summary' => [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
                'managers' => User::whereIn('role', ['system_admin', 'msd_admin', 'kcdiom_liaison'])->count(),
                'staff' => User::where('role', 'staff_user')->count(),
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorizeManager($request);
        $users = $this->filteredQuery($request)->get();

        return response()->streamDownload(function () use ($users): void {
            $stream = fopen('php://output', 'wb');
            fputcsv($stream, ['Staff ID', 'CAS Username', 'Name', 'Email', 'Role', 'Unit', 'Status', 'Last CAS Sync']);

            foreach ($users as $user) {
                fputcsv($stream, [
                    $user->staff_id,
                    $user->cas_username,
                    $user->name,
                    $user->email,
                    $user->role,
                    $user->unit,
                    $user->is_active ? 'Active' : 'Inactive',
                    $user->last_cas_sync_at?->toDateTimeString(),
                ]);
            }

            fclose($stream);
        }, 'hr19-user-access-report-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = User::query()->orderBy('name');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(fn (Builder $builder) => $builder
                ->where('name', 'like', $term)
                ->orWhere('email', 'like', $term)
                ->orWhere('staff_id', 'like', $term)
                ->orWhere('cas_username', 'like', $term));
        }

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        if ($request->filled('unit')) {
            $query->where('unit', $request->string('unit'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status')->toString() === 'active');
        }

        return $query;
    }

    private function authorizeManager(Request $request): void
    {
        abort_unless($request->user()?->canAdministerAccess(), Response::HTTP_FORBIDDEN);
    }
}

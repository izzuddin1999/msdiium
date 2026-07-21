<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DirectorySyncController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canAdministerAccess(), 403);

        return view('directory_sync.index', [
            'runs' => DB::table('directory_sync_runs')
                ->leftJoin('users', 'directory_sync_runs.initiated_by', '=', 'users.id')
                ->select('directory_sync_runs.*', 'users.name as initiator_name')
                ->latest('directory_sync_runs.created_at')
                ->limit(20)
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canAdministerAccess(), 403);
        $data = $request->validate(['csv_data' => ['required', 'string', 'max:200000']]);
        $lines = preg_split('/\r\n|\r|\n/', trim($data['csv_data']));
        $header = array_map(fn ($value) => str($value)->trim()->lower()->snake()->toString(), str_getcsv(array_shift($lines)));
        $required = ['staff_id', 'cas_username', 'name', 'email', 'unit'];

        if (array_diff($required, $header)) {
            return back()->withInput()->withErrors(['csv_data' => 'CSV header must contain: '.implode(', ', $required)]);
        }

        $created = 0;
        $updated = 0;
        $rejected = 0;
        $errors = [];

        DB::transaction(function () use ($lines, $header, &$created, &$updated, &$rejected, &$errors): void {
            foreach ($lines as $index => $line) {
                if (trim($line) === '') {
                    continue;
                }

                $values = str_getcsv($line);
                if (count($values) !== count($header)) {
                    $rejected++;
                    $errors[] = 'Row '.($index + 2).': column count does not match the header.';
                    continue;
                }

                $row = array_map('trim', array_combine($header, $values));
                $unit = strtolower($row['unit']);
                if (! filter_var($row['email'], FILTER_VALIDATE_EMAIL) || ! in_array($unit, ['all', 'msd', 'kcdiom'], true)
                    || $row['staff_id'] === '' || $row['cas_username'] === '' || $row['name'] === '') {
                    $rejected++;
                    $errors[] = 'Row '.($index + 2).': invalid identity, email, or unit.';
                    continue;
                }

                $user = User::where('staff_id', $row['staff_id'])->orWhere('cas_username', $row['cas_username'])->first();
                $attributes = [
                    'staff_id' => $row['staff_id'],
                    'cas_username' => $row['cas_username'],
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'unit' => $unit,
                    'is_active' => true,
                    'last_cas_sync_at' => now(),
                ];

                if ($user) {
                    $user->update($attributes);
                    $updated++;
                } else {
                    User::create($attributes + ['role' => 'staff_user', 'password' => Str::random(40)]);
                    $created++;
                }
            }
        });

        DB::table('directory_sync_runs')->insert([
            'initiated_by' => $request->user()->id,
            'source' => 'huris_csv',
            'rows_received' => count(array_filter($lines, fn ($line) => trim($line) !== '')),
            'rows_created' => $created,
            'rows_updated' => $updated,
            'rows_rejected' => $rejected,
            'errors' => $errors === [] ? null : json_encode(array_slice($errors, 0, 50)),
            'completed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('directory-sync.index')->with('status', "Directory sync completed: {$created} created, {$updated} updated, {$rejected} rejected.");
    }
}

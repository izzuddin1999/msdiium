<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canAdministerAccess(), 403);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:organizations,code'],
            'name' => ['required', 'string', 'max:180'],
            'organization_type' => ['required', 'string', Rule::in(['kulliyyah', 'centre', 'division', 'institute', 'office', 'other'])],
            'parent_id' => ['nullable', 'integer', 'exists:organizations,id'],
        ]);

        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = true;
        Organization::create($data);

        return back()->with('status', 'Organization added successfully. It is now available for user and document assignment.');
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()?->canAdministerAccess(), 403);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('organizations', 'code')->ignore($organization->id)],
            'name' => ['required', 'string', 'max:180'],
            'organization_type' => ['required', 'string', Rule::in(['kulliyyah', 'centre', 'division', 'institute', 'office', 'other'])],
            'parent_id' => ['nullable', 'integer', Rule::exists('organizations', 'id')->where(fn ($query) => $query->where('id', '!=', $organization->id))],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $organization->update($data);

        return back()->with('status', 'Organization updated successfully.');
    }

    public function destroy(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()?->canAdministerAccess(), 403);

        if (in_array(strtoupper($organization->code), ['MSD', 'KCDIOM'], true)) {
            return back()->withErrors(['organization' => 'Core MSD and AIKOL organizations cannot be deleted. Deactivate them instead.']);
        }

        $linkedTables = ['users', 'policy_documents', 'topic_categories', 'topic_subtopics', 'lookup_values', 'form_templates', 'organization_profiles'];
        $linkedRecords = collect($linkedTables)
            ->filter(fn (string $table) => Schema::hasTable($table) && Schema::hasColumn($table, 'organization_id'))
            ->sum(fn (string $table) => DB::table($table)->where('organization_id', $organization->id)->count());

        if ($organization->children()->exists() || $linkedRecords > 0) {
            return back()->withErrors(['organization' => 'This organization is still linked to users, documents, topics, settings, or child organizations. Reassign those records or deactivate it instead.']);
        }

        $code = $organization->code;
        $organization->delete();

        return back()->with('status', $code.' organization deleted successfully.');
    }
}

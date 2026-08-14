<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use App\Models\Organization;
use App\Models\OrganizationProfile;
use App\Models\PolicyDocument;
use App\Models\TopicCategory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class OrganizationProfileController extends Controller
{
    public function show(Request $request): View
    {
        abort_unless($request->user()?->canManagePolicies(), 403);
        $organization = $this->organization($request);
        $profile = $this->profileFor($organization);

        return view('organization_profiles.show', [
            'profile' => $profile,
            'organization' => strtolower($organization->code),
            'selectedOrganization' => $organization,
            'organizations' => $request->user()?->isSystemAdmin()
                ? Organization::query()->where('is_active', true)->orderBy('name')->get()
                : collect([$organization]),
            'canEdit' => true,
            'metrics' => [
                'documents' => PolicyDocument::where('organization_id', $organization->id)->count(),
                'active' => PolicyDocument::where('organization_id', $organization->id)->where('status', 'published')->count(),
                'topics' => TopicCategory::where('organization_id', $organization->id)->count(),
                'templates' => Schema::hasTable('form_templates')
                    ? FormTemplate::where('organization_id', $organization->id)->count()
                    : 0,
                'users' => User::where('organization_id', $organization->id)->where('is_active', true)->count(),
            ],
            'managers' => User::query()
                ->where('organization_id', $organization->id)
                ->where('is_active', true)
                ->whereIn('role', ['system_admin', 'msd_admin', 'kcdiom_liaison'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canManagePolicies(), 403);
        $profile = $this->profileFor($this->organization($request));
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'short_name' => ['required', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:2000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'office_location' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
        ]);
        $profile->update($data);

        return redirect()->route('organization-profile.show', $request->user()?->isSystemAdmin()
            ? ['organization' => $profile->organization_id]
            : [])->with('status', strtoupper($profile->code).' organization profile updated successfully.');
    }

    private function organization(Request $request): Organization
    {
        if ($request->user()?->isSystemAdmin() && $request->filled('organization')) {
            return Organization::query()->where('is_active', true)->findOrFail((int) $request->input('organization'));
        }

        if ($request->user()?->organization_id) {
            return Organization::query()->findOrFail($request->user()->organization_id);
        }

        $code = $request->user()?->unit === 'kcdiom' ? 'KCDIOM' : 'MSD';

        return Organization::query()->where('code', $code)->firstOrFail();
    }

    private function profileFor(Organization $organization): OrganizationProfile
    {
        return OrganizationProfile::query()->firstOrCreate(
            ['organization_id' => $organization->id],
            [
                'code' => strtolower($organization->code),
                'name' => $organization->name,
                'short_name' => strtoupper($organization->code === 'KCDIOM' ? 'AIKOL' : $organization->code),
                'description' => null,
                'is_active' => $organization->is_active,
            ]
        );
    }
}

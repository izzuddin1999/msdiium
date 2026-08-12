<?php

namespace App\Http\Controllers;

use App\Models\LookupValue;
use App\Models\Organization;
use App\Models\PolicyDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LookupValueController extends Controller
{
    private const ALLOWED_TYPES = ['DOCUMENT_TYPE', 'DOCUMENT_STATUS'];
    private const DOCUMENT_STATUSES = ['draft', 'published', 'inactive', 'superseded', 'archived'];

    public function index(Request $request): View
    {
        abort_unless($request->user()?->canManagePolicies(), 403);
        $organization = $this->organization($request);

        return view('lookup_values.index', [
            'lookupValues' => LookupValue::where('owner_unit', $organization)->orderBy('type')->orderBy('sort_order')->orderBy('description')->paginate(25),
            'allowedTypes' => self::ALLOWED_TYPES,
            'organization' => $organization,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canManagePolicies(), 403);

        $this->normalizeSubmittedCode($request);
        $data = $request->validate($this->rules());
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['owner_unit'] = $this->organization($request);
        $data['organization_id'] = $request->user()?->organization_id
            ?: Organization::idForLegacyUnit($data['owner_unit']);

        LookupValue::create($data);

        return redirect()->route('lookup-values.index')->with('status', 'Lookup value created successfully.');
    }

    public function update(Request $request, LookupValue $lookupValue): RedirectResponse
    {
        abort_unless($request->user()?->canManagePolicies(), 403);
        $this->ensureOrganizationAccess($request, $lookupValue);

        $this->normalizeSubmittedCode($request);
        $data = $request->validate($this->rules($lookupValue));
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $lookupValue->update($data);

        return redirect()->route('lookup-values.index')->with('status', 'Lookup value updated successfully.');
    }

    public function destroy(Request $request, LookupValue $lookupValue): RedirectResponse
    {
        abort_unless($request->user()?->canManagePolicies(), 403);
        $this->ensureOrganizationAccess($request, $lookupValue);

        $documentColumn = match ($lookupValue->type) {
            'DOCUMENT_TYPE' => 'document_type',
            'DOCUMENT_STATUS' => 'status',
            default => null,
        };

        if ($documentColumn && PolicyDocument::where($documentColumn, $lookupValue->code)->exists()) {
            return redirect()->route('lookup-values.index')->withErrors([
                'lookup_value' => 'This lookup value is used by existing documents. Mark it inactive instead of deleting it.',
            ]);
        }

        $lookupValue->delete();

        return redirect()->route('lookup-values.index')->with('status', 'Lookup value deleted successfully.');
    }

    private function rules(?LookupValue $lookupValue = null): array
    {
        return [
            'type' => ['required', Rule::in(self::ALLOWED_TYPES)],
            'code' => [
                'required', 'string', 'max:50',
                Rule::when(request('type') === 'DOCUMENT_STATUS', Rule::in(self::DOCUMENT_STATUSES)),
                Rule::unique('lookup_values')->where(fn ($query) => $query
                    ->where('owner_unit', $this->organization(request()))
                    ->where('type', request('type')))->ignore($lookupValue),
            ],
            'description' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function normalizeSubmittedCode(Request $request): void
    {
        $code = str((string) $request->input('code'))->lower()->snake()->toString();

        // Accept the common misspelling while storing the enterprise-standard term.
        $request->merge(['code' => $code === 'superceded' ? 'superseded' : $code]);
    }

    private function organization(Request $request): string
    {
        return $request->user()?->unit === 'kcdiom' ? 'kcdiom' : 'msd';
    }

    private function ensureOrganizationAccess(Request $request, LookupValue $lookupValue): void
    {
        abort_unless($lookupValue->owner_unit === $this->organization($request), 404);
    }
}

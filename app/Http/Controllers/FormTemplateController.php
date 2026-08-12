<?php

namespace App\Http\Controllers;

use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\LookupValue;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FormTemplateController extends Controller
{
    private const FIELD_TYPES = ['text', 'textarea', 'paragraph', 'heading', 'number', 'date', 'email', 'file', 'select', 'radio', 'checkbox', 'multi_select'];
    private const DATA_SOURCES = ['' => 'Manual options', 'users' => 'Active users', 'main_topics' => 'Main Topic reference', 'subtopics' => 'Sub Topic reference', 'departments' => 'Department master data', 'user_roles' => 'User Role master data', 'access_scopes' => 'Document access scopes', 'document_types' => 'Document types', 'document_statuses' => 'Document statuses'];

    public function index(Request $request): View
    {
        $this->authorizeManager($request);
        $query = FormTemplate::with(['fields', 'creator'])
            ->where('owner_unit', $this->organization($request))
            ->latest();

        return view('form_templates.index', [
            'templates' => $query->get(),
            'documentTypes' => $this->documentTypes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManager($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'document_type' => ['nullable', 'string', 'max:50'],
            'owner_unit' => ['required', 'in:msd,kcdiom'],
            'columns' => ['required', 'integer', 'between:1,3'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['owner_unit'] = $this->organization($request);
        $data['organization_id'] = $request->user()?->organization_id
            ?: Organization::idForLegacyUnit($data['owner_unit']);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['created_by'] = $request->user()->id;
        $template = FormTemplate::create($data);
        $this->addStandardDocumentFields($template);

        return redirect()->route('form-templates.edit', $template)->with('status', 'Template created. Add its fields below.');
    }

    public function edit(Request $request, FormTemplate $formTemplate): View
    {
        $this->authorizeTemplate($request, $formTemplate);
        return view('form_templates.edit', [
            'template' => $formTemplate->load('fields'),
            'fieldTypes' => self::FIELD_TYPES,
            'dataSources' => $this->dataSources(),
            'documentTypes' => $this->documentTypes(),
            'systemBindings' => $this->systemBindings(),
        ]);
    }

    public function update(Request $request, FormTemplate $formTemplate): RedirectResponse
    {
        $this->authorizeTemplate($request, $formTemplate);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'], 'description' => ['nullable', 'string', 'max:1000'],
            'document_type' => ['nullable', 'string', 'max:50'], 'owner_unit' => ['required', 'in:msd,kcdiom'],
            'columns' => ['required', 'integer', 'between:1,3'], 'is_active' => ['nullable', 'boolean'],
        ]);
        $data['owner_unit'] = $this->organization($request);
        $data['organization_id'] = $request->user()?->organization_id
            ?: Organization::idForLegacyUnit($data['owner_unit']);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $formTemplate->update($data);
        return back()->with('status', 'Template settings updated.');
    }

    public function destroy(Request $request, FormTemplate $formTemplate): RedirectResponse
    {
        $this->authorizeTemplate($request, $formTemplate);
        if ($formTemplate->responses()->exists()) {
            return back()->withErrors(['template' => 'This template has submitted document data. Deactivate it to preserve the audit record.']);
        }
        if ($formTemplate->newQuery()->whereKey($formTemplate)->whereHas('fields')->exists() && $formTemplate->is_active) {
            return back()->withErrors(['template' => 'Deactivate the template before deleting it.']);
        }
        $formTemplate->delete();
        return redirect()->route('form-templates.index')->with('status', 'Template deleted.');
    }

    public function storeField(Request $request, FormTemplate $formTemplate): RedirectResponse
    {
        $this->authorizeTemplate($request, $formTemplate);
        $data = $this->validateField($request, $formTemplate);
        $data['form_template_id'] = $formTemplate->id;
        $data['sort_order'] = $data['sort_order'] ?? (($formTemplate->fields()->max('sort_order') ?? 0) + 10);
        FormField::create($data);
        return back()->with('status', 'Field added to the template.');
    }

    public function quickAddField(Request $request, FormTemplate $formTemplate): JsonResponse
    {
        $this->authorizeTemplate($request, $formTemplate);
        $data = $request->validate(['type' => ['required', Rule::in(self::FIELD_TYPES)]]);
        $type = $data['type'];
        $labels = ['text'=>'Text Field','textarea'=>'Long Text','paragraph'=>'Text Block','heading'=>'Section Heading','number'=>'Number Field','date'=>'Date Field','email'=>'Email Field','file'=>'File Upload','select'=>'Dropdown','radio'=>'Radio Buttons','checkbox'=>'Checkbox','multi_select'=>'Multi Select'];
        $base = Str::snake($labels[$type]);
        $name = $base; $suffix = 2;
        while ($formTemplate->fields()->where('name', $name)->exists()) $name = $base.'_'.$suffix++;
        $choiceOptions = in_array($type, ['select','radio','multi_select'], true)
            ? [['value'=>'option_1','label'=>'Option 1'],['value'=>'option_2','label'=>'Option 2']]
            : [];
        $field = $formTemplate->fields()->create([
            'label' => $labels[$type], 'name' => $name, 'type' => $type, 'section' => 'Form content',
            'width' => in_array($type, ['textarea','paragraph','heading','file'], true) ? min(3, $formTemplate->columns) : 1,
            'is_required' => false, 'options' => $choiceOptions,
            'default_value' => $type === 'paragraph' ? 'Add your text here.' : ($type === 'heading' ? 'New section' : null),
            'sort_order' => ($formTemplate->fields()->max('sort_order') ?? 0) + 10,
        ]);

        return response()->json(['message' => $field->label.' added.', 'field_id' => $field->id]);
    }

    public function updateField(Request $request, FormTemplate $formTemplate, FormField $formField): RedirectResponse|JsonResponse
    {
        $this->authorizeTemplate($request, $formTemplate);
        abort_unless($formField->form_template_id === $formTemplate->id, 404);
        $data = $this->validateField($request, $formTemplate, $formField);
        if (in_array($formField->binding, ['document_type','status','title','owner_unit','created_by','access_scope','content'], true) && ($data['binding'] ?? null) !== $formField->binding) {
            if ($request->expectsJson()) return response()->json(['message' => $formField->label.' must remain bound to its required document property.'], 422);
            return back()->withErrors(['field' => $formField->label.' must remain bound to its required document property.']);
        }
        $formField->update($data);
        if ($request->expectsJson()) return response()->json(['message' => 'Field updated.']);
        return back()->with('status', 'Field updated.');
    }

    public function destroyField(Request $request, FormTemplate $formTemplate, FormField $formField): RedirectResponse|JsonResponse
    {
        $this->authorizeTemplate($request, $formTemplate);
        abort_unless($formField->form_template_id === $formTemplate->id, 404);
        if ($formField->binding && in_array($formField->binding, ['document_type','status','title','owner_unit','created_by','access_scope','content'], true)) {
            if ($request->expectsJson()) return response()->json(['message' => $formField->label.' is required and cannot be removed.'], 422);
            return back()->withErrors(['field' => $formField->label.' is an enterprise-required document field and cannot be removed.']);
        }
        $formField->delete();
        if ($request->expectsJson()) return response()->json(['message' => 'Field removed.']);
        return back()->with('status', 'Field removed.');
    }

    public function reorderFields(Request $request, FormTemplate $formTemplate): JsonResponse
    {
        $this->authorizeTemplate($request, $formTemplate);
        $data = $request->validate(['field_ids' => ['required', 'array'], 'field_ids.*' => ['required', 'integer']]);
        $submitted = array_map('intval', $data['field_ids']);
        $existing = $formTemplate->fields()->pluck('id')->map(fn ($id) => (int) $id)->all();
        abort_unless(count($submitted) === count($existing) && array_diff($submitted, $existing) === [] && array_diff($existing, $submitted) === [], 422, 'The field order is invalid.');

        DB::transaction(function () use ($submitted, $formTemplate): void {
            foreach ($submitted as $index => $fieldId) {
                $formTemplate->fields()->whereKey($fieldId)->update(['sort_order' => ($index + 1) * 10]);
            }
        });

        return response()->json(['message' => 'Field order saved.', 'field_ids' => $submitted]);
    }

    private function validateField(Request $request, FormTemplate $template, ?FormField $field = null): array
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:150'],
            'name' => ['required', 'regex:/^[a-z][a-z0-9_]*$/', 'max:80', Rule::unique('form_fields')->where('form_template_id', $template->id)->ignore($field)],
            'binding' => ['nullable', Rule::in(array_keys($this->systemBindings())), Rule::unique('form_fields')->where('form_template_id', $template->id)->ignore($field)],
            'type' => ['required', Rule::in(self::FIELD_TYPES)], 'section' => ['required', 'string', 'max:100'],
            'width' => ['required', 'integer', 'between:1,3'], 'is_required' => ['nullable', 'boolean'],
            'placeholder' => ['nullable', 'string', 'max:255'], 'help_text' => ['nullable', 'string', 'max:500'],
            'default_value' => ['nullable', 'string', 'max:1000'], 'options_text' => ['nullable', 'string', 'max:5000'],
            'data_source' => ['nullable', Rule::in(array_keys($this->dataSources()))],
            'min' => ['nullable', 'numeric'], 'max' => ['nullable', 'numeric'], 'pattern' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['is_required'] = (bool) ($data['is_required'] ?? false);
        $data['options'] = collect(preg_split('/\r\n|\r|\n/', $data['options_text'] ?? ''))->filter()->map(function ($line) {
            [$value, $label] = array_pad(explode('|', $line, 2), 2, null); return ['value' => trim($value), 'label' => trim($label ?: $value)];
        })->values()->all();
        $data['validation'] = array_filter(['min' => $data['min'] ?? null, 'max' => $data['max'] ?? null, 'pattern' => $data['pattern'] ?? null], fn ($v) => $v !== null && $v !== '');
        unset($data['options_text'], $data['min'], $data['max'], $data['pattern']);
        return $data;
    }

    private function authorizeManager(Request $request): void { abort_unless($request->user()?->canManagePolicies(), 403); }
    private function authorizeTemplate(Request $request, FormTemplate $template): void
    {
        $this->authorizeManager($request);
        abort_unless($template->owner_unit === $this->organization($request), 404);
    }
    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'template'; $slug = $base; $i = 2;
        while (FormTemplate::where('slug', $slug)->exists()) $slug = $base.'-'.$i++;
        return $slug;
    }
    private function documentTypes() { return LookupValue::where('type', 'DOCUMENT_TYPE')->where('owner_unit', $this->organization(request()))->where('is_active', true)->orderBy('sort_order')->pluck('description', 'code')->whenEmpty(fn () => collect(['policy' => 'Policy', 'guideline' => 'Guideline', 'circular' => 'Circular'])); }
    private function dataSources(): array
    {
        $sources = self::DATA_SOURCES;
        LookupValue::query()->where('owner_unit', $this->organization(request()))->where('is_active', true)->distinct()->orderBy('type')->pluck('type')->each(function ($type) use (&$sources): void {
            $sources['lov:'.$type] = 'LOV: '.str_replace('_', ' ', $type);
        });
        return $sources;
    }

    private function organization(Request $request): string
    {
        return $request->user()?->unit === 'kcdiom' ? 'kcdiom' : 'msd';
    }

    private function systemBindings(): array
    {
        return collect($this->standardFieldDefinitions())->mapWithKeys(fn ($field) => [$field['binding'] => $field['label']])->all();
    }

    private function addStandardDocumentFields(FormTemplate $template): void
    {
        foreach ($this->standardFieldDefinitions() as $index => $field) {
            $template->fields()->create($field + ['name' => $field['binding'], 'sort_order' => ($index + 1) * 10]);
        }
    }

    private function standardFieldDefinitions(): array
    {
        return [
            ['label'=>'Document Type','binding'=>'document_type','type'=>'select','section'=>'Document identity','width'=>1,'is_required'=>true,'data_source'=>'document_types'],
            ['label'=>'Status','binding'=>'status','type'=>'select','section'=>'Document identity','width'=>1,'is_required'=>true,'data_source'=>'document_statuses'],
            ['label'=>'Title','binding'=>'title','type'=>'text','section'=>'Document identity','width'=>1,'is_required'=>true],
            ['label'=>'Official Reference Number','binding'=>'reference_number','type'=>'text','section'=>'Document identity','width'=>1,'is_required'=>false],
            ['label'=>'Main Topic','binding'=>'topic_category','type'=>'select','section'=>'Classification','width'=>1,'is_required'=>false,'data_source'=>'main_topics'],
            ['label'=>'Sub Topic','binding'=>'subtopic_id','type'=>'select','section'=>'Classification','width'=>1,'is_required'=>false,'data_source'=>'subtopics'],
            ['label'=>'Owner Unit','binding'=>'owner_unit','type'=>'select','section'=>'Ownership and access','width'=>1,'is_required'=>true,'data_source'=>'departments'],
            ['label'=>'Owner / Reporting Officer','binding'=>'owner_report','type'=>'text','section'=>'Ownership and access','width'=>1,'is_required'=>false],
            ['label'=>'Creator','binding'=>'created_by','type'=>'select','section'=>'Ownership and access','width'=>1,'is_required'=>true,'data_source'=>'users'],
            ['label'=>'Access Scope','binding'=>'access_scope','type'=>'select','section'=>'Ownership and access','width'=>1,'is_required'=>true,'data_source'=>'access_scopes'],
            ['label'=>'Show on Public Portal','binding'=>'public_flag','type'=>'checkbox','section'=>'Ownership and access','width'=>1,'is_required'=>false],
            ['label'=>'Content','binding'=>'content','type'=>'textarea','section'=>'Content and validity','width'=>3,'is_required'=>true],
            ['label'=>'Effective Date','binding'=>'effective_date','type'=>'date','section'=>'Content and validity','width'=>1,'is_required'=>false],
            ['label'=>'Expiry Date','binding'=>'expiry_date','type'=>'date','section'=>'Content and validity','width'=>1,'is_required'=>false],
            ['label'=>'Controlled Source File','binding'=>'file','type'=>'file','section'=>'Content and validity','width'=>1,'is_required'=>false],
            ['label'=>'Remarks','binding'=>'remarks','type'=>'textarea','section'=>'Content and validity','width'=>3,'is_required'=>false],
        ];
    }
}

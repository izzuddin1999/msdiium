@extends('layouts.app')

@section('content')
<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Dashboard</a><span class="material-icons">chevron_right</span><a href="{{ route('policy-documents.index') }}">Documents</a><span class="material-icons">chevron_right</span><span>Register</span></div>
<div class="page-heading"><div><span class="eyebrow">Step 1 of the document lifecycle</span><h2>Register a document</h2><p>Enter the approved document identity, ownership, access, and effective dates.</p></div></div>

@if($lookupTerm !== '')
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Historical Records for "{{ e($lookupTerm) }}"</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Latest Version</th>
                        <th>Current Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($matchingDocuments as $record)
                        <tr>
                            <td>{{ $record->title }}</td>
                            <td>{{ ucfirst($record->document_type) }}</td>
                            <td>v{{ $record->effective_version_number ?? $record->version_number }}</td>
                            <td>{{ $record->statusLabel() }}</td>
                            <td>
                                <a href="{{ route('policy-documents.show', $record) }}#new-version" class="btn btn-sm btn-primary">Create New Version</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-3">No matching historical records found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<style>
    .registration-intent{margin-bottom:18px;padding:14px 16px;border:1px solid #dde5e2;border-radius:12px;background:#fff;box-shadow:0 4px 14px rgba(25,52,47,.04)}.registration-intent-head{display:flex;align-items:center;gap:10px;margin-bottom:10px}.registration-intent-head>.material-icons{display:grid;place-items:center;width:32px;height:32px;border-radius:8px;background:#edf6f4;color:#087e75;font-size:19px}.registration-intent-head strong,.registration-intent-head small{display:block}.registration-intent-head strong{color:#173b35;font-size:14px}.registration-intent-head small{margin-top:1px;color:#7a8985;font-size:11px}.intent-options{display:inline-flex;max-width:100%;gap:4px;padding:4px;border:1px solid #dce4e1;border-radius:10px;background:#f2f5f4}.intent-option{position:relative;display:flex;align-items:center;gap:7px;min-height:38px;padding:8px 13px;border:1px solid transparent;border-radius:7px;background:transparent;cursor:pointer;transition:.18s;color:#4d5d59}.intent-option:hover{color:#087e75;background:#f8fbfa}.intent-option.active{border-color:#cddbd7;background:#fff;color:#087e75;box-shadow:0 2px 7px rgba(35,65,59,.08)}.intent-option input{position:absolute;opacity:0}.intent-option>.material-icons{color:currentColor;font-size:18px}.intent-option strong{display:block;font-size:12px;white-space:nowrap}.intent-option small{display:none}.modify-version-panel{display:none;margin-top:12px;padding:13px;border-radius:9px;background:#fffaf0;border:1px solid #eadfca}.modify-version-panel.active{display:block}.modify-version-panel .modify-grid{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:9px;align-items:end}.modify-version-panel .form-label{font-size:11px;font-weight:700;color:#594719}.modify-version-panel .form-control{min-height:38px;border-radius:7px}.modify-version-panel .btn{min-height:38px;padding:7px 12px;display:inline-flex;align-items:center;gap:5px;border-radius:7px;font-size:12px;font-weight:700}
    .document-search{position:relative}.document-search-box{position:relative}.document-search-box>.material-icons{position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:20px;color:#008f85;pointer-events:none}.document-search-box .form-control{padding-left:42px;padding-right:42px}.document-search-clear{display:none;position:absolute;right:8px;top:50%;transform:translateY(-50%);width:30px;height:30px;border:0;border-radius:7px;background:transparent;color:#70817c;cursor:pointer}.document-search-clear.visible{display:grid;place-items:center}.document-search-results{display:none;position:absolute;z-index:20;left:0;right:0;top:calc(100% + 6px);max-height:250px;overflow-y:auto;padding:6px;background:#fff;border:1px solid #d5e2df;border-radius:10px;box-shadow:0 14px 30px rgba(28,64,57,.16)}.document-search-results.open{display:block}.document-search-result{width:100%;display:flex;align-items:flex-start;gap:10px;padding:10px 11px;border:0;border-radius:8px;background:#fff;text-align:left;color:#173e38;cursor:pointer}.document-search-result:hover,.document-search-result.active{background:#eaf8f5}.document-search-result>.material-icons{margin-top:1px;color:#009c92;font-size:20px}.document-search-result strong,.document-search-result small{display:block}.document-search-result small{margin-top:2px;color:#748680}.document-search-empty{padding:14px;text-align:center;color:#748680;font-size:13px}
    .document-form { --form-accent:#0b8f85; border:1px solid #dde5e2; border-radius:14px; box-shadow:0 10px 30px rgba(31,54,49,.07); overflow:hidden; background:#fff; }
    .document-form-topbar { padding:13px 20px; color:#183a34; background:#fff; border-top:3px solid var(--form-accent); border-bottom:1px solid #e0e7e5; display:flex; justify-content:space-between; align-items:center; gap:18px; }
    .document-form-topbar strong,.document-form-topbar small { display:block; }.document-form-topbar strong{font-size:15px}.document-form-topbar small{margin-top:2px;color:#7a8985;font-size:11px}
    .form-progress { display:flex; align-items:center; gap:7px; white-space:nowrap; }.form-progress span{width:24px;height:24px;display:grid;place-items:center;border:1px solid #d3dfdb;border-radius:50%;background:#f3f6f5;color:#687975;font-size:11px;font-weight:800}.form-progress span:first-child{border-color:var(--form-accent);background:var(--form-accent);color:#fff}.form-progress i{width:20px;height:1px;background:#d2dcda}
    .document-form .card-body { counter-reset:form-section; padding:18px 22px 20px; background:#fff; }
    .document-form .form-section-title { counter-increment:form-section; position:relative; margin:7px 0 2px; padding:9px 14px 9px 52px; background:#f7f9f8; border:1px solid #e0e6e4; border-radius:9px; min-height:50px; display:flex; flex-direction:column; justify-content:center; }
    .document-form .form-section-title:first-child { margin-top: 0; }
    .document-form .form-section-title::before { content:counter(form-section); position:absolute; left:13px; top:50%; transform:translateY(-50%); width:27px;height:27px;display:grid;place-items:center;border-radius:7px;background:#e2f1ee;color:#087e75;font-size:12px;font-weight:800; }
    .document-form .form-section-title h6 { margin-bottom:1px; font-size:14px; font-weight:750; color:#173b35; }
    .document-form .form-section-title small{color:#7b8986;font-size:11px}
    .document-form .form-label { margin-bottom:5px; color:#183d38; font-size:12px; font-weight:700; }
    .required-mark{margin-left:3px;color:#dc3545;font-weight:800}.required-note{display:inline-flex;align-items:center;gap:4px;color:#667b76;font-size:11px}.required-note .required-mark{margin-left:0}
    .document-form .form-control { min-height:43px; border-color:#d5e2df; border-radius:9px; background:#fff; font-size:13px; transition:border-color .2s,box-shadow .2s,transform .2s; }
    .document-form .form-control:focus { border-color: #009c92; box-shadow: 0 0 0 3px rgba(0, 156, 146, .12); }
    .document-form textarea.form-control { min-height: auto; }
    .document-form .ii-featured-field{padding:12px 14px;border:1px solid #dfe6e4;border-radius:9px;background:#fafbfb}
    .document-form .ii-title-input{min-height:96px!important;height:auto!important;padding:12px 14px;border-left:3px solid #009c92;font-size:15px;font-weight:650;line-height:1.55;resize:vertical}
    .document-form .ii-field-help{display:block;margin-top:6px;color:#71847f;font-size:10px}
    .document-form .resizable-textarea{width:100%;max-width:100%;min-width:min(260px,100%);min-height:140px;resize:both;overflow:auto}
    .document-form .resize-hint{display:flex;align-items:center;gap:4px;margin-top:5px;color:#71847f;font-size:10px}
    .document-form .resize-hint .material-icons{font-size:13px}
    .document-form .option-tile { min-height:40px; padding:8px 12px; border:1px solid #dfe5e3; border-radius:8px; background:#fafbfb; }
    .document-form .option-tile .form-check { margin: 0; }
    .document-form .form-section { border-top:0; padding-top:10px; margin-top:5px; }
    .document-form .card-footer { position:sticky;bottom:0;z-index:5;padding:14px 24px;background:rgba(247,250,249,.96);backdrop-filter:blur(10px);border-top:1px solid #dce8e4;box-shadow:0 -8px 24px rgba(21,65,56,.07); }
    .document-form .card-footer .btn{min-height:36px;padding:7px 14px;border-radius:7px;font-size:12px;font-weight:700}.document-form .card-footer .btn-primary{box-shadow:0 4px 10px rgba(0,156,146,.14)}
    .document-form .content-field textarea,.document-form .remarks-field textarea{height:auto;min-height:140px;resize:both}
    .document-form .dynamic-template>.row{box-shadow:inset 0 0 0 1px rgba(0,156,146,.03)}

    /* Latest IIUM governed-workspace visual standard */
    .registration-intent{padding:12px 14px;border-color:#d7e6e2;border-radius:12px;box-shadow:0 8px 22px rgba(19,70,60,.06)}
    .registration-intent-head>.material-icons{background:#e8f7f4;color:#078b80}
    .registration-intent-head strong{color:#1f2937}
    .registration-intent-head small{color:#708090}
    .intent-options{gap:3px;padding:3px;border-color:#dbe3ea;background:#f4f6f8}
    .intent-option{min-height:36px;padding:7px 12px;color:#53606d}
    .intent-option:hover{color:#087e75;background:#f1faf8}
    .intent-option.active{border-color:#8fd2c9;background:#eaf8f5;color:#087e75;box-shadow:none}
    .modify-version-panel{background:#f2faf8;border-color:#cce7e1}
    .modify-version-panel .form-label{color:#334155}
    .document-search-box>.material-icons,.document-search-result>.material-icons{color:#078b80}
    .document-search-result:hover,.document-search-result.active{background:#eaf8f5}
    .document-form{--form-accent:#07998d;border-color:#d7e6e2;border-radius:18px;box-shadow:0 18px 50px rgba(19,70,60,.10)}
    .document-form-topbar{padding:20px 26px;background:linear-gradient(115deg,#075c51 0%,#008f85 58%,#25b6a4 100%);color:#fff;border:0}
    .document-form-topbar strong{font-size:17px}.document-form-topbar small{color:rgba(255,255,255,.78)}
    .document-form-topbar .required-note{color:rgba(255,255,255,.9)}.document-form-topbar .required-mark{color:#ffd8d4}
    .form-progress span:first-child{border-color:var(--form-accent);background:var(--form-accent)}
    .document-form .card-body{padding:24px 26px;background:linear-gradient(180deg,#fbfdfc,#fff 220px)}
    .document-form .form-section-title{min-height:58px;padding:12px 16px 12px 66px;background:linear-gradient(90deg,#eaf8f5,#f8fcfb);border-color:#d7eae5;border-radius:11px}
    .document-form .form-section-title::before{left:17px;width:35px;height:35px;border-radius:10px;background:linear-gradient(135deg,#007d73,#18ad9f);color:#fff;box-shadow:0 5px 12px rgba(0,125,115,.22)}
    .document-form .form-section-title h6{font-size:15px}.document-form .form-section-title small{font-size:12px}
    .document-form .form-section-title h6,.document-form .form-label{color:#1f2937}
    .document-form .form-control{border-color:#d7dee5}
    .document-form .form-control:focus{border-color:#009c92;box-shadow:0 0 0 3px rgba(0,156,146,.12)}
    .document-form .ii-featured-field{padding:14px 16px;background:linear-gradient(135deg,#f2faf8 0%,#fff 72%);border-color:#d8e7e3;border-radius:12px}
    .document-form .ii-title-input{min-height:96px!important;border-left:4px solid #009c92;font-size:17px}
    .document-form .option-tile{background:#fbfcfd;border-color:#e1e6eb}
    .document-form .content-field textarea{min-height:170px}
    .document-form .remarks-field textarea{min-height:130px}
    .document-form .card-footer{padding:14px 26px;background:rgba(247,250,249,.97);border-color:#dce8e4;box-shadow:0 -8px 24px rgba(21,65,56,.07)}
    .document-form .card-footer .btn{min-height:42px;padding:9px 20px;border-radius:9px}.document-form .card-footer .btn-primary{box-shadow:0 5px 14px rgba(0,156,146,.20)}
    @media (max-width:767px){.intent-options{display:grid;width:100%;grid-template-columns:1fr}.intent-option{justify-content:center}.modify-version-panel .modify-grid{grid-template-columns:1fr}.document-form-topbar{align-items:flex-start;flex-direction:column}.document-form .card-body{padding:16px}.document-form .card-footer{padding:12px 16px}.form-progress{display:none}}
</style>

<div class="registration-intent" id="registrationIntent">
    <div class="registration-intent-head"><span class="material-icons">rule</span><div><strong>What would you like to do?</strong><small>Choose whether this is a new record or a modification of an existing Version 1 document.</small></div></div>
    <div class="intent-options">
        <label class="intent-option active" data-intent-option="new"><input type="radio" name="registration_intent" value="new" checked><span class="material-icons">note_add</span><span><strong>Create a new document</strong><small>Register a new root record beginning at Version 1.</small></span></label>
        <label class="intent-option" data-intent-option="modify"><input type="radio" name="registration_intent" value="modify"><span class="material-icons">edit_document</span><span><strong>Modify an existing document</strong><small>Create a new version from its original Version 1 record.</small></span></label>
    </div>
    <div class="modify-version-panel" id="modifyVersionPanel">
        <div class="modify-grid">
            <div class="document-search" id="rootDocumentSearch">
                <label class="form-label" for="rootDocumentSearchInput">Search for the Version 1 document to modify</label>
                <div class="document-search-box">
                    <span class="material-icons">search</span>
                    <input id="rootDocumentSearchInput" class="form-control" type="search" autocomplete="off" placeholder="Search by title or reference number" aria-autocomplete="list" aria-controls="rootDocumentSearchResults" aria-expanded="false">
                    <button type="button" class="document-search-clear" id="rootDocumentSearchClear" aria-label="Clear document search"><span class="material-icons">close</span></button>
                </div>
                <input type="hidden" id="rootDocumentUrl" value="">
                <div class="document-search-results" id="rootDocumentSearchResults" role="listbox"></div>
            </div>
            <button type="button" class="btn btn-primary btn-sm" id="continueVersionButton" disabled><span class="material-icons">history</span>Continue to new version</button>
        </div>
        @if($rootDocuments->isEmpty())<small class="d-block mt-2 text-muted">No Version 1 documents are available to modify.</small>@else<small class="d-block mt-2 text-muted">Version 1 will remain unchanged. Your modification will be saved as the next version.</small>@endif
    </div>
</div>

<form action="{{ route('policy-documents.store') }}" method="POST" enctype="multipart/form-data" class="card document-form" id="newDocumentForm">
    @csrf
    <div class="document-form-topbar">
        <div><strong>Document registration workspace</strong><small>Complete all required governance information before saving the record.</small></div>
        <span class="required-note"><span class="required-mark" aria-hidden="true">*</span> Required field</span>
        <div class="form-progress" aria-label="Form sections"><span>1</span><i></i><span>2</span><i></i><span>3</span><i></i><span>4</span></div>
    </div>
    <div class="card-body row g-3">
        <div class="col-12 form-section-title"><h6>Document details</h6><small>Choose the document type first, then enter its official identity and classification.</small></div>
        <div class="col-12 ii-featured-field">
            <label class="form-label" for="documentTitle">Document Title <span class="required-mark" aria-hidden="true">*</span><span class="visually-hidden">required</span></label>
            <textarea id="documentTitle" name="title" class="form-control ii-title-input" rows="3" maxlength="255" placeholder="Enter the official document title" required>{{ old('title') }}</textarea>
            <small class="ii-field-help">Use the complete approved title as it should appear in the repository.</small>
        </div>
        <div class="col-md-4">
            <label class="form-label">Document Type <span class="required-mark" aria-hidden="true">*</span><span class="visually-hidden">required</span></label>
            <select name="document_type" class="form-control" required>
                @foreach($documentTypes as $type => $label)
                    <option value="{{ $type }}" @selected(old('document_type') === $type)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status <span class="required-mark" aria-hidden="true">*</span><span class="visually-hidden">required</span></label>
            <select name="status" class="form-control" required>
                @foreach($documentStatuses as $status => $label)
                    <option value="{{ $status }}" @selected(old('status') === $status)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Official Reference Number</label>
            <input name="reference_number" class="form-control" value="{{ old('reference_number') }}" maxlength="100">
        </div>

        <div class="col-12 form-section-title form-section"><h6>Topic classification</h6><small>Select the category, main topic number, then its detailed subtopic.</small></div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label">Topic Category</label>
            <select name="topic_category" id="createMainTopic" class="form-control">
                <option value="">Select topic category</option>
                @foreach($topicCategories as $slug => $label)
                    <option value="{{ $slug }}" @selected(old('topic_category') === $slug)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label">Main Topic</label>
            <select name="subtopic_id" id="createSubtopic" class="form-control">
                <option value="">Select main topic</option>
                @foreach($subtopics as $subtopic)
                    <option
                        value="{{ $subtopic->id }}"
                        data-main="{{ $subtopic->mainTopic?->slug }}"
                        @selected((int) old('subtopic_id') === $subtopic->id)
                    >
                        {{ $subtopic->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label">Subtopic</label>
            <select name="topic_detail_id" id="createTopicDetail" class="form-control">
                <option value="">Select subtopic</option>
                @foreach($topicDetails as $detail)
                    <option value="{{ $detail->id }}" data-main-topic="{{ $detail->main_topic_id }}" @selected((int) old('topic_detail_id') === $detail->id)>{{ $detail->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12 form-section-title form-section"><h6>Ownership and access</h6><small>Choose which permitted users can access and view this document.</small></div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label">Permitted Users <span class="required-mark" aria-hidden="true">*</span><span class="visually-hidden">required</span></label>
            <select name="access_scope" class="form-control" required>
                @foreach(['all', $managementUnit] as $scope)
                    <option value="{{ $scope }}" @selected(old('access_scope') === $scope)>{{ $scope === 'all' ? 'All permitted users' : strtoupper($scope).' permitted users' }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 col-lg-4 d-flex align-items-end">
            <div class="option-tile w-100 d-flex align-items-center"><div class="form-check">
                <input class="form-check-input" type="checkbox" name="public_flag" value="1" id="publicFlag" @checked(old('public_flag'))>
                <label class="form-check-label" for="publicFlag">Show on public portal</label>
            </div></div>
        </div>

        @if($formTemplates->isNotEmpty())
        <div class="col-12 form-section-title form-section template-selector-section"><h6>Document template</h6><small>Select a template to build the complete registration form live.</small></div>
        <div class="col-12 template-picker">
            <label class="form-label" for="formTemplate">Form Template</label>
            <select name="form_template_id" id="formTemplate" class="form-control">
                <option value="">No additional template</option>
                @foreach($formTemplates as $template)
                    <option value="{{ $template->id }}" data-document-type="{{ $template->document_type }}" @selected((int) old('form_template_id') === $template->id)>{{ $template->name }} ({{ strtoupper($template->owner_unit) }})</option>
                @endforeach
            </select>
        </div>
        @foreach($formTemplates as $template)
            <div class="col-12 dynamic-template d-none" data-template-id="{{ $template->id }}">
                <div class="row g-3 p-3 rounded" style="background:#f8fbfa;border:1px solid #dcebe7">
                    @php
                        $currentSection = null;
                    @endphp
                    @foreach($template->fields as $field)
                        @if($currentSection !== $field->section)
                            <div class="col-12"><h6 class="mb-0 text-primary">{{ $field->section }}</h6></div>
                            @php
                                $currentSection = $field->section;
                            @endphp
                        @endif
                        @php
                            $span = min($field->width, $template->columns);
                            $bootstrapWidth = (int) (12 / $template->columns * $span);
                            $fieldName = $field->binding ?: 'dynamic['.$field->name.']';
                            $oldKey = $field->binding ?: 'dynamic.'.$field->name;
                        @endphp
                        <div class="col-md-6 col-lg-{{ $bootstrapWidth }}">
                            <label class="form-label" for="dynamic_{{ $field->id }}">{{ $field->label }} @if($field->is_required)<span class="text-danger">*</span>@endif</label>
                            @if($field->type === 'heading')
                                <h5 class="mb-0">{{ $field->default_value ?: $field->label }}</h5>
                            @elseif($field->type === 'paragraph')
                                <p class="text-muted mb-0">{{ $field->default_value ?: $field->help_text }}</p>
                            @elseif($field->type === 'textarea')
                                <textarea class="form-control resizable-textarea" id="dynamic_{{ $field->id }}" name="{{ $fieldName }}" rows="3" placeholder="{{ $field->placeholder }}" disabled>{{ old($oldKey, $field->default_value) }}</textarea>
                            @elseif(in_array($field->type, ['select','multi_select'], true))
                                <select class="form-control" id="dynamic_{{ $field->id }}" name="{{ $fieldName }}{{ $field->type === 'multi_select' ? '[]' : '' }}" @if($field->type === 'multi_select') multiple @endif disabled>
                                    @if($field->type === 'select')<option value="">{{ $field->placeholder ?: 'Select an option' }}</option>@endif
                                    @foreach($field->resolved_options as $option)<option value="{{ $option['value'] }}" @selected(in_array((string)$option['value'], array_map('strval', (array)old($oldKey, $field->default_value)), true))>{{ $option['label'] }}</option>@endforeach
                                </select>
                            @elseif($field->type === 'radio')
                                <div class="option-tile">@foreach($field->resolved_options as $option)<div class="form-check form-check-inline"><input class="form-check-input" type="radio" id="dynamic_{{ $field->id }}_{{ $loop->index }}" name="{{ $fieldName }}" value="{{ $option['value'] }}" @checked((string)old($oldKey,$field->default_value)===(string)$option['value']) disabled><label class="form-check-label" for="dynamic_{{ $field->id }}_{{ $loop->index }}">{{ $option['label'] }}</label></div>@endforeach</div>
                            @elseif($field->type === 'checkbox')
                                <div class="option-tile d-flex align-items-center"><div class="form-check"><input type="hidden" name="{{ $fieldName }}" value="0" disabled><input class="form-check-input" type="checkbox" id="dynamic_{{ $field->id }}" name="{{ $fieldName }}" value="1" @checked(old($oldKey,$field->default_value)) disabled><label class="form-check-label" for="dynamic_{{ $field->id }}">{{ $field->placeholder ?: $field->label }}</label></div></div>
                            @elseif($field->type === 'file')
                                <input class="form-control" id="dynamic_{{ $field->id }}" name="{{ $fieldName }}" type="file" accept=".pdf,.doc,.docx" disabled>
                            @else
                                <input class="form-control" id="dynamic_{{ $field->id }}" name="{{ $fieldName }}" type="{{ in_array($field->type,['number','date','email'],true) ? $field->type : 'text' }}" value="{{ old($oldKey, $field->default_value) }}" placeholder="{{ $field->placeholder }}" disabled>
                            @endif
                            @if($field->help_text)<small class="text-muted">{{ $field->help_text }}</small>@endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
        @endif

        <div class="col-12 form-section-title form-section"><h6>Content and validity</h6><small>Add searchable content, effective dates, and the controlled source file.</small></div>
        <div class="col-lg-8 content-field">
            <label class="form-label">Content <span class="required-mark" aria-hidden="true">*</span><span class="visually-hidden">required</span></label>
            <textarea name="content" class="form-control resizable-textarea" rows="4" placeholder="Enter searchable document content or a concise executive summary..." required>{{ old('content') }}</textarea>
            <small class="resize-hint"><span class="material-icons">open_in_full</span>Drag the lower-right corner to resize this field.</small>
        </div>
        <div class="col-lg-4 remarks-field">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control resizable-textarea" rows="4" maxlength="2000" placeholder="Optional internal notes...">{{ old('remarks') }}</textarea>
            <small class="resize-hint"><span class="material-icons">open_in_full</span>Drag the lower-right corner to resize this field.</small>
        </div>

        <div class="col-md-6 col-lg-4">
            <label class="form-label">Effective Date</label>
            <input type="date" name="effective_date" class="form-control" value="{{ old('effective_date') }}">
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label">Expiry Date</label>
            <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date') }}">
        </div>
        <div class="col-md-12 col-lg-4">
            <label class="form-label">Upload PDF Documents</label>
            <input type="file" name="files[]" class="form-control" accept="application/pdf,.pdf" multiple>
            <small class="text-muted">PDF only. Select up to 10 documents (maximum 3 MB each).</small>
        </div>
    </div>
    <div class="card-footer d-flex gap-2">
        <button class="btn btn-primary" type="submit"><span class="material-icons align-middle me-1" style="font-size:18px">save</span>Register document</button>
        <a href="{{ route('policy-documents.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<script>
    (function () {
        const intentOptions = document.querySelectorAll('[data-intent-option]');
        const modifyPanel = document.getElementById('modifyVersionPanel');
        const newDocumentForm = document.getElementById('newDocumentForm');
        const rootDocumentSearch = document.getElementById('rootDocumentSearch');
        const rootDocumentSearchInput = document.getElementById('rootDocumentSearchInput');
        const rootDocumentSearchResults = document.getElementById('rootDocumentSearchResults');
        const rootDocumentSearchClear = document.getElementById('rootDocumentSearchClear');
        const rootDocumentUrl = document.getElementById('rootDocumentUrl');
        const continueVersionButton = document.getElementById('continueVersionButton');
        @php
            $rootDocumentSearchData = $rootDocuments->map(function ($item) {
                return [
                    'title' => $item->title ?: 'Untitled document',
                    'reference' => $item->reference_number ?: 'No reference number',
                    'url' => route('policy-documents.show', $item).'#new-version',
                ];
            })->values();
        @endphp
        const rootDocuments = {{ Illuminate\Support\Js::from($rootDocumentSearchData) }};
        intentOptions.forEach(option => option.addEventListener('click', () => {
            const modifying = option.dataset.intentOption === 'modify';
            intentOptions.forEach(item => item.classList.toggle('active', item === option));
            modifyPanel?.classList.toggle('active', modifying);
            newDocumentForm?.classList.toggle('d-none', modifying);
        }));
        const closeDocumentResults = () => {
            rootDocumentSearchResults?.classList.remove('open');
            rootDocumentSearchInput?.setAttribute('aria-expanded', 'false');
        };
        const selectRootDocument = item => {
            rootDocumentSearchInput.value = item.title;
            rootDocumentUrl.value = item.url;
            rootDocumentSearchClear?.classList.add('visible');
            continueVersionButton.disabled = false;
            closeDocumentResults();
        };
        const renderDocumentResults = () => {
            if (!rootDocumentSearchResults) return;
            const query = rootDocumentSearchInput.value.trim().toLocaleLowerCase();
            const matches = rootDocuments.filter(item => `${item.title} ${item.reference}`.toLocaleLowerCase().includes(query)).slice(0, 10);
            rootDocumentSearchResults.replaceChildren();
            if (!matches.length) {
                const empty = document.createElement('div');
                empty.className = 'document-search-empty';
                empty.textContent = 'No matching Version 1 documents found.';
                rootDocumentSearchResults.appendChild(empty);
            }
            matches.forEach(item => {
                const option = document.createElement('button');
                option.type = 'button'; option.className = 'document-search-result'; option.setAttribute('role', 'option');
                const icon = document.createElement('span'); icon.className = 'material-icons'; icon.textContent = 'description';
                const text = document.createElement('span');
                const title = document.createElement('strong'); title.textContent = item.title;
                const meta = document.createElement('small'); meta.textContent = `Version 1 · ${item.reference}`;
                text.append(title, meta); option.append(icon, text);
                option.addEventListener('click', () => selectRootDocument(item));
                rootDocumentSearchResults.appendChild(option);
            });
            rootDocumentSearchResults.classList.add('open');
            rootDocumentSearchInput.setAttribute('aria-expanded', 'true');
        };
        rootDocumentSearchInput?.addEventListener('focus', renderDocumentResults);
        rootDocumentSearchInput?.addEventListener('input', () => {
            rootDocumentUrl.value = '';
            continueVersionButton.disabled = true;
            rootDocumentSearchClear?.classList.toggle('visible', rootDocumentSearchInput.value !== '');
            renderDocumentResults();
        });
        rootDocumentSearchClear?.addEventListener('click', () => {
            rootDocumentSearchInput.value = ''; rootDocumentUrl.value = ''; continueVersionButton.disabled = true;
            rootDocumentSearchClear.classList.remove('visible'); rootDocumentSearchInput.focus(); renderDocumentResults();
        });
        document.addEventListener('click', event => {
            if (rootDocumentSearch && !rootDocumentSearch.contains(event.target)) closeDocumentResults();
        });
        continueVersionButton?.addEventListener('click', () => {
            if (rootDocumentUrl.value) window.location.assign(rootDocumentUrl.value);
        });

        const mainSelect = document.getElementById('createMainTopic');
        const subSelect = document.getElementById('createSubtopic');
        const detailSelect = document.getElementById('createTopicDetail');
        if (!mainSelect || !subSelect) {
            return;
        }

        const filterSubtopics = () => {
            const selectedMain = mainSelect.value;
            Array.from(subSelect.options).forEach((option, index) => {
                if (index === 0) {
                    option.hidden = false;
                    return;
                }
                option.hidden = selectedMain !== '' && option.dataset.main !== selectedMain;
            });

            const currentOption = subSelect.options[subSelect.selectedIndex];
            if (currentOption && currentOption.hidden) {
                subSelect.value = '';
            }
            filterTopicDetails();
        };

        const filterTopicDetails = () => {
            if (!detailSelect) return;
            let availableDetails = 0;
            Array.from(detailSelect.options).forEach((option, index) => {
                option.hidden = index > 0 && option.dataset.mainTopic !== subSelect.value;
                if (index > 0 && !option.hidden) availableDetails++;
            });
            const current = detailSelect.options[detailSelect.selectedIndex];
            if (current && current.hidden) detailSelect.value = '';
            const placeholder = detailSelect.options[0];
            if (!subSelect.value) {
                placeholder.textContent = 'Select main topic first';
                detailSelect.disabled = true;
            } else if (availableDetails === 0) {
                placeholder.textContent = 'No subtopics available for this main topic';
                detailSelect.value = '';
                detailSelect.disabled = true;
            } else {
                placeholder.textContent = 'Select subtopic';
                detailSelect.disabled = false;
            }
        };

        mainSelect.addEventListener('change', filterSubtopics);
        subSelect.addEventListener('change', filterTopicDetails);
        filterSubtopics();

        const templateSelect = document.getElementById('formTemplate');
        const documentType = document.querySelector('[name="document_type"]');
        if (templateSelect) {
            const updateTemplate = () => {
                const usingTemplate = templateSelect.value !== '';
                document.querySelectorAll('.document-form .card-body > [class*="col-"]').forEach(column => {
                    const templateElement = column.classList.contains('template-selector-section') || column.classList.contains('template-picker') || column.classList.contains('dynamic-template');
                    if (!templateElement) column.classList.toggle('d-none', usingTemplate);
                });
                document.querySelectorAll('.dynamic-template').forEach(panel => {
                    const active = panel.dataset.templateId === templateSelect.value;
                    panel.classList.toggle('d-none', !active);
                    panel.querySelectorAll('input,select,textarea').forEach(control => control.disabled = !active);
                });
            };
            const filterTemplates = () => {
                Array.from(templateSelect.options).forEach((option, index) => {
                    if (index === 0) return;
                    option.hidden = option.dataset.documentType !== '' && option.dataset.documentType !== documentType.value;
                    if (option.hidden && option.selected) templateSelect.value = '';
                });
                updateTemplate();
            };
            templateSelect.addEventListener('change', updateTemplate);
            documentType?.addEventListener('change', filterTemplates);
            filterTemplates();
        }
    })();
</script>
@endsection

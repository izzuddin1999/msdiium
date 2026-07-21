@extends('layouts.app')

@section('content')
<style>
    .builder-panel{border:0;border-radius:12px;box-shadow:0 5px 22px rgba(20,70,63,.08)}.field-card{border:1px solid #dbe8e5;border-left:4px solid #009c92;border-radius:9px;background:#fff}.preview-grid{display:grid;grid-template-columns:repeat({{ $template->columns }},1fr);gap:14px}.preview-field{padding:13px;border:1px dashed #b8d1cc;border-radius:8px;background:#f8fbfa;transition:.2s}.preview-field span{display:block;height:40px;margin-top:6px;border:1px solid #d8e3e1;border-radius:6px;background:white}.preview-field small em{display:block;color:#748984;font-style:normal;font-size:10px;text-transform:uppercase}.sortable-field{position:relative;transition:transform .18s,opacity .18s,box-shadow .18s}.sortable-field.is-dragging{opacity:.45;transform:scale(.985)}.sortable-field.drag-over .field-card{box-shadow:0 -4px 0 #009c92,0 8px 22px rgba(0,120,110,.12)}.drag-toolbar{display:flex;align-items:center;gap:8px;margin:-4px 0 10px;color:#55736d}.drag-handle{display:inline-flex;align-items:center;border:0;background:#eaf6f3;color:#007c74;border-radius:7px;padding:5px 9px;cursor:grab}.drag-handle:active{cursor:grabbing}.drag-hint{font-size:12px}.save-state{font-size:12px;color:#647d77}.save-state.saving{color:#b47700}.save-state.saved{color:#00866f}.save-state.error{color:#dc3545}.fields-empty-drop{border:2px dashed #bed7d2;border-radius:10px;padding:35px;text-align:center;color:#718781}@media(max-width:767px){.preview-grid{grid-template-columns:1fr}}
    .component-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:9px}.component-item{display:flex;align-items:center;gap:9px;min-height:52px;padding:10px 12px;text-align:left;border:1px solid #d9e7e4;border-radius:9px;background:#f8fbfa;color:#254c46;cursor:grab;transition:.18s}.component-item:hover{border-color:#009c92;background:#eaf7f4;transform:translateY(-1px);box-shadow:0 5px 12px rgba(0,130,120,.1)}.component-item .material-icons{color:#009c92;font-size:21px}.component-item span:last-child{font-size:12px;font-weight:650}.component-state{padding:9px;border-radius:7px;background:#f1f6f5;color:#6d817d;font-size:12px}.component-state.adding{color:#9a6900;background:#fff6dc}.component-state.success{color:#007b68;background:#e5f7f1}.component-state.error{color:#b42318;background:#fff0ef}#sortableFields.component-drop-active{outline:3px dashed #009c92;outline-offset:-10px;background:#f0fbf8}.visual-field-preview{padding:15px;border:1px solid #e0e9e7;border-radius:8px;background:#fbfdfd}.visual-field-preview .form-control{pointer-events:none;background:#fff}.field-actions{margin-left:auto;display:flex;gap:5px}.field-actions button{border:0;border-radius:6px;background:#eef5f3;color:#37675f;padding:5px 9px}
    body .deznav{display:none!important}body .content-body{margin-left:0!important}body .header{padding-left:0!important}body .footer{margin-left:0!important}.builder-page-heading{margin-bottom:12px}.builder-modebar{display:flex;align-items:center;justify-content:space-between;margin:0 -15px 22px;padding:0 24px;background:#063f3a;border-radius:10px;color:#fff;box-shadow:0 6px 18px rgba(5,48,44,.16)}.builder-tabs{display:flex}.builder-tab{display:flex;align-items:center;gap:7px;padding:17px 25px;border:0;border-bottom:4px solid transparent;background:transparent;color:#b9d4cf;font-weight:700}.builder-tab.active{color:#fff;border-bottom-color:#38d2bd;background:rgba(255,255,255,.07)}.builder-mode-actions{display:flex;align-items:center;gap:12px}.builder-workspace{display:grid;grid-template-columns:300px minmax(0,1fr);gap:0;min-height:680px;background:#eef2f7;border:1px solid #dce5e3;border-radius:12px;overflow:hidden;box-shadow:0 10px 35px rgba(18,58,53,.1)}.builder-sidebar{padding:18px;background:#253b3f;border-right:1px solid #1d3235}.builder-sidebar .builder-panel{box-shadow:none;border-radius:9px}.builder-sidebar .component-library{background:transparent;color:#fff}.builder-sidebar .component-library .card-header{padding:10px 5px 18px;border:0;color:#fff}.builder-sidebar .component-library .card-body{padding:0}.builder-sidebar .component-item{background:#314b50;border-color:#405d62;color:#fff}.builder-sidebar .component-item:hover{background:#086e66;border-color:#20c5b3}.builder-sidebar .component-item .material-icons{color:#5de0d0}.builder-main{padding:30px;overflow:auto}.builder-main>.builder-panel{max-width:980px;margin:0 auto!important;border-radius:4px;box-shadow:0 5px 24px rgba(35,55,65,.12)}.builder-main .card-header{padding:22px 28px}.builder-main .card-body{padding:26px}.builder-main .sortable-field{max-width:820px;margin:0 auto}.builder-main .field-card{border-left:0;border-radius:7px}.builder-main .field-card:hover{box-shadow:0 0 0 2px #00a695}.settings-mode .component-library{display:none}.settings-mode .template-settings{display:block!important}.build-mode .template-settings{display:none}.preview-mode .builder-sidebar{display:none}.preview-mode{grid-template-columns:1fr}.preview-mode .design-canvas{display:none}.preview-mode .preview-panel{display:block!important;max-width:980px!important}.build-mode .preview-panel,.settings-mode .preview-panel{display:none}.canvas-drop-message{padding:12px 18px;background:#e5f7f3;color:#08766c;border-bottom:1px solid #cbe9e3;text-align:center;font-size:13px}@media(max-width:991px){.builder-workspace{grid-template-columns:240px minmax(0,1fr)}.component-grid{grid-template-columns:1fr}.builder-main{padding:18px}}@media(max-width:700px){.builder-workspace{display:block}.builder-sidebar{border:0}.builder-tabs span:last-child{display:none}.builder-tab{padding:15px}.builder-mode-actions .btn span:last-child{display:none}}
    /* Keep the visual builder inside the standard dashboard shell. */
    body .deznav{display:block!important}
    body .content-body{margin-left:17.1875rem!important}
    body .header{padding-left:17.1875rem!important}
    body .footer{margin-left:17.1875rem!important}
    .builder-modebar{margin:0 0 20px;padding:0 18px;background:#fff;color:#173f39;border:1px solid #dce8e5;box-shadow:0 5px 18px rgba(25,70,64,.06)}
    .builder-tab{color:#627b76;border-bottom-color:transparent}
    .builder-tab:hover{color:#007f76;background:#f2faf8}
    .builder-tab.active{color:#007f76;border-bottom-color:#009c92;background:#eaf7f4}
    .builder-workspace{background:#f4f8f7;border-color:#dce8e5;box-shadow:0 6px 24px rgba(25,70,64,.07)}
    .builder-sidebar{background:#fff;border-right-color:#dce8e5}
    .builder-sidebar .component-library{color:#173f39}
    .builder-sidebar .component-library .card-header{color:#173f39}
    .builder-sidebar .component-item{background:#f7fbfa;border-color:#d7e6e2;color:#264c46}
    .builder-sidebar .component-item:hover{background:#e8f7f4;border-color:#009c92;color:#006f67}
    .builder-sidebar .component-item .material-icons{color:#009c92}
    .builder-main{background:#f2f6f7}
    .canvas-drop-message{background:#e8f7f4;color:#00786f;border-color:#cee9e3}
    @media(max-width:1199px){body .deznav{display:none!important}body .content-body{margin-left:0!important}body .header{padding-left:0!important}body .footer{margin-left:0!important}}
</style>
<div class="breadcrumb-flow"><a href="{{ route('form-templates.index') }}">Form Builder</a><span class="material-icons">chevron_right</span><span>{{ $template->name }}</span></div>
<div class="page-heading builder-page-heading"><div><span class="eyebrow">Visual form designer</span><h2>{{ $template->name }}</h2><p>Drag components onto the canvas to build the registration form.</p></div></div>
<div class="builder-modebar"><div class="builder-tabs"><button type="button" class="builder-tab active" data-builder-mode="build"><span class="material-icons">build</span><span>BUILD</span></button><button type="button" class="builder-tab" data-builder-mode="settings"><span class="material-icons">settings</span><span>SETTINGS</span></button><button type="button" class="builder-tab" data-builder-mode="preview"><span class="material-icons">visibility</span><span>PREVIEW</span></button></div><div class="builder-mode-actions"><span class="badge bg-success">{{ $template->is_active ? 'Active' : 'Inactive' }}</span><a href="{{ route('form-templates.index') }}" class="btn btn-sm btn-light"><span class="material-icons">arrow_back</span><span>Templates</span></a></div></div>

<div class="builder-workspace build-mode" id="builderWorkspace">
 <div class="builder-sidebar">
  <form method="POST" action="{{ route('form-templates.update',$template) }}" class="card builder-panel mb-4 template-settings">@csrf @method('PUT')
   <div class="card-header"><h5 class="mb-0">Template settings</h5></div><div class="card-body">
    <div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ $template->name }}" required></div>
    <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2">{{ $template->description }}</textarea></div>
    <div class="mb-3"><label class="form-label">Document type</label><select class="form-control" name="document_type"><option value="">Any type</option>@foreach($documentTypes as $code=>$label)<option value="{{ $code }}" @selected($template->document_type===$code)>{{ $label }}</option>@endforeach</select></div>
    <div class="row g-2"><div class="col-6"><label class="form-label">Owner</label><select class="form-control" name="owner_unit"><option value="kcdiom" @selected($template->owner_unit==='kcdiom')>KCDIOM</option><option value="msd" @selected($template->owner_unit==='msd')>MSD</option></select></div><div class="col-6"><label class="form-label">Columns</label><select class="form-control" name="columns">@foreach([1,2,3] as $n)<option @selected($template->columns===$n)>{{ $n }}</option>@endforeach</select></div></div>
    <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="active" @checked($template->is_active)><label class="form-check-label" for="active">Active</label></div>
   </div><div class="card-footer"><button class="btn btn-primary w-100">Save settings</button></div>
  </form>

  <div class="card builder-panel component-library" data-add-url="{{ route('form-templates.components.store',$template) }}">
   <div class="card-header"><h5 class="mb-1">Components</h5><small class="text-muted">Drag onto the canvas or click to add</small></div>
   <div class="card-body"><div class="component-grid">
    @foreach([
      'text'=>['short_text','Text field'],'textarea'=>['notes','Long text'],'heading'=>['title','Heading'],'paragraph'=>['subject','Text block'],
      'select'=>['arrow_drop_down_circle','Dropdown'],'radio'=>['radio_button_checked','Radio buttons'],'checkbox'=>['check_box','Checkbox'],'multi_select'=>['checklist','Multi select'],
      'number'=>['pin','Number'],'date'=>['calendar_month','Date'],'email'=>['alternate_email','Email'],'file'=>['upload_file','File upload']
    ] as $type=>$component)
      <button type="button" class="component-item" draggable="true" data-component-type="{{ $type }}"><span class="material-icons">{{ $component[0] }}</span><span>{{ $component[1] }}</span></button>
    @endforeach
   </div><div id="componentState" class="component-state mt-3">Components are configured automatically.</div></div>
  </div>
 </div>
 <div class="builder-main">
  <div class="card builder-panel mb-4 design-canvas"><div class="card-header d-flex justify-content-between align-items-center"><div><h4 class="mb-0">{{ $template->name }}</h4><small class="text-muted">Form canvas · drag fields to reorder</small></div><span id="orderSaveState" class="save-state">Order is saved</span></div><div class="canvas-drop-message">Drop a component here to add it to this form</div><div class="card-body" id="sortableFields" data-reorder-url="{{ route('form-templates.fields.reorder',$template) }}">
    @forelse($template->fields as $field)
    <div class="sortable-field" draggable="true" data-field-id="{{ $field->id }}">
    <form method="POST" action="{{ route('form-templates.fields.update',[$template,$field]) }}" class="field-card p-3 mb-3 field-update-form">@csrf @method('PUT')
     <div class="drag-toolbar"><button type="button" class="drag-handle" title="Drag to reorder"><span class="material-icons">drag_indicator</span></button><strong>{{ $field->label }}</strong>@if($field->binding)<span class="badge bg-info">Document data</span>@else<span class="badge bg-light text-dark">Custom</span>@endif<span class="drag-hint">Drag this field to a new position</span></div>
     <div class="visual-field-preview">
       @if($field->type==='heading')<h5 class="mb-0">{{ $field->default_value ?: $field->label }}</h5>
       @elseif($field->type==='paragraph')<p class="text-muted mb-0">{{ $field->default_value ?: 'Add your text here.' }}</p>
       @elseif($field->type==='textarea')<textarea class="form-control" rows="2" placeholder="{{ $field->placeholder ?: $field->label }}" disabled></textarea>
       @elseif(in_array($field->type,['select','multi_select'],true))<select class="form-control" @if($field->type==='multi_select') multiple @endif disabled><option>{{ $field->type==='multi_select' ? 'Select one or more options' : 'Select an option' }}</option></select>
       @elseif($field->type==='radio')<div class="d-flex gap-3">@foreach(($field->options ?: [['label'=>'Option 1'],['label'=>'Option 2']]) as $option)<label><input type="radio" disabled> {{ $option['label'] }}</label>@endforeach</div>
       @elseif($field->type==='checkbox')<label><input type="checkbox" disabled> {{ $field->placeholder ?: $field->label }}</label>
       @elseif($field->type==='file')<input class="form-control" type="file" disabled>
       @else<input class="form-control" type="{{ in_array($field->type,['number','date','email'],true)?$field->type:'text' }}" placeholder="{{ $field->placeholder ?: $field->label }}" disabled>@endif
     </div>
     <details class="mt-3"><summary class="text-primary" style="cursor:pointer">Edit component settings</summary><div class="row g-2 align-items-end mt-1"><div class="col-md-3"><label class="form-label">Label</label><input class="form-control" name="label" value="{{ $field->label }}" required></div><div class="col-md-2"><label class="form-label">Key</label><input class="form-control" name="name" value="{{ $field->name }}" required></div><div class="col-md-2"><label class="form-label">Type</label><select class="form-control" name="type">@foreach($fieldTypes as $type)<option value="{{ $type }}" @selected($field->type===$type)>{{ $type }}</option>@endforeach</select></div><div class="col-md-2"><label class="form-label">Width</label><select class="form-control" name="width">@foreach([1,2,3] as $n)<option value="{{ $n }}" @selected($field->width===$n)>{{ $n }} column{{ $n > 1 ? 's' : '' }}</option>@endforeach</select></div><div class="col-md-2"><label class="form-label">Order</label><input class="form-control" type="number" name="sort_order" value="{{ $field->sort_order }}"></div><div class="col-md-1"><button class="btn btn-primary">Save</button></div></div>
    <details class="mt-3"><summary class="text-primary" style="cursor:pointer">Advanced field configuration</summary><div class="row g-2 mt-1">
      <div class="col-md-4"><label class="form-label">Section</label><input class="form-control" name="section" value="{{ $field->section }}" required></div><div class="col-md-4"><label class="form-label">Placeholder</label><input class="form-control" name="placeholder" value="{{ $field->placeholder }}"></div><div class="col-md-4"><label class="form-label">Default value</label><input class="form-control" name="default_value" value="{{ $field->default_value }}"></div>
      <div class="col-12"><label class="form-label">Document data binding</label><select class="form-control" name="binding"><option value="">Custom template field</option>@foreach($systemBindings as $value=>$label)<option value="{{ $value }}" @selected($field->binding===$value)>{{ $label }}</option>@endforeach</select></div>
      <div class="col-md-6"><label class="form-label">Help text</label><input class="form-control" name="help_text" value="{{ $field->help_text }}"></div><div class="col-md-6"><label class="form-label">Data source</label><select class="form-control" name="data_source">@foreach($dataSources as $value=>$label)<option value="{{ $value }}" @selected($field->data_source===$value)>{{ $label }}</option>@endforeach</select></div>
      <div class="col-12"><label class="form-label">Manual options</label><textarea class="form-control" name="options_text" rows="3">{{ collect($field->options)->map(fn($o)=>$o['value'].'|'.$o['label'])->join("\n") }}</textarea></div>
      <div class="col-md-3"><label class="form-label">Minimum</label><input class="form-control" type="number" name="min" value="{{ $field->validation['min'] ?? '' }}"></div><div class="col-md-3"><label class="form-label">Maximum</label><input class="form-control" type="number" name="max" value="{{ $field->validation['max'] ?? '' }}"></div><div class="col-md-4"><label class="form-label">Validation pattern</label><input class="form-control" name="pattern" value="{{ $field->validation['pattern'] ?? '' }}" placeholder="/^[A-Z0-9-]+$/"></div><div class="col-md-2 d-flex align-items-end"><div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="is_required" value="1" id="required_{{ $field->id }}" @checked($field->is_required)><label class="form-check-label" for="required_{{ $field->id }}">Required</label></div></div>
     </div></details></details>
    </form>
    <form method="POST" action="{{ route('form-templates.fields.destroy',[$template,$field]) }}" class="text-end mt-n3 mb-3 field-delete-form">@csrf @method('DELETE')<button class="btn btn-sm btn-link text-danger">Remove {{ $field->label }}</button></form>
    </div>
    @empty<p class="fields-empty-drop">Add the first field to begin designing this form.</p>@endforelse
  </div></div>
  <div class="card builder-panel preview-panel"><div class="card-header"><h4 class="mb-0">{{ $template->name }}</h4><small class="text-muted">Live form preview</small></div><div class="card-body"><div class="preview-grid" id="layoutPreview">@foreach($template->fields as $field)<div class="preview-field" data-preview-field="{{ $field->id }}" style="grid-column:span {{ min($field->width,$template->columns) }}"><small><em>{{ $field->section }}</em>{{ $field->label }} @if($field->is_required)<b class="text-danger">*</b>@endif</small><span></span></div>@endforeach</div></div><div class="card-footer text-center"><button type="button" class="btn btn-success px-5">Submit</button></div></div>
 </div>
</div>
<script>
(() => {
    const list = document.getElementById('sortableFields');
    const preview = document.getElementById('layoutPreview');
    const state = document.getElementById('orderSaveState');
    const library = document.querySelector('.component-library');
    const componentState = document.getElementById('componentState');
    const workspace = document.getElementById('builderWorkspace');
    if (!list) return;
    let dragged = null;
    let csrfToken = '{{ csrf_token() }}';

    const requestWithFreshCsrf = async (url, options, retry = true) => {
        options.headers = {...(options.headers || {}), 'Accept':'application/json', 'X-CSRF-TOKEN':csrfToken};
        let response = await fetch(url, {...options, credentials:'same-origin'});
        if (response.status === 419 && retry) {
            const refresh = await fetch('{{ route('csrf-token') }}', {headers:{'Accept':'application/json'}, credentials:'same-origin'});
            if (!refresh.ok) return response;
            csrfToken = (await refresh.json()).token;
            document.querySelectorAll('input[name="_token"]').forEach(input => input.value = csrfToken);
            response = await requestWithFreshCsrf(url, options, false);
        }
        return response;
    };

    const responseMessage = async response => {
        try { const data = await response.json(); return data.message || Object.values(data.errors || {}).flat()[0] || 'Request failed.'; }
        catch { return response.ok ? 'Saved.' : 'Request failed.'; }
    };

    document.querySelectorAll('[data-builder-mode]').forEach(tab => tab.addEventListener('click', () => {
        const mode = tab.dataset.builderMode;
        document.querySelectorAll('[data-builder-mode]').forEach(item => item.classList.toggle('active', item === tab));
        workspace.classList.remove('build-mode','settings-mode','preview-mode');
        workspace.classList.add(mode+'-mode');
    }));

    document.querySelectorAll('.field-update-form').forEach(form => form.addEventListener('submit', async event => {
        event.preventDefault();
        const button = form.querySelector('button[type="submit"], button:not([type])');
        if (button) button.disabled = true;
        state.textContent = 'Saving field…'; state.className = 'save-state saving';
        const response = await requestWithFreshCsrf(form.action, {method:'POST', body:new FormData(form)});
        const message = await responseMessage(response);
        if (response.ok) { state.textContent = message; state.className = 'save-state saved'; window.location.reload(); }
        else { state.textContent = message; state.className = 'save-state error'; if (button) button.disabled = false; }
    }));

    document.querySelectorAll('.field-delete-form').forEach(form => form.addEventListener('submit', async event => {
        event.preventDefault();
        if (!confirm('Remove this component from the form?')) return;
        const wrapper = form.closest('.sortable-field');
        state.textContent = 'Removing field…'; state.className = 'save-state saving';
        const response = await requestWithFreshCsrf(form.action, {method:'POST', body:new FormData(form)});
        const message = await responseMessage(response);
        if (response.ok) {
            preview?.querySelector(`[data-preview-field="${wrapper.dataset.fieldId}"]`)?.remove();
            wrapper.remove(); state.textContent = message; state.className = 'save-state saved';
        } else { state.textContent = message; state.className = 'save-state error'; }
    }));

    const fields = () => [...list.querySelectorAll('.sortable-field')];
    const syncPreview = () => fields().forEach(field => {
        const item = preview?.querySelector(`[data-preview-field="${field.dataset.fieldId}"]`);
        if (item) preview.appendChild(item);
    });
    const saveOrder = async () => {
        const ids = fields().map(field => Number(field.dataset.fieldId));
        fields().forEach((field, index) => {
            const order = field.querySelector('[name="sort_order"]');
            if (order) order.value = (index + 1) * 10;
        });
        syncPreview();
        state.textContent = 'Saving order…'; state.className = 'save-state saving';
        try {
            const response = await fetch(list.dataset.reorderUrl, {
                method: 'POST', headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                body: JSON.stringify({field_ids: ids})
            });
            if (!response.ok) throw new Error('Unable to save order');
            state.textContent = 'Order saved'; state.className = 'save-state saved';
        } catch (error) {
            state.textContent = 'Could not save — refresh and try again'; state.className = 'save-state error';
        }
    };
    fields().forEach(field => {
        field.addEventListener('dragstart', event => { dragged = field; field.classList.add('is-dragging'); event.dataTransfer.effectAllowed = 'move'; });
        field.addEventListener('dragend', () => { field.classList.remove('is-dragging'); fields().forEach(item => item.classList.remove('drag-over')); dragged = null; saveOrder(); });
        field.addEventListener('dragover', event => {
            event.preventDefault(); if (!dragged || dragged === field) return;
            fields().forEach(item => item.classList.remove('drag-over')); field.classList.add('drag-over');
            const box = field.getBoundingClientRect();
            list.insertBefore(dragged, event.clientY < box.top + box.height / 2 ? field : field.nextSibling);
            syncPreview();
        });
    });

    const addComponent = async type => {
        if (!type || !library) return;
        componentState.textContent = 'Adding component…'; componentState.className = 'component-state mt-3 adding';
        try {
            const response = await fetch(library.dataset.addUrl, {
                method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                body:JSON.stringify({type})
            });
            if (!response.ok) throw new Error('Unable to add component');
            componentState.textContent = 'Component added'; componentState.className = 'component-state mt-3 success';
            window.location.reload();
        } catch (error) {
            componentState.textContent = 'Could not add component'; componentState.className = 'component-state mt-3 error';
        }
    };
    document.querySelectorAll('.component-item').forEach(item => {
        item.addEventListener('click', () => addComponent(item.dataset.componentType));
        item.addEventListener('dragstart', event => {
            event.dataTransfer.setData('application/x-form-component', item.dataset.componentType);
            event.dataTransfer.effectAllowed = 'copy';
        });
    });
    list.addEventListener('dragenter', event => {
        if (event.dataTransfer.types.includes('application/x-form-component')) list.classList.add('component-drop-active');
    });
    list.addEventListener('dragover', event => {
        if (event.dataTransfer.types.includes('application/x-form-component')) { event.preventDefault(); event.dataTransfer.dropEffect = 'copy'; }
    });
    list.addEventListener('dragleave', event => { if (!list.contains(event.relatedTarget)) list.classList.remove('component-drop-active'); });
    list.addEventListener('drop', event => {
        const type = event.dataTransfer.getData('application/x-form-component');
        if (type) { event.preventDefault(); list.classList.remove('component-drop-active'); addComponent(type); }
    });
})();
</script>
@endsection

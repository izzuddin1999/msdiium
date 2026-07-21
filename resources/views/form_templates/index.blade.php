@extends('layouts.app')

@section('content')
<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Dashboard</a><span class="material-icons">chevron_right</span><span>Form Builder</span></div>
<div class="page-heading"><div><span class="eyebrow">No-code configuration</span><h2>Dynamic form templates</h2><p>Create reusable document forms for KCDIOM and MSD operations.</p></div></div>

<div class="row g-4">
    <div class="col-xl-4">
        <form method="POST" action="{{ route('form-templates.store') }}" class="card h-100">@csrf
            <div class="card-header"><h5 class="mb-0">New template</h5></div>
            <div class="card-body">
                <div class="mb-3"><label class="form-label">Template name</label><input class="form-control" name="name" required value="{{ old('name') }}" placeholder="e.g. KCDIOM Policy Review"></div>
                <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea></div>
                <div class="mb-3"><label class="form-label">Document type</label><select class="form-control" name="document_type"><option value="">Any document type</option>@foreach($documentTypes as $code => $label)<option value="{{ $code }}">{{ $label }}</option>@endforeach</select></div>
                <div class="row g-3">
                    <div class="col-6"><label class="form-label">Owner unit</label><select class="form-control" name="owner_unit"><option value="kcdiom">KCDIOM</option><option value="msd">MSD</option></select></div>
                    <div class="col-6"><label class="form-label">Columns</label><select class="form-control" name="columns"><option>1</option><option>2</option><option selected>3</option></select></div>
                </div>
                <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="newActive" checked><label class="form-check-label" for="newActive">Available for new documents</label></div>
            </div>
            <div class="card-footer"><button class="btn btn-primary w-100">Create and configure</button></div>
        </form>
    </div>
    <div class="col-xl-8">
        <div class="card"><div class="card-header d-flex justify-content-between"><h5 class="mb-0">Configured templates</h5><span class="badge bg-primary">{{ $templates->count() }}</span></div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table mb-0 align-middle">
                <thead><tr><th>Template</th><th>Applies to</th><th>Layout</th><th>Status</th><th></th></tr></thead>
                <tbody>@forelse($templates as $template)<tr>
                    <td><strong>{{ $template->name }}</strong><small class="d-block text-muted">{{ $template->owner_unit === 'kcdiom' ? 'KCDIOM' : 'MSD' }} · {{ $template->fields->count() }} fields</small></td>
                    <td>{{ $template->document_type ? ($documentTypes[$template->document_type] ?? ucfirst($template->document_type)) : 'All types' }}</td>
                    <td>{{ $template->columns }} columns</td><td><span class="badge {{ $template->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $template->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td class="text-end"><a class="btn btn-sm btn-primary" href="{{ route('form-templates.edit', $template) }}">Design form</a></td>
                </tr>@empty<tr><td colspan="5" class="text-center py-5 text-muted">No templates configured yet.</td></tr>@endforelse</tbody>
            </table></div></div>
        </div>
    </div>
</div>
@endsection

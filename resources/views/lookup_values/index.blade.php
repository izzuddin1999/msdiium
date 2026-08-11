@extends('layouts.app')

@section('content')
<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Dashboard</a><span class="material-icons">chevron_right</span><span>Lookup values</span></div>
<div class="page-heading"><div><span class="eyebrow">LOV_MAIN governance</span><h2>Lookup values</h2><p>Control the document types and lifecycle statuses presented throughout the system.</p></div></div>

<div class="row g-4">
    <div class="col-xl-4">
        <div class="card flow-card">
            <div class="card-header"><h5>Add lookup value</h5><small>Codes are normalized to lowercase snake case.</small></div>
            <form action="{{ route('lookup-values.store') }}" method="POST">
                @csrf
                <div class="card-body row g-3">
                    <div class="col-12"><label class="form-label">Lookup Type</label><select name="type" class="form-control" required>@foreach($allowedTypes as $type)<option value="{{ $type }}" @selected(old('type') === $type)>{{ str_replace('_', ' ', $type) }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label">Code</label><input name="code" class="form-control" value="{{ old('code') }}" required maxlength="50"></div>
                    <div class="col-md-6"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 10) }}" required min="0" max="999"></div>
                    <div class="col-12"><label class="form-label">Description</label><input name="description" class="form-control" value="{{ old('description') }}" required maxlength="255"></div>
                    <div class="col-12"><div class="form-check"><input type="checkbox" name="is_active" value="1" id="lookupActive" class="form-check-input" @checked(old('is_active', true))><label for="lookupActive" class="form-check-label">Available for selection</label></div></div>
                </div>
                <div class="card-footer"><button class="btn btn-primary w-100">Add lookup value</button></div>
            </form>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card flow-card">
            <div class="card-header"><h5>Configured values</h5><small>Inactive values remain on historical records but disappear from new selections.</small></div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table flow-table mb-0">
                <thead><tr><th>Type</th><th>Code</th><th>Description</th><th>Order</th><th>Status</th><th>Update</th><th>Delete</th></tr></thead>
                <tbody>
                @forelse($lookupValues as $lookup)
                    <tr>
                        <form action="{{ route('lookup-values.update', $lookup) }}" method="POST">@csrf @method('PUT')
                            <td><select name="type" class="form-control form-control-sm">@foreach($allowedTypes as $type)<option value="{{ $type }}" @selected($lookup->type === $type)>{{ str_replace('_', ' ', $type) }}</option>@endforeach</select></td>
                            <td><input name="code" class="form-control form-control-sm" value="{{ $lookup->code }}" required></td>
                            <td><input name="description" class="form-control form-control-sm" value="{{ $lookup->description }}" required></td>
                            <td><input type="number" name="sort_order" class="form-control form-control-sm" value="{{ $lookup->sort_order }}" min="0" max="999" required></td>
                            <td><div class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" id="lookup-{{ $lookup->id }}" @checked($lookup->is_active)><label class="form-check-label" for="lookup-{{ $lookup->id }}">Active</label></div></td>
                            <td><button class="btn btn-sm btn-warning">Save</button></td>
                        </form>
                        <td>
                            <form action="{{ route('lookup-values.destroy', $lookup) }}" method="POST" onsubmit="return confirm('Delete this lookup value? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit" title="Delete {{ $lookup->description }}">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4">No lookup values configured.</td></tr>
                @endforelse
                </tbody>
            </table></div></div>
            <div class="card-footer">{{ $lookupValues->links() }}</div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Dashboard</a><span class="material-icons">chevron_right</span><span>Circular report</span></div>
<div class="page-heading"><div><span class="eyebrow">Repository insight</span><h2>Circular report</h2><p>Review circular ownership, publication status, and effective versions.</p></div><a href="{{ route('policy-documents.index', ['document_type' => 'circular']) }}" class="btn btn-secondary">Open filtered repository</a></div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table flow-table mb-0">
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Version</th>
                    <th>Status</th>
                    <th>Owner Unit</th>
                    <th>Published At</th>
                </tr>
                </thead>
                <tbody>
                @forelse($documents as $document)
                    <tr>
                        <td><a href="{{ route('policy-documents.show', $document) }}"><strong>{{ $document->title }}</strong></a><br><small class="text-muted">{{ $document->reference_number ?: 'No official reference' }}</small></td>
                        <td>v{{ $document->version_number }}</td>
                        <td><span class="status-pill status-{{ $document->status }}">{{ $document->statusLabel() }}</span></td>
                        <td>{{ strtoupper($document->owner_unit) }}</td>
                        <td>{{ $document->published_at?->format('d M Y H:i') ?? 'Not published' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">No circular records available.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

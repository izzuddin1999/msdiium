@extends('layouts.app')

@section('content')
<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Home</a><span class="material-icons">chevron_right</span><span>Dashboard</span></div>
<div class="page-heading">
    <div>
        <span class="eyebrow">Operations overview</span>
        <h2>Policy & Circular Dashboard</h2>
        <p>Track the document lifecycle, find recent publications, and continue common tasks.</p>
    </div>
    @if($canManageDocuments)
        <a href="{{ route('policy-documents.create') }}" class="btn btn-primary action-primary"><span class="material-icons">add</span> New document</a>
    @else
        <a href="{{ route('policy-documents.index') }}" class="btn btn-primary action-primary"><span class="material-icons">search</span> Browse documents</a>
    @endif
</div>

@php
    $dashboardMetrics = [
        ['label' => 'Visible records', 'value' => $metrics['total'], 'icon' => 'folder_open', 'tone' => 'teal'],
        ['label' => 'Active', 'value' => $metrics['published'], 'icon' => 'verified', 'tone' => 'green'],
        ['label' => 'Circulars', 'value' => $metrics['circulars'], 'icon' => 'campaign', 'tone' => 'blue'],
    ];

    if ($canManageDocuments) {
        array_splice($dashboardMetrics, 2, 0, [[
            'label' => 'Drafts', 'value' => $metrics['draft'], 'icon' => 'edit_note', 'tone' => 'amber',
        ]]);
    }
@endphp
<div class="metric-grid metric-grid-{{ count($dashboardMetrics) }}">
    @foreach($dashboardMetrics as $metric)
        <div class="metric-card {{ $metric['tone'] }}">
            <span class="material-icons">{{ $metric['icon'] }}</span>
            <div><strong>{{ $metric['value'] }}</strong><small>{{ $metric['label'] }}</small></div>
        </div>
    @endforeach
</div>

<div class="row g-4 dashboard-content-row">
    <div class="col-xl-8">
        <div class="card flow-card">
            <div class="card-header card-header-row">
                <div><h5>Recent documents</h5><small>Your latest accessible records</small></div>
                <a href="{{ route('policy-documents.index') }}">View all <span class="material-icons">arrow_forward</span></a>
            </div>
            <div class="document-list">
                @forelse($recentDocuments as $document)
                    <a class="document-row" href="{{ route('policy-documents.show', $document) }}">
                        <span class="document-icon {{ $document->is_circular ? 'circular' : '' }} material-icons">{{ $document->is_circular ? 'campaign' : 'description' }}</span>
                        <span class="document-main"><strong>{{ $document->title }}</strong><small>{{ $document->reference_number ?: 'No reference' }} · {{ ucfirst($document->document_type) }} · v{{ $document->version_number }}</small></span>
                        <span class="status-pill status-{{ $document->status }}">{{ $document->statusLabel() }}</span>
                        <span class="material-icons row-arrow">chevron_right</span>
                    </a>
                @empty
                    <div class="empty-state"><span class="material-icons">folder_off</span><p>No documents are available yet.</p></div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-xl-4 dashboard-side-column">
        <div class="card flow-card mb-4">
            <div class="card-header"><h5>Quick actions</h5><small>Continue a common workflow</small></div>
            <div class="quick-actions">
                <a href="{{ route('policy-documents.index') }}"><span class="material-icons">manage_search</span><span><strong>Find a record</strong><small>Search by title, type, topic, or status</small></span></a>
                @if($canManageDocuments)
                    <a href="{{ route('policy-documents.create') }}"><span class="material-icons">note_add</span><span><strong>Register document</strong><small>Create a governed root record</small></span></a>
                    <a href="{{ route('roles.index') }}"><span class="material-icons">admin_panel_settings</span><span><strong>Manage access</strong><small>Review CAS identities and roles</small></span></a>
                @endif
                <a href="{{ route('notifications.index') }}"><span class="material-icons">notifications</span><span><strong>Notifications</strong><small>Review publication alerts</small></span></a>
            </div>
        </div>
        <div class="card flow-card">
            <div class="card-header"><h5>Upcoming expiry</h5><small>Documents requiring attention</small></div>
            <div class="compact-list">
                @forelse($expiringDocuments as $document)
                    <a href="{{ route('policy-documents.show', $document) }}"><strong>{{ $document->title }}</strong><small>{{ $document->expiry_date->format('d M Y') }}</small></a>
                @empty
                    <div class="empty-compact">No upcoming expiry dates.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

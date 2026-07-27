@extends('layouts.app')

@section('content')
<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Home</a><span class="material-icons">chevron_right</span><span>Dashboard</span></div>
<div class="page-heading">
    <div>
        <span class="eyebrow">{{ $isSystemAdministrator ? 'MSD governance command centre' : ($isKcdiomLiaison ? 'KCDIOM policy management workspace' : 'Operations overview') }}</span>
        <h2>{{ $isSystemAdministrator ? 'Administrator Dashboard' : ($isKcdiomLiaison ? 'KCDIOM Liaison Dashboard' : 'Policy & Circular Dashboard') }}</h2>
        <p>{{ $isSystemAdministrator ? 'Monitor repository health, classification coverage, access and governance activity.' : ($isKcdiomLiaison ? 'Manage the shared MSD and KCDIOM document lifecycle from registration through publication.' : 'Track the document lifecycle, find recent publications, and continue common tasks.') }}</p>
    </div>
    @if($canManageDocuments)
        <a href="{{ route('policy-documents.create') }}" class="btn btn-primary action-primary"><span class="material-icons">add</span> New document</a>
    @else
        <a href="{{ route('policy-documents.index') }}" class="btn btn-primary action-primary"><span class="material-icons">search</span> Browse documents</a>
    @endif
</div>

@if($isKcdiomLiaison)
    <section class="liaison-workflow" aria-label="KCDIOM liaison document workflow">
        <div class="liaison-workflow-heading">
            <div>
                <span class="eyebrow">Your working scope</span>
                <h4>One governed workflow for MSD and KCDIOM</h4>
                <p>MSD is part of KCDIOM. You can maintain documents across both owner units; system access and audit administration remain with the administrator.</p>
            </div>
            <span class="liaison-role-badge"><span class="material-icons">verified_user</span> Policy Manager</span>
        </div>
        <div class="liaison-steps">
            <a href="{{ route('policy-documents.create') }}">
                <span class="liaison-step-number">1</span>
                <span><strong>Register</strong><small>Create a root record or start a revision.</small></span>
                <span class="material-icons">arrow_forward</span>
            </a>
            <a href="{{ route('policy-documents.index') }}">
                <span class="liaison-step-number">2</span>
                <span><strong>Maintain</strong><small>Update metadata, PDFs and version history.</small></span>
                <span class="material-icons">arrow_forward</span>
            </a>
            <a href="{{ route('notifications.index') }}">
                <span class="liaison-step-number">3</span>
                <span><strong>Publish & monitor</strong><small>Release approved records and review alerts.</small></span>
                <span class="material-icons">arrow_forward</span>
            </a>
        </div>
    </section>
@endif

@if($isStaffViewer)
    <section class="staff-directory-panel" aria-label="Browse public documents by organisation">
        <div class="staff-directory-heading">
            <div>
                <span class="eyebrow">KCDIOM public document directory</span>
                <h4>Browse KCDIOM units</h4>
                <p>MSD is part of KCDIOM. Select MSD or another KCDIOM unit group to view documents available to staff.</p>
            </div>
            <span class="staff-directory-total">{{ $metrics['total'] }} accessible {{ Str::plural('record', $metrics['total']) }}</span>
        </div>
        <div class="staff-unit-grid">
            @foreach($staffUnitCards as $unit)
                <a class="staff-unit-card staff-unit-{{ $unit['unit'] }}" href="{{ route('policy-documents.index', ['unit' => $unit['unit']]) }}">
                    <span class="staff-unit-icon material-icons">{{ $unit['icon'] }}</span>
                    <span class="staff-unit-content">
                        <span class="staff-parent-label">KCDIOM unit</span>
                        <span class="staff-unit-code">{{ $unit['code'] }}</span>
                        <strong>{{ $unit['name'] }}</strong>
                        <small>{{ $unit['latest'] ? 'Latest: '.$unit['latest'] : 'No accessible publications yet' }}</small>
                    </span>
                    <span class="staff-unit-summary">
                        <strong>{{ $unit['documents'] }}</strong>
                        <small>{{ Str::plural('document', $unit['documents']) }}</small>
                        <span>{{ $unit['circulars'] }} {{ Str::plural('circular', $unit['circulars']) }}</span>
                    </span>
                    <span class="material-icons staff-unit-arrow">arrow_forward</span>
                </a>
            @endforeach
        </div>
    </section>

@endif

@php
    $dashboardMetrics = [
        ['label' => ($isSystemAdministrator || $isKcdiomLiaison) ? 'Governed records' : 'Visible records', 'value' => $metrics['total'], 'icon' => 'folder_open', 'tone' => 'teal'],
        ['label' => 'Active', 'value' => $metrics['published'], 'icon' => 'verified', 'tone' => 'green'],
        [
            'label' => $isSystemAdministrator ? 'Expiring in 30 days' : 'Circulars',
            'value' => $isSystemAdministrator ? $metrics['expiring'] : $metrics['circulars'],
            'icon' => $isSystemAdministrator ? 'event_busy' : 'campaign',
            'tone' => 'blue',
        ],
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

@if($isSystemAdministrator)
    <section class="topic-governance-panel">
        <div class="topic-panel-heading">
            <div>
                <span class="admin-kicker">Classification coverage</span>
                <h4>Topic governance map</h4>
                <p>Live category and main-topic structure used by the document repository.</p>
            </div>
            <a href="{{ route('topic-categories.index') }}">Manage topics <span class="material-icons">arrow_forward</span></a>
        </div>
        <div class="topic-dashboard-grid">
            @forelse($topicOverview as $category)
                @php
                    preg_match('/^([A-Z]{2,3})\b/', $category->name, $categoryCode);
                    $code = $categoryCode[1] ?? strtoupper(substr($category->name, 0, 2));
                    $categoryName = trim((string) preg_replace('/^[A-Z]{2,3}\s*[—–-]\s*/u', '', $category->name));
                @endphp
                <article class="topic-dashboard-card">
                    <div class="topic-card-top">
                        <span class="topic-code">{{ $code }}</span>
                        <div><h5>{{ $categoryName }}</h5><small>{{ $category->documents_count }} related {{ Str::plural('document', $category->documents_count) }}</small></div>
                    </div>
                    <ul>
                        @forelse($category->subtopics as $mainTopic)
                            <li><span>{{ $mainTopic->name }}</span><small>{{ $mainTopic->details_count }} {{ Str::plural('subtopic', $mainTopic->details_count) }}</small></li>
                        @empty
                            <li class="topic-empty">No main topics configured</li>
                        @endforelse
                    </ul>
                </article>
            @empty
                <div class="empty-state topic-empty-state"><span class="material-icons">account_tree</span><p>No active topic categories are configured.</p></div>
            @endforelse
        </div>
    </section>
@endif

<div class="row g-4 dashboard-content-row">
    <div class="col-xl-8">
        <div class="card flow-card">
            <div class="card-header card-header-row">
                <div><h5>{{ $isKcdiomLiaison ? 'Recently managed documents' : 'Recent documents' }}</h5><small>{{ $isKcdiomLiaison ? 'Latest changes across the shared KCDIOM repository' : 'Your latest accessible records' }}</small></div>
                <a href="{{ route('policy-documents.index') }}">View all <span class="material-icons">arrow_forward</span></a>
            </div>
            <div class="document-list">
                @forelse($recentDocuments as $document)
                    <a class="document-row" href="{{ route('policy-documents.show', $document) }}">
                        <span class="document-icon {{ $document->is_circular ? 'circular' : '' }} material-icons">{{ $document->is_circular ? 'campaign' : 'description' }}</span>
                        <span class="document-main"><strong>{{ $document->title ?: 'Untitled document' }}</strong><small>{{ $document->reference_number ?: 'No reference' }} · {{ ucfirst($document->document_type) }} · v{{ $document->version_number }}</small></span>
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
                    <a href="{{ route('topic-categories.index') }}"><span class="material-icons">account_tree</span><span><strong>Classify documents</strong><small>Review categories, main topics and subtopics</small></span></a>
                @endif
                @if($isSystemAdministrator)
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
        @if($isSystemAdministrator)
            <div class="card flow-card mt-4">
                <div class="card-header card-header-row">
                    <div><h5>Latest governance activity</h5><small>Most recent repository changes</small></div>
                    <a href="{{ route('document-activity-logs.index') }}">Audit log <span class="material-icons">arrow_forward</span></a>
                </div>
                <div class="activity-compact-list">
                    @forelse($recentActivity as $activity)
                        <div class="activity-compact-row">
                            <span class="activity-action-icon material-icons">{{ $activity->action === 'created' ? 'add_circle' : ($activity->action === 'published' ? 'publish' : 'edit_note') }}</span>
                            <div>
                                <strong>{{ ucfirst($activity->action) }} · {{ $activity->document?->title ?: 'Document record' }}</strong>
                                <small>{{ $activity->user?->name ?: 'System' }} · {{ $activity->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="empty-compact">No governance activity recorded yet.</div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

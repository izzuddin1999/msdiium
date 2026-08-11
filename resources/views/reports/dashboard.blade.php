@extends('layouts.app')

@section('content')
<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Dashboard</a><span class="material-icons">chevron_right</span><span>Reporting dashboard</span></div>
<div class="page-heading">
    <div><span class="eyebrow">Management insight</span><h2>Reporting dashboard</h2><p>Monitor repository activity, publication progress, ownership, classification, and version growth.</p></div>
    <div class="d-flex gap-2"><a href="{{ route('reports.circulars') }}" class="btn btn-secondary">Circular report</a><a href="{{ route('reports.versions') }}" class="btn btn-secondary">Version report</a></div>
</div>

<div class="metric-grid">
    @foreach([
        ['label' => 'Document records', 'value' => $metrics['documents'], 'icon' => 'description', 'tone' => 'teal'],
        ['label' => 'Published records', 'value' => $metrics['published'], 'icon' => 'verified', 'tone' => 'green'],
        ['label' => 'Derived versions', 'value' => $metrics['versions'], 'icon' => 'history', 'tone' => 'blue'],
        ['label' => 'Expiring in 90 days', 'value' => $metrics['expiring'], 'icon' => 'event_busy', 'tone' => 'amber'],
    ] as $metric)
        <div class="metric-card {{ $metric['tone'] }}"><span class="material-icons">{{ $metric['icon'] }}</span><div><strong>{{ $metric['value'] }}</strong><small>{{ $metric['label'] }}</small></div></div>
    @endforeach
</div>

@php
    $charts = [
        ['title' => 'Lifecycle status', 'subtitle' => 'Records by workflow state', 'data' => $statusCounts, 'max' => $statusMaximum],
        ['title' => 'Submissions by unit', 'subtitle' => 'MSD and KCDIOM ownership', 'data' => $unitCounts, 'max' => $unitMaximum],
        ['title' => 'Document types', 'subtitle' => 'Policies, guidelines, and circulars', 'data' => $typeCounts, 'max' => $typeMaximum],
        ['title' => 'Leading topics', 'subtitle' => 'Most-used classifications', 'data' => $topicCounts, 'max' => $topicMaximum],
    ];
@endphp

<div class="row g-4">
    @foreach($charts as $chart)
        <div class="col-xl-6">
            <div class="card flow-card h-100">
                <div class="card-header"><h5>{{ $chart['title'] }}</h5><small>{{ $chart['subtitle'] }}</small></div>
                <div class="chart-list">
                    @forelse($chart['data'] as $label => $total)
                        <div class="chart-row">
                            <div class="chart-meta"><span>{{ ucwords(str_replace(['_', '-'], ' ', $label ?: 'Unspecified')) }}</span><strong>{{ $total }}</strong></div>
                            <div class="chart-track"><span style="width: {{ max(4, round(($total / $chart['max']) * 100)) }}%"></span></div>
                        </div>
                    @empty
                        <div class="empty-compact">No data available.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card flow-card mt-4">
    <div class="card-header card-header-row"><div><h5>Recent publications</h5><small>Latest records released to their target audience</small></div><a href="{{ route('policy-documents.index', ['status' => 'published']) }}">Open repository <span class="material-icons">arrow_forward</span></a></div>
    <div class="document-list">
        @forelse($recentPublications as $document)
            <a class="document-row" href="{{ route('policy-documents.show', $document) }}">
                <span class="document-icon material-icons">{{ $document->is_circular ? 'campaign' : 'description' }}</span>
                <span class="document-main"><strong>{{ $document->title }}</strong><small>{{ strtoupper($document->owner_unit) }} · v{{ $document->version_number }} · {{ $document->publisher?->name ?? 'Publisher not recorded' }}</small></span>
                <span>{{ $document->published_at?->format('d M Y') }}</span><span class="material-icons row-arrow">chevron_right</span>
            </a>
        @empty
            <div class="empty-state"><span class="material-icons">insights</span><p>No publication data available.</p></div>
        @endforelse
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
@php
    $publishedCount = $documents->whereIn('status', ['active', 'published'])->count();
    $ownerCount = $documents->pluck('owner_unit')->filter()->unique()->count();
    $categorizedDocuments = $documents->groupBy(
        fn ($document) => $document->topic_category
            ? ($topicCategories[$document->topic_category] ?? ucfirst($document->topic_category))
            : 'Uncategorized'
    );
@endphp

<style>
    .circular-report-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:22px}
    .circular-report-metric{display:flex;align-items:center;gap:13px;padding:17px 18px;border:1px solid var(--flow-border);border-radius:14px;background:#fff;box-shadow:var(--flow-shadow)}
    .circular-report-metric .material-icons{width:43px;height:43px;display:grid;place-items:center;flex:0 0 43px;border-radius:12px;background:#e8f7f4;color:var(--flow-primary)}
    .circular-report-metric strong,.circular-report-metric small{display:block}.circular-report-metric strong{font-size:22px;line-height:1;color:var(--flow-dark)}.circular-report-metric small{margin-top:5px;color:var(--flow-muted)}
    .circular-repository{overflow:hidden;border:1px solid #dce8e4;border-radius:16px;background:#fff;box-shadow:var(--flow-shadow)}
    .circular-results-heading{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:17px 20px;border-bottom:1px solid #e2ece9;background:linear-gradient(90deg,#fff,#f5fbf9)}
    .circular-results-heading strong{display:flex;align-items:center;gap:9px;color:var(--flow-dark)}.circular-results-heading strong .material-icons{width:34px;height:34px;display:grid;place-items:center;border-radius:9px;background:#e5f6f2;color:#008f85;font-size:19px}.circular-results-heading small{color:var(--flow-muted)}
    .classified-repository{padding:16px;background:linear-gradient(180deg,#f5faf8,#eef6f4)}
    .classification-category{overflow:hidden;margin-bottom:14px;border:1px solid #cfe3df;border-radius:16px;background:#fff;box-shadow:0 8px 24px rgba(18,64,55,.07)}.classification-category:last-child{margin-bottom:0}
    .classification-category>summary,.classification-main-topic>summary{list-style:none;cursor:pointer}.classification-category>summary::-webkit-details-marker,.classification-main-topic>summary::-webkit-details-marker{display:none}
    .classification-category-header{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px;background:linear-gradient(110deg,#087a71,#00a094);color:#fff}
    .category-heading-main{display:flex;align-items:center;gap:12px;min-width:0}.category-monogram{display:grid;place-items:center;width:42px;height:42px;flex:0 0 42px;border-radius:12px;background:rgba(255,255,255,.16);font-size:13px;font-weight:900}.classification-label{display:block;margin-bottom:2px;font-size:9px;font-weight:800;letter-spacing:.13em;text-transform:uppercase;opacity:.78}.classification-category-header h4{margin:0;color:#fff;font-size:18px;font-weight:800;overflow-wrap:anywhere}
    .classification-summary-actions,.main-summary-actions{display:flex;align-items:center;gap:9px}.classification-count{padding:5px 10px;border-radius:20px;background:rgba(255,255,255,.17);font-size:11px;font-weight:700;white-space:nowrap}.classification-toggle{font-size:21px;transition:.2s}.classification-category[open]>.classification-category-header .classification-toggle,.classification-main-topic[open]>.classification-main-heading .classification-toggle{transform:rotate(180deg)}
    .classification-main-topic{margin:12px;border:1px solid #deebe8;border-radius:12px;background:#fbfdfc}.classification-main-heading,.classification-subtopic-heading{display:flex;align-items:center;justify-content:space-between;gap:12px}.classification-main-heading{padding:11px 13px}.classification-heading-title{display:flex;align-items:center;gap:9px;color:#173e38;font-weight:800}.classification-heading-title .material-icons{display:grid;place-items:center;width:30px;height:30px;border-radius:8px;background:#def3ee;color:#008f85;font-size:17px}.main-summary-actions .classification-count{background:#e8f4f1;color:#496b65}
    .classification-subtopic{margin:0 11px 11px;border:1px solid #e2ece9;border-radius:10px;background:#fff}.classification-subtopic-heading{padding:9px 12px;border-bottom:1px solid #e7efed;background:#f1f8f6;color:#365d56;font-size:12px;font-weight:750}.subtopic-marker{display:inline-block;width:6px;height:6px;margin-right:7px;border-radius:50%;background:#00a094}
    .circular-document-row{display:grid;grid-template-columns:minmax(300px,1.8fr) minmax(130px,.65fr) minmax(150px,.75fr) auto 42px;gap:14px;align-items:center;padding:13px 14px;transition:.18s}.circular-document-row+.circular-document-row{border-top:1px solid #edf2f0}.circular-document-row:hover{background:#f6fbfa;box-shadow:inset 4px 0 #00a094}
    .circular-document-main{display:grid;grid-template-columns:42px minmax(0,1fr);align-items:center;gap:12px;min-width:0}.circular-document-icon{width:42px;height:42px;display:grid;place-items:center;border-radius:11px;background:#fff0e7;color:#b35b20}.circular-document-copy{min-width:0}.circular-document-copy a{display:-webkit-box;overflow:hidden;color:var(--flow-dark);font-weight:750;line-height:1.35;overflow-wrap:anywhere;-webkit-line-clamp:2;-webkit-box-orient:vertical}.circular-document-copy a:hover{color:var(--flow-primary)}.circular-document-copy small,.circular-meta strong,.circular-meta small{display:block}.circular-document-copy small,.circular-meta small{margin-top:4px;color:var(--flow-muted);font-size:11px}.circular-meta strong{color:#244a43;font-size:12px}
    .circular-view{width:40px;height:40px;display:grid;place-items:center;border-radius:10px;background:#54b7d3;color:#fff}.circular-view:hover{background:#159cf5;color:#fff;transform:translateY(-2px)}.circular-view .material-icons{font-size:19px}.circular-repository .status-active{background:#e6f5e9;color:#26753b}.uncategorized-heading{background:linear-gradient(110deg,#647a75,#82938f)}
    @media(max-width:1100px){.circular-document-row{grid-template-columns:minmax(250px,1.5fr) minmax(130px,.7fr) auto 42px}.circular-document-row>.circular-meta:nth-of-type(2){display:none}}
    @media(max-width:767px){.circular-report-summary{grid-template-columns:1fr}.circular-results-heading{align-items:flex-start;flex-direction:column}.classified-repository{padding:9px}.classification-category-header{padding:13px}.classification-count{display:none}.circular-document-row{grid-template-columns:minmax(0,1fr) 40px;gap:10px}.circular-document-main{grid-column:1/-1}.circular-document-row>.circular-meta{grid-column:1}.circular-document-row>.circular-meta:nth-of-type(2){display:block}.circular-document-row>div:nth-last-child(2){grid-column:1}.circular-document-row>.circular-view{grid-column:2;grid-row:1}.classification-subtopic-heading{align-items:flex-start;flex-direction:column}}
</style>

<div class="page-heading">
    <div><span class="eyebrow">Repository insight</span><h2>Circular report</h2><p>Review circulars using the same governed classification structure as the document repository.</p></div>
    <a href="{{ route('policy-documents.index', ['document_type' => 'circular']) }}" class="btn btn-secondary action-primary"><span class="material-icons">folder_open</span>Open filtered repository</a>
</div>

<div class="circular-report-summary">
    <div class="circular-report-metric"><span class="material-icons">campaign</span><div><strong>{{ $documents->count() }}</strong><small>Accessible circulars</small></div></div>
    <div class="circular-report-metric"><span class="material-icons">verified</span><div><strong>{{ $publishedCount }}</strong><small>Active releases</small></div></div>
    <div class="circular-report-metric"><span class="material-icons">apartment</span><div><strong>{{ $ownerCount }}</strong><small>Document owners</small></div></div>
</div>

<section class="circular-repository">
    <div class="circular-results-heading">
        <strong><span class="material-icons">folder_open</span>Circular repository</strong>
        <small>{{ $documents->count() }} governed {{ \Illuminate\Support\Str::plural('record', $documents->count()) }}</small>
    </div>
    <div class="classified-repository">
        @forelse($categorizedDocuments as $categoryName => $categoryDocuments)
            @php
                preg_match('/^([A-Z]{2,3})\b/', $categoryName, $categoryCodeMatch);
                $categoryCode = $categoryCodeMatch[1] ?? strtoupper(substr($categoryName, 0, 2));
            @endphp
            <details class="classification-category" open>
                <summary class="classification-category-header {{ $categoryName === 'Uncategorized' ? 'uncategorized-heading' : '' }}">
                    <div class="category-heading-main"><span class="category-monogram">{{ $categoryCode }}</span><div><span class="classification-label">Category of topic</span><h4>{{ $categoryName }}</h4></div></div>
                    <span class="classification-summary-actions"><span class="classification-count">{{ $categoryDocuments->count() }} {{ \Illuminate\Support\Str::plural('document', $categoryDocuments->count()) }}</span><span class="material-icons classification-toggle">expand_more</span></span>
                </summary>
                @foreach($categoryDocuments->groupBy(fn ($document) => $document->subtopic?->name ?: 'Main topic not assigned') as $mainTopicName => $mainTopicDocuments)
                    <details class="classification-main-topic" open>
                        <summary class="classification-main-heading">
                            <div class="classification-heading-title"><span class="material-icons">format_list_numbered</span><span><span class="classification-label">Main topic</span>{{ $mainTopicName }}</span></div>
                            <span class="main-summary-actions"><span class="classification-count">{{ $mainTopicDocuments->count() }}</span><span class="material-icons classification-toggle">expand_more</span></span>
                        </summary>
                        @foreach($mainTopicDocuments->groupBy(fn ($document) => $document->topicDetail?->name ?: 'Subtopic not assigned') as $subtopicName => $subtopicDocuments)
                            <div class="classification-subtopic">
                                <div class="classification-subtopic-heading"><span><span class="classification-label">Subtopic</span><span class="subtopic-marker"></span>{{ $subtopicName }}</span><span>{{ $subtopicDocuments->count() }} {{ \Illuminate\Support\Str::plural('record', $subtopicDocuments->count()) }}</span></div>
                                @foreach($subtopicDocuments as $document)
                                    <article class="circular-document-row">
                                        <div class="circular-document-main"><span class="material-icons circular-document-icon">campaign</span><div class="circular-document-copy"><a href="{{ route('policy-documents.show', $document) }}">{{ trim($document->title, " -\t\n\r\0\x0B") ?: 'Untitled circular' }}</a><small>#&nbsp; {{ $document->reference_number ?: 'Reference not assigned' }}</small></div></div>
                                        <div class="circular-meta"><small>Governance</small><strong>{{ strtoupper($document->owner_unit ?: 'Not assigned') }} · {{ strtoupper($document->access_scope ?: 'Not set') }}</strong></div>
                                        <div class="circular-meta"><small>Release</small><strong>Version {{ $document->version_number }}</strong><small>{{ $document->published_at?->format('d M Y, H:i') ?? 'Not published' }}</small></div>
                                        <div><span class="status-pill status-{{ $document->status }}">{{ $document->statusLabel() }}</span></div>
                                        <a class="circular-view" href="{{ route('policy-documents.show', $document) }}" aria-label="View {{ $document->title }}" title="View circular"><span class="material-icons">visibility</span></a>
                                    </article>
                                @endforeach
                            </div>
                        @endforeach
                    </details>
                @endforeach
            </details>
        @empty
            <div class="empty-state"><span class="material-icons">folder_off</span><h5 class="mt-2">No circular records available</h5><p class="mb-0">Accessible circulars will appear here.</p></div>
        @endforelse
    </div>
</section>
@endsection

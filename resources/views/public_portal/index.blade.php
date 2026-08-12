@extends('layouts.public')

@section('title', $pageMeta['title'])

@section('content')
@if(!$viewer)
<style>
    .public-header-title{margin-left:22px;color:#fff}.public-header-title strong,.public-header-title small{display:block}.public-header-title strong{font-size:17px}.public-header-title small{margin-top:2px;font-size:10px;opacity:.88}.public-header-actions{display:flex;align-items:center;gap:10px;margin-left:auto}.public-access-active{background:#fff!important;color:#006d70!important}.page-bar{min-height:46px}.page-bar>h1{display:none}.page-bar .breadcrumb{margin:0}.public-v2{padding:0 24px 34px}.public-v2-container{max-width:1280px;margin:0 auto}.public-v2-hero{position:relative;overflow:hidden;display:grid;grid-template-columns:minmax(0,1.55fr) minmax(310px,.72fr);gap:24px;padding:18px;border-radius:12px;background:linear-gradient(120deg,#003e49,#006b70 72%,#087e7b);box-shadow:0 10px 25px rgba(4,67,69,.18)}.public-v2-hero:after{content:'';position:absolute;right:300px;top:-90px;width:310px;height:310px;border:34px solid rgba(255,255,255,.035);border-radius:50%}.public-v2-copy{position:relative;z-index:1;padding:13px 14px;color:#fff}.public-v2-copy .eyebrow{color:#5fe0d4}.public-v2-copy h1{max-width:680px;margin:12px 0 11px;color:#fff;font-size:36px;line-height:1.15}.public-v2-copy>p{max-width:690px;margin:0 0 17px;color:#e1f2f1;font-size:14px}.public-hero-search{display:grid;grid-template-columns:minmax(0,1fr) 130px 88px;gap:7px;padding:7px;border-radius:9px;background:#fff}.public-hero-search label{position:relative;margin:0}.public-hero-search .material-icons{position:absolute;left:9px;top:10px;color:#006d70;font-size:19px}.public-hero-search input,.public-hero-search select{width:100%;height:38px;border:0;color:#475467;font-size:11px}.public-hero-search input{padding-left:36px}.public-hero-search select{padding:0 9px;border-left:1px solid #e1e7e9}.public-hero-search button{border:0;border-radius:7px;background:#008f86;color:#fff;font-size:11px;font-weight:750}.public-filter-chips{display:flex;flex-wrap:wrap;gap:7px;margin-top:11px}.public-filter-chips a{display:inline-flex;align-items:center;gap:5px;padding:7px 11px;border:1px solid rgba(255,255,255,.22);border-radius:6px;color:#fff;font-size:10px;font-weight:650}.public-filter-chips a:first-child{background:#daf6f1;color:#006d70}.public-filter-chips .material-icons{font-size:14px}.public-record-card{position:relative;z-index:1;padding:20px;border-radius:10px;background:#fff;color:#172b4d}.public-record-card>span{color:#00766f;font-size:10px;font-weight:800;text-transform:uppercase}.public-record-card>strong{display:block;margin:12px 0 4px;font-size:48px;line-height:1}.public-record-card>p{margin:0 0 18px;color:#475467;font-size:11px}.public-record-facts{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;padding-top:14px;border-top:1px solid #e4e9eb}.public-record-facts div{border-right:1px solid #e7ecee}.public-record-facts div:last-child{border:0}.public-record-facts strong,.public-record-facts small{display:block}.public-record-facts strong{font-size:18px}.public-record-facts small{margin-top:4px;color:#667085;font-size:8px}.public-record-link{display:flex;align-items:center;justify-content:center;gap:8px;margin-top:15px;padding:9px;border-radius:6px;background:#f1f4f5;color:#006d70;font-size:10px;font-weight:750}
    .public-v2-section{margin-top:14px;padding:22px;border:1px solid #e0e7e9;border-radius:11px;background:rgba(255,255,255,.94);box-shadow:0 5px 17px rgba(30,60,64,.06)}.public-v2-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:18px}.public-v2-heading strong{display:block;color:#006d70;font-size:14px;text-transform:uppercase;letter-spacing:.04em}.public-v2-heading p{margin:6px 0 0;color:#667085;font-size:13px;line-height:1.5}.public-v2-heading a{padding:9px 13px;border:1px solid #d9e3e5;border-radius:7px;color:#31515a;font-size:12px;font-weight:700}.topic-group-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}.topic-group-card{display:grid;grid-template-columns:48px minmax(0,1fr) 20px;gap:12px;align-items:center;padding:12px;border:1px solid #dfe7ea;border-radius:9px;color:#172b4d;background:#fff}.topic-group-icon,.public-unit-icon{display:grid;place-items:center;width:48px;height:48px;border-radius:9px;background:#e6f7f3;color:#008f86}.topic-group-card:nth-child(even) .topic-group-icon{background:#eaf4ff;color:#1788c6}.topic-group-card strong,.topic-group-card small,.topic-group-card span{display:block}.topic-group-card strong{font-size:12px}.topic-group-card span{margin-top:3px;color:#344054;font-size:9px}.topic-group-card small{margin-top:4px;color:#667085;font-size:8px}.topic-group-card>.material-icons{color:#008f86}.public-unit-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}.public-unit-card{display:grid;grid-template-columns:54px minmax(0,1fr) 20px;gap:12px;align-items:center;padding:15px;border:1px solid #dfe7ea;border-radius:9px;background:#fff;color:#172b4d}.public-unit-icon{width:54px;height:54px;background:#daf6ec;color:#07906f}.public-unit-card:nth-child(2) .public-unit-icon{background:#eeecff;color:#5f5bd5}.public-unit-card em{display:block;color:#007a70;font-size:8px;font-style:normal;font-weight:750;text-transform:uppercase}.public-unit-card strong{display:block;margin:3px 0;font-size:14px}.public-unit-card small{display:block;color:#667085;font-size:9px}.public-unit-card>.material-icons{color:#008f86}.public-directory-v2{scroll-margin-top:80px}.public-directory-filter{display:grid;grid-template-columns:minmax(240px,1.5fr) repeat(3,minmax(120px,.65fr)) auto;gap:10px}.public-directory-filter input,.public-directory-filter select{height:48px;padding:0 14px;border:1px solid #d7e1e4;border-radius:7px;background:#fff;font-size:14px}.public-directory-filter button{min-width:82px;border:0;border-radius:7px;background:#008f86;color:#fff;font-size:13px;font-weight:750}.public-doc-list{margin-top:17px;border:1px solid #e0e7e9;border-radius:8px;overflow:hidden}.public-doc-item{display:grid;grid-template-columns:48px minmax(0,1fr) 120px 90px;gap:16px;align-items:center;min-height:82px;padding:15px 16px;border-bottom:1px solid #e8edef;background:#fff}.public-doc-item:last-child{border:0}.public-doc-icon{display:grid;place-items:center;width:44px;height:44px;border-radius:9px;background:#ffecec;color:#d94141;font-size:11px;font-weight:800}.public-doc-item strong,.public-doc-item small{display:block}.public-doc-item strong{color:#172b4d;font-size:14px;line-height:1.45}.public-doc-item small{margin-top:5px;color:#667085;font-size:12px;line-height:1.4}.public-doc-date{color:#475467;font-size:12px}.public-doc-item>a{padding:9px 10px;border:1px solid #cfe0e2;border-radius:7px;color:#00756f;text-align:center;font-size:12px;font-weight:750}.public-help{display:grid;grid-template-columns:1fr auto;align-items:center}.public-help h2{font-size:20px}.public-help p{max-width:750px;color:#667085;font-size:10px}.public-help a{padding:9px 13px;border:1px solid #007c74;border-radius:7px;color:#007c74;font-size:9px;font-weight:700}
    .msd-public-topics{margin-top:14px;padding:27px 28px 30px;border:1px solid #cbdce1;border-radius:11px;background:linear-gradient(120deg,rgba(213,255,221,.94),rgba(182,226,238,.94) 48%,rgba(146,183,244,.94));box-shadow:0 5px 17px rgba(30,60,64,.08)}.msd-public-topics h2{margin:0 0 18px;color:#063b68;font-size:28px;letter-spacing:.04em;text-transform:uppercase}.msd-public-topic-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}.msd-public-topic-card{position:relative;min-height:190px;border:2px solid #f1c82e;background:rgba(255,255,255,.08);color:#101828}.msd-public-topic-head{display:grid;grid-template-columns:64px minmax(0,1fr);min-height:56px;background:#ffdc55}.msd-public-topic-code{position:relative;display:grid;place-items:center;margin:-8px 0 -7px 8px;background:linear-gradient(#5fa9a2,#267c7a);color:#fff;font-size:22px;font-weight:850;filter:drop-shadow(0 3px 2px rgba(0,0,0,.18))}.msd-public-topic-code:after{content:'';position:absolute;bottom:-11px;left:0;border-top:12px solid #267c7a;border-right:32px solid transparent;border-left:32px solid transparent}.msd-public-topic-head strong{display:flex;align-items:center;padding:8px 12px;font-size:13px;line-height:1.15;text-transform:uppercase}.msd-public-topic-list{margin:20px 10px 12px;padding-left:19px}.msd-public-topic-list li{margin:4px 0;font-size:9px;line-height:1.3}.msd-public-topic-list a{color:#101828;text-decoration:none}.msd-public-topic-list a:hover{color:#006d70;text-decoration:underline}.msd-public-topic-empty{padding:24px 14px;color:#526477;font-size:9px}
    @media(max-width:991px){.public-v2-hero{grid-template-columns:1fr}.public-record-card{display:none}.public-unit-grid{grid-template-columns:1fr}.public-directory-filter{grid-template-columns:1fr 1fr}.public-directory-filter input{grid-column:1/-1}.public-header-title{display:none}.msd-public-topic-grid{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:640px){.public-v2{padding:0 12px 25px}.public-v2-copy h1{font-size:27px}.public-hero-search{grid-template-columns:1fr}.public-hero-search select{border:0}.topic-group-grid{grid-template-columns:1fr}.public-directory-filter{grid-template-columns:1fr}.public-doc-item{grid-template-columns:36px minmax(0,1fr) 60px}.public-doc-date{display:none}.public-header-actions .staff-shortcut:first-child{display:none}.msd-public-topics{padding:20px 14px}.msd-public-topics h2{font-size:22px}.msd-public-topic-grid{grid-template-columns:1fr}}
</style>
<div class="public-v2"><div class="public-v2-container">
    <section class="public-v2-hero">
        <div class="public-v2-copy"><span class="eyebrow">IIUM PUBLIC DOCUMENT PORTAL</span><h1>Policies, guidelines and circulars in one trusted directory</h1><p>Search current public documents issued by IIUM. Browse by year, topic or document type, then preview the official PDF online.</p>
            <form class="public-hero-search" method="GET" action="{{ route('public-portal') }}"><label><span class="material-icons">search</span><input name="q" value="{{ request('q') }}" placeholder="Search by title, keyword, reference number..."></label><select name="type"><option value="">All Types</option>@foreach($types as $type)<option value="{{ $type }}" @selected(request('type')===$type)>{{ str($type)->title() }}</option>@endforeach</select><button>Search</button></form>
            <nav class="public-filter-chips"><a href="{{ route('public-portal') }}">All Types</a><a href="{{ route('public-portal',['type'=>'policy']) }}"><span class="material-icons">description</span>Policies</a><a href="{{ route('public-portal',['type'=>'circular']) }}"><span class="material-icons">notifications_none</span>Circulars</a><a href="{{ route('public-portal',['type'=>'guideline']) }}"><span class="material-icons">menu_book</span>Guidelines</a><a href="#advanced-search"><span class="material-icons">event</span>By Year</a></nav>
        </div>
        <aside class="public-record-card"><span>Public Records</span><strong>{{ $totalDocuments }}</strong><p>current documents available<br>to staff and the public</p><div class="public-record-facts"><div><strong>{{ $latestYear ?: '—' }}</strong><small>Latest Year</small></div><div><strong>{{ $allDocumentCount }}</strong><small>Total Documents</small></div></div><a class="public-record-link" href="#directory">View all documents <span class="material-icons">arrow_forward</span></a></aside>
    </section>
    @if($selectedUnit === 'msd' && $topicHierarchy->isNotEmpty())
    <section class="msd-public-topics" id="msd-topics">
        <h2>Categories of Topics</h2>
        @php
            $preferredTopicOrder = ['SM', 'SS', 'HRD', 'CB', 'OA', 'RB'];
            $msdDisplayTopics = $topicHierarchy->sortBy(function ($topic) use ($preferredTopicOrder) {
                $code = strtoupper(preg_split('/\s+[—–-]\s+/u', $topic->name, 2)[0] ?? '');
                $position = array_search($code, $preferredTopicOrder, true);
                return $position === false ? 999 + (int) $topic->sort_order : $position;
            });
        @endphp
        <div class="msd-public-topic-grid">
            @foreach($msdDisplayTopics as $category)
                @php
                    $categoryParts = preg_split('/\s+[—–-]\s+/u', $category->name, 2);
                    $categoryCode = strtoupper($categoryParts[0] ?? 'MSD');
                    $categoryTitle = $categoryParts[1] ?? $category->name;
                @endphp
                <article class="msd-public-topic-card">
                    <header class="msd-public-topic-head"><span class="msd-public-topic-code">{{ $categoryCode }}</span><strong>{{ $categoryTitle }}</strong></header>
                    @if($category->subtopics->isNotEmpty())
                        <ul class="msd-public-topic-list">
                            @foreach($category->subtopics as $mainTopic)
                                <li><a href="{{ route('public.msd.topics.show',$mainTopic) }}">{{ $mainTopic->name }}</a></li>
                            @endforeach
                        </ul>
                    @else
                        <p class="msd-public-topic-empty">No active main topics have been configured.</p>
                    @endif
                </article>
            @endforeach
        </div>
    </section>
    @endif
    <section class="public-v2-section"><div class="public-v2-heading"><div><strong>Browse by KCDIOM unit</strong><p>Select a unit to view public documents published under that unit</p></div></div><div class="public-unit-grid">@foreach($unitCards as $unit)<a class="public-unit-card" href="{{ route($unit['unit']==='msd'?'public.msd':'public.kcdiom') }}"><span class="public-unit-icon material-icons">{{ $unit['icon'] }}</span><span><em>KCDIOM Unit</em><strong>{{ $unit['code'] }}</strong><small>{{ $unit['name'] }} · {{ $unit['documents'] }} {{ Str::plural('document',$unit['documents']) }}</small></span><span class="material-icons">arrow_forward</span></a>@endforeach</div></section>
    <section class="public-v2-section public-directory-v2" id="directory"><div class="public-v2-heading"><div><strong>Document directory</strong><p>Search and filter the latest published public versions</p></div></div><form id="advanced-search" class="public-directory-filter" method="GET" action="{{ route('public-portal') }}"><input name="q" value="{{ request('q') }}" placeholder="Title, reference number or keyword"><select name="year"><option value="">All years</option>@foreach($years as $year)<option value="{{ $year }}" @selected((string)request('year')===(string)$year)>{{ $year }}</option>@endforeach</select><select name="category"><option value="">All topics</option>@foreach($categories as $category)<option value="{{ $category }}" @selected(request('category')===$category)>{{ str($category)->replace(['_','-'],' ')->title() }}</option>@endforeach</select><select name="type"><option value="">All types</option>@foreach($types as $type)<option value="{{ $type }}" @selected(request('type')===$type)>{{ str($type)->title() }}</option>@endforeach</select><button>Search</button></form><div class="public-doc-list">@forelse($documents as $document)<article class="public-doc-item"><span class="public-doc-icon">PDF</span><span><strong>{{ $document->title }}</strong><small>{{ $document->reference_number ?: 'No official reference' }} · {{ str($document->document_type)->title() }}</small></span><span class="public-doc-date">{{ ($document->effective_date ?? $document->published_at)?->format('d M Y') ?? 'Current' }}</span><a href="{{ route('public.documents.show',$document) }}">View →</a></article>@empty<div class="empty-state"><strong>No public documents found</strong><p>Try another search term or filter.</p></div>@endforelse</div>@if($documents->hasPages())<nav class="pagination">{{ $documents->links() }}</nav>@endif</section>
    <section class="public-v2-section public-help" id="about"><div><strong class="eyebrow">ABOUT THIS PORTAL</strong><h2>Current, controlled and easy to verify</h2><p>This portal provides access to public documents published by IIUM. Browse, search and view official policies, guidelines and circulars.</p></div><a href="{{ route('staff-portal.create') }}">Open Staff Portal</a></section><span id="help"></span>
</div></div>
@else
@if($viewer?->canManagePolicies())
<style>
    .admin-header-title{margin-left:22px;color:#fff}.admin-header-title strong,.admin-header-title small{display:block}.admin-header-title strong{font-size:17px}.admin-header-title small{margin-top:2px;font-size:10px;opacity:.86}.admin-page-bar{display:none}.admin-dashboard-v2{padding:22px 18px 28px}.admin-wrap{max-width:1360px;margin:0 auto}.admin-welcome{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:15px}.admin-welcome h1{margin:0;color:#142542;font-size:25px}.admin-welcome p{margin:3px 0 0;color:#667085;font-size:12px}.admin-new{display:inline-flex;align-items:center;gap:7px;padding:11px 16px;border-radius:8px;background:linear-gradient(135deg,#00776f,#09a39a);color:#fff;font-size:11px;font-weight:750;box-shadow:0 5px 12px rgba(0,118,111,.18)}.admin-new .material-icons{font-size:17px}.admin-kpis{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px}.admin-kpi{position:relative;display:grid;grid-template-columns:54px 1fr;gap:12px;align-items:center;min-height:104px;padding:13px;border:1px solid #dfe5e8;border-radius:10px;background:rgba(255,255,255,.96);box-shadow:0 3px 9px rgba(22,45,61,.07);overflow:hidden}.admin-kpi:after{content:'';position:absolute;left:78px;bottom:10px;width:28px;height:3px;border-radius:3px;background:var(--accent)}.admin-kpi-icon{display:grid;place-items:center;width:54px;height:54px;border-radius:10px;background:var(--soft);color:var(--accent)}.admin-kpi-icon .material-icons{font-size:31px}.admin-kpi strong,.admin-kpi small,.admin-kpi em{display:block}.admin-kpi strong{color:#142542;font-size:29px;line-height:1}.admin-kpi small{margin-top:6px;color:#142542;font-size:10px;font-weight:750}.admin-kpi em{margin-top:3px;color:#667085;font-size:8px;font-style:normal}.admin-kpi.blue{--accent:#168cf0;--soft:#e9f4ff}.admin-kpi.green{--accent:#06a66d;--soft:#e6f8ef}.admin-kpi.orange{--accent:#ff9717;--soft:#fff3df}.admin-kpi.purple{--accent:#8247db;--soft:#f2eaff}.admin-kpi.red{--accent:#ef3340;--soft:#ffebed}
    .admin-grid-top{display:grid;grid-template-columns:.86fr 1fr 1.18fr;gap:12px;margin-top:13px}.admin-panel{border:1px solid #dfe5e8;border-radius:10px;background:rgba(255,255,255,.97);box-shadow:0 3px 9px rgba(22,45,61,.06);overflow:hidden}.admin-panel-title{display:flex;align-items:center;justify-content:space-between;min-height:43px;padding:0 15px;border-bottom:1px solid #e4e8eb;color:#142542;font-size:13px;font-weight:800}.overview-list{padding:5px 15px}.overview-row{display:grid;grid-template-columns:12px 1fr auto;gap:8px;align-items:center;padding:10px 0;border-bottom:1px solid #e8ecee;color:#273b59;font-size:10px}.overview-row:last-child{border:0;font-weight:800}.overview-dot{width:8px;height:8px;border-radius:50%;background:var(--dot)}.type-content{display:grid;grid-template-columns:135px 1fr;align-items:center;gap:7px;padding:17px}.type-donut{position:relative;width:112px;height:112px;margin:auto;border-radius:50%;background:conic-gradient(#168cf0 0 var(--policy),#05a873 var(--policy) var(--circular),#ff991f var(--circular) var(--guideline),#8247db var(--guideline) 100%)}.type-donut:after{content:'';position:absolute;inset:27px;border-radius:50%;background:#fff}.type-legend div{display:grid;grid-template-columns:8px 1fr auto;gap:8px;align-items:center;padding:7px 0;color:#273b59;font-size:10px}.type-legend i{width:8px;height:8px;border-radius:50%;background:var(--dot)}.attention-row{display:grid;grid-template-columns:38px 1fr auto;gap:10px;align-items:center;padding:11px 14px;border-bottom:1px solid #e5e9eb}.attention-row:last-child{border:0}.attention-icon{display:grid;place-items:center;width:34px;height:34px;border-radius:8px;background:var(--soft);color:var(--accent)}.attention-icon .material-icons{font-size:19px}.attention-row strong,.attention-row small{display:block}.attention-row strong{color:#1c3150;font-size:10px}.attention-row small{margin-top:3px;color:#667085;font-size:8px}.attention-row>a{color:#168cf0}.admin-grid-bottom{display:grid;grid-template-columns:1.8fr 1fr;gap:12px;margin-top:13px}.admin-panel-title a{color:#087f78;font-size:9px}.recent-table{width:100%;border-collapse:collapse}.recent-table th{padding:8px 12px;background:#f7f7fb;color:#30415d;font-size:8px;text-align:left}.recent-table td{padding:8px 12px;border-bottom:1px solid #e7ebed;color:#263b59;font-size:9px}.recent-table td:nth-child(2){max-width:360px}.recent-table strong,.recent-table small{display:block}.recent-table strong{font-size:9px}.recent-table small{margin-top:2px;color:#667085;font-size:7px}.doc-pill,.status-pill{display:inline-flex;padding:4px 8px;border-radius:7px;font-size:7px;font-weight:800;text-transform:uppercase}.doc-pill{background:#e7f5ff;color:#087ed3}.status-pill{background:#e6f7ed;color:#07864f}.status-pill.superseded{background:#f0e7ff;color:#6b36c5}.topic-admin-card{display:grid;grid-template-columns:48px 1fr auto;gap:11px;align-items:start;padding:13px;border-bottom:1px solid #e7ebed}.topic-admin-card:last-child{border:0}.topic-admin-icon{display:grid;place-items:center;width:44px;height:44px;border-radius:8px;background:#dff5f1;color:#008f86}.topic-admin-card strong,.topic-admin-card small{display:block}.topic-admin-card strong{font-size:11px;color:#172b4d}.topic-admin-card small{margin-top:3px;color:#667085;font-size:8px}.topic-admin-card ul{grid-column:2;margin:2px 0 0;padding-left:15px;color:#172b4d;font-size:8px}.topic-admin-card>a{align-self:center;padding:7px 9px;border:1px solid #d5e2e4;border-radius:7px;color:#007c75;font-size:8px;font-weight:700}.admin-quick{display:flex;align-items:center;gap:12px;margin-top:13px;padding:11px;border:1px solid #dfe5e8;border-radius:10px;background:rgba(255,255,255,.97)}.admin-quick>strong{margin-right:4px;color:#142542;font-size:12px}.admin-quick a{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-width:130px;padding:10px 13px;border:1px solid #dde4e8;border-radius:8px;color:#203653;font-size:9px;font-weight:700}.admin-quick .material-icons{color:#008f86;font-size:20px}
    /* Readable administrator scale: retain the compact dashboard grid while
       keeping labels, supporting text and table content comfortably legible. */
    .admin-dashboard-v2 .admin-welcome h1{font-size:30px!important}.admin-dashboard-v2 .admin-welcome p{font-size:15px}.admin-dashboard-v2 .admin-new{font-size:14px}.admin-dashboard-v2 .admin-kpi small{font-size:14px}.admin-dashboard-v2 .admin-kpi em{font-size:11px;line-height:1.45}.admin-dashboard-v2 .admin-panel-title{min-height:52px;font-size:17px!important}.admin-dashboard-v2 .admin-panel-title a{font-size:12px}.admin-dashboard-v2 .overview-row,.admin-dashboard-v2 .type-legend div{font-size:13px}.admin-dashboard-v2 .attention-row{min-height:70px}.admin-dashboard-v2 .attention-row strong{font-size:13px}.admin-dashboard-v2 .attention-row small{font-size:11px;line-height:1.45}.admin-dashboard-v2 .recent-table th{padding:11px 14px;font-size:11px!important}.admin-dashboard-v2 .recent-table td{padding:12px 14px;font-size:12px!important}.admin-dashboard-v2 .recent-table strong{font-size:12px}.admin-dashboard-v2 .recent-table small{font-size:10px}.admin-dashboard-v2 .doc-pill,.admin-dashboard-v2 .status-pill{font-size:10px!important}.admin-dashboard-v2 .topic-admin-card strong{font-size:14px}.admin-dashboard-v2 .topic-admin-card small,.admin-dashboard-v2 .topic-admin-card ul{font-size:11px;line-height:1.5}.admin-dashboard-v2 .topic-admin-card>a{font-size:11px}.admin-dashboard-v2 .admin-quick>strong{font-size:15px}.admin-dashboard-v2 .admin-quick a{font-size:12px}
    @media(max-width:1100px){.admin-kpis{grid-template-columns:repeat(3,1fr)}.admin-grid-top{grid-template-columns:1fr 1fr}.admin-grid-top .attention-panel{grid-column:1/-1}.admin-grid-bottom{grid-template-columns:1fr}.admin-header-title{display:none}}@media(max-width:700px){.admin-dashboard-v2{padding:15px 10px}.admin-welcome{align-items:flex-start}.admin-welcome h1{font-size:21px}.admin-kpis{grid-template-columns:1fr 1fr}.admin-grid-top{grid-template-columns:1fr}.admin-grid-top .attention-panel{grid-column:auto}.type-content{grid-template-columns:120px 1fr}.recent-table th:nth-child(3),.recent-table td:nth-child(3){display:none}.admin-quick{align-items:stretch;flex-direction:column}.admin-quick a{justify-content:flex-start}.admin-new span:last-child{display:none}}@media(max-width:430px){.admin-kpis{grid-template-columns:1fr}.admin-welcome p{max-width:220px}}
</style>
@php
    $typeTotal = max(1, $staffMetrics['policies'] + $staffMetrics['circulars'] + $staffMetrics['guidelines']);
    $policyStop = round($staffMetrics['policies'] / $typeTotal * 100, 1).'%';
    $circularStop = round(($staffMetrics['policies'] + $staffMetrics['circulars']) / $typeTotal * 100, 1).'%';
    $guidelineStop = round(($staffMetrics['policies'] + $staffMetrics['circulars'] + $staffMetrics['guidelines']) / $typeTotal * 100, 1).'%';
@endphp
<section class="admin-dashboard-v2" id="staff-workspace"><div class="admin-wrap">
    <div class="admin-welcome"><div><h1>Welcome back, {{ $viewer->name }}</h1><p>Here’s what’s happening with your document repository.</p></div><a class="admin-new" href="{{ route('policy-documents.create') }}"><span class="material-icons">note_add</span><span>New Document</span><span class="material-icons">expand_more</span></a></div>
    <div class="admin-kpis">
        <a class="admin-kpi blue" href="{{ route('policy-documents.index') }}"><span class="admin-kpi-icon"><span class="material-icons">description</span></span><span><strong>{{ $staffMetrics['total'] }}</strong><small>Total Documents</small><em>All documents in repository</em></span></a>
        <a class="admin-kpi green" href="{{ route('policy-documents.index',['status'=>'published']) }}"><span class="admin-kpi-icon"><span class="material-icons">check_circle</span></span><span><strong>{{ $staffMetrics['published'] }}</strong><small>Active</small><em>Currently in effect</em></span></a>
        <a class="admin-kpi orange" href="{{ route('policy-documents.index',['status'=>'draft']) }}"><span class="admin-kpi-icon"><span class="material-icons">edit</span></span><span><strong>{{ $staffMetrics['draft'] }}</strong><small>Draft</small><em>Pending completion</em></span></a>
        <a class="admin-kpi purple" href="{{ route('policy-documents.index',['status'=>'superseded']) }}"><span class="admin-kpi-icon"><span class="material-icons">visibility_off</span></span><span><strong>{{ $staffMetrics['superseded'] }}</strong><small>Superseded</small><em>No longer in effect</em></span></a>
        <a class="admin-kpi red" href="{{ route('policy-documents.index') }}"><span class="admin-kpi-icon"><span class="material-icons">event_busy</span></span><span><strong>{{ $staffMetrics['expiring'] }}</strong><small>Expiring in 30 days</small><em>Require attention</em></span></a>
    </div>
    <div class="admin-grid-top">
        <section class="admin-panel"><div class="admin-panel-title">Document Overview</div><div class="overview-list"><div class="overview-row"><i class="overview-dot" style="--dot:#06a66d"></i><span>Active</span><strong>{{ $staffMetrics['published'] }}</strong></div><div class="overview-row"><i class="overview-dot" style="--dot:#ff9717"></i><span>Draft</span><strong>{{ $staffMetrics['draft'] }}</strong></div><div class="overview-row"><i class="overview-dot" style="--dot:#8247db"></i><span>Superseded</span><strong>{{ $staffMetrics['superseded'] }}</strong></div><div class="overview-row"><i class="overview-dot" style="--dot:#ef3340"></i><span>Expiring in 30 days</span><strong>{{ $staffMetrics['expiring'] }}</strong></div><div class="overview-row"><span></span><span>Total Documents</span><strong>{{ $staffMetrics['total'] }}</strong></div></div></section>
        <section class="admin-panel"><div class="admin-panel-title">Documents by Type</div><div class="type-content"><div class="type-donut" style="--policy:{{ $policyStop }};--circular:{{ $circularStop }};--guideline:{{ $guidelineStop }}"></div><div class="type-legend"><div><i style="--dot:#168cf0"></i><span>Policy</span><strong>{{ $staffMetrics['policies'] }}</strong></div><div><i style="--dot:#05a873"></i><span>Circular</span><strong>{{ $staffMetrics['circulars'] }}</strong></div><div><i style="--dot:#ff991f"></i><span>Guideline</span><strong>{{ $staffMetrics['guidelines'] }}</strong></div><div><i style="--dot:#8247db"></i><span>Other</span><strong>{{ max(0,$staffMetrics['total']-$staffMetrics['policies']-$staffMetrics['circulars']-$staffMetrics['guidelines']) }}</strong></div></div></div></section>
        <section class="admin-panel attention-panel"><div class="admin-panel-title">Needs Attention</div><div class="attention-row"><span class="attention-icon" style="--accent:#8247db;--soft:#f2eaff"><span class="material-icons">visibility_off</span></span><span><strong>{{ $staffMetrics['superseded'] ? $staffMetrics['superseded'].' document superseded' : 'No superseded documents' }}</strong><small>{{ $staffMetrics['superseded'] ? 'Review documents that are no longer in effect.' : 'All controlled documents are current.' }}</small></span><a href="{{ route('policy-documents.index',['status'=>'superseded']) }}" class="material-icons">chevron_right</a></div><div class="attention-row"><span class="attention-icon" style="--accent:#06a66d;--soft:#e6f8ef"><span class="material-icons">task_alt</span></span><span><strong>{{ $staffMetrics['expiring'] ? $staffMetrics['expiring'].' documents expiring soon' : 'No documents expiring' }}</strong><small>{{ $staffMetrics['expiring'] ? 'Review records expiring within 30 days.' : 'No documents expire within 30 days.' }}</small></span></div><div class="attention-row"><span class="attention-icon" style="--accent:#06a66d;--soft:#e6f8ef"><span class="material-icons">task_alt</span></span><span><strong>{{ $staffMetrics['draft'] ? $staffMetrics['draft'].' draft documents' : 'No draft documents' }}</strong><small>{{ $staffMetrics['draft'] ? 'Complete pending drafts.' : 'No drafts awaiting completion.' }}</small></span></div></section>
    </div>
    <div class="admin-grid-bottom">
        <section class="admin-panel"><div class="admin-panel-title"><span>Recent Documents</span><a href="{{ route('policy-documents.index') }}">View all</a></div><table class="recent-table"><thead><tr><th>TYPE</th><th>DOCUMENT TITLE</th><th>STATUS</th><th>UPDATED</th></tr></thead><tbody>@forelse($recentStaffDocuments as $recent)<tr><td><span class="doc-pill">{{ $recent->is_circular ? 'Circular' : $recent->document_type }}</span></td><td><a href="{{ route('policy-documents.show',$recent) }}"><strong>{{ $recent->title }}</strong><small>{{ $recent->reference_number ?: 'No reference number' }}</small></a></td><td><span class="status-pill {{ $recent->status }}">{{ $recent->status === 'published' ? 'Active' : $recent->status }}</span></td><td>{{ $recent->updated_at->format('d M Y') }}</td></tr>@empty<tr><td colspan="4">No documents are currently available.</td></tr>@endforelse</tbody></table></section>
        <section class="admin-panel"><div class="admin-panel-title"><span>Category of Topics</span><a href="{{ route('topic-categories.index') }}">View all</a></div>@forelse($topicHierarchy->take(3) as $category)<article class="topic-admin-card"><span class="topic-admin-icon material-icons">folder</span><span><strong>{{ $category->name }}</strong><small>{{ $category->documents_count }} {{ Str::plural('document',$category->documents_count) }}</small></span><a href="{{ route('topic-categories.index') }}">View topics →</a>@if($category->subtopics->isNotEmpty())<ul>@foreach($category->subtopics->take(2) as $topic)<li>{{ $topic->name }}</li>@endforeach</ul>@endif</article>@empty<div class="empty-state compact"><p>No topic categories are available.</p></div>@endforelse</section>
    </div>
    <nav class="admin-quick"><strong>Quick Actions</strong><a href="{{ route('policy-documents.create') }}"><span class="material-icons">add</span>New Document</a><a href="{{ route('topic-categories.index') }}"><span class="material-icons">folder_open</span>Manage Topics</a><a href="{{ route('reports.versions') }}"><span class="material-icons">history</span>Version History</a><a href="{{ route('document-activity-logs.index') }}"><span class="material-icons">fact_check</span>Audit Log</a><a href="{{ route('roles.index') }}"><span class="material-icons">group</span>User Roles</a><a href="{{ route('reports.dashboard') }}"><span class="material-icons">bar_chart</span>Reports</a></nav>
</div></section>
@else
<section class="hero">
    <div class="portal-container hero-grid">
        <div>
            <span class="eyebrow">{{ $pageMeta['eyebrow'] }}</span>
            <h1>{{ $pageMeta['heading'] }}</h1>
            <p>{{ $pageMeta['description'] }}</p>
            @if($viewer?->canManagePolicies())
                <a class="primary-button" href="{{ route('policy-documents.index', ['unit' => $viewer->unit === 'kcdiom' ? 'kcdiom' : 'msd']) }}">Open governed repository <span class="material-icons" aria-hidden="true">arrow_forward</span></a>
            @else
                <a class="primary-button" href="#directory">Browse documents <span class="material-icons" aria-hidden="true">south</span></a>
            @endif
        </div>
        <aside class="hero-panel" aria-label="Directory summary">
            <span class="hero-panel-label">PUBLIC RECORDS</span>
            <strong>{{ $totalDocuments }}</strong>
            <p>current documents available to staff and the public</p>
            <div class="hero-facts"><span>{{ $categoryCount }} topic groups</span><span>{{ $latestYear ?: 'Current' }} latest year</span></div>
        </aside>
    </div>
</section>

@if($selectedUnit === 'msd' && $topicHierarchy->isNotEmpty())
<style>
    .msd-topics{padding:54px 0;background:linear-gradient(120deg,#e5faf0 0%,#d8f4f0 42%,#dceaff 100%);border-top:1px solid #d7e8e4;border-bottom:1px solid #d3e3e0}.msd-topics-heading{display:flex;align-items:end;justify-content:space-between;gap:20px;margin-bottom:22px}.msd-topics-heading h2{margin:4px 0 0;color:#142f4f;font-size:34px}.msd-topics-heading p{max-width:620px;margin:0;color:#60756f}.msd-topic-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}.msd-topic-card{position:relative;min-height:210px;border:1px solid #d3ba40;border-radius:12px;background:rgba(255,255,255,.74);box-shadow:0 10px 24px rgba(32,78,69,.08);overflow:hidden}.msd-topic-card-head{display:flex;align-items:stretch;min-height:64px;background:#f5d957}.msd-topic-code{display:grid;place-items:center;flex:0 0 74px;padding:8px;background:linear-gradient(160deg,#08796f,#56aba1);color:#fff;font-size:25px;font-weight:800}.msd-topic-card-head h3{display:flex;align-items:center;margin:0;padding:10px 15px;color:#1d2936;font-size:16px;line-height:1.2;text-transform:uppercase}.msd-topic-list{margin:0;padding:17px 20px 19px 38px;color:#203e3a}.msd-topic-list li{margin:0 0 7px;padding-left:3px;font-size:13px}.msd-topic-list a{color:#203e3a;text-decoration:none}.msd-topic-list a:hover{color:#008f84;text-decoration:underline}.msd-topic-empty{padding:20px;color:#7b8c88;font-size:12px}.msd-topics-action{display:inline-flex;align-items:center;gap:5px;color:#087e75;font-size:12px;font-weight:750;text-decoration:none}.msd-topics-action .material-icons{font-size:17px}@media(max-width:991px){.msd-topic-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:640px){.msd-topics{padding:36px 0}.msd-topics-heading{align-items:flex-start;flex-direction:column}.msd-topics-heading h2{font-size:27px}.msd-topic-grid{grid-template-columns:1fr}.msd-topic-card{min-height:0}}
</style>
<section class="msd-topics" id="topics">
    <div class="portal-container">
        <div class="msd-topics-heading"><div><span class="eyebrow">MSD CLASSIFICATION DIRECTORY</span><h2>Categories of Topics</h2></div><p>Browse the official Management Services Division topic structure. Select a main topic to filter the public document directory.</p></div>
        <div class="msd-topic-grid">
            @foreach($topicHierarchy as $category)
                @php
                    $prefix = str($category->name)->before('—')->trim();
                    $code = $prefix->length() <= 5 ? $prefix->upper() : str($category->name)->explode(' ')->filter()->take(2)->map(fn ($word) => str($word)->substr(0, 1))->implode('');
                @endphp
                <article class="msd-topic-card">
                    <header class="msd-topic-card-head"><span class="msd-topic-code">{{ $code }}</span><h3>{{ $category->name }}</h3></header>
                    @if($category->subtopics->isNotEmpty())
                        <ul class="msd-topic-list">
                            @foreach($category->subtopics as $mainTopic)
                                <li><a href="{{ route('public.msd.topics.show', $mainTopic) }}">{{ $mainTopic->name }}</a></li>
                            @endforeach
                        </ul>
                    @else
                        <p class="msd-topic-empty">No active main topics are currently published.</p>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($viewer && $staffMetrics && !$selectedUnit)
<section class="staff-workspace" id="staff-workspace">
    <div class="portal-container">
        <div class="staff-workspace-heading">
            <div><span class="eyebrow">STAFF WORKSPACE</span><h2>Welcome back, {{ $viewer->name }}</h2><p>Review your accessible repository and continue common policy workflows.</p></div>
            @if($viewer->canManagePolicies())<a class="primary-button staff-new-document" href="{{ route('policy-documents.create') }}"><span class="material-icons">add</span>New document</a>@endif
        </div>
        <div class="combined-metrics">
            <a href="{{ route('policy-documents.index') }}"><span class="material-icons">folder_open</span><strong>{{ $staffMetrics['total'] }}</strong><small>Visible records</small></a>
            <a href="{{ route('policy-documents.index', ['status' => 'published']) }}"><span class="material-icons">verified</span><strong>{{ $staffMetrics['published'] }}</strong><small>Published</small></a>
            @if($staffMetrics['draft'] !== null)<a href="{{ route('policy-documents.index', ['status' => 'draft']) }}"><span class="material-icons">edit_note</span><strong>{{ $staffMetrics['draft'] }}</strong><small>Drafts</small></a>@endif
            <a href="{{ route('reports.circulars') }}"><span class="material-icons">campaign</span><strong>{{ $staffMetrics['circulars'] }}</strong><small>Circulars</small></a>
        </div>
        <section class="staff-unit-browser" aria-labelledby="staff-unit-browser-title">
            <div class="staff-unit-browser-heading"><span><strong id="staff-unit-browser-title">Browse documents by unit</strong><small>Open the documents available to you under MSD or KCDIOM.</small></span><span class="material-icons">account_tree</span></div>
            <div class="staff-unit-grid">
                @foreach($unitCards as $unit)
                    <a href="{{ route($unit['unit'] === 'msd' ? 'public.msd' : 'public.kcdiom') }}">
                        <span class="staff-unit-icon material-icons">{{ $unit['icon'] }}</span>
                        <span><em>KCDIOM UNIT</em><strong>{{ $unit['code'] }}</strong><small>{{ $unit['name'] }}</small></span>
                        <span class="staff-unit-count"><strong>{{ $unit['documents'] }}</strong><small>{{ Str::plural('document', $unit['documents']) }}</small></span>
                        <span class="material-icons">arrow_forward</span>
                    </a>
                @endforeach
            </div>
        </section>
        <section class="combined-panel quick-workflows"><div class="combined-panel-heading"><strong>Quick actions</strong></div><div class="quick-workflow-grid"><a href="{{ route('policy-documents.index') }}"><span class="material-icons">search</span><span><strong>Browse repository</strong><small>Search all documents available to you</small></span></a><a href="{{ route('reports.versions') }}"><span class="material-icons">history</span><span><strong>Version history</strong><small>Review controlled document revisions</small></span></a><a href="{{ route('notifications.index') }}"><span class="material-icons">notifications</span><span><strong>Notifications</strong><small>Review publishing and expiry alerts</small></span></a></div></section>
    </div>
</section>
@endif
@endif

@unless($viewer?->canManagePolicies())
<section class="portal-section" id="directory">
    <div class="portal-container">
        @if(!$viewer)
        <section class="unit-directory" aria-labelledby="unit-directory-title">
            <div class="unit-directory-heading">
                <div><span class="eyebrow">KCDIOM PUBLIC DOCUMENT DIRECTORY</span><h2 id="unit-directory-title">Browse by KCDIOM unit</h2><p>MSD is part of KCDIOM. Choose MSD or another KCDIOM unit group to narrow the public directory.</p></div>
                @if($selectedUnit)<a class="all-units-link" href="{{ route('public-portal') }}#directory"><span class="material-icons">apps</span>View all units</a>@else<span class="unit-total">{{ $allDocumentCount }} public {{ str('record')->plural($allDocumentCount) }}</span>@endif
            </div>
            <div class="unit-card-grid">
                @foreach($unitCards as $unit)
                    <a class="unit-card {{ $selectedUnit === $unit['unit'] ? 'selected' : '' }}" href="{{ route($unit['unit'] === 'msd' ? 'public.msd' : 'public.kcdiom') }}" @if($selectedUnit === $unit['unit']) aria-current="page" @endif>
                        <span class="unit-card-icon material-icons">{{ $unit['icon'] }}</span>
                        <span class="unit-card-copy"><span class="unit-parent">KCDIOM UNIT</span><span class="unit-code">{{ $unit['code'] }}</span><strong>{{ $unit['name'] }}</strong><small>{{ $unit['latest'] ? 'Latest: '.$unit['latest'] : 'No public publications yet' }}</small></span>
                        <span class="unit-card-summary"><strong>{{ $unit['documents'] }}</strong><small>{{ str('document')->plural($unit['documents']) }}</small><em>{{ $unit['circulars'] }} {{ str('circular')->plural($unit['circulars']) }}</em></span>
                        <span class="material-icons unit-arrow">arrow_forward</span>
                    </a>
                @endforeach
            </div>
        </section>
        @endif

        <div class="section-heading">
            <div><span class="eyebrow">DOCUMENT DIRECTORY</span><h2>{{ $viewer ? 'Your accessible documents' : ($selectedUnit === 'msd' ? 'MSD public documents' : ($selectedUnit === 'kcdiom' ? 'Other KCDIOM public documents' : 'Find an official document')) }}</h2><p>{{ $viewer ? 'Search the latest documents available to your staff account.' : 'Only the latest published public version is shown.' }}</p></div>
        </div>

        <form class="filter-card" method="GET" action="{{ $selectedUnit === 'msd' ? route('public.msd') : ($selectedUnit === 'kcdiom' ? route('public.kcdiom') : route('public-portal')) }}">
            <label class="search-field"><span>Search</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Title, reference number or keyword"></label>
            <label><span>Year</span><select name="year"><option value="">All years</option>@foreach($years as $year)<option value="{{ $year }}" @selected((string) request('year') === (string) $year)>{{ $year }}</option>@endforeach</select></label>
            <label><span>Topic</span><select name="category"><option value="">All topics</option>@foreach($categories as $category)<option value="{{ $category }}" @selected(request('category') === $category)>{{ str($category)->replace(['_', '-'], ' ')->title() }}</option>@endforeach</select></label>
            <label><span>Type</span><select name="type"><option value="">All types</option>@foreach($types as $type)<option value="{{ $type }}" @selected(request('type') === $type)>{{ str($type)->replace('_', ' ')->title() }}</option>@endforeach</select></label>
            <button class="filter-button" type="submit">Search</button>
            @if(request()->hasAny(['q','year','category','type']))<a class="reset-link" href="{{ $selectedUnit === 'msd' ? route('public.msd') : ($selectedUnit === 'kcdiom' ? route('public.kcdiom') : route('public-portal')) }}#directory">Clear filters</a>@endif
        </form>

        @php
            $groupedDocuments = $documents->getCollection()->groupBy(function ($document) {
                $date = $document->effective_date ?? $document->published_at ?? $document->created_at;
                return $date ? \Illuminate\Support\Carbon::parse($date)->format('Y') : 'Undated';
            });
        @endphp

        <div class="directory-card">
            <div class="directory-header"><div><span class="folder-icon material-icons" aria-hidden="true">folder_open</span><strong>Published documents</strong></div><span>{{ $documents->total() }} {{ str('record')->plural($documents->total()) }}</span></div>
            @forelse($groupedDocuments as $year => $yearDocuments)
                <section class="year-group">
                    <div class="year-label"><span>{{ $year }}</span><small>{{ $yearDocuments->count() }} {{ str('document')->plural($yearDocuments->count()) }}</small></div>
                    <div class="document-list">
                    @foreach($yearDocuments as $document)
                        @php
                            $mainTopic = $document->subtopic?->mainTopic?->name;
                            $subtopic = $document->subtopic?->name ?? $document->topicDetail?->name;
                            $published = $document->effective_date ?? $document->published_at ?? $document->created_at;
                        @endphp
                        <article class="document-row">
                            <div class="doc-icon" aria-hidden="true">PDF</div>
                            <div class="document-copy">
                                <div class="document-meta"><span>{{ str($document->document_type)->replace('_', ' ')->title() }}</span><span>{{ $document->topic_category ? str($document->topic_category)->replace(['_', '-'], ' ')->title() : 'General' }}</span></div>
                                <h3><a href="{{ route('public.documents.show', $document) }}">{{ $document->title }}</a></h3>
                                <p>{{ $document->reference_number ?: 'No official reference' }}@if($mainTopic) &middot; {{ $mainTopic }}@endif @if($subtopic) &middot; {{ $subtopic }}@endif</p>
                            </div>
                            <div class="document-date"><small>Effective</small><strong>{{ $published ? \Illuminate\Support\Carbon::parse($published)->format('d M Y') : 'Not specified' }}</strong></div>
                            <a class="view-button" href="{{ route('public.documents.show', $document) }}">View <span aria-hidden="true">&rarr;</span></a>
                        </article>
                    @endforeach
                    </div>
                </section>
            @empty
                <div class="empty-state"><strong>No public documents found</strong><p>Try another search term or remove one of the filters.</p></div>
            @endforelse
        </div>

        @if($documents->hasPages())
            <nav class="pagination" aria-label="Document pages">
                @if($documents->onFirstPage())<span class="disabled">Previous</span>@else<a href="{{ $documents->previousPageUrl() }}#directory">Previous</a>@endif
                <span>Page {{ $documents->currentPage() }} of {{ $documents->lastPage() }}</span>
                @if($documents->hasMorePages())<a href="{{ $documents->nextPageUrl() }}#directory">Next</a>@else<span class="disabled">Next</span>@endif
            </nav>
        @endif
    </div>
</section>

@endunless
@endif
@endsection

@extends('layouts.app')

@section('content')
<style>
    .document-detail-page{--doc-teal:#008f85;--doc-dark:#123d37;--doc-soft:#eaf7f4}.document-hero{position:relative;overflow:hidden;display:flex;justify-content:space-between;align-items:center;gap:25px;margin-bottom:20px;padding:28px 30px;border-radius:15px;background:linear-gradient(125deg,#006f68 0%,#009c92 58%,#34b9aa 100%);color:#fff;box-shadow:0 12px 30px rgba(0,119,109,.2)}.document-hero:after{content:'';position:absolute;width:240px;height:240px;right:-65px;top:-110px;border:42px solid rgba(255,255,255,.08);border-radius:50%}.document-hero>*{position:relative;z-index:1}.document-hero .eyebrow{color:#c9fff7}.document-hero h2{margin:4px 0 8px;color:#fff;font-size:30px;font-weight:800}.hero-meta{display:flex;flex-wrap:wrap;gap:8px}.hero-chip{display:inline-flex;align-items:center;gap:5px;padding:6px 11px;border-radius:20px;background:rgba(255,255,255,.16);font-size:12px}.hero-chip .material-icons{font-size:15px}.document-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:8px}.document-actions .btn{display:inline-flex;align-items:center;gap:6px;min-height:42px;border:0;border-radius:9px;font-weight:650}.document-actions .material-icons{font-size:18px}.summary-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:13px;margin-bottom:20px}.summary-tile{display:flex;align-items:center;gap:12px;padding:16px;background:#fff;border:1px solid #dfeae7;border-radius:11px;box-shadow:0 4px 14px rgba(24,67,61,.05)}.summary-icon{display:grid;place-items:center;flex:0 0 42px;height:42px;border-radius:10px;background:var(--doc-soft);color:var(--doc-teal)}.summary-tile small{display:block;color:#80908c}.summary-tile strong{display:block;color:var(--doc-dark)}.document-detail-page .card{border:0;border-radius:12px;box-shadow:0 6px 22px rgba(24,67,61,.07);overflow:hidden}.document-detail-page .card-header{padding:17px 22px;background:linear-gradient(90deg,#f1f9f7,#fff);border-bottom:1px solid #dfebe8}.document-detail-page .card-header h5{color:var(--doc-dark);font-weight:750}.record-card .card-body{padding:22px}.record-card .row>.col-md-6{padding:13px 15px}.record-card .row>.col-md-6 strong{display:block;margin-bottom:4px;color:#718580;font-size:11px;text-transform:uppercase;letter-spacing:.055em}.record-card .row>.col-md-6>div{color:#173e38;font-weight:600}.content-panel{padding:18px!important;border:1px solid #dfebe8!important;border-left:4px solid var(--doc-teal)!important;border-radius:9px!important;background:#f8fbfa!important;line-height:1.65}.document-detail-page table thead th{padding:13px 15px;background:#edf6f4;color:#4b6c66;font-size:11px;text-transform:uppercase;letter-spacing:.05em;border:0}.document-detail-page table tbody td{padding:14px 15px;vertical-align:middle;border-color:#e7efed}.document-detail-page table tbody tr:hover{background:#f8fcfb}.version-panel{position:sticky;top:90px}.version-panel .card-header{background:linear-gradient(135deg,#006f68,#009c92);color:#fff}.version-panel .card-header h5{color:#fff}.version-panel .form-control{min-height:44px;border-color:#d9e5e2;border-radius:8px}.version-panel .form-label{color:#244a44;font-weight:650}.custom-template-card{border-top:4px solid #7367d9!important}@media(max-width:1199px){.summary-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:767px){.document-hero{align-items:flex-start;flex-direction:column;padding:22px}.document-actions{justify-content:flex-start}.summary-grid{grid-template-columns:1fr}.document-hero h2{font-size:24px}}
    /* Override the theme's percentage card height so long records remain in normal flow. */
    .document-detail-page>.row{align-items:flex-start}
    .document-detail-page .card{height:auto!important;min-height:0!important}
    .document-detail-page{padding-bottom:35px;display:flex;flex-direction:column}.document-hero{order:1}.summary-grid{order:2}.pdf-preview-card{order:3}.document-detail-page>.row{order:4}
    .pdf-preview-card{margin-top:0;border:1px solid #d8e7e3!important;border-top:4px solid #e04b4b!important;border-radius:15px!important;box-shadow:0 14px 34px rgba(20,62,55,.10)!important}.pdf-preview-card .card-header{padding:13px 15px!important;background:linear-gradient(90deg,#fff7f7,#fff)!important}.pdf-preview-heading{display:flex;align-items:center;gap:9px;min-width:0}.pdf-preview-icon{width:34px;height:34px;display:grid;place-items:center;flex:0 0 34px;border-radius:9px;background:#ffe9e9;color:#d94343}.pdf-preview-icon .material-icons{font-size:20px}.pdf-preview-heading strong,.pdf-preview-heading small{display:block}.pdf-preview-heading strong{color:#173e38}.pdf-preview-heading small{margin-top:2px;color:#7c8d88;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.pdf-viewer-shell{padding:10px;background:#25302e}.pdf-viewer-shell iframe{height:620px!important;border-radius:7px;background:#3c3c3c}.staff-reading-layout{align-items:flex-start}.booklet-column{position:sticky;top:82px}.booklet-column .version-panel{position:static}.booklet-preview+.version-panel{margin-top:18px}.booklet-preview{border-top-color:#008f85!important;box-shadow:0 18px 42px rgba(17,54,48,.16)!important}.booklet-preview .card-header{background:linear-gradient(90deg,#effaf7,#fff)!important}.booklet-shell{position:relative;padding:16px 12px 16px 27px;background:linear-gradient(145deg,#283532,#16211f)}.booklet-shell:before{content:'';position:absolute;z-index:2;left:10px;top:20px;bottom:20px;width:8px;border-radius:8px;background:linear-gradient(90deg,#09100f,#53615e,#0c1513);box-shadow:3px 0 8px rgba(0,0,0,.38)}.booklet-shell:after{content:'';position:absolute;left:23px;right:10px;bottom:8px;height:8px;border-radius:50%;background:rgba(0,0,0,.35);filter:blur(7px)}.booklet-shell iframe{position:relative;z-index:1;height:540px!important;border-radius:3px;background:#fff;box-shadow:0 12px 28px rgba(0,0,0,.45)}.booklet-label{display:inline-flex;align-items:center;gap:5px;margin-top:4px;color:#68807a;font-size:10px;text-transform:uppercase;letter-spacing:.08em}.booklet-label .material-icons{font-size:13px}.summary-grid{margin-top:5px}.document-hero h2{max-width:760px;line-height:1.2}.document-hero .title-placeholder{opacity:.88}.document-actions .btn{box-shadow:0 5px 12px rgba(0,58,53,.13);transition:transform .16s,box-shadow .16s}.document-actions .btn:hover{transform:translateY(-2px);box-shadow:0 9px 18px rgba(0,58,53,.2)}
    @media(max-width:991px){.booklet-column{position:static;margin-top:18px}.booklet-shell iframe{height:640px!important}}
    @media(max-width:767px){.pdf-preview-card .card-header{align-items:flex-start!important;flex-direction:column}.pdf-preview-card .card-header .btn{width:100%}.pdf-viewer-shell{padding:5px}.booklet-shell{padding:13px 8px 13px 22px}.booklet-shell:before{left:7px;top:18px;bottom:18px;width:8px}.pdf-viewer-shell iframe,.booklet-shell iframe{height:480px!important}}
    .booklet-document-switcher{display:grid;grid-template-columns:auto minmax(0,1fr) auto;align-items:center;gap:8px;padding:10px 13px;background:#f7fbfa;border-top:1px solid #e1ece9}.booklet-document-switcher label{display:flex;align-items:center;gap:5px;margin:0;color:#315c55;font-size:11px;font-weight:700;white-space:nowrap}.booklet-document-switcher label .material-icons,.booklet-document-switcher .btn .material-icons{font-size:17px}.booklet-document-switcher .form-select{min-width:0}.version-attachment-menu{min-width:145px}.version-attachment-menu summary{display:inline-flex;align-items:center;gap:6px;padding:7px 10px;border:0;border-radius:7px;background:#55b8d4;color:#fff;font-size:11px;font-weight:750;cursor:pointer;list-style:none}.version-attachment-menu summary::-webkit-details-marker{display:none}.version-attachment-menu summary .material-icons{font-size:16px}.version-attachment-menu[open] summary{background:#238ead}.version-attachment-list{display:flex;flex-direction:column;gap:5px;width:260px;max-width:100%;margin-top:7px;padding:7px;border:1px solid #d9e7e3;border-radius:9px;background:#fff}.version-attachment-link{display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:7px;align-items:center;padding:8px;border-radius:7px;background:#f6faf9;color:#244d46;font-size:11px}.version-attachment-link:hover{background:#e8f6f2;color:#007e73}.version-attachment-link .material-icons{font-size:17px;color:#d94343}.version-attachment-link span:nth-child(2){overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.version-attachment-link .download-icon{color:#1683a2}.version-pdf-manager{display:flex;flex-direction:column;gap:8px;padding:10px;border:1px solid #cfe2dd;border-radius:11px;background:#f3faf8}.version-pdf-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:9px;align-items:center;padding:10px;border:1px solid #e3ece9;border-radius:9px;background:#fff;transition:.18s}.version-pdf-row:hover{border-color:#aed8d0;box-shadow:0 5px 13px rgba(20,76,66,.07)}.version-pdf-row.is-excluded{opacity:.58;background:#f3f4f4;border-style:dashed}.version-pdf-row .form-check{min-width:0;margin:0}.version-pdf-row .form-check-label{display:grid;grid-template-columns:auto minmax(0,1fr);gap:7px;min-width:0}.version-pdf-row .form-check-label .material-icons{grid-row:1/3;flex:0 0 auto;color:#d94343;font-size:21px}.version-pdf-name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#173e38;font-size:12px;font-weight:700}.version-pdf-state{color:#168276;font-size:10px;font-weight:700}.version-pdf-row.is-excluded .version-pdf-state{color:#8a9491}.version-pdf-actions{display:flex;gap:5px}.version-pdf-actions .btn{display:grid;place-items:center;width:30px;height:30px;padding:0;border-radius:7px}.version-pdf-actions .material-icons{font-size:16px}.version-pdf-help{color:#71847f;font-size:11px;line-height:1.4}.pdf-upload-zone{position:relative;padding:14px;border:1px dashed #9ecdc5;border-radius:10px;background:#f6fbfa;text-align:center}.pdf-upload-zone .material-icons{display:block;margin:auto;color:#008f85;font-size:30px}.pdf-upload-zone strong,.pdf-upload-zone small{display:block}.pdf-upload-zone strong{margin-top:3px;color:#214b44}.pdf-upload-zone small{color:#7b8d88}.pdf-upload-zone input{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}.selected-pdf-list{display:flex;flex-direction:column;gap:6px;margin-top:8px}.selected-pdf-item{display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:7px;align-items:center;padding:7px 9px;border-radius:7px;background:#edf7f4;color:#244b44;font-size:11px}.selected-pdf-item .material-icons{color:#d94343;font-size:17px}.selected-pdf-item span:nth-child(2){overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.selected-pdf-item button{display:grid;place-items:center;width:24px;height:24px;padding:0;border:0;border-radius:5px;background:#fff;color:#d94343}.audit-toggle{display:grid;place-items:center;width:34px;height:34px;padding:0;border:1px solid #d6e6e2;border-radius:8px;background:#fff;color:#087d72;transition:.18s}.audit-toggle:hover{background:#e8f7f3;border-color:#99d5cc}.audit-toggle .material-icons{font-size:21px;transition:transform .2s}.audit-card.is-collapsed .audit-toggle .material-icons{transform:rotate(180deg)}.audit-card.is-collapsed .audit-card-body{display:none}.audit-card.is-collapsed .card-header{border-bottom:0}.audit-list{display:flex;flex-direction:column}.audit-entry{padding:16px 20px;border-bottom:1px solid #e5eeeb;background:#fff}.audit-entry:last-child{border-bottom:0}.audit-entry:nth-child(even){background:#fbfdfc}.audit-entry-head{display:grid;grid-template-columns:145px 95px minmax(140px,1fr);gap:14px;align-items:center;margin-bottom:12px}.audit-meta{min-width:0}.audit-meta small{display:block;margin-bottom:2px;color:#78908a;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em}.audit-meta strong{display:block;overflow-wrap:anywhere;color:#214a43;font-size:13px}.audit-action{display:inline-flex!important;width:max-content;padding:4px 9px;border-radius:16px;background:#e8f6f2;color:#087c70!important}.audit-entry-body{display:grid;grid-template-columns:minmax(180px,.7fr) minmax(0,1.3fr);gap:14px}.audit-section{min-width:0;padding:11px 12px;border:1px solid #e0ebe8;border-radius:9px;background:#f7fbfa}.audit-section-title{display:block;margin-bottom:7px;color:#6b837d;font-size:10px;font-weight:750;text-transform:uppercase;letter-spacing:.06em}.audit-fields{overflow-wrap:anywhere;color:#244c45;font-size:12px;line-height:1.55}.audit-old-values{display:flex;flex-direction:column;gap:6px;min-width:0}.audit-old-item{display:grid;grid-template-columns:minmax(85px,.55fr) minmax(0,1.45fr);gap:8px;align-items:start;padding:6px 8px;border-radius:6px;background:#fff;font-size:12px}.audit-old-item strong{overflow-wrap:anywhere;color:#58716b;font-size:10px;text-transform:uppercase;letter-spacing:.04em}.audit-old-item span{display:block;min-width:0;overflow-wrap:anywhere;color:#173e38;line-height:1.45}.audit-empty{color:#98a6a2;font-style:italic}@media(max-width:767px){.version-pdf-row{grid-template-columns:1fr}.version-pdf-actions{justify-content:flex-end}.audit-entry{padding:14px}.audit-entry-head,.audit-entry-body{grid-template-columns:1fr}.audit-entry-head{gap:8px}.audit-old-item{grid-template-columns:1fr}}
    /* Reading workspace: compact metadata and give the controlled document priority. */
    .staff-reading-layout{--reading-gap:18px;--detail-border:#dfeae7;display:flex;gap:var(--reading-gap);margin-right:0;margin-left:0}
    .staff-reading-layout>[class*="col-"]{padding-right:0;padding-left:0}
    .record-card .card-header{padding:13px 17px}
    .record-card .card-body{padding:13px}
    .record-card .card-body>.row{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px!important;margin:0}
    .record-card .row>.col-md-6{width:auto;min-width:0;padding:9px 11px;border:1px solid var(--detail-border);border-radius:9px;background:linear-gradient(135deg,#f7fbfa,#fff)}
    .record-card .row>.col-md-6 strong{margin-bottom:2px;font-size:9px;line-height:1.25;letter-spacing:.075em}
    .record-card .row>.col-md-6>div{font-size:12px;line-height:1.35;overflow-wrap:anywhere}
    .record-card .row>.col-12{grid-column:1/-1;width:auto;padding:3px 0}
    .record-card .content-panel{max-height:145px;margin-top:3px!important;padding:11px 12px!important;overflow:auto;font-size:12px;line-height:1.5}
    .record-card .content-panel strong{display:block;margin-bottom:4px;color:#627c76;font-size:9px;text-transform:uppercase;letter-spacing:.075em}
    .booklet-column{flex:1 1 0;min-width:0}
    .booklet-preview .card-header{padding:11px 13px!important}
    .booklet-preview .card-header .btn{white-space:nowrap}
    .booklet-shell{padding:13px 10px 13px 25px}
    .booklet-shell iframe{height:min(75vh,790px)!important;min-height:660px}
    @media(min-width:992px){
        .staff-reading-layout>.col-lg-8{flex:0 0 calc(40% - (var(--reading-gap) / 2));max-width:calc(40% - (var(--reading-gap) / 2))}
        .staff-reading-layout>.col-lg-4{flex:0 0 calc(60% - (var(--reading-gap) / 2));max-width:calc(60% - (var(--reading-gap) / 2))}
    }
    @media(max-width:1199px) and (min-width:992px){
        .staff-reading-layout>.col-lg-8{flex-basis:calc(44% - (var(--reading-gap) / 2));max-width:calc(44% - (var(--reading-gap) / 2))}
        .staff-reading-layout>.col-lg-4{flex-basis:calc(56% - (var(--reading-gap) / 2));max-width:calc(56% - (var(--reading-gap) / 2))}
        .record-card .card-body>.row{grid-template-columns:1fr}
        .record-card .row>.col-12{grid-column:1}
    }
    @media(max-width:991px){
        .staff-reading-layout{display:block}
        .staff-reading-layout>[class*="col-"]{width:100%;max-width:none}
        .booklet-shell iframe{height:680px!important;min-height:0}
    }
    @media(max-width:575px){
        .record-card .card-body>.row{grid-template-columns:1fr}
        .record-card .row>.col-12{grid-column:1}
        .booklet-shell iframe{height:500px!important}
    }
    .footer{position:relative!important;clear:both!important;bottom:auto!important}
</style>
@php
    $displayTitle = trim($document->title, " -\t\n\r\0\x0B") !== '' ? $document->title : (($document->document_type === 'circular' ? 'Untitled Circular' : 'Untitled Document'));
    $pdfAttachments = $currentAttachments->filter(fn ($attachment) => strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION)) === 'pdf')->values();
    $legacyPdfAvailable = $legacyPdfAllowed && $document->file_path && strtolower(pathinfo($document->file_original_name ?: $document->file_path, PATHINFO_EXTENSION)) === 'pdf';
    $canPreviewPdf = $legacyPdfAvailable || $pdfAttachments->isNotEmpty();
    $initialPdf = $pdfAttachments->first();
    $initialPreviewUrl = $initialPdf ? route('document-attachments.preview', $initialPdf) : route('policy-documents.preview', $document);
    $formatAuditValue = static function ($value, string $field): string {
        if ($value === null || $value === '') return 'Not set';
        if (is_bool($value)) return $value ? 'Yes' : 'No';
        if (is_array($value)) return implode(', ', array_map(fn ($item) => is_scalar($item) ? (string) $item : json_encode($item), $value));
        if ($field === 'status') return match ((string) $value) { 'published' => 'Active', 'superseded' => 'Superceded', default => ucfirst((string) $value) };
        if ($field === 'file_path') return basename((string) $value);
        return (string) $value;
    };
@endphp
<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Dashboard</a><span class="material-icons">chevron_right</span><a href="{{ route('policy-documents.index') }}">Documents</a><span class="material-icons">chevron_right</span><span>{{ $displayTitle }}</span></div>
<div class="document-detail-page">
<div class="document-hero">
    <div>
        <span class="eyebrow">{{ $document->reference_number ?: 'Unreferenced record' }}</span>
        <h2 class="{{ $displayTitle !== $document->title ? 'title-placeholder' : '' }}">{{ $displayTitle }}</h2>
        <div class="hero-meta"><span class="hero-chip"><span class="material-icons">description</span>{{ ucfirst($document->document_type) }}</span><span class="hero-chip"><span class="material-icons">history</span>Version {{ $document->version_number }}</span><span class="hero-chip"><span class="material-icons">business</span>{{ strtoupper($document->owner_unit) }}</span><span class="hero-chip"><span class="material-icons">verified</span>{{ $document->statusLabel() }}</span></div>
    </div>
    <div class="document-actions">
        @if($document->file_path && $legacyPdfAllowed)
            @if($canPreviewPdf)<a href="{{ $initialPreviewUrl }}" target="_blank" class="btn btn-light"><span class="material-icons">picture_as_pdf</span>Preview PDF</a>@endif
            <a href="{{ route('policy-documents.download', $document) }}" class="btn btn-light"><span class="material-icons">download</span>Download</a>
        @endif
        @if($canPublishDocument)
            <form action="{{ route('policy-documents.publish', $document) }}" method="POST" data-refresh-csrf>
                @csrf
                <button class="btn btn-success"><span class="material-icons">publish</span>Publish</button>
            </form>
        @endif
        @if($canManageDocuments)
            <a href="{{ route('policy-documents.edit', $document) }}" class="btn btn-warning"><span class="material-icons">edit</span>Edit</a>
            <form action="{{ route('policy-documents.destroy', $document) }}" method="POST" data-refresh-csrf onsubmit="return confirm('Delete this document permanently? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger"><span class="material-icons">delete</span>Delete</button>
            </form>
        @endif
        <a href="{{ route('policy-documents.index') }}" class="btn btn-dark"><span class="material-icons">arrow_back</span>Back</a>
    </div>
</div>

<div class="summary-grid">
    <div class="summary-tile"><span class="summary-icon material-icons">admin_panel_settings</span><div><small>Access scope</small><strong>{{ strtoupper($document->access_scope) }}</strong></div></div>
    <div class="summary-tile"><span class="summary-icon material-icons">person</span><div><small>Record owner</small><strong>{{ $document->owner_report ?: strtoupper($document->owner_unit) }}</strong></div></div>
    <div class="summary-tile"><span class="summary-icon material-icons">event_available</span><div><small>Effective date</small><strong>{{ $document->effective_date?->format('d M Y') ?? 'Not set' }}</strong></div></div>
    <div class="summary-tile"><span class="summary-icon material-icons">event_busy</span><div><small>Expiry date</small><strong>{{ $document->expiry_date?->format('d M Y') ?? 'No expiry' }}</strong></div></div>
</div>

<div class="row staff-reading-layout">
    <div class="col-lg-8">
        <div class="card mb-3 record-card">
            <div class="card-header">
                <h5 class="mb-0">Record Details</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>Status</strong>
                        <div>{{ $document->statusLabel() }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Official Reference Number</strong>
                        <div>{{ $document->reference_number ?? 'Not assigned' }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Access Scope</strong>
                        <div>{{ strtoupper($document->access_scope) }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Public Flag</strong>
                        <div>{{ $document->public_flag ? 'Yes' : 'No' }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Owner / Reporting Officer</strong>
                        <div>{{ $document->owner_report ?? 'Not assigned' }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Created By</strong>
                        <div>{{ $document->creator?->name ?? 'Not assigned' }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Published At</strong>
                        <div>{{ $document->published_at?->format('d M Y H:i') ?? 'Not published' }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Published By</strong>
                        <div>{{ $document->publisher?->name ?? 'Not published' }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Last Updated By</strong>
                        <div>{{ $document->updater?->name ?? 'Not recorded' }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Circular Flag</strong>
                        <div>{{ $document->is_circular ? 'Yes' : 'No' }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Effective Published Version</strong>
                        <div>
                            @if($document->effective_version_number)
                                v{{ $document->effective_version_number }}
                            @else
                                Not published
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <strong>Attachment</strong>
                        <div>{{ $document->file_original_name ?? 'No file uploaded' }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Topic Category</strong>
                        <div>{{ $document->topic_category ? ($topicCategories[$document->topic_category] ?? ucfirst($document->topic_category)) : 'Not categorized' }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Subtopic</strong>
                        <div>{{ $document->subtopic?->name ?? 'Not selected' }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Effective Date</strong>
                        <div>{{ $document->effective_date?->format('d M Y') ?? 'Not set' }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Expiry Date</strong>
                        <div>{{ $document->expiry_date?->format('d M Y') ?? 'Not set' }}</div>
                    </div>
                    <div class="col-12">
                        <strong>Content</strong>
                        <div class="content-panel mt-2">
                            {!! nl2br(e($document->content ?: 'No content provided.')) !!}
                        </div>
                    </div>
                    <div class="col-12">
                        <strong>Revision Summary</strong>
                        <div class="content-panel mt-2">
                            {!! nl2br(e($document->revision_summary ?: 'No revision summary provided.')) !!}
                        </div>
                    </div>
                    <div class="col-12">
                        <strong>Remarks</strong>
                        <div class="content-panel mt-2">
                            {!! nl2br(e($document->remarks ?: 'No remarks provided.')) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(config('features.form_builder'))
        @foreach($document->formResponses as $response)
        <div class="card mb-3 custom-template-card">
            <div class="card-header"><h5 class="mb-0">{{ $response->template->name }}</h5><small class="text-muted">Custom template information</small></div>
            <div class="card-body"><div class="row g-3">
                @foreach($response->template->fields as $field)
                    <div class="col-md-4"><strong>{{ $field->label }}</strong><div>{{ is_array($response->values[$field->name] ?? null) ? implode(', ', $response->values[$field->name]) : (($response->values[$field->name] ?? '') !== '' ? $response->values[$field->name] : 'Not provided') }}</div></div>
                @endforeach
            </div></div>
        </div>
        @endforeach
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Version History</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                        <tr>
                            <th>Version</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Published</th>
                            <th>Published By</th>
                            <th>Attachment</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($versions as $version)
                            @php
                                $versionHistory = $normalizedHistories->firstWhere('version_number', $version->version_number);
                                $versionAttachments = $versionHistory?->attachments ?? collect();
                            @endphp
                            <tr>
                                <td>v{{ $version->version_number }}</td>
                                <td>{{ $version->statusLabel() }}</td>
                                <td>{{ $version->creator?->name ?? 'Not assigned' }}</td>
                                <td>{{ $version->published_at?->format('d M Y H:i') ?? 'Not published' }}</td>
                                <td>{{ $version->publisher?->name ?? 'Not published' }}</td>
                                <td>
                                    @if($versionAttachments->count() === 1)
                                        <a href="{{ route('document-attachments.download', $versionAttachments->first()) }}" class="btn btn-sm btn-info"><span class="material-icons align-middle me-1" style="font-size:15px">download</span>Download</a>
                                    @elseif($versionAttachments->count() > 1)
                                        <details class="version-attachment-menu">
                                            <summary><span class="material-icons">folder_zip</span>{{ $versionAttachments->count() }} attachments<span class="material-icons">expand_more</span></summary>
                                            <div class="version-attachment-list">
                                                @foreach($versionAttachments as $attachment)
                                                    <a href="{{ route('document-attachments.download', $attachment) }}" class="version-attachment-link" title="Download {{ $attachment->file_name }}">
                                                        <span class="material-icons">picture_as_pdf</span>
                                                        <span>{{ $attachment->file_name }}</span>
                                                        <span class="material-icons download-icon">download</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </details>
                                    @elseif($version->file_path)
                                        <a href="{{ route('policy-documents.download', $version) }}" class="btn btn-sm btn-info"><span class="material-icons align-middle me-1" style="font-size:15px">download</span>Download</a>
                                    @else
                                        <span class="text-muted">No file</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-3">No additional versions recorded yet.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($canManageDocuments)
            <div class="card mt-3">
                <div class="card-header"><h5 class="mb-0">Attachment Register</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead><tr><th>File</th><th>Version</th><th>Type</th><th>Integrity</th><th>Uploaded By</th><th>Uploaded</th><th>Action</th></tr></thead>
                            <tbody>
                            @forelse($normalizedAttachments as $attachment)
                                <tr>
                                    <td>{{ $attachment->file_name }}</td>
                                    <td>{{ $attachment->history ? 'v'.$attachment->history->version_number : 'Unlinked' }}</td>
                                    <td>{{ $attachment->file_type ?: 'Unknown' }}<br><small class="text-muted">{{ $attachment->file_size ? number_format($attachment->file_size / 1024, 1).' KB' : 'Size unknown' }}</small></td>
                                    <td><span class="status-pill status-published">{{ ucfirst($attachment->security_status) }}</span>@if($attachment->checksum_sha256)<br><small class="text-muted" title="{{ $attachment->checksum_sha256 }}">SHA-256: {{ substr($attachment->checksum_sha256, 0, 12) }}…</small>@endif</td>
                                    <td>{{ $attachment->uploader?->name ?? 'System' }}</td>
                                    <td>{{ $attachment->created_at->format('d M Y H:i') }}</td>
                                    <td><a href="{{ route('document-attachments.download', $attachment) }}" class="btn btn-sm btn-info">Download</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center py-3">No normalized attachments.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mt-3 audit-card" id="activityAuditCard">
                <div class="card-header d-flex align-items-center justify-content-between gap-3">
                    <h5 class="mb-0">Activity Audit Trail</h5>
                    <button type="button" class="audit-toggle" id="activityAuditToggle" aria-expanded="true" aria-controls="activityAuditBody" title="Minimise audit trail"><span class="material-icons">expand_less</span></button>
                </div>
                <div class="card-body p-0 audit-card-body" id="activityAuditBody">
                    <div class="audit-list">
                        @forelse($document->activityLogs as $log)
                            <article class="audit-entry">
                                <div class="audit-entry-head">
                                    <div class="audit-meta"><small>When</small><strong>{{ $log->created_at->format('d M Y H:i') }}</strong></div>
                                    <div class="audit-meta"><small>Action</small><strong class="audit-action">{{ ucfirst($log->action) }}</strong></div>
                                    <div class="audit-meta"><small>User</small><strong>{{ $log->user?->name ?? 'System' }}</strong></div>
                                </div>
                                <div class="audit-entry-body">
                                    <div class="audit-section">
                                        <span class="audit-section-title">Changed Fields</span>
                                        <div class="audit-fields">{{ implode(', ', array_map(fn ($field) => str_replace('_', ' ', $field), array_keys($log->new_values ?? []))) ?: 'No fields recorded' }}</div>
                                    </div>
                                    <div class="audit-section">
                                        <span class="audit-section-title">Old Values</span>
                                        @if(!empty($log->old_values))
                                            <div class="audit-old-values">
                                                @foreach($log->old_values as $field => $value)
                                                    @php($formattedOldValue = $formatAuditValue($value, $field))
                                                    <div class="audit-old-item">
                                                        <strong>{{ str_replace('_', ' ', $field) }}</strong>
                                                        <span title="{{ $formattedOldValue }}">{{ \Illuminate\Support\Str::limit($formattedOldValue, 180) }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="audit-empty">No previous value</span>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="text-center py-4 text-muted">No activity recorded.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if($canPreviewPdf || $canManageDocuments)
        <div class="col-lg-4 booklet-column">
            @if($canPreviewPdf)
            <div class="card pdf-preview-card booklet-preview">
                <div class="card-header d-flex align-items-center justify-content-between gap-3">
                    <div class="pdf-preview-heading">
                        <span class="pdf-preview-icon"><span class="material-icons">menu_book</span></span>
                        <div>
                            <strong>Document preview</strong>
                            <small>{{ $document->file_original_name ?: 'Attached PDF document' }}</small>
                            <span class="booklet-label"><span class="material-icons">auto_stories</span>Interactive PDF reader</span>
                        </div>
                    </div>
                    <a href="{{ $initialPreviewUrl }}" target="_blank" class="btn btn-sm btn-outline-primary" id="bookletFullScreen">
                        <span class="material-icons align-middle me-1" style="font-size:16px">open_in_new</span>Full screen
                    </a>
                </div>
                @if($pdfAttachments->count() > 1)
                <div class="booklet-document-switcher">
                    <label for="bookletDocumentSelect"><span class="material-icons">picture_as_pdf</span>Choose PDF</label>
                    <select id="bookletDocumentSelect" class="form-select form-select-sm">
                        @foreach($pdfAttachments as $attachment)
                            <option value="{{ route('document-attachments.preview', $attachment) }}" data-download="{{ route('document-attachments.download', $attachment) }}">{{ $attachment->file_name }}</option>
                        @endforeach
                    </select>
                    <a href="{{ route('document-attachments.download', $pdfAttachments->first()) }}" class="btn btn-sm btn-outline-secondary" id="bookletDownload"><span class="material-icons">download</span></a>
                </div>
                @endif
                <div class="card-body pdf-viewer-shell booklet-shell">
                    <iframe id="bookletFrame" src="{{ $initialPreviewUrl }}#toolbar=1&navpanes=0&view=FitH" title="PDF booklet for {{ $displayTitle }}" style="display:block;width:100%;border:0" loading="lazy"></iframe>
                </div>
            </div>
            @endif

            @if($canManageDocuments)
            <div class="card version-panel" id="new-version">
                <div class="card-header">
                    <div>
                        <h5 class="mb-1">Create New Version</h5>
                        <p class="mb-0 text-muted small">The current document details and classification will be carried forward automatically.</p>
                    </div>
                    <span class="badge bg-light text-dark">New version · Draft</span>
                </div>
                <form action="{{ route('policy-documents.versions.store', $document) }}" method="POST" enctype="multipart/form-data" id="newVersionForm" data-refresh-csrf>
                    @csrf
                    @if($errors->any())
                        <div class="alert alert-danger mx-3 mt-3 mb-0" role="alert">
                            <strong>Unable to create the version.</strong>
                            <ul class="mb-0 mt-1 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="card-body row g-3">
                        <div class="col-12">
                            <label class="form-label">Revision Summary</label>
                            <textarea name="revision_summary" class="form-control" rows="3" placeholder="Explain what changed in this new version.">{{ old('revision_summary') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Effective Date</label>
                            <input type="date" name="effective_date" class="form-control" value="{{ old('effective_date', $document->effective_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date', $document->expiry_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $document->remarks) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">PDFs from Version {{ $document->version_number }}</label>
                            <input type="hidden" name="attachments_reviewed" value="1">
                            @if($currentAttachments->isNotEmpty())
                                <div class="version-pdf-manager">
                                    @foreach($currentAttachments as $attachment)
                                        <div class="version-pdf-row">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="retain_attachment_ids[]" value="{{ $attachment->id }}" id="retainAttachment{{ $attachment->id }}" checked>
                                                <label class="form-check-label" for="retainAttachment{{ $attachment->id }}">
                                                    <span class="material-icons">picture_as_pdf</span>
                                                    <span class="version-pdf-name" title="{{ $attachment->file_name }}">{{ $attachment->file_name }}</span>
                                                    <span class="version-pdf-state">Included in next version</span>
                                                </label>
                                            </div>
                                            <div class="version-pdf-actions">
                                                <a href="{{ route('document-attachments.preview', $attachment) }}" target="_blank" class="btn btn-sm btn-outline-info" title="Preview PDF"><span class="material-icons">visibility</span></a>
                                                <a href="{{ route('document-attachments.download', $attachment) }}" class="btn btn-sm btn-outline-secondary" title="Download PDF"><span class="material-icons">download</span></a>
                                                <button type="button" class="btn btn-sm btn-outline-warning pdf-retain-toggle" data-checkbox="retainAttachment{{ $attachment->id }}" title="Exclude from next version"><span class="material-icons">remove_circle_outline</span></button>
                                                @if($document->status === 'draft')
                                                    <button type="submit" form="deleteAttachment{{ $attachment->id }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this PDF permanently from the draft version?')" title="Delete permanently"><span class="material-icons">delete</span></button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="version-pdf-help">Keep checked to carry the PDF into the new version. Uncheck it to exclude the PDF without altering the historical version.</div>
                                </div>
                            @else
                                <div class="version-pdf-help">This version has no registered PDF. Add one below if required.</div>
                            @endif
                        </div>
                        <div class="col-12">
                            <label class="form-label">Add PDF Documents</label>
                            <div class="pdf-upload-zone">
                                <span class="material-icons">cloud_upload</span>
                                <strong>Choose or drop PDF files here</strong>
                                <small>Up to 10 PDFs, maximum 3 MB each</small>
                                <input type="file" name="files[]" id="versionPdfFiles" accept="application/pdf,.pdf" multiple>
                            </div>
                            <div class="selected-pdf-list" id="selectedPdfList"></div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_circular" value="1" id="newVersionCircular" @checked($document->is_circular)>
                                <label class="form-check-label" for="newVersionCircular">Keep marked as circular</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary w-100">Create Version</button>
                    </div>
                </form>
                @if($document->status === 'draft')
                    @foreach($currentAttachments as $attachment)
                        <form id="deleteAttachment{{ $attachment->id }}" action="{{ route('document-attachments.destroy', $attachment) }}" method="POST" data-refresh-csrf class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach
                @endif
            </div>
            @endif
        </div>
    @endif
</div>
</div>

<script>
    (function () {
        document.querySelectorAll('.pdf-retain-toggle').forEach((button) => {
            const checkbox = document.getElementById(button.dataset.checkbox);
            if (!checkbox) return;
            const row = button.closest('.version-pdf-row');
            const state = row.querySelector('.version-pdf-state');
            const icon = button.querySelector('.material-icons');
            const syncRetainState = () => {
                row.classList.toggle('is-excluded', !checkbox.checked);
                state.textContent = checkbox.checked ? 'Included in next version' : 'Excluded from next version';
                icon.textContent = checkbox.checked ? 'remove_circle_outline' : 'add_circle_outline';
                button.title = checkbox.checked ? 'Exclude from next version' : 'Include in next version';
            };
            button.addEventListener('click', () => {
                checkbox.checked = !checkbox.checked;
                syncRetainState();
            });
            checkbox.addEventListener('change', syncRetainState);
            syncRetainState();
        });

        const pdfInput = document.getElementById('versionPdfFiles');
        const selectedPdfList = document.getElementById('selectedPdfList');
        if (pdfInput && selectedPdfList) {
            let selectedFiles = [];
            const syncPdfInput = () => {
                const transfer = new DataTransfer();
                selectedFiles.forEach((file) => transfer.items.add(file));
                pdfInput.files = transfer.files;
                selectedPdfList.innerHTML = '';
                selectedFiles.forEach((file, index) => {
                    const item = document.createElement('div');
                    item.className = 'selected-pdf-item';
                    item.innerHTML = '<span class="material-icons">picture_as_pdf</span><span title="' + file.name.replace(/"/g, '&quot;') + '">' + file.name.replace(/</g, '&lt;') + ' · ' + (file.size / 1048576).toFixed(2) + ' MB</span><button type="button" title="Remove selected file"><span class="material-icons" style="font-size:15px">close</span></button>';
                    item.querySelector('button').addEventListener('click', () => {
                        selectedFiles.splice(index, 1);
                        syncPdfInput();
                    });
                    selectedPdfList.appendChild(item);
                });
            };
            pdfInput.addEventListener('change', () => {
                const incoming = Array.from(pdfInput.files);
                const invalid = incoming.find((file) => file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf'));
                const oversized = incoming.find((file) => file.size > 3 * 1024 * 1024);
                if (invalid || oversized || selectedFiles.length + incoming.length > 10) {
                    window.alert(invalid ? 'Only PDF files are allowed.' : oversized ? 'Each PDF must be 3 MB or smaller.' : 'A maximum of 10 PDFs can be uploaded.');
                    syncPdfInput();
                    return;
                }
                incoming.forEach((file) => {
                    if (!selectedFiles.some((existing) => existing.name === file.name && existing.size === file.size)) selectedFiles.push(file);
                });
                syncPdfInput();
            });
        }

        const auditCard = document.getElementById('activityAuditCard');
        const auditToggle = document.getElementById('activityAuditToggle');
        if (auditCard && auditToggle) {
            const storageKey = 'policy-document-{{ $document->id }}-audit-collapsed';
            const applyAuditState = (collapsed) => {
                auditCard.classList.toggle('is-collapsed', collapsed);
                auditToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                auditToggle.title = collapsed ? 'Expand audit trail' : 'Minimise audit trail';
            };
            applyAuditState(window.localStorage.getItem(storageKey) === '1');
            auditToggle.addEventListener('click', () => {
                const collapsed = !auditCard.classList.contains('is-collapsed');
                applyAuditState(collapsed);
                window.localStorage.setItem(storageKey, collapsed ? '1' : '0');
            });
        }

        const bookletSelect = document.getElementById('bookletDocumentSelect');
        const bookletFrame = document.getElementById('bookletFrame');
        const bookletFullScreen = document.getElementById('bookletFullScreen');
        const bookletDownload = document.getElementById('bookletDownload');
        if (bookletSelect && bookletFrame) {
            bookletSelect.addEventListener('change', () => {
                const option = bookletSelect.options[bookletSelect.selectedIndex];
                bookletFrame.src = option.value + '#toolbar=1&navpanes=0&view=FitH';
                if (bookletFullScreen) bookletFullScreen.href = option.value;
                if (bookletDownload) bookletDownload.href = option.dataset.download;
            });
        }

        document.querySelectorAll('form[data-refresh-csrf]').forEach((secureForm) => {
            let tokenRefreshed = false;
            secureForm.addEventListener('submit', async (event) => {
                if (tokenRefreshed) return;

                event.preventDefault();
                const submitButton = event.submitter || secureForm.querySelector('button[type="submit"], button:not([type])');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.dataset.originalText = submitButton.textContent;
                    submitButton.textContent = 'Processing...';
                }

                try {
                    const response = await fetch('{{ route('csrf-token') }}', {
                        credentials: 'same-origin',
                        cache: 'no-store',
                        headers: {'Accept': 'application/json'}
                    });
                    if (!response.ok) throw new Error('Unable to refresh the secure form token.');
                    const payload = await response.json();
                    secureForm.querySelector('input[name="_token"]').value = payload.token;
                    tokenRefreshed = true;
                    if (submitButton) submitButton.disabled = false;
                    secureForm.requestSubmit(submitButton || undefined);
                } catch (error) {
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = submitButton.dataset.originalText || 'Submit';
                    }
                    window.alert(error.message + ' Please refresh the page and try again.');
                }
            });
        });

        filterSubtopics();
    })();
</script>
@endsection

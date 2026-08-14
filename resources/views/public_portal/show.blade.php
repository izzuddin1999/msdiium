@extends('layouts.public')

@section('title', $document->title)

@section('content')
@php
    $mainTopic = $document->subtopic?->mainTopic?->name;
    $subtopic = $document->subtopic?->name ?? $document->topicDetail?->name;
    $firstAttachment = $attachments->first();
@endphp
<section class="detail-hero">
    <div class="portal-container">
        <a class="back-link" href="{{ route($directoryRoute) }}#directory">&larr; Back to {{ $document->owner_unit === 'msd' ? 'MSD' : 'AIKOL' }} directory</a>
        <div class="detail-title-grid">
            <div><span class="eyebrow">OFFICIAL PUBLIC DOCUMENT</span><h1>{{ $document->title }}</h1><p>{{ $document->reference_number ?: 'No official reference number' }}</p></div>
            <div class="detail-badges"><span>{{ str($document->document_type)->replace('_', ' ')->title() }}</span><span>Version {{ $document->version_number }}</span><span>Published</span></div>
        </div>
    </div>
</section>

<section class="portal-section detail-section">
    <div class="portal-container detail-layout">
        <article class="document-detail-card">
            <div class="card-heading"><span>Document information</span><small>Public record</small></div>
            <dl class="metadata-grid">
                <div><dt>Reference number</dt><dd>{{ $document->reference_number ?: 'Not assigned' }}</dd></div>
                <div><dt>Effective date</dt><dd>{{ $document->effective_date?->format('d M Y') ?? 'Not specified' }}</dd></div>
                <div><dt>Document type</dt><dd>{{ str($document->document_type)->replace('_', ' ')->title() }}</dd></div>
                <div><dt>Owner</dt><dd>{{ $document->organization?->name ?? strtoupper($document->owner_unit) }}</dd></div>
                <div><dt>Topic category</dt><dd>{{ $document->topic_category ?: 'General' }}</dd></div>
                <div><dt>Main topic</dt><dd>{{ $mainTopic ?: 'Not classified' }}</dd></div>
                <div><dt>Subtopic</dt><dd>{{ $subtopic ?: 'Not classified' }}</dd></div>
                <div><dt>Version</dt><dd>Version {{ $document->version_number }}</dd></div>
            </dl>
            <div class="content-block">
                <h2>Document content</h2>
                @if(filled($document->content))
                    <p class="document-content-text">{!! nl2br(e($document->content)) !!}</p>
                @else
                    <p class="document-content-empty">No content has been provided for this document version.</p>
                @endif
            </div>
            <div class="attachment-section">
                <div class="card-heading"><span>Public PDF documents</span><small>{{ $attachments->count() }} {{ str('attachment')->plural($attachments->count()) }}</small></div>
                @forelse($attachments as $attachment)
                    @php($previewUrl = route('public.attachments.preview', $attachment))
                    <div class="attachment-row {{ $loop->first ? 'is-previewing' : '' }}" data-attachment-row>
                        <span class="pdf-chip">PDF</span>
                        <div><strong>{{ $attachment->file_name }}</strong><small>{{ $attachment->file_size ? number_format($attachment->file_size / 1024, 1).' KB' : 'Official attachment' }}</small></div>
                        <div class="attachment-actions">
                            <a href="{{ $previewUrl }}" class="{{ $loop->first ? 'active' : '' }}" data-pdf-preview data-preview-url="{{ $previewUrl }}" data-preview-name="{{ $attachment->file_name }}" aria-current="{{ $loop->first ? 'true' : 'false' }}">Preview</a>
                            <a href="{{ route('public.attachments.download', $attachment) }}">Download</a>
                        </div>
                    </div>
                @empty
                    <div class="empty-state compact"><p>No public PDF is attached to this record.</p></div>
                @endforelse
            </div>
        </article>

        <aside class="preview-card">
            <div class="card-heading">
                <span class="preview-heading-copy">Document preview @if($firstAttachment)<small id="activePdfName">{{ $firstAttachment->file_name }}</small>@endif</span>
                @if($firstAttachment)<a id="openPdfFullScreen" href="{{ route('public.attachments.preview', $firstAttachment) }}" target="_blank" rel="noopener">Open full screen</a>@endif
            </div>
            @if($firstAttachment)
                <iframe id="publicPdfPreview" title="Preview of {{ $firstAttachment->file_name }}" src="{{ route('public.attachments.preview', $firstAttachment) }}#toolbar=1&navpanes=0"></iframe>
            @else
                <div class="preview-empty"><span>PDF</span><strong>Preview unavailable</strong><p>This record does not have a public PDF attachment.</p></div>
            @endif
        </aside>
    </div>
</section>

@if($firstAttachment)
    <style>
        .attachment-row.is-previewing { background: #eef9f7; box-shadow: inset 4px 0 0 var(--portal-teal, #009688); }
        .attachment-actions a.active { background: var(--portal-teal, #009688); border-color: var(--portal-teal, #009688); color: #fff; }
        .preview-heading-copy { min-width: 0; }
        .preview-heading-copy small { display: block; margin-top: .15rem; max-width: 22rem; overflow: hidden; color: #6b7280; font-size: .72rem; font-weight: 500; text-overflow: ellipsis; white-space: nowrap; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const fullScreenLink = document.getElementById('openPdfFullScreen');
            const activeName = document.getElementById('activePdfName');
            const previewLinks = document.querySelectorAll('[data-pdf-preview]');

            previewLinks.forEach((link) => {
                link.addEventListener('click', (event) => {
                    event.preventDefault();

                    const previewUrl = link.dataset.previewUrl;
                    const previewName = link.dataset.previewName;
                    const currentFrame = document.getElementById('publicPdfPreview');
                    const nextFrame = document.createElement('iframe');
                    nextFrame.id = 'publicPdfPreview';
                    nextFrame.title = `Preview of ${previewName}`;
                    nextFrame.src = `${previewUrl}#toolbar=1&navpanes=0`;
                    currentFrame.replaceWith(nextFrame);
                    fullScreenLink.href = previewUrl;
                    activeName.textContent = previewName;

                    previewLinks.forEach((item) => {
                        item.classList.remove('active');
                        item.setAttribute('aria-current', 'false');
                    });
                    document.querySelectorAll('[data-attachment-row]').forEach((row) => row.classList.remove('is-previewing'));
                    link.classList.add('active');
                    link.setAttribute('aria-current', 'true');
                    link.closest('[data-attachment-row]')?.classList.add('is-previewing');
                });
            });
        });
    </script>
@endif
@endsection

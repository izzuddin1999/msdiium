@extends('layouts.app')

@section('content')
<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Dashboard</a><span class="material-icons">chevron_right</span><a href="{{ route('policy-documents.index') }}">Documents</a><span class="material-icons">chevron_right</span><a href="{{ route('policy-documents.show', $document) }}">{{ $document->title }}</a><span class="material-icons">chevron_right</span><span>Edit</span></div>
<div class="page-heading"><div><span class="eyebrow">Metadata maintenance</span><h2>Update document record</h2><p>Edit metadata or replace the controlled file. Use “Create New Version” for substantive revisions.</p></div></div>

<style>
    .edit-workspace{border:1px solid #d7e6e2;border-radius:18px;overflow:hidden;background:#fff;box-shadow:0 18px 50px rgba(19,70,60,.10)}
    .edit-workspace-bar{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:18px 24px;color:#fff;background:linear-gradient(115deg,#075c51 0%,#008f85 58%,#25b6a4 100%)}
    .edit-workspace-bar strong,.edit-workspace-bar small{display:block}.edit-workspace-bar strong{font-size:17px}.edit-workspace-bar small{margin-top:3px;color:rgba(255,255,255,.78)}
    .record-chip{display:inline-flex;align-items:center;gap:7px;padding:8px 12px;border:1px solid rgba(255,255,255,.35);border-radius:999px;background:rgba(255,255,255,.13);font-size:12px;font-weight:700}.record-chip .material-icons{font-size:17px}
    .edit-workspace .card-body{padding:20px 24px 24px;background:linear-gradient(180deg,#fbfdfc,#fff 190px)}
    .edit-workspace .form-section-title{position:relative;margin:8px 0 2px;padding:12px 16px 12px 58px;min-height:58px;border:1px solid #d7eae5;border-radius:11px;background:linear-gradient(90deg,#eaf8f5,#f8fcfb);display:flex;flex-direction:column;justify-content:center}
    .edit-workspace .form-section-title:before{position:absolute;left:15px;top:50%;transform:translateY(-50%);width:31px;height:31px;display:grid;place-items:center;border-radius:9px;background:linear-gradient(135deg,#007d73,#18ad9f);color:#fff;font-size:13px;font-weight:800;box-shadow:0 5px 12px rgba(0,125,115,.22)}
    .section-identity:before{content:'1'}.section-classification:before{content:'2'}.section-ownership:before{content:'3'}.section-content:before{content:'4'}
    .edit-workspace .form-section-title h6{margin:0 0 2px;color:#103e37;font-size:15px;font-weight:750}.edit-workspace .form-section-title small{color:#71847f;font-size:12px}
    .edit-workspace .form-label{margin-bottom:5px;color:#183d38;font-size:12px;font-weight:700}.edit-workspace .form-control{min-height:44px;border-color:#d5e2df;border-radius:9px;background:#fff;font-size:13px}.edit-workspace .form-control:focus{border-color:#009c92;box-shadow:0 0 0 3px rgba(0,156,146,.12)}
    .required-mark{margin-left:3px;color:#dc3545;font-weight:800}.required-note{display:inline-flex;align-items:center;gap:4px;margin:0 24px 12px;color:#667b76;font-size:11px}.required-note .required-mark{margin-left:0}
    .edit-workspace .ii-featured-field{padding:14px 16px;border:1px solid #d8e7e3;border-radius:12px;background:linear-gradient(135deg,#f2faf8 0%,#fff 72%)}
    .edit-workspace .ii-title-input{min-height:96px!important;height:auto!important;padding:15px 17px;border-left:4px solid #009c92;font-size:16px;font-weight:650;line-height:1.55;resize:vertical}
    .edit-workspace .ii-field-help{display:block;margin-top:6px;color:#71847f;font-size:10px}
    .edit-workspace .resizable-textarea{width:100%;max-width:100%;min-width:min(260px,100%);min-height:140px;resize:both;overflow:auto}
    .edit-workspace .resize-hint{display:flex;align-items:center;gap:4px;margin-top:5px;color:#71847f;font-size:10px}
    .edit-workspace .resize-hint .material-icons{font-size:13px}
    .edit-workspace .option-tile{min-height:44px;padding:11px 14px;border:1px solid #d5e2df;border-radius:9px;background:#f7fbfa}.edit-workspace .option-tile .form-check{margin:0}
    .edit-pdf-uploader{padding:14px;border:1px dashed #8fc9c0;border-radius:12px;background:linear-gradient(135deg,#f1faf8,#fff)}
    .edit-pdf-picker{position:relative;display:flex;align-items:center;justify-content:center;gap:10px;min-height:64px;padding:12px;border:1px solid #d7e8e4;border-radius:9px;background:#fff;color:#24534c;text-align:center;cursor:pointer;transition:.18s}
    .edit-pdf-picker:hover,.edit-pdf-picker.is-dragging{border-color:#009c92;background:#edfaf7;box-shadow:0 0 0 3px rgba(0,156,146,.09)}
    .edit-pdf-picker .material-icons{color:#d94343;font-size:27px}.edit-pdf-picker strong,.edit-pdf-picker small{display:block}.edit-pdf-picker small{margin-top:2px;color:#7b8d88}
    .edit-pdf-picker input{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}
    .edit-pdf-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:7px;margin-top:10px}
    .edit-pdf-item{display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:8px;align-items:center;padding:9px 10px;border:1px solid #dfebe8;border-radius:8px;background:#fff}
    .edit-pdf-item>.material-icons{color:#d94343;font-size:20px}.edit-pdf-item-name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#173e38;font-size:11px;font-weight:700}.edit-pdf-item-size{display:block;color:#82918d;font-size:9px;font-weight:500}
    .edit-pdf-remove{display:grid;place-items:center;width:27px;height:27px;padding:0;border:0;border-radius:7px;background:#fff0f0;color:#d94343}.edit-pdf-remove .material-icons{font-size:16px}
    .edit-pdf-summary{display:none;align-items:center;justify-content:space-between;gap:10px;margin-top:9px;padding-top:9px;border-top:1px solid #dfeae7;color:#55736d;font-size:11px}.edit-pdf-summary.is-visible{display:flex}.edit-pdf-summary strong{color:#087c70}
    .edit-pdf-error{display:none;margin-top:8px;color:#b93939;font-size:11px;font-weight:650}.edit-pdf-error.is-visible{display:block}
    .uploaded-pdf-register{overflow:hidden;border:1px solid #d8e7e3;border-radius:12px;background:#f8fbfa}
    .uploaded-pdf-register-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:11px 14px;border-bottom:1px solid #dfeae7;background:#edf7f5}
    .uploaded-pdf-register-head strong{color:#174b43;font-size:13px}.uploaded-pdf-register-head span{color:#71847f;font-size:10px}
    .uploaded-pdf-row{display:grid;grid-template-columns:minmax(0,1fr) 190px auto;align-items:center;gap:12px;padding:12px 14px;border-bottom:1px solid #e4ecea}.uploaded-pdf-row:last-child{border-bottom:0}
    .uploaded-pdf-file{display:flex;align-items:center;gap:9px;min-width:0}.uploaded-pdf-file>.material-icons{flex:0 0 auto;color:#d94343;font-size:23px}
    .uploaded-pdf-file strong,.uploaded-pdf-file small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.uploaded-pdf-file strong{color:#173e38;font-size:11px}.uploaded-pdf-file small{margin-top:2px;color:#83928e;font-size:9px}
    .attachment-access-control label{display:block;margin:0 0 4px;color:#6d837e;font-size:9px;font-weight:750;text-transform:uppercase;letter-spacing:.055em}.attachment-visibility{min-height:36px!important;padding:6px 9px!important;font-size:11px!important}
    .uploaded-pdf-actions{display:flex;gap:5px}.uploaded-pdf-actions .btn{display:grid;place-items:center;width:32px;height:32px;padding:0}.uploaded-pdf-actions .material-icons{font-size:16px}
    .attachment-flow{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin:0 0 12px}.attachment-flow-step{display:flex;align-items:center;gap:8px;padding:9px 10px;border:1px solid #dbe9e5;border-radius:8px;background:#fff;color:#55716b;font-size:10px}.attachment-flow-step strong{display:grid;place-items:center;flex:0 0 23px;height:23px;border-radius:50%;background:#e5f6f2;color:#087d72}.new-pdf-options{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:10px 0 0}.new-pdf-options label{margin:0;color:#365f58;font-size:11px;font-weight:700}.new-pdf-options label small{display:block;margin-top:2px;color:#7b8d88;font-weight:500}.new-pdf-options select{width:240px;min-height:36px!important}
    .edit-workspace .card-footer{position:sticky;bottom:0;z-index:5;padding:14px 24px;background:rgba(247,250,249,.96);backdrop-filter:blur(10px);border-top:1px solid #dce8e4;box-shadow:0 -8px 24px rgba(21,65,56,.07)}.edit-workspace .card-footer .btn{min-height:42px;padding:9px 20px;border-radius:9px;font-weight:700}
    @media(max-width:767px){.edit-workspace-bar{align-items:flex-start;flex-direction:column}.edit-workspace .card-body{padding:16px}.record-chip{display:none}.edit-pdf-list,.attachment-flow{grid-template-columns:1fr}.uploaded-pdf-row{grid-template-columns:minmax(0,1fr) auto}.uploaded-pdf-row>.attachment-access-control{grid-column:1/-1;grid-row:2}.new-pdf-options{align-items:flex-start;flex-direction:column}.new-pdf-options select{width:100%}}
</style>

<form action="{{ route('policy-documents.update', $document) }}" method="POST" enctype="multipart/form-data" class="card edit-workspace">
    @csrf
    @method('PUT')
    <div class="edit-workspace-bar"><div><strong>Document maintenance workspace</strong><small>Update governed metadata while preserving this record’s version history.</small></div><span class="record-chip"><span class="material-icons">verified_user</span>Version {{ $document->version_number }} · {{ $document->statusLabel() }}</span></div>
    <div class="required-note"><span class="required-mark" aria-hidden="true">*</span> Required field</div>
    <div class="card-body row g-3">
        <div class="col-12 form-section-title section-identity"><h6>Document identity</h6><small>Maintain the official title, reference number, type and lifecycle status.</small></div>
        <div class="col-12 ii-featured-field">
            <label class="form-label" for="documentTitle">Document Title <span class="required-mark" aria-hidden="true">*</span><span class="visually-hidden">required</span></label>
            <textarea id="documentTitle" name="title" class="form-control ii-title-input" rows="3" maxlength="255" placeholder="Enter the full official document title" required>{{ old('title', $document->title) }}</textarea>
            <small class="ii-field-help">Use the complete title exactly as it appears on the approved document.</small>
        </div>
        <div class="col-md-6">
            <label class="form-label">Official Reference Number</label>
            <input name="reference_number" class="form-control" value="{{ old('reference_number', $document->reference_number) }}" maxlength="100">
        </div>
        <div class="col-md-3">
            <label class="form-label">Document Type <span class="required-mark" aria-hidden="true">*</span><span class="visually-hidden">required</span></label>
            <select name="document_type" class="form-control" required>
                @foreach($documentTypes as $type => $label)
                    <option value="{{ $type }}" @selected(old('document_type', $document->document_type) === $type)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Status <span class="required-mark" aria-hidden="true">*</span><span class="visually-hidden">required</span></label>
            <select name="status" class="form-control" required>
                @foreach($documentStatuses as $status => $label)
                    <option value="{{ $status }}" @selected(old('status', $document->status) === $status)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12 form-section-title section-classification"><h6>Topic classification</h6><small>Follow the controlled category → main topic → subtopic hierarchy.</small></div>
        <div class="col-md-4">
            <label class="form-label">Topic Category</label>
            <select name="topic_category" id="editMainTopic" class="form-control">
                <option value="">Select topic category</option>
                @foreach($topicCategories as $slug => $label)
                    <option value="{{ $slug }}" @selected(old('topic_category', $document->topic_category) === $slug)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Main Topic</label>
            <select name="subtopic_id" id="editSubtopic" class="form-control">
                <option value="">Select main topic</option>
                @foreach($subtopics as $subtopic)
                    <option
                        value="{{ $subtopic->id }}"
                        data-main="{{ $subtopic->mainTopic?->slug }}"
                        @selected((int) old('subtopic_id', $document->subtopic_id) === $subtopic->id)
                    >
                        {{ $subtopic->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Subtopic</label>
            <select name="topic_detail_id" id="editTopicDetail" class="form-control">
                <option value="">Select subtopic</option>
                @foreach($topicDetails as $detail)
                    <option value="{{ $detail->id }}" data-main-topic="{{ $detail->main_topic_id }}" @selected((int) old('topic_detail_id', $document->topic_detail_id) === $detail->id)>{{ $detail->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12 form-section-title section-ownership"><h6>Ownership and access</h6><small>Identify responsibility and control the document’s visibility.</small></div>
        <div class="col-md-4">
            <label class="form-label">Permitted Users <span class="required-mark" aria-hidden="true">*</span><span class="visually-hidden">required</span></label>
            <select name="access_scope" class="form-control" required>
                @foreach(['all', $managementUnit] as $scope)
                    <option value="{{ $scope }}" @selected(old('access_scope', $document->access_scope) === $scope)>{{ $scope === 'all' ? 'All permitted users' : strtoupper($scope).' permitted users' }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div class="option-tile w-100"><div class="form-check">
                <input class="form-check-input" type="checkbox" name="public_flag" value="1" id="publicFlag" @checked(old('public_flag', $document->public_flag))>
                <label class="form-check-label" for="publicFlag">Show on public portal</label>
            </div></div>
        </div>

        <div class="col-12 form-section-title section-content"><h6>Content, validity and controlled file</h6><small>Keep the searchable record, dates, remarks and source attachment current.</small></div>
        <div class="col-12">
            <label class="form-label">Content <span class="required-mark" aria-hidden="true">*</span><span class="visually-hidden">required</span></label>
            <textarea name="content" class="form-control resizable-textarea" rows="5" required>{{ old('content', $document->content) }}</textarea>
            <small class="resize-hint"><span class="material-icons">open_in_full</span>Drag the lower-right corner to resize this field.</small>
        </div>

        <div class="col-md-6">
            <label class="form-label">Effective Date</label>
            <input type="date" name="effective_date" class="form-control" value="{{ old('effective_date', $document->effective_date?->format('Y-m-d')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Expiry Date</label>
            <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date', $document->expiry_date?->format('Y-m-d')) }}">
        </div>
        <div class="col-12">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control resizable-textarea" rows="3" maxlength="2000">{{ old('remarks', $document->remarks) }}</textarea>
            <small class="resize-hint"><span class="material-icons">open_in_full</span>Drag the lower-right corner to resize this field.</small>
        </div>

        <div class="col-12">
            <label class="form-label">Uploaded PDF Documents</label>
            <div class="uploaded-pdf-register">
                <div class="uploaded-pdf-register-head">
                    <strong>{{ $currentAttachments->count() }} {{ Str::plural('PDF', $currentAttachments->count()) }} attached to Version {{ $document->version_number }}</strong>
                    <span>Access applies to preview, download and AI answers</span>
                </div>
                @forelse($currentAttachments as $attachment)
                    <div class="uploaded-pdf-row">
                        <div class="uploaded-pdf-file">
                            <span class="material-icons">picture_as_pdf</span>
                            <span>
                                <strong title="{{ $attachment->file_name }}">{{ $attachment->file_name }}</strong>
                                <small>{{ $attachment->file_size ? number_format($attachment->file_size / 1024, 1).' KB' : 'PDF document' }}</small>
                            </span>
                        </div>
                        <div class="attachment-access-control">
                            <label for="attachmentVisibility{{ $attachment->id }}">PDF Access Role</label>
                            <select id="attachmentVisibility{{ $attachment->id }}" name="attachment_visibility[{{ $attachment->id }}]" class="form-control attachment-visibility" aria-label="Access for {{ $attachment->file_name }}">
                                <option value="public" @selected(old("attachment_visibility.{$attachment->id}", $attachment->is_public ? 'public' : 'internal') === 'public')>Permitted users</option>
                                <option value="internal" @selected(old("attachment_visibility.{$attachment->id}", $attachment->is_public ? 'public' : 'internal') === 'internal')>Policy managers &amp; system administrators</option>
                            </select>
                        </div>
                        <div class="uploaded-pdf-actions">
                            <a href="{{ route('document-attachments.preview', $attachment) }}" target="_blank" class="btn btn-outline-info" title="Preview PDF"><span class="material-icons">visibility</span></a>
                            <a href="{{ route('document-attachments.download', $attachment) }}" class="btn btn-outline-secondary" title="Download PDF"><span class="material-icons">download</span></a>
                        </div>
                    </div>
                @empty
                    <div class="p-3 text-center text-muted small">No PDF has been uploaded for this version.</div>
                @endforelse
            </div>
        </div>

        <div class="col-12">
            <label class="form-label">Add PDF Documents</label>
            <div class="edit-pdf-uploader">
                <div class="attachment-flow" aria-label="PDF access workflow">
                    <div class="attachment-flow-step"><strong>1</strong><span>Select one or more PDF files</span></div>
                    <div class="attachment-flow-step"><strong>2</strong><span>Choose who may open them</span></div>
                    <div class="attachment-flow-step"><strong>3</strong><span>Save and verify the document page</span></div>
                </div>
                <label class="edit-pdf-picker" id="editPdfPicker">
                    <span class="material-icons">picture_as_pdf</span>
                    <span><strong>Add one or more PDFs</strong><small>Choose several files together, or return to add more</small></span>
                    <input type="file" name="files[]" id="editPdfFiles" accept="application/pdf,.pdf" multiple>
                </label>
                <div class="edit-pdf-list" id="editPdfList" aria-live="polite"></div>
                <div class="edit-pdf-summary" id="editPdfSummary"><span><strong id="editPdfCount">0</strong> of 10 PDFs selected</span><span id="editPdfTotal">0 MB total</span></div>
                <div class="edit-pdf-error" id="editPdfError" role="alert"></div>
                <div class="new-pdf-options">
                    <label for="newAttachmentVisibility">PDF access role for new uploads<small>Applied to every PDF currently in the upload queue.</small></label>
                    <select name="new_attachment_visibility" id="newAttachmentVisibility" class="form-control">
                        <option value="internal" @selected(old('new_attachment_visibility', 'internal') === 'internal')>Policy managers &amp; system administrators</option>
                        <option value="public" @selected(old('new_attachment_visibility') === 'public')>Permitted users</option>
                    </select>
                </div>
            </div>
            <small class="text-muted d-block mt-1">PDF only. Select up to 10 documents (maximum 3 MB each). New selections are added to the queue.</small>
            @if($document->file_path)
                <small class="text-muted">Primary document: {{ $document->file_original_name }}</small>
            @endif
        </div>
    </div>
    <div class="card-footer d-flex gap-2">
        <button class="btn btn-primary"><span class="material-icons align-middle me-1" style="font-size:18px">save</span>Save changes</button>
        <a href="{{ route('policy-documents.show', $document) }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<script>
    (function () {
        const mainSelect = document.getElementById('editMainTopic');
        const subSelect = document.getElementById('editSubtopic');
        const detailSelect = document.getElementById('editTopicDetail');
        if (!mainSelect || !subSelect) {
            return;
        }

        const filterSubtopics = () => {
            const selectedMain = mainSelect.value;
            Array.from(subSelect.options).forEach((option, index) => {
                if (index === 0) {
                    option.hidden = false;
                    return;
                }
                option.hidden = selectedMain !== '' && option.dataset.main !== selectedMain;
            });

            const currentOption = subSelect.options[subSelect.selectedIndex];
            if (currentOption && currentOption.hidden) {
                subSelect.value = '';
            }
            filterTopicDetails();
        };

        const filterTopicDetails = () => {
            if (!detailSelect) return;
            Array.from(detailSelect.options).forEach((option, index) => option.hidden = index > 0 && option.dataset.mainTopic !== subSelect.value);
            const current = detailSelect.options[detailSelect.selectedIndex];
            if (current && current.hidden) detailSelect.value = '';
        };

        mainSelect.addEventListener('change', filterSubtopics);
        subSelect.addEventListener('change', filterTopicDetails);
        filterSubtopics();

        const pdfInput = document.getElementById('editPdfFiles');
        const pdfList = document.getElementById('editPdfList');
        const pdfSummary = document.getElementById('editPdfSummary');
        const pdfCount = document.getElementById('editPdfCount');
        const pdfTotal = document.getElementById('editPdfTotal');
        const pdfError = document.getElementById('editPdfError');
        const pdfPicker = document.getElementById('editPdfPicker');
        const queuedPdfs = new Map();
        const fileKey = (file) => `${file.name}:${file.size}:${file.lastModified}`;

        const formatSize = (bytes) => bytes < 1048576
            ? `${Math.max(1, Math.round(bytes / 1024))} KB`
            : `${(bytes / 1048576).toFixed(2)} MB`;

        const syncPdfInput = () => {
            const transfer = new DataTransfer();
            queuedPdfs.forEach((file) => transfer.items.add(file));
            pdfInput.files = transfer.files;
        };

        const renderPdfQueue = () => {
            pdfList.innerHTML = '';
            let totalBytes = 0;
            queuedPdfs.forEach((file, key) => {
                totalBytes += file.size;
                const item = document.createElement('div');
                item.className = 'edit-pdf-item';
                const icon = document.createElement('span');
                icon.className = 'material-icons';
                icon.textContent = 'picture_as_pdf';
                const name = document.createElement('span');
                name.className = 'edit-pdf-item-name';
                name.title = file.name;
                name.textContent = file.name;
                const size = document.createElement('small');
                size.className = 'edit-pdf-item-size';
                size.textContent = formatSize(file.size);
                name.appendChild(size);
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'edit-pdf-remove';
                remove.setAttribute('aria-label', `Remove ${file.name}`);
                const removeIcon = document.createElement('span');
                removeIcon.className = 'material-icons';
                removeIcon.textContent = 'close';
                remove.appendChild(removeIcon);
                remove.addEventListener('click', () => {
                    queuedPdfs.delete(key);
                    syncPdfInput();
                    renderPdfQueue();
                });
                item.append(icon, name, remove);
                pdfList.appendChild(item);
            });
            pdfCount.textContent = queuedPdfs.size;
            pdfTotal.textContent = `${(totalBytes / 1048576).toFixed(2)} MB total`;
            pdfSummary.classList.toggle('is-visible', queuedPdfs.size > 0);
        };

        pdfInput?.addEventListener('change', () => {
            pdfError.classList.remove('is-visible');
            const incoming = Array.from(pdfInput.files || []);
            const problems = [];
            incoming.forEach((file) => {
                if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
                    problems.push(`${file.name} is not a PDF.`);
                } else if (file.size > 3 * 1024 * 1024) {
                    problems.push(`${file.name} exceeds 3 MB.`);
                } else if (!queuedPdfs.has(fileKey(file)) && queuedPdfs.size < 10) {
                    queuedPdfs.set(fileKey(file), file);
                } else if (!queuedPdfs.has(fileKey(file))) {
                    problems.push('Only 10 PDFs can be uploaded at once.');
                }
            });
            syncPdfInput();
            renderPdfQueue();
            if (problems.length) {
                pdfError.textContent = problems.join(' ');
                pdfError.classList.add('is-visible');
            }
        });

        ['dragenter', 'dragover'].forEach((eventName) => pdfPicker?.addEventListener(eventName, () => pdfPicker.classList.add('is-dragging')));
        ['dragleave', 'drop'].forEach((eventName) => pdfPicker?.addEventListener(eventName, () => pdfPicker.classList.remove('is-dragging')));
    })();
</script>
@endsection

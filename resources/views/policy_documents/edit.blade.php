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
    .edit-workspace .option-tile{min-height:44px;padding:11px 14px;border:1px solid #d5e2df;border-radius:9px;background:#f7fbfa}.edit-workspace .option-tile .form-check{margin:0}
    .edit-workspace .card-footer{position:sticky;bottom:0;z-index:5;padding:14px 24px;background:rgba(247,250,249,.96);backdrop-filter:blur(10px);border-top:1px solid #dce8e4;box-shadow:0 -8px 24px rgba(21,65,56,.07)}.edit-workspace .card-footer .btn{min-height:42px;padding:9px 20px;border-radius:9px;font-weight:700}
    @media(max-width:767px){.edit-workspace-bar{align-items:flex-start;flex-direction:column}.edit-workspace .card-body{padding:16px}.record-chip{display:none}}
</style>

<form action="{{ route('policy-documents.update', $document) }}" method="POST" enctype="multipart/form-data" class="card edit-workspace">
    @csrf
    @method('PUT')
    <div class="edit-workspace-bar"><div><strong>Document maintenance workspace</strong><small>Update governed metadata while preserving this record’s version history.</small></div><span class="record-chip"><span class="material-icons">verified_user</span>Version {{ $document->version_number }} · {{ $document->statusLabel() }}</span></div>
    <div class="card-body row g-3">
        <div class="col-12 form-section-title section-identity"><h6>Document identity</h6><small>Maintain the official title, reference number, type and lifecycle status.</small></div>
        <div class="col-md-6">
            <label class="form-label">Title</label>
            <input name="title" class="form-control" value="{{ old('title', $document->title) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Official Reference Number</label>
            <input name="reference_number" class="form-control" value="{{ old('reference_number', $document->reference_number) }}" maxlength="100">
        </div>
        <div class="col-md-3">
            <label class="form-label">Document Type</label>
            <select name="document_type" class="form-control" required>
                @foreach($documentTypes as $type => $label)
                    <option value="{{ $type }}" @selected(old('document_type', $document->document_type) === $type)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
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
            <label class="form-label">Access Scope</label>
            <select name="access_scope" class="form-control" required>
                @foreach(['all','kcdiom','msd'] as $scope)
                    <option value="{{ $scope }}" @selected(old('access_scope', $document->access_scope) === $scope)>{{ strtoupper($scope) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div class="option-tile w-100"><div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_circular" value="1" id="isCircular" @checked(old('is_circular', $document->is_circular))>
                <label class="form-check-label" for="isCircular">Set as Circular</label>
            </div></div>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div class="option-tile w-100"><div class="form-check">
                <input class="form-check-input" type="checkbox" name="public_flag" value="1" id="publicFlag" @checked(old('public_flag', $document->public_flag))>
                <label class="form-check-label" for="publicFlag">Publicly visible</label>
            </div></div>
        </div>

        <div class="col-12 form-section-title section-content"><h6>Content, validity and controlled file</h6><small>Keep the searchable record, dates, remarks and source attachment current.</small></div>
        <div class="col-12">
            <label class="form-label">Content</label>
            <textarea name="content" class="form-control" rows="5" required>{{ old('content', $document->content) }}</textarea>
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
            <textarea name="remarks" class="form-control" rows="3" maxlength="2000">{{ old('remarks', $document->remarks) }}</textarea>
        </div>

        <div class="col-12">
            <label class="form-label">Add PDF Documents</label>
            <input type="file" name="files[]" class="form-control" accept="application/pdf,.pdf" multiple>
            <small class="text-muted d-block">PDF only. Select up to 10 documents (maximum 3 MB each).</small>
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
    })();
</script>
@endsection

@extends('layouts.app')

@section('content')
<style>
    .tree-shell{border:1px solid #d7e6e2;border-radius:18px;background:#fff;box-shadow:0 18px 50px rgba(19,70,60,.09);overflow:hidden}.tree-head{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:20px 24px;border-bottom:1px solid #deebe8;background:linear-gradient(115deg,#075c51,#00988c 65%,#26b7a5);color:#fff}.tree-head h3{margin:0;font-size:20px}.tree-head p{margin:4px 0 0;color:rgba(255,255,255,.8);font-size:12px}.tree-save{display:inline-flex;align-items:center;gap:7px;min-height:40px;padding:8px 15px;border:1px solid rgba(255,255,255,.45);border-radius:9px;background:rgba(255,255,255,.14);color:#fff;font-weight:750}.tree-save .material-icons{font-size:18px}.tree-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 20px;border-bottom:1px solid #e3ecea;background:#f8fbfa}.tree-search{position:relative;max-width:420px;flex:1}.tree-search .material-icons{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#68817b;font-size:19px}.tree-search input{width:100%;min-height:40px;padding:8px 12px 8px 39px;border:1px solid #d5e3df;border-radius:9px}.tree-help{color:#6e827d;font-size:11px}.topic-tree{padding:18px 20px 24px;background:#fbfdfc}.tree-category{margin-bottom:12px;border:1px solid #cfe3de;border-radius:13px;background:#fff;overflow:hidden}.tree-row{display:flex;align-items:center;gap:10px;min-height:58px;padding:10px 13px}.tree-row.dragging{opacity:.45;background:#e9f8f5}.tree-row.drag-over{box-shadow:inset 0 0 0 2px #0aa497}.drag-handle{display:grid;place-items:center;flex:0 0 28px;height:34px;border:0;background:transparent;color:#879a95;cursor:grab}.drag-handle:active{cursor:grabbing}.tree-icon{display:grid;place-items:center;flex:0 0 38px;height:38px;border-radius:10px;background:#e8f7f4;color:#008f84}.tree-category>.tree-row{background:linear-gradient(90deg,#eefaf7,#fff)}.tree-category>.tree-row .tree-icon{background:#d8f2ec}.tree-copy{min-width:0;flex:1}.tree-copy strong,.tree-copy small{display:block}.tree-copy strong{overflow:hidden;color:#173d37;font-size:14px;text-overflow:ellipsis;white-space:nowrap}.tree-copy small{margin-top:2px;color:#7a8d88;font-size:10px}.tree-badge{padding:5px 8px;border-radius:999px;background:#e8f5eb;color:#27823c;font-size:9px;font-weight:750}.tree-actions{display:flex;align-items:center;gap:5px}.tree-action{display:grid;place-items:center;width:33px;height:33px;padding:0;border:1px solid #d5e4e0;border-radius:8px;background:#fff;color:#087f75}.tree-action .material-icons{font-size:17px}.tree-action.add{display:flex;width:auto;padding:0 10px;gap:4px}.tree-action.delete{color:#dc4a4a}.tree-children{position:relative;margin-left:50px;padding:0 12px 12px 19px;border-left:1px solid #cbded9}.tree-main{margin-top:8px;border:1px solid #dce8e5;border-radius:11px;background:#fff}.tree-main>.tree-row{min-height:52px}.tree-main .tree-icon{width:34px;height:34px;background:#edf4fb;color:#237eb5}.tree-details{margin:0 10px 9px 47px;padding-left:16px;border-left:1px dashed #cedbd8}.tree-detail{margin-top:6px;border:1px solid #e0e8e6;border-radius:9px;background:#fafcfb}.tree-detail .tree-row{min-height:46px;padding:6px 9px}.tree-detail .tree-icon{flex-basis:31px;height:31px;background:#f1f4f8;color:#64748b}.inline-add{display:none;grid-template-columns:minmax(0,1fr) auto;gap:8px;margin:0 12px 12px 64px;padding:10px;border:1px solid #bfe1da;border-radius:9px;background:#f2faf8}.inline-add.open{display:grid}.inline-add input{min-height:38px;border:1px solid #cfe0dc;border-radius:8px;padding:8px 10px}.inline-add button{border:0;border-radius:8px;background:#159cf0;color:#fff;padding:7px 13px;font-weight:700}.empty-children{padding:10px;color:#879792;font-size:11px}.tree-status{min-width:170px;text-align:right;color:#647b75;font-size:11px}.tree-status.success{color:#17814a}.tree-status.error{color:#c13f3f}@media(max-width:767px){.tree-head,.tree-toolbar{align-items:flex-start;flex-direction:column}.tree-save{width:100%;justify-content:center}.tree-help{display:none}.topic-tree{padding:12px}.tree-children{margin-left:25px;padding-left:8px}.tree-details{margin-left:24px}.tree-action.add span:last-child,.tree-badge{display:none}.inline-add{margin-left:24px}.tree-copy strong{font-size:12px}}
    .tree-head h3{color:#fff}.tree-save:hover{background:#fff;color:#087b72}
    .topic-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:16px}.topic-summary-card{display:flex;align-items:center;gap:12px;padding:14px 16px;border:1px solid #dce8e5;border-radius:12px;background:#fff;box-shadow:0 5px 16px rgba(18,67,58,.06)}.topic-summary-icon{display:grid;place-items:center;width:40px;height:40px;border-radius:10px;background:#e7f6f2;color:#008b81}.topic-summary-card:nth-child(2) .topic-summary-icon{background:#edf4ff;color:#287dc0}.topic-summary-card:nth-child(3) .topic-summary-icon{background:#f2edff;color:#7048c8}.topic-summary-card:nth-child(4) .topic-summary-icon{background:#fff3df;color:#d78700}.topic-summary-card strong,.topic-summary-card small{display:block}.topic-summary-card strong{color:#173d37;font-size:21px;line-height:1}.topic-summary-card small{margin-top:5px;color:#71837e;font-size:10px}@media(max-width:767px){.topic-summary{grid-template-columns:repeat(2,1fr)}}
    .tree-toolbar-actions{display:flex;align-items:center;gap:6px}.tree-tool{display:inline-flex;align-items:center;gap:4px;height:34px;padding:0 10px;border:1px solid #d2e1dd;border-radius:8px;background:#fff;color:#42645e;font-size:10px;font-weight:700}.tree-tool:hover{border-color:#78beb4;color:#007c72}.tree-tool .material-icons{font-size:16px}.tree-collapse{display:grid;place-items:center;flex:0 0 30px;height:30px;border:0;border-radius:7px;background:#f0f7f5;color:#377168}.tree-collapse .material-icons{font-size:18px;transition:.2s}.is-collapsed>.tree-row .tree-collapse .material-icons{transform:rotate(-90deg)}.tree-category.is-collapsed>.tree-children,.tree-main.is-collapsed>.tree-details{display:none}.inline-edit{display:none;grid-template-columns:minmax(0,1fr) auto auto;gap:8px;margin:0 12px 12px 64px;padding:10px;border:1px solid #c9dff2;border-radius:9px;background:#f4f9fe}.inline-edit.open{display:grid}.inline-edit input[type="text"]{min-height:38px;border:1px solid #cedce7;border-radius:8px;padding:8px 10px}.inline-edit button,.inline-edit .cancel-edit{border:0;border-radius:8px;padding:7px 12px;font-size:10px;font-weight:700}.inline-edit button{background:#167fc0;color:#fff}.inline-edit .cancel-edit{background:#e9eef2;color:#52636e}.tree-action.edit{color:#1675ad}.tree-action.delete{color:#c63d45}.tree-action:hover{background:#edf8f6}.tree-result-count{color:#52716b;font-size:10px;font-weight:700}@media(max-width:767px){.tree-toolbar-actions{width:100%;flex-wrap:wrap}.tree-tool{flex:1;justify-content:center}.inline-edit{grid-template-columns:1fr;margin-left:24px}}
</style>

<div id="topicManager">
<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Dashboard</a><span class="material-icons">chevron_right</span><span>Topics</span></div>
<div class="page-heading"><div><span class="eyebrow">{{ strtoupper($organization) }} · Classification governance</span><h2>Topic Hierarchy Manager</h2><p>Manage categories, main topics and subtopics together on one page.</p></div><span class="status-pill status-published">{{ strtoupper($organization) }} data</span></div>

@php
    $mainTopicCount = $categories->sum(fn ($category) => $category->subtopics->count());
    $detailCount = $categories->sum(fn ($category) => $category->subtopics->sum(fn ($mainTopic) => $mainTopic->details->count()));
    $documentCount = $categories->sum('documents_count');
@endphp
<section class="topic-summary" aria-label="Topic hierarchy summary">
    <div class="topic-summary-card"><span class="topic-summary-icon material-icons">folder</span><div><strong>{{ $categories->count() }}</strong><small>Topic categories</small></div></div>
    <div class="topic-summary-card"><span class="topic-summary-icon material-icons">topic</span><div><strong>{{ $mainTopicCount }}</strong><small>Main topics</small></div></div>
    <div class="topic-summary-card"><span class="topic-summary-icon material-icons">account_tree</span><div><strong>{{ $detailCount }}</strong><small>Subtopics</small></div></div>
    <div class="topic-summary-card"><span class="topic-summary-icon material-icons">description</span><div><strong>{{ $documentCount }}</strong><small>Linked documents</small></div></div>
</section>

<section class="tree-shell">
    <header class="tree-head"><div><h3>Draggable Tree View</h3><p>Drag rows to reorder them or move topics into another parent, then save the hierarchy.</p></div><button class="tree-save" id="saveTree" type="button"><span class="material-icons">save</span>Save hierarchy</button></header>
    <div class="tree-toolbar"><div class="tree-search"><span class="material-icons">search</span><input id="treeSearch" type="search" placeholder="Search the topic hierarchy..."></div><span class="tree-result-count" id="treeResultCount">{{ $categories->count() }} categories</span><div class="tree-toolbar-actions"><button class="tree-tool" id="expandAll" type="button"><span class="material-icons">unfold_more</span>Expand all</button><button class="tree-tool" id="collapseAll" type="button"><span class="material-icons">unfold_less</span>Collapse all</button></div><span class="tree-status" id="treeStatus"></span></div>
    <div class="topic-tree" id="topicTree">
        @forelse($categories as $category)
            <article class="tree-category" data-kind="category" data-id="{{ $category->id }}" draggable="true">
                <div class="tree-row"><button class="drag-handle" type="button" title="Drag category"><span class="material-icons">drag_indicator</span></button><button class="tree-collapse" type="button" aria-label="Collapse category"><span class="material-icons">expand_more</span></button><span class="tree-icon material-icons">folder_open</span><span class="tree-copy"><strong>{{ $category->name }}</strong><small>{{ $category->subtopics->count() }} main topics · {{ $category->documents_count }} documents</small></span><span class="tree-badge">{{ $category->is_active ? 'Active' : 'Inactive' }}</span><div class="tree-actions"><button class="tree-action add" type="button" data-toggle-add="main-{{ $category->id }}" title="Add main topic"><span class="material-icons">add</span><span>Add Main Topic</span></button><button class="tree-action edit" type="button" data-toggle-edit="category-{{ $category->id }}" title="Rename category"><span class="material-icons">edit</span></button><form action="{{ route('topic-categories.destroy',$category) }}" method="POST" onsubmit="return confirm('Delete this category? Categories with linked topics or documents are protected.')">@csrf @method('DELETE')<button class="tree-action delete" title="Delete category"><span class="material-icons">delete</span></button></form></div></div>
                <form class="inline-add" id="add-main-{{ $category->id }}" action="{{ route('topic-subtopics.store') }}" method="POST">@csrf<input type="hidden" name="topic_category_id" value="{{ $category->id }}"><input type="hidden" name="is_active" value="1"><input name="name" required maxlength="80" placeholder="Main topic name"><button>Add</button></form>
                <form class="inline-edit" id="edit-category-{{ $category->id }}" action="{{ route('topic-categories.update',$category) }}" method="POST">@csrf @method('PUT')<input type="hidden" name="is_active" value="{{ $category->is_active ? 1 : 0 }}"><input type="text" name="name" value="{{ $category->name }}" required maxlength="80"><button>Save changes</button><button class="cancel-edit" type="button">Cancel</button></form>
                <div class="tree-children" data-dropzone="main" data-parent-id="{{ $category->id }}">
                    @forelse($category->subtopics as $mainTopic)
                        <section class="tree-main" data-kind="main" data-id="{{ $mainTopic->id }}" draggable="true">
                            <div class="tree-row"><button class="drag-handle" type="button" title="Drag main topic"><span class="material-icons">drag_indicator</span></button><button class="tree-collapse" type="button" aria-label="Collapse main topic"><span class="material-icons">expand_more</span></button><span class="tree-icon material-icons">topic</span><span class="tree-copy"><strong>{{ $mainTopic->name }}</strong><small>{{ $mainTopic->details->count() }} subtopics</small></span><div class="tree-actions"><button class="tree-action add" type="button" data-toggle-add="detail-{{ $mainTopic->id }}" title="Add subtopic"><span class="material-icons">add</span><span>Add Subtopic</span></button><button class="tree-action edit" type="button" data-toggle-edit="main-{{ $mainTopic->id }}" title="Rename main topic"><span class="material-icons">edit</span></button><form action="{{ route('topic-subtopics.destroy',$mainTopic) }}" method="POST" onsubmit="return confirm('Delete this main topic? Linked subtopics and documents are protected.')">@csrf @method('DELETE')<button class="tree-action delete" title="Delete main topic"><span class="material-icons">delete</span></button></form></div></div>
                            <form class="inline-add" id="add-detail-{{ $mainTopic->id }}" action="{{ route('topic-details.store') }}" method="POST">@csrf<input type="hidden" name="main_topic_id" value="{{ $mainTopic->id }}"><input type="hidden" name="is_active" value="1"><input name="name" required maxlength="100" placeholder="Subtopic name"><button>Add</button></form>
                            <form class="inline-edit" id="edit-main-{{ $mainTopic->id }}" action="{{ route('topic-subtopics.update',$mainTopic) }}" method="POST">@csrf @method('PUT')<input type="hidden" name="topic_category_id" value="{{ $category->id }}"><input type="hidden" name="is_active" value="{{ $mainTopic->is_active ? 1 : 0 }}"><input type="text" name="name" value="{{ $mainTopic->name }}" required maxlength="80"><button>Save changes</button><button class="cancel-edit" type="button">Cancel</button></form>
                            <div class="tree-details" data-dropzone="detail" data-parent-id="{{ $mainTopic->id }}">
                                @foreach($mainTopic->details as $detail)
                                    <div class="tree-detail" data-kind="detail" data-id="{{ $detail->id }}" draggable="true"><div class="tree-row"><button class="drag-handle" type="button" title="Drag subtopic"><span class="material-icons">drag_indicator</span></button><span class="tree-icon material-icons">description</span><span class="tree-copy"><strong>{{ $detail->name }}</strong><small>Detailed document classification</small></span><span class="tree-badge">{{ $detail->is_active ? 'Active' : 'Inactive' }}</span><div class="tree-actions"><button class="tree-action edit" type="button" data-toggle-edit="detail-{{ $detail->id }}" title="Rename subtopic"><span class="material-icons">edit</span></button><form action="{{ route('topic-details.destroy',$detail) }}" method="POST" onsubmit="return confirm('Delete this subtopic? Subtopics used by documents are protected.')">@csrf @method('DELETE')<button class="tree-action delete" title="Delete subtopic"><span class="material-icons">delete</span></button></form></div></div><form class="inline-edit" id="edit-detail-{{ $detail->id }}" action="{{ route('topic-details.update',$detail) }}" method="POST">@csrf @method('PUT')<input type="hidden" name="main_topic_id" value="{{ $mainTopic->id }}"><input type="hidden" name="is_active" value="{{ $detail->is_active ? 1 : 0 }}"><input type="text" name="name" value="{{ $detail->name }}" required maxlength="100"><button>Save changes</button><button class="cancel-edit" type="button">Cancel</button></form></div>
                                @endforeach
                            </div>
                        </section>
                    @empty<div class="empty-children">No main topics yet. Use “Add Main Topic”.</div>@endforelse
                </div>
            </article>
        @empty
            <div class="empty-children">No topic categories have been created.</div>
        @endforelse
        <form class="inline-add open" action="{{ route('topic-categories.store') }}" method="POST" style="margin:12px 0 0">@csrf<input type="hidden" name="is_active" value="1"><input name="name" required maxlength="80" placeholder="Add a new topic category"><button>Add Category</button></form>
    </div>
</section>
</div>

<script>
function initializeTopicManager() {
    const manager = document.getElementById('topicManager');
    const tree = document.getElementById('topicTree');
    const status = document.getElementById('treeStatus');
    const resultCount = document.getElementById('treeResultCount');
    if (!manager || !tree || !status || !resultCount) return;
    let dragged = null;
    document.querySelectorAll('[data-toggle-add]').forEach(button => button.addEventListener('click', () => document.getElementById(`add-${button.dataset.toggleAdd}`)?.classList.toggle('open')));
    document.querySelectorAll('[data-toggle-edit]').forEach(button => button.addEventListener('click', () => document.getElementById(`edit-${button.dataset.toggleEdit}`)?.classList.toggle('open')));
    document.querySelectorAll('.cancel-edit').forEach(button => button.addEventListener('click', () => button.closest('.inline-edit')?.classList.remove('open')));
    document.querySelectorAll('.tree-collapse').forEach(button => button.addEventListener('click', () => {
        const item = button.closest('.tree-category,.tree-main');
        item?.classList.toggle('is-collapsed');
        button.setAttribute('aria-expanded', item?.classList.contains('is-collapsed') ? 'false' : 'true');
    }));
    document.getElementById('expandAll').addEventListener('click', () => document.querySelectorAll('.tree-category,.tree-main').forEach(item => item.classList.remove('is-collapsed')));
    document.getElementById('collapseAll').addEventListener('click', () => document.querySelectorAll('.tree-category,.tree-main').forEach(item => item.classList.add('is-collapsed')));
    tree.addEventListener('dragstart', event => { const item = event.target.closest('[data-kind]'); if (!item) return; dragged = item; item.classList.add('dragging'); event.dataTransfer.effectAllowed = 'move'; });
    tree.addEventListener('dragend', () => { dragged?.classList.remove('dragging'); document.querySelectorAll('.drag-over').forEach(el => el.classList.remove('drag-over')); dragged = null; });
    tree.addEventListener('dragover', event => {
        if (!dragged) return; event.preventDefault();
        const kind = dragged.dataset.kind;
        const target = event.target.closest(`[data-kind="${kind}"]`);
        const zone = event.target.closest(`[data-dropzone="${kind === 'main' ? 'main' : kind === 'detail' ? 'detail' : ''}"]`);
        if (target && target !== dragged) { target.classList.add('drag-over'); const rect = target.getBoundingClientRect(); target.parentNode.insertBefore(dragged, event.clientY < rect.top + rect.height / 2 ? target : target.nextSibling); }
        else if (zone && kind !== 'category') zone.appendChild(dragged);
        else if (kind === 'category' && event.target.closest('.topic-tree')) tree.insertBefore(dragged, tree.querySelector('form.inline-add.open'));
    });
    tree.addEventListener('dragleave', event => event.target.closest('.drag-over')?.classList.remove('drag-over'));
    document.getElementById('treeSearch').addEventListener('input', event => {
        const term = event.target.value.toLowerCase().trim();
        let matches = 0;
        document.querySelectorAll('.tree-category').forEach(category => {
            const categoryMatch = term === '' || category.textContent.toLowerCase().includes(term);
            category.hidden = !categoryMatch;
            if (categoryMatch) matches++;
            if (term !== '' && categoryMatch) category.classList.remove('is-collapsed');
        });
        document.querySelectorAll('.tree-main,.tree-detail').forEach(item => item.hidden = term !== '' && !item.textContent.toLowerCase().includes(term));
        resultCount.textContent = term === '' ? `${document.querySelectorAll('.tree-category').length} categories` : `${matches} matching categories`;
    });
    document.getElementById('saveTree').addEventListener('click', async () => {
        const payload = { categories:[], main_topics:[], subtopics:[] };
        document.querySelectorAll('.tree-category').forEach(category => { payload.categories.push(+category.dataset.id); category.querySelectorAll(':scope > .tree-children > .tree-main').forEach(main => { payload.main_topics.push({id:+main.dataset.id,category_id:+category.dataset.id}); main.querySelectorAll(':scope > .tree-details > .tree-detail').forEach(detail => payload.subtopics.push({id:+detail.dataset.id,main_topic_id:+main.dataset.id})); }); });
        status.className='tree-status'; status.textContent='Saving...';
        try { const response = await fetch(@json(route('topic-categories.reorder')), {method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':@json(csrf_token())},body:JSON.stringify(payload)}); const data=await response.json(); if(!response.ok) throw new Error(data.message||'Unable to save hierarchy.'); status.className='tree-status success'; status.textContent=data.message; }
        catch(error){ status.className='tree-status error'; status.textContent=error.message; }
    });

    manager.addEventListener('submit', async event => {
        const form = event.target.closest('form');
        if (!form || form.classList.contains('viewer-switch')) return;
        event.preventDefault();

        const submitter = event.submitter;
        if (submitter) submitter.disabled = true;
        status.className = 'tree-status';
        status.textContent = 'Updating...';

        try {
            const response = await fetch(form.action, {
                method: form.method || 'POST',
                body: new FormData(form),
                headers: {'Accept':'application/json, text/html','X-Requested-With':'XMLHttpRequest'},
            });
            const contentType = response.headers.get('content-type') || '';

            if (!response.ok) {
                if (contentType.includes('application/json')) {
                    const data = await response.json();
                    const firstError = Object.values(data.errors || {}).flat()[0];
                    throw new Error(firstError || data.message || 'Unable to update the topic hierarchy.');
                }
                throw new Error('Unable to update the topic hierarchy.');
            }

            const html = await response.text();
            const page = new DOMParser().parseFromString(html, 'text/html');
            const replacement = page.getElementById('topicManager');
            if (!replacement) throw new Error('The updated hierarchy could not be loaded.');

            const message = page.querySelector('.alert-success')?.textContent?.trim() || 'Topic hierarchy updated successfully.';
            manager.replaceWith(replacement);
            initializeTopicManager();
            const refreshedStatus = document.getElementById('treeStatus');
            refreshedStatus.className = 'tree-status success';
            refreshedStatus.textContent = message;
        } catch (error) {
            status.className = 'tree-status error';
            status.textContent = error.message;
            if (submitter) submitter.disabled = false;
        }
    });
}
initializeTopicManager();
</script>
@endsection

@extends('layouts.app')

@section('content')
<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Dashboard</a><span class="material-icons">chevron_right</span><span>Notifications</span></div>
<div class="page-heading"><div><span class="eyebrow">Publication inbox</span><h2>Notifications</h2><p>Review policy and circular publication alerts and manage their read state.</p></div></div>

<form method="GET" action="{{ route('notifications.index') }}" class="row g-2 flow-toolbar">
    <div class="col-md-3">
        <select name="status" class="form-control">
            <option value="">All Notifications</option>
            <option value="unread" @selected(request('status') === 'unread')>Unread Only</option>
            <option value="read" @selected(request('status') === 'read')>Read Only</option>
        </select>
    </div>
    <div class="col-md-3">
        <select name="category" class="form-control">
            <option value="">All Categories</option>
            @foreach($availableCategories as $value => $label)
                <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-primary w-100">Filter</button>
        @if(request()->filled('status') || request()->filled('category'))
            <a href="{{ route('notifications.index') }}" class="btn btn-light border">Clear</a>
        @endif
    </div>
</form>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
        <h5 class="mb-0">All Notifications</h5>
        @if($notifications->whereNull('read_at')->count() > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button class="btn btn-sm btn-outline-primary">Mark All Read</button>
            </form>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table flow-table mb-0">
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Received</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse($notifications as $notification)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="material-icons text-info" style="font-size: 18px;">{{ $notification->data['icon'] ?? 'notifications' }}</span>
                                <div>
                                    <div>{{ $notification->data['title'] ?? 'Notification' }}</div>
                                    <small class="text-muted">{{ $notification->data['category_label'] ?? 'General' }}</small>
                                    @if(! empty($notification->data['change_summary']))
                                        <div><small class="text-info">{{ $notification->data['change_summary'] }}</small></div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>{{ $notification->data['message'] ?? '' }}</div>
                            @if(! empty($notification->data['preview_excerpt']))
                                <small class="text-muted">{{ $notification->data['preview_excerpt'] }}</small>
                            @endif
                        </td>
                        <td>
                            @if($notification->read_at)
                                <span class="status-pill status-published">Read</span>
                            @else
                                <span class="status-pill status-draft">Unread</span>
                            @endif
                        </td>
                        <td>{{ $notification->created_at?->format('d M Y H:i') }}</td>
                        <td class="d-flex gap-2 flex-wrap">
                            @if(! empty($notification->data['policy_document_id']))
                                <a href="{{ route('policy-documents.show', $notification->data['policy_document_id']) }}" class="btn btn-sm btn-info">Open Circular</a>
                            @endif
                            @if(! $notification->read_at)
                                <form action="{{ route('notifications.update', $notification->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-secondary">Mark Read</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">No notifications found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $notifications->links() }}
    </div>
</div>
@endsection

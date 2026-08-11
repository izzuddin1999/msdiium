<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $viewer = $request->user();
        abort_unless($viewer, 403);

        $query = $viewer->notifications()->latest();

        if ($request->input('status') === 'unread') {
            $query->whereNull('read_at');
        }

        if ($request->input('status') === 'read') {
            $query->whereNotNull('read_at');
        }

        if ($request->filled('category')) {
            $query->where('data->category', $request->string('category'));
        }

        return view('notifications.index', [
            'notifications' => $query->paginate(15)->withQueryString(),
            'availableCategories' => $viewer->notifications()
                ->select('data')
                ->get()
                ->pluck('data.category_label', 'data.category')
                ->filter()
                ->unique(),
        ]);
    }

    public function update(Request $request, string $notification): RedirectResponse
    {
        $viewer = $request->user();
        abort_unless($viewer, 403);

        $viewer->notifications()
            ->whereKey($notification)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('status', 'Notification marked as read.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $viewer = $request->user();
        abort_unless($viewer, 403);

        $viewer->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', 'All notifications marked as read.');
    }
}
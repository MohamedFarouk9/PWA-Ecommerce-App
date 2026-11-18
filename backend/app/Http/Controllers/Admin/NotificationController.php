<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Repositories\Contracts\Admin\AdminNotificationRepositoryInterface;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private AdminNotificationRepositoryInterface $notificationRepository) {}

    /**
     * Display a listing of notifications
     */
    public function index(Request $request)
    {
        $notifications = Notification::with('user')->latest()->get();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'success',
                'notifications' => $notifications,
            ], 200);
        }

        $unreadCount = Notification::whereNull('read_at')->count();

        return view('admin.notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Display the specified notification
     */
    public function show($id, Request $request)
    {
        $notification = Notification::with('user')->findOrFail($id);

        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'success',
                'notification' => $notification,
            ]);
        }

        return view('admin.notifications.show', compact('notification'));
    }

    /**
     * Mark single notification as read
     */
    public function markRead($id, Request $request)
    {
        $notification = $this->notificationRepository->markAsRead((int) $id);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'success',
                'message' => 'Notification marked as read',
                'notification' => $notification,
            ]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllRead(Request $request)
    {
        Notification::whereNull('read_at')->update(['read_at' => now()]);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'success',
                'message' => 'All notifications marked as read',
            ]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Delete notification
     */
    public function destroy($id, Request $request)
    {
        $this->notificationRepository->delete((int) $id);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'success',
                'message' => 'Notification deleted successfully',
            ]);
        }

        return back()->with('success', 'Notification deleted successfully.');
    }
}

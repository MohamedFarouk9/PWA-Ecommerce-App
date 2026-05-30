<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\Admin\AdminNotificationRepositoryInterface;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private AdminNotificationRepositoryInterface $notificationRepository) {}

    /**
     * Get all notifications
     */
    public function index(Request $request)
    {
        $notifications = $this->notificationRepository->getAll();

        return response()->json(['notifications' => $notifications], 200);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = $this->notificationRepository->markAsRead($id);

        return response()->json(['message' => 'Notification marked as read', 'notification' => $notification]);
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        $this->notificationRepository->delete($id);

        return response()->json(['message' => 'Notification deleted']);
    }
}

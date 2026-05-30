<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private NotificationRepositoryInterface $notificationRepository) {}

    /**
     * GET /api/notifications - Get user notifications
     */
    public function index(Request $request)
    {
        $notifications = $this->notificationRepository->getUserNotifications(auth()->id(), 15);

        return response()->json([
            'status' => 'success',
            'data'   => $notifications,
        ]);
    }

    /**
     * GET /api/notifications/{id} - Get single notification
     */
    public function show($id)
    {
        $notification = $this->notificationRepository->getUserNotificationById(auth()->id(), $id);

        if (!$notification) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Notification not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $notification,
        ]);
    }

    /**
     * PUT /api/notifications/{id}/read - Mark as read
     */
    public function markAsRead($id)
    {
        $notification = $this->notificationRepository->getUserNotificationById(auth()->id(), $id);

        if (!$notification) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Notification not found',
            ], 404);
        }

        $this->notificationRepository->markAsRead(auth()->id(), $id);

        return response()->json([
            'status'  => 'success',
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * DELETE /api/notifications/{id} - Delete notification
     */
    public function destroy($id)
    {
        $notification = $this->notificationRepository->getUserNotificationById(auth()->id(), $id);

        if (!$notification) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Notification not found',
            ], 404);
        }

        $this->notificationRepository->delete(auth()->id(), $id);

        return response()->json([
            'status'  => 'success',
            'message' => 'Notification deleted',
        ]);
    }

    /**
     * GET /api/notifications/unread-count - Count unread
     */
    public function unreadCount()
    {
        $count = $this->notificationRepository->getUnreadCount(auth()->id());

        return response()->json([
            'status' => 'success',
            'data'   => ['unread_count' => $count],
        ]);
    }
}


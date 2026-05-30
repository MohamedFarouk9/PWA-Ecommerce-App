<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use \Illuminate\Support\Facades\DB;

use App\Repositories\Contracts\NotificationRepositoryInterface;

class NotificationRepository implements NotificationRepositoryInterface
{
    /**
     * Get user notifications paginated
     */
    public function getUserNotifications(int $userId, int $perPage = 15)
    {
        // Using Illuminate\Notifications\DatabaseNotification model via user relationship
        return DB::table('notifications')
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get a specific notification for user
     */
    public function getUserNotificationById(int $userId, string $notificationId)
    {
        return DB::table('notifications')
            ->where('id', $notificationId)
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->first();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $userId, string $notificationId)
    {
        return DB::table('notifications')
            ->where('id', $notificationId)
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->update(['read_at' => now()]);
    }

    /**
     * Delete a notification
     */
    public function delete(int $userId, string $notificationId)
    {
        return DB::table('notifications')
            ->where('id', $notificationId)
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->delete();
    }

    /**
     * Get unread notification count
     */
    public function getUnreadCount(int $userId): int
    {
        return DB::table('notifications')
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->whereNull('read_at')
            ->count();
    }
}

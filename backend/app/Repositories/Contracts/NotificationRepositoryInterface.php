<?php

namespace App\Repositories\Contracts;

interface NotificationRepositoryInterface
{
    public function getUserNotifications(int $userId, int $perPage = 15);
    public function getUserNotificationById(int $userId, string $notificationId);
    public function markAsRead(int $userId, string $notificationId);
    public function delete(int $userId, string $notificationId);
    public function getUnreadCount(int $userId): int;
}

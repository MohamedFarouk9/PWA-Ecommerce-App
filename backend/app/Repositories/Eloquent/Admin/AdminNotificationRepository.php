<?php

namespace App\Repositories\Eloquent\Admin;

use App\Models\Notification;
use App\Repositories\Contracts\Admin\AdminNotificationRepositoryInterface;

class AdminNotificationRepository implements AdminNotificationRepositoryInterface
{
    protected $model;

    public function __construct(Notification $model)
    {
        $this->model = $model;
    }

    /**
     * Get all notifications
     */
    public function getAll()
    {
        return $this->model->latest()->get();
    }

    /**
     * Find notification by ID
     */
    public function find(int $id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $id)
    {
        $notification = $this->model->findOrFail($id);
        $notification->update(['read_at' => now()]);
        return $notification;
    }

    /**
     * Delete notification
     */
    public function delete(int $id)
    {
        $notification = $this->model->findOrFail($id);
        return $notification->delete();
    }

    /**
     * Get count of unread notifications
     */
    public function getUnreadCount()
    {
        return $this->model->whereNull('read_at')->count();
    }
}

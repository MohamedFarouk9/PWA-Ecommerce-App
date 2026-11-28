<?php

namespace Modules\Marketing\Repositories\Eloquent;

use Modules\Marketing\Models\Notification;
use Modules\Marketing\Repositories\Contracts\NotificationRepositoryInterface;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function __construct(protected Notification $model) {}

    public function getAll()
    {
        return $this->model->latest()->get();
    }

    public function find(int $id)
    {
        return $this->model->findOrFail($id);
    }

    public function markAsRead(int $id)
    {
        $notification = $this->model->findOrFail($id);
        $notification->update(['read_at' => now()]);
        return $notification;
    }

    public function delete(int $id)
    {
        $notification = $this->model->findOrFail($id);
        return $notification->delete();
    }

    public function getUnreadCount()
    {
        return $this->model->whereNull('read_at')->count();
    }
}

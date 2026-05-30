<?php

namespace App\Repositories\Contracts\Admin;

interface AdminNotificationRepositoryInterface
{
    public function getAll();
    public function find(int $id);
    public function markAsRead(int $id);
    public function delete(int $id);
    public function getUnreadCount();
}

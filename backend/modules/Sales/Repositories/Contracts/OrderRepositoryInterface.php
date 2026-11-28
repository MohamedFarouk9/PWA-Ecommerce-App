<?php

namespace Modules\Sales\Repositories\Contracts;

interface OrderRepositoryInterface
{
    public function getPaginated($perPage = 15);
    public function getAll();
    public function find($id);
    public function getWithDetails($id);
    public function getByStatus($status, $perPage = 15);
    public function getByUser($userId, $perPage = 15);
    public function updateStatus($id, $status);
    public function update($id, array $data);
    public function getItems($id);
    public function getPendingCount();
    public function getStatistics();
    public function search($query, $perPage = 15);
}

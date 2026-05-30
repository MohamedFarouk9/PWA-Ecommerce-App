<?php

namespace App\Repositories\Contracts;

interface ContactRepositoryInterface
{
    public function create(array $data);
    public function getAll(int $perPage = 15);
    public function find(int $id);
    public function delete(int $id);
    public function markAsRead(int $id);
}

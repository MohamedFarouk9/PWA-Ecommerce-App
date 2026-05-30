<?php

namespace App\Repositories\Contracts\Admin;

interface AdminSliderRepositoryInterface
{
    public function getAll();
    public function getPaginated(int $perPage = 15);
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function toggleActive(int $id);
}

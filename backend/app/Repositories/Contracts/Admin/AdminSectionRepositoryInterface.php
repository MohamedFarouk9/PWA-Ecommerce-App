<?php

namespace App\Repositories\Contracts\Admin;

interface AdminSectionRepositoryInterface
{
    public function getAll();
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function assignProducts(int $sectionId, array $productIds);
    public function getProducts(int $sectionId);
}

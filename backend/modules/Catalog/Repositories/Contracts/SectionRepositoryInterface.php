<?php

namespace Modules\Catalog\Repositories\Contracts;

interface SectionRepositoryInterface
{
    public function getAll();
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function assignProducts(int $sectionId, array $productIds);
    public function getProducts(int $sectionId);
}

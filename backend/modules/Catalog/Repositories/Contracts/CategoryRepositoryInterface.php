<?php

namespace Modules\Catalog\Repositories\Contracts;

interface CategoryRepositoryInterface
{
    public function all();
    public function paginate($perPage = 15);
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function getParentCategories();
    public function checkHasChildren($id);
    public function checkHasProducts($id);
    public function updateOrder(array $categories);
    public function toggleStatus($id);
}

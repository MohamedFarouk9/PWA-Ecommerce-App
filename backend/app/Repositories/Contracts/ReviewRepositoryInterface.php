<?php

namespace App\Repositories\Contracts;

interface ReviewRepositoryInterface
{
    public function getByProductId(int $productId, int $limit = 5);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function findOrFail(int $id);
    public function isOwnedBy(int $reviewId, int $userId): bool;
}

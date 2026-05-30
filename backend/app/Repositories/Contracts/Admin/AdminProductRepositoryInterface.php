<?php

namespace App\Repositories\Contracts\Admin;

interface AdminProductRepositoryInterface
{
    public function getDetails(int $productId);
    public function getRelated(int $productId, int $limit = 4);
}

<?php

namespace Modules\Catalog\Repositories\Contracts;

interface ProductDetailsRepositoryInterface
{
    public function getDetails(int $productId);
    public function getRelated(int $productId, int $limit = 4);
}

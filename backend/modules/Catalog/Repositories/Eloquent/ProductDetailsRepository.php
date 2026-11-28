<?php

namespace Modules\Catalog\Repositories\Eloquent;

use Modules\Catalog\Models\Product;
use Modules\Catalog\Repositories\Contracts\ProductDetailsRepositoryInterface;

class ProductDetailsRepository implements ProductDetailsRepositoryInterface
{
    public function __construct(protected Product $model) {}

    public function getDetails(int $productId)
    {
        return $this->model
            ->with(['productDetails', 'category.subcategories', 'images'])
            ->findOrFail($productId);
    }

    public function getRelated(int $productId, int $limit = 4)
    {
        $currentProduct = $this->model->findOrFail($productId);

        $related = $this->model
            ->where('product_category_id', $currentProduct->product_category_id)
            ->where('id', '!=', $productId)
            ->limit($limit)
            ->get();

        return $related;
    }
}

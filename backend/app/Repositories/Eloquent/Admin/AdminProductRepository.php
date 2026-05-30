<?php

namespace App\Repositories\Eloquent\Admin;

use App\Models\Product;
use App\Repositories\Contracts\Admin\AdminProductRepositoryInterface;

class AdminProductRepository implements AdminProductRepositoryInterface
{
    protected $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    /**
     * Get product details with relations
     */
    public function getDetails(int $productId)
    {
        return $this->model
            ->with(['productDetails', 'category.subcategories'])
            ->findOrFail($productId);
    }

    /**
     * Get related products
     */
    public function getRelated(int $productId, int $limit = 4)
    {
        $currentProduct = $this->model->findOrFail($productId);

        // First try to get products from same subcategory
        $related = $this->model
            ->where('subcategory_id', $currentProduct->subcategory_id)
            ->where('id', '!=', $productId)
            ->limit($limit)
            ->get();

        // If not enough, get from same category
        if ($related->count() < $limit) {
            $related = $this->model
                ->where('category_id', $currentProduct->category_id)
                ->where('id', '!=', $productId)
                ->limit($limit)
                ->get();
        }

        return $related;
    }
}

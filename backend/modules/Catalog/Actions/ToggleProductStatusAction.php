<?php

namespace Modules\Catalog\Actions;

use Modules\Catalog\Models\Product;
use Modules\Catalog\Services\ProductService;

class ToggleProductStatusAction
{
    public function __construct(private ProductService $productService) {}

    public function execute(Product $product): void
    {
        $this->productService->toggleStatus($product);
    }
}

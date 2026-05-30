<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\Admin\AdminProductRepositoryInterface;

class ProductDetailsController extends Controller
{
    public function __construct(private AdminProductRepositoryInterface $productRepository) {}

    /**
     * Get product details
     */
    public function productDetails($id)
    {
        try {
            $product = $this->productRepository->getDetails($id);

            if ($product->productDetails) {
                $product->productDetails->color = json_decode($product->productDetails->color, true);
                $product->productDetails->size = json_decode($product->productDetails->size, true);
            }

            return response()->json(['product' => $product], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Product not found'], 404);
        }
    }

    /**
     * Get related products
     */
    public function relatedProduct($product_id)
    {
        try {
            $relatedProducts = $this->productRepository->getRelated($product_id, 4);

            return response()->json(['related_products' => $relatedProducts], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Product Not Found'], 404);
        }
    }
}

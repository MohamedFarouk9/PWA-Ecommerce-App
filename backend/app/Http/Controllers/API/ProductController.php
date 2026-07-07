<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Repositories\Contracts\API\ApiProductRepositoryInterface;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function __construct(private ApiProductRepositoryInterface $productRepository)
    {}

    /**
     * Search products by title, brand, or description
     */

    public function ProductBySearh($query)
    {
        $results = $this->productRepository->search($query, 20);

        if ($results->isEmpty()) {
            return response()->json(['message' => 'No products found'], 404);
        }
        // return response()->json(['status' => 'success', 'data' => ['products' => $results]]);
        return ProductResource::collection($results);
    }

    /**
     * Get products by remark/tag
     */
    public function productsByRemark($remark)
    {
        $results = $this->productRepository->getByRemark($remark, 20);

        if ($results->isEmpty()) {
            return response()->json(['message' => 'No products found for this remark'], 404);
        }
        return response()->json(['status' => 'success', 'data' => ['products' => $results]]);
    }

    /**
     * Get products by category
     */
    public function getProductsByCategory($slug)
    {
        // Find category by slug
        $category = ProductCategory::where('slug', $slug)->first();

        if (! $category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $results = $this->productRepository->getByCategory($category->id, 12);
        if ($results->isEmpty()) {
            return response()->json(['message' => 'No published products in this category'], 404);
        }
        return ProductResource::collection($results);
    }

    // Returns all homepage sections with their products
    public function homepageSections()
    {
        $result = $this->productRepository->getHomepageSections();
        if (! $result) {
            return response()->json(['message' => 'No homepage sections found'], 404);
        }
        return response()->json(['status' => 'success', 'data' => ['sections' => $result]]);
    }
    /**
     * Get all published products (done)
     */
    public function getAllPublished(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $results = $this->productRepository->getAllPublished($perPage);

        if ($results->isEmpty()) {
            return response()->json(['message' => 'No published products found'], 404);
        }
        return ProductResource::collection($results);
    }

    /**
     * Get single product by ID (done)
     */
    public function getProductById($id)
    {
        return $this->buildProductResponse($id);
    }

    public function getProductDetails($id)
    {
        return $this->buildProductResponse($id);
    }

    public function getRelatedProducts($productId)
    {
        $results = $this->productRepository->getRelatedProducts($productId, 6);

        if ($results->isEmpty()) {
            return response()->json(['message' => 'No related products found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'related_products' => ProductResource::collection($results),
            ],
        ], 200);
    }

    private function buildProductResponse($id)
    {
        $product = $this->productRepository->getById($id);

        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'product' => new ProductResource($product),
            ],
        ], 200);
    }

    /*
      * Get related products based on category
     */
    public function RelatedProducts($productId)
    {
        $results = $this->productRepository->getRelatedProducts($productId, 6);
        if ($results->isEmpty()) {
            return response()->json(['message' => 'No related products found'], 404);
        }
        return ProductResource::collection($results);
    }

    /*
    * Get Lastest Products
    */

    public function LastestProducts()
    {
        $results = $this->productRepository->getLatest(10);
        if ($results->isEmpty()) {
            return response()->json(['message' => 'No latest products found'], 404);
        }
        return ProductResource::collection($results);
    }

    public function productBySectionName($sectionName)
    {
        $results = $this->productRepository->getBySectionName($sectionName, 8);
        if ($results->isEmpty()) {
            return response()->json(['message' => 'No products found for this section'], 404);
        }
        // return response()->json(['status' => 'success', 'data' => ['products' => $results]]);
        return ProductResource::collection($results);
    }

    public function getBySection($sectionId)
    {
        $results = $this->productRepository->getBySection($sectionId, 8);
        if ($results->isEmpty()) {
            return response()->json(['message' => 'No products found for this section'], 404);
        }
        // return response()->json(['status' => 'success', 'data' => ['products' => $results]]);
        return ProductResource::collection($results);
    }

}

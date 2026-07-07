<?php
namespace App\Repositories\Eloquent\API;

use App\Models\Product;
use App\Models\Section;
use App\Repositories\Contracts\API\ApiProductRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class ApiProductRepository implements ApiProductRepositoryInterface
{ // i need to rewrite the whole file
    protected $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    /**
     * Search products by title or brand
     */
    public function search($query, $limit = 20)
    {
        return $this->model->with('status')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->whereHas('status', function ($q) {
                $q->where('name', 'published'); // Only published products
            })
            ->with(['category', 'tags', 'productVariants.productAttribute']) // Removed 'sections' if not needed
            ->limit($limit)
            ->get();
    }

    /**
     * Get all published products (paginated)
     * NO CACHE - Pagination with cache creates bugs
     */
    public function getAllPublished($perPage = 15)
    {

        return $this->model->with(['status', 'category', 'tags', 'productVariants.productAttribute'])
            ->whereHas('status', function ($q) {
                $q->where('name', 'published'); // Only published products
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a single product by ID with relations
     * ✅ GOOD CACHE - Product details rarely change, high cache hit rate
     */
    public function getById($id)
    {
        return $this->model
            ->with(['category', 'status', 'tags', 'productVariants.productAttribute', 'reviews', 'sections', 'images'])
            ->find($id);
    }

    /**
     * Get products by category (paginated)
     * ⚠️ REMOVED CACHE - Pagination with cache is problematic
     */
    public function getByCategory($categoryId, $perPage = 12)
    {
        return $this->model
            ->where('product_category_id', $categoryId) // Filter 1: By category
            ->whereHas('status', function ($q) {        // Filter 2: By status
                $q->where('name', 'published');             // Only published products
            })
            ->with(['category', 'tags', 'productVariants.productAttribute', 'status']) // Load for filtered results
            ->latest()
            ->paginate($perPage);
    }


    // public function getProductBySubCategory($subCategoryId, $perPage = 12)
    // {
    //     return $this->model
    //         ->where('product_sub_category_id', $subCategoryId) // Filter 1: By sub-category
    //         ->whereHas('status', function ($q) {        // Filter 2: By status
    //             $q->where('name', 'published');             // Only published products
    //         })
    //         ->with(['category', 'tags', 'productVariants.productAttribute', 'status']) // Load for filtered results
    //         ->latest()
    //         ->paginate($perPage);
    // }
    /**
     * Get products by remark/tag
     */
    public function getByRemark($remark, $limit = 20)
    {
        return $this->model
            ->whereHas('tags', function ($query) use ($remark) {
                $query->where('name', $remark);
            })
            ->whereHas('status', function ($q) {
                $q->where('name', 'published'); // Only published products
            })
            ->with(['category', 'tags', 'productVariants.productAttribute'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get homepage sections with products
     * ✅ KEEP CACHE - Homepage changes infrequently
     */
    public function getHomepageSections()
    {
        return Cache::remember('api.sections.homepage', 900, function () { // 15 minutes
            $sections = Section::with(['products' => function ($q) {
                $q->whereHas('status', function ($q) {
                    $q->where('name', 'published'); // Only published products
                })
                    ->with(['category', 'tags', 'productVariants.productAttribute', 'status'])
                    ->limit(8);
            }])->get();

            $result = [];
            foreach ($sections as $section) {
                $result[$section->name] = $section->products;
            }

            return $result;
        });
    }

    /**
     * Get products by section
     * ✅ KEEP CACHE - Section products don't change often
     */
    public function getBySection($sectionId, $limit = 8)
    {
        $cacheKey = "api.products.section.{$sectionId}";

        // return Cache::remember($cacheKey, 1800, function () use ($sectionId, $limit) { // 30 minutes
        return $this->model
            ->whereHas('status', function ($q) {
                $q->where('name', 'published');
            })
            ->whereHas('sections', function ($q) use ($sectionId) {
                $q->where('section_id', $sectionId);
            })
            ->with(['category', 'tags', 'productVariants.productAttribute', 'status'])
            ->limit($limit)
            ->get();
        // });
    }

    /**
     * Get featured/top rated products
     * ✅ KEEP CACHE - Featured products change 1-2 times/day
     */
    public function getFeatured($limit = 10)
    {
        $featuredSection = Section::where('name', 'Featured')->first();
        if (! $featuredSection) {
            return collect(); //Return empty collection if section doesn't exist
        }
        // return Cache::remember('featured_products', 1800, function () use ($limit) { // 30 minutes
        return $this->model
            ->whereHas('status', function ($q) {
                $q->where('name', 'published');
            })
            ->with(['category', 'tags', 'productVariants.productAttribute'])
            ->limit($limit)
            ->get();
        // });
    }

public function getBySectionName($sectionName, $limit = 10)
{
    $section = Section::where('name', $sectionName)->first();

    if (!$section) {
        return collect();
    }

    return $this->model
        ->whereHas('status', function ($q) {
            $q->where('name', 'published');
        })
        ->whereHas('sections', function ($q) use ($section) {
            $q->where('section_id', $section->id);
        })
        ->with(['category', 'tags', 'productVariants.productAttribute'])
        ->limit($limit)
        ->get();
}

    /**
     * Get new/latest products
     * ✅ KEEP CACHE - New products added a few times/day
     */
    public function getLatest($limit = 10)
    {
        // return Cache::remember('latest_products', 1800, function () use ($limit) { // 30 minutes
        return $this->model
            ->whereHas('status', function ($q) {
                $q->where('name', 'published');
            })
            ->with(['category', 'tags', 'productVariants.productAttribute'])
            ->latest('created_at')
            ->limit($limit)
            ->get();
        // });
    }

    /**
     * Get best selling products
     * ✅ NEW CACHE - Best sellers change weekly/monthly
     */
    public function getBestSellers($limit = 10)
    {
        // return Cache::remember('best_sellers', 3600, function () use ($limit) { // 1 hour
        return $this->model
            ->whereHas('status', function ($q) {
                $q->where('name', 'published');
            })
        // ->orderByDesc('sales_count') // Assuming you have sales_count column
            ->with(['category', 'productVariants.productAttribute'])
            ->limit($limit)
            ->get();
        // });
    }

    /**
     * Get related products
     * NO CACHE - Unique per product
     */
    public function getRelatedProducts($productId, $limit = 6)
    {
        $product = $this->getById($productId);

        if (! $product) {
            return collect();
        }

        return $this->model
        ->where('product_category_id', $product->product_category_id)
            ->where('id', '!=', $productId)
            ->whereHas('status', function ($q) {
                $q->where('name', 'published');
            })
            ->with(['category', 'productVariants.productAttribute'])
            ->limit($limit)
            ->get();
    }

    /**
     * Clear cache for a specific product
     */
    public function clearProductCache($productId)
    {
        // Clear individual product cache
        Cache::forget("product:{$productId}:v1");

        // Clear aggregated caches that might contain this product
        Cache::forget('featured_products');
        Cache::forget('latest_products');
        Cache::forget('best_sellers');
        Cache::forget('api.sections.homepage');

        // Note: We can't efficiently clear section caches without knowing all sections
        // Consider using cache tags if you need this
    }

    /**
     * Clear all product caches
     * Useful when bulk updating products
     */
    public function clearAllProductCaches()
    {
        Cache::forget('featured_products');
        Cache::forget('latest_products');
        Cache::forget('best_sellers');
        Cache::forget('api.sections.homepage');

        // Note: Individual product caches remain
        // Use cache:clear in production for full reset
    }
}

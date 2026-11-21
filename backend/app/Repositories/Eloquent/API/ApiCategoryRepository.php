<?php

namespace App\Repositories\Eloquent\API;

use App\Models\ProductCategory;
use App\Repositories\Contracts\API\ApiCategoryRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class ApiCategoryRepository implements ApiCategoryRepositoryInterface
{
    protected $model;

    public function __construct(ProductCategory $model)
    {
        $this->model = $model;
    }

    /**
     * Get all active categories with their children.
     */
    public function index()
    {
        return Cache::remember('api.categories.all', 3600, function () {
            return $this->model
                ->with(['children' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('order');
                }])
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->get();
        });
    }

    /**
     * Get a specific category with its children and parent.
     */
    public function show($slug)
    {
        return Cache::remember("api.category.{$slug}", 3600, function () use ($slug) {
            return $this->model
                ->with([
                    'children' => function ($query) {
                        $query->where('is_active', true)
                            ->orderBy('order');
                    },
                    'parent'
                ])
                ->where('slug', $slug)
                ->where('is_active', true)
                ->first();
        });
    }

    /**
     * Get all products in a category.
     */
    public function products($slug, $perPage = 12)
    {
        $category = $this->model
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$category) {
            return null;
        }

        $products = $category->products()
            ->with(['category', 'images', 'status'])
            ->whereHas('status', function ($q) {
                $q->where('name', 'published');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return [
            'category' => $category,
            'products' => $products,
        ];
    }

    /**
     * Get category breadcrumb hierarchy.
     */
    public function breadcrumb($slug)
    {
        $category = $this->model
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$category) {
            return null;
        }

        $breadcrumb = collect([$category]);
        $current = $category;

        while ($current->parent) {
            $current = $current->parent;
            $breadcrumb->prepend($current);
        }

        return $breadcrumb;
    }

    /**
     * Search categories by query.
     */
    public function search($query, $limit = 10)
    {
        return $this->model
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->with('parent')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }
}

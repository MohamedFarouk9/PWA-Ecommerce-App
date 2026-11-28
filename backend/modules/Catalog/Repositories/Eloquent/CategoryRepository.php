<?php

namespace Modules\Catalog\Repositories\Eloquent;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Catalog\Models\ProductCategory;
use Modules\Catalog\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    protected ProductCategory $model;

    public function __construct()
    {
        $this->model = new ProductCategory();
    }

    public function all()
    {
        return $this->model->with('children')->orderBy('order')->get();
    }

    public function paginate($perPage = 15)
    {
        return $this->model->with('children')->orderBy('order')->paginate($perPage);
    }

    public function find($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['slug'] = Str::slug($data['name']);
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $category = $this->find($id);
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $category->update($data);
        Cache::forget('categories.all');
        return $category;
    }

    public function delete($id)
    {
        $category = $this->find($id);

        if ($this->checkHasChildren($id)) {
            throw new \Exception('Cannot delete category with child categories.');
        }

        if ($this->checkHasProducts($id)) {
            throw new \Exception('Cannot delete category with associated products.');
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        $category->delete();
        Cache::forget('categories.all');
    }

    public function getParentCategories()
    {
        return $this->model->whereNull('parent_id')->get();
    }

    public function checkHasChildren($id)
    {
        return $this->model->find($id)->children()->exists();
    }

    public function checkHasProducts($id)
    {
        return $this->model->find($id)->products()->exists();
    }

    public function updateOrder(array $categories)
    {
        foreach ($categories as $item) {
            $this->model->where('id', $item['id'])->update(['order' => $item['order']]);
        }
        Cache::forget('categories.all');
    }

    public function toggleStatus($id)
    {
        $category            = $this->find($id);
        $category->is_active = !$category->is_active;
        $category->save();
        Cache::forget('categories.all');
        return $category;
    }
}

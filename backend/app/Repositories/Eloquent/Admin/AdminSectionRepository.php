<?php

namespace App\Repositories\Eloquent\Admin;

use App\Models\Section;
use App\Repositories\Contracts\Admin\AdminSectionRepositoryInterface;

class AdminSectionRepository implements AdminSectionRepositoryInterface
{
    protected $model;

    public function __construct(Section $model)
    {
        $this->model = $model;
    }

    /**
     * Get all sections with product counts
     */
    public function getAll()
    {
        return $this->model->withCount('products')->get();
    }

    /**
     * Find section by ID
     */
    public function find(int $id)
    {
        return $this->model->with('products')->findOrFail($id);
    }

    /**
     * Create new section
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update section
     */
    public function update(int $id, array $data)
    {
        $section = $this->model->findOrFail($id);
        $section->update($data);
        return $section;
    }

    /**
     * Delete section
     */
    public function delete(int $id)
    {
        $section = $this->model->findOrFail($id);
        return $section->delete();
    }

    /**
     * Assign products to section
     */
    public function assignProducts(int $sectionId, array $productIds)
    {
        $section = $this->model->findOrFail($sectionId);
        $section->products()->sync($productIds);
        return $section;
    }

    /**
     * Get section products
     */
    public function getProducts(int $sectionId)
    {
        $section = $this->model->findOrFail($sectionId);
        return $section->products()->get();
    }
}

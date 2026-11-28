<?php

namespace Modules\Catalog\Repositories\Eloquent;

use Modules\Catalog\Models\Section;
use Modules\Catalog\Repositories\Contracts\SectionRepositoryInterface;

class SectionRepository implements SectionRepositoryInterface
{
    public function __construct(protected Section $model) {}

    public function getAll()
    {
        return $this->model->withCount('products')->get();
    }

    public function find(int $id)
    {
        return $this->model->with('products')->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $section = $this->model->findOrFail($id);
        $section->update($data);
        return $section;
    }

    public function delete(int $id)
    {
        $section = $this->model->findOrFail($id);
        return $section->delete();
    }

    public function assignProducts(int $sectionId, array $productIds)
    {
        $section = $this->model->findOrFail($sectionId);
        $section->products()->sync($productIds);
        return $section;
    }

    public function getProducts(int $sectionId)
    {
        $section = $this->model->findOrFail($sectionId);
        return $section->products()->get();
    }
}

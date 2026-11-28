<?php

namespace Modules\Catalog\Repositories\Eloquent;

use Modules\Catalog\Models\Slider;
use Modules\Catalog\Repositories\Contracts\SliderRepositoryInterface;

class SliderRepository implements SliderRepositoryInterface
{
    public function __construct(protected Slider $model) {}

    public function getAll()
    {
        return $this->model->all();
    }

    public function getPaginated(int $perPage = 15)
    {
        return $this->model->latest()->paginate($perPage);
    }

    public function find(int $id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $slider = $this->model->findOrFail($id);
        $slider->update($data);
        return $slider;
    }

    public function delete(int $id)
    {
        $slider = $this->model->findOrFail($id);
        return $slider->delete();
    }

    public function toggleActive(int $id)
    {
        $slider = $this->model->findOrFail($id);
        $slider->update(['is_active' => !$slider->is_active]);
        return $slider;
    }
}

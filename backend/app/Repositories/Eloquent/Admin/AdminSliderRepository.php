<?php

namespace App\Repositories\Eloquent\Admin;

use App\Models\Slider;
use App\Repositories\Contracts\Admin\AdminSliderRepositoryInterface;

class AdminSliderRepository implements AdminSliderRepositoryInterface
{
    protected $model;

    public function __construct(Slider $model)
    {
        $this->model = $model;
    }

    /**
     * Get all sliders
     */
    public function getAll()
    {
        return $this->model->all();
    }

    /**
     * Get paginated sliders
     */
    public function getPaginated(int $perPage = 15)
    {
        return $this->model
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find slider by ID
     */
    public function find(int $id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create new slider
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update slider
     */
    public function update(int $id, array $data)
    {
        $slider = $this->model->findOrFail($id);
        $slider->update($data);
        return $slider;
    }

    /**
     * Delete slider
     */
    public function delete(int $id)
    {
        $slider = $this->model->findOrFail($id);
        return $slider->delete();
    }

    /**
     * Toggle slider active status
     */
    public function toggleActive(int $id)
    {
        $slider = $this->model->findOrFail($id);
        $slider->update(['is_active' => !$slider->is_active]);
        return $slider;
    }
}

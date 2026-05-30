<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sliders\StoreRequest;
use App\Http\Requests\Admin\Sliders\UpdateRequest;
use App\Repositories\Contracts\Admin\AdminSliderRepositoryInterface;

class SliderController extends Controller
{
    public function __construct(private AdminSliderRepositoryInterface $sliderRepository) {}

    /**
     * Get active sliders (API endpoint)
     */
    public function sliders()
    {
        $sliders = $this->sliderRepository->getAll();
        $active = collect($sliders)->where('is_active', true)->take(3);

        if ($active->isEmpty()) {
            return response()->json(['message' => 'No sliders available now'], 404);
        }

        return response()->json(['sliders' => $active], 200);
    }

    /**
     * Display a listing of sliders
     */
    public function index()
    {
        $sliders = $this->sliderRepository->getAll();
        return view('admin.sliders.index', compact('sliders'));
    }

    /**
     * Show the form for creating a new slider
     */
    public function create()
    {
        return view('admin.sliders.create');
    }

    /**
     * Store a newly created slider
     */
    public function store(StoreRequest $request)
    {
        $slider = $this->sliderRepository->create($request->validated());

        return redirect()->route('admin.sliders.index')->with('success', 'Slider created successfully.');
    }

    /**
     * Display the specified slider
     */
    public function show($id)
    {
        $slider = $this->sliderRepository->find($id);
        return view('admin.sliders.show', compact('slider'));
    }

    /**
     * Show the form for editing a slider
     */
    public function edit($id)
    {
        $slider = $this->sliderRepository->find($id);
        return view('admin.sliders.edit', compact('slider'));
    }

    /**
     * Update the specified slider
     */
    public function update(UpdateRequest $request, $id)
    {
        $this->sliderRepository->update($id, $request->validated());

        return redirect()->route('admin.sliders.index')->with('success', 'Slider updated successfully.');
    }

    /**
     * Delete the specified slider
     */
    public function destroy($id)
    {
        $this->sliderRepository->delete($id);

        return response()->json(['message' => 'Slider deleted successfully']);
    }

    /**
     * Toggle slider active status
     */
    public function toggleActive($id)
    {
        $this->sliderRepository->toggleActive($id);

        return response()->json(['message' => 'Slider status updated successfully']);
    }
}

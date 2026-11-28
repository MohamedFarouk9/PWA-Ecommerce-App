<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Catalog\Http\Requests\Sliders\StoreRequest;
use Modules\Catalog\Http\Requests\Sliders\UpdateRequest;
use Modules\Catalog\Repositories\Contracts\SliderRepositoryInterface;

class SliderController extends Controller
{
    public function __construct(private SliderRepositoryInterface $sliderRepository) {}

    public function sliders()
    {
        $sliders = $this->sliderRepository->getAll();
        $active  = collect($sliders)->where('is_active', true)->take(3);

        if ($active->isEmpty()) {
            return response()->json(['message' => 'No sliders available now'], 404);
        }

        return response()->json(['sliders' => $active], 200);
    }

    public function index()
    {
        $sliders = $this->sliderRepository->getAll();
        return view('admin.sliders.index', compact('sliders'));
    }

    public function create()
    {
        return view('admin.sliders.create');
    }

    public function store(StoreRequest $request)
    {
        $this->sliderRepository->create($request->validated());
        return redirect()->route('admin.sliders.index')->with('success', 'Slider created successfully.');
    }

    public function show($id)
    {
        $slider = $this->sliderRepository->find($id);
        return view('admin.sliders.show', compact('slider'));
    }

    public function edit($id)
    {
        $slider = $this->sliderRepository->find($id);
        return view('admin.sliders.edit', compact('slider'));
    }

    public function update(UpdateRequest $request, $id)
    {
        $this->sliderRepository->update($id, $request->validated());
        return redirect()->route('admin.sliders.index')->with('success', 'Slider updated successfully.');
    }

    public function destroy($id)
    {
        $this->sliderRepository->delete($id);
        return response()->json(['message' => 'Slider deleted successfully']);
    }

    public function toggleStatus($id)
    {
        $this->sliderRepository->toggleActive($id);
        return response()->json(['message' => 'Slider status updated successfully']);
    }

    public function toggleActive($id)
    {
        return $this->toggleStatus($id);
    }
}

<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalog\Http\Requests\Sections\StoreSectionRequest;
use Modules\Catalog\Http\Requests\Sections\UpdateSectionRequest;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Repositories\Contracts\SectionRepositoryInterface;

class ProductSectionsController extends Controller
{
    public function __construct(private SectionRepositoryInterface $sectionRepository) {}

    public function index()
    {
        $sections = $this->sectionRepository->getAll();
        return view('admin.sections.index', compact('sections'));
    }

    public function create()
    {
        return view('admin.sections.create');
    }

    public function store(StoreSectionRequest $request)
    {
        $this->sectionRepository->create($request->validated());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'   => 'success',
                'message'  => 'Section created successfully.',
                'redirect' => route('admin.sections.index'),
            ], 201);
        }

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section created successfully.');
    }

    public function edit($id)
    {
        $section     = $this->sectionRepository->find($id);
        $allProducts = Product::whereHas('status', fn ($q) => $q->where('name', 'published'))->get();
        return view('admin.sections.edit', compact('section', 'allProducts'));
    }

    public function update(UpdateSectionRequest $request, $id)
    {
        $this->sectionRepository->update($id, $request->validated());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'   => 'success',
                'message'  => 'Section updated successfully.',
                'redirect' => route('admin.sections.index'),
            ]);
        }

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section updated successfully.');
    }

    public function destroy($id)
    {
        $this->sectionRepository->delete($id);
        return response()->json(['message' => 'Section deleted successfully']);
    }

    public function show($id)
    {
        $section     = $this->sectionRepository->find($id);
        $allProducts = Product::whereHas('status', fn ($q) => $q->where('name', 'published'))->get();
        return view('admin.sections.show', compact('section', 'allProducts'));
    }

    public function assignProducts(Request $request, $id)
    {
        $request->validate([
            'product_ids'   => 'required|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        $this->sectionRepository->assignProducts($id, $request->product_ids);

        return response()->json(['message' => 'Products assigned to section successfully']);
    }

    public function productsBySection($id)
    {
        $products = $this->sectionRepository->getProducts($id);
        return response()->json(['data' => $products]);
    }
}

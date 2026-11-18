<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sections\StoreSectionRequest;
use App\Http\Requests\Admin\Sections\UpdateSectionRequest;
use App\Models\Product;
use App\Repositories\Contracts\Admin\AdminSectionRepositoryInterface;
use Illuminate\Http\Request;

class ProductSectionsController extends Controller
{
    public function __construct(private AdminSectionRepositoryInterface $sectionRepository) {}

    /**
     * List all sections
     */
    public function index()
    {
        $sections = $this->sectionRepository->getAll();
        return view('admin.sections.index', compact('sections'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.sections.create');
    }

    /**
     * Store new section
     */
    public function store(StoreSectionRequest $request)
    {
        $validated = $request->validated();

        $this->sectionRepository->create($validated);

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

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $section = $this->sectionRepository->find($id);
        $allProducts = Product::where('status', 'published')->get();
        return view('admin.sections.edit', compact('section', 'allProducts'));
    }

    /**
     * Update section
     */
    public function update(UpdateSectionRequest $request, $id)
    {
        $validated = $request->validated();

        $this->sectionRepository->update($id, $validated);

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

    /**
     * Delete section
     */
    public function destroy($id)
    {
        $this->sectionRepository->delete($id);

        return response()->json(['message' => 'Section deleted successfully']);
    }

    /**
     * Show section details and manage products
     */
    public function show($id)
    {
        $section = $this->sectionRepository->find($id);
        $allProducts = Product::where('status', 'published')->get();
        return view('admin.sections.show', compact('section', 'allProducts'));
    }

    /**
     * Assign products to section (API endpoint from routes)
     */
    public function assignProducts(Request $request, $id)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer|exists:products,id'
        ]);

        $this->sectionRepository->assignProducts($id, $request->product_ids);

        return response()->json([
            'message' => 'Products assigned to section successfully'
        ]);
    }

    /**
     * Get section products (API endpoint from routes)
     */
    public function productsBySection($id)
    {
        $products = $this->sectionRepository->getProducts($id);

        return response()->json([
            'data' => $products
        ]);
    }
}


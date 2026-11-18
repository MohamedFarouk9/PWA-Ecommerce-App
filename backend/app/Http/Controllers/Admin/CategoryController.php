<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Categories\StoreRequest;
use App\Http\Requests\Admin\Categories\UpdateOrderRequest;
use App\Http\Requests\Admin\Categories\UpdateRequest;
use App\Models\ProductCategory;
use App\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryController extends Controller
{
    protected $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function index()
    {
        $categories = $this->categoryRepository->all();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = $this->categoryRepository->getParentCategories();
        return view('admin.categories.create', compact('categories'));
    }

    public function store(StoreRequest $request)
    {
        $category = $this->categoryRepository->create($request->validated());

        return response()->json([
            'status'   => 'success',
            'message'  => 'Category created successfully',
            'redirect' => route('admin.categories.index'),
            'category' => $category,
        ], 201);
    }

    public function edit($id)
    {
        $category   = $this->categoryRepository->find($id);
        $categories = $this->categoryRepository->getParentCategories();
        return view('admin.categories.edit', compact('category', 'categories'));
    }

    public function update(UpdateRequest $request, $id)
    {
        $category = $this->categoryRepository->update($id, $request->validated());

        return response()->json([
            'status'   => 'success',
            'message'  => 'Category updated successfully',
            'redirect' => route('admin.categories.index'),
            'category' => $category,
        ], 200);
    }

    public function show($id)
    {
        $category = $this->categoryRepository->find($id);
        return view('admin.categories.show', compact('category'));
    }

    public function destroy($id)
    {
        try {
            $this->categoryRepository->delete($id);
            return response()->json([
                'message' => 'Category deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function updateOrder(UpdateOrderRequest $request)
    {
        $this->categoryRepository->updateOrder($request->validated()['categories']);

        return response()->json([
            'message' => 'Category order updated successfully',
        ], 200);
    }

}

<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Catalog\Actions\ToggleProductStatusAction;
use Modules\Catalog\Http\Requests\Products\StoreRequest;
use Modules\Catalog\Http\Requests\Products\UpdateRequest;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductImage;
use Modules\Catalog\Services\ProductService;

final class ProductController extends Controller
{
    public function __construct(
        private ProductService $service,
        private ImageOptimizationService $imageOptimizationService
    ) {}

    public function index(Request $request)
    {
        $products = $this->service->list($request->only(['q', 'category', 'status', 'sort', 'dir']));
        return view('admin.products.index', compact('products'));
    }

    public function show(Product $product)
    {
        $product->load(['category', 'tags', 'sections', 'productVariants.productAttribute', 'images']);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load(['category', 'tags', 'sections', 'productVariants.productAttribute', 'images']);
        [$categories, $tags, $attributes, $sections] = $this->service->formData();
        return view('admin.products.edit', compact('product', 'categories', 'tags', 'attributes', 'sections'));
    }

    public function create()
    {
        [$categories, $tags, $attributes, $sections] = $this->service->formData();
        return view('admin.products.create', compact('categories', 'tags', 'attributes', 'sections'));
    }

    public function store(StoreRequest $request)
    {
        $validatedData = $request->validated();

        $product = $this->service->create($validatedData);
        $this->syncProductImages($product, $request);

        Log::info('Final product variants count: ' . $product->productVariants()->count());

        return response()->json(['product' => $product, 'message' => 'Product created successfully']);
    }

    public function update(UpdateRequest $request, Product $product)
    {
        $this->service->update($product, $request->validated());
        $this->syncProductImages($product, $request);
        return response()->json(['message' => 'Product updated successfully']);
    }

    private function syncProductImages(Product $product, Request $request): void
    {
        if (! $request->hasFile('images') && ! $request->hasFile('image')) {
            return;
        }

        $product->load('images');
        foreach ($product->images as $image) {
            $this->imageOptimizationService->deleteProductImages($product->id, $image->path);
        }
        $product->images()->delete();

        $files = [];
        if ($request->hasFile('images')) {
            $files = $request->file('images');
        } elseif ($request->hasFile('image')) {
            $files = [$request->file('image')];
        }

        foreach ($files as $position => $file) {
            $result = $this->imageOptimizationService->uploadProductImage($file, $product->id);

            if ($result['success']) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path'       => $result['original_path'],
                    'position'   => $position + 1,
                    'is_cover'   => $position === 0,
                ]);
            }
        }
    }

    public function destroy(Product $product)
    {
        $this->imageOptimizationService->deleteProductImages($product->id);
        $this->service->delete($product);
        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function toggleStatus(Product $product, ToggleProductStatusAction $action)
    {
        $action->execute($product);
        return response()->json(['message' => 'Product status updated successfully']);
    }

    public function bulkAction(Request $request)
    {
        $data = $request->validate([
            'ids'    => 'required|array',
            'ids.*'  => 'integer|exists:products,id',
            'action' => 'required|string|in:delete,publish,unpublish',
        ]);
        $this->service->bulkAction($data);
        return back()->with('success', 'Bulk action applied.');
    }
}

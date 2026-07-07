<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Products\StoreRequest;
use App\Http\Requests\Admin\Products\UpdateRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

final class ProductController extends Controller
{
    public function __construct(private ProductService $service)
    {
    }

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

        // Debug: Log the variants data
        Log::info('Controller - Validated data:', $validatedData);
        Log::info('Controller - Raw variants data:', $request->input('variants', []));

        $product = $this->service->create($validatedData);
        $this->syncProductImages($product, $request);

        // Debug: Check final product variants
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

        // Delete existing product images and their files first.
        $product->load('images');
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }
        $product->images()->delete();

        $files = [];
        if ($request->hasFile('images')) {
            $files = $request->file('images');
        } elseif ($request->hasFile('image')) {
            $files = [$request->file('image')];
        }

        foreach ($files as $position => $file) {
            $path = $file->store('products', 'public');
            ProductImage::create([
                'product_id' => $product->id,
                'path' => $path,
                'position' => $position + 1,
                'is_cover' => $position === 0,
            ]);
        }
    }

    public function destroy(Product $product)
    {
        $this->service->delete($product);
        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function toggleStatus(Product $product)
    {
        $this->service->toggleStatus($product);
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

<?php
namespace App\Services;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Models\ProductTag;
use App\Models\Section;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

final class ProductService
{
    public function list(array $filters = [])
    {
        $query = Product::query()->with(['category', 'tags', 'sections'])->latest();

        if ($q = Arr::get($filters, 'q')) {
            $query->where('name', 'like', "%{$q}%");
        }
        if ($category = Arr::get($filters, 'category')) {
            $query->where('product_category_id', $category);
        }
        if ($status = Arr::get($filters, 'status')) {
            $query->where('status', $status);
        }
        if ($sort = Arr::get($filters, 'sort')) {
            $dir = Arr::get($filters, 'dir', 'asc');
            $query->orderBy($sort, $dir);
        }
        return $query->get();
    }

    public function formData(): array
    {
        return [
            ProductCategory::orderBy('name')->get(),
            ProductTag::orderBy('name')->get(),
            ProductAttribute::orderBy('name')->get(),
            Section::orderBy('label')->get(),
        ];
    }

    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create(Arr::only($data, [
                'product_category_id', 'name', 'description', 'price', 'quantity', 'product_status_id', 'status',
            ]));
            $this->syncRelations($product, $data);
            return $product;
        });
    }

    public function update(Product $product, array $data): void
    {
        DB::transaction(function () use ($product, $data) {
            $product->update(Arr::only($data, [
                'product_category_id', 'name', 'description', 'price', 'quantity', 'product_status_id', 'status',
            ]));
            $this->syncRelations($product, $data);
        });
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $product->load('images');
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->path);
            }
            $product->tags()->detach();
            $product->sections()->detach();
            $product->productVariants()->delete();
            $product->images()->delete();
            $product->delete();
        });
    }

    public function toggleStatus(Product $product): void
    {
        $product->update(['status' => $product->status === 'published' ? 'draft' : 'published']);
    }

    public function bulkAction(array $data): void
    {
        $ids    = $data['ids'];
        $action = $data['action'];
        Product::whereIn('id', $ids)->chunkById(200, function ($chunk) use ($action) {
            foreach ($chunk as $product) {
                match ($action) {
                    'delete' => $this->delete($product),
                    'publish'   => $product->update(['status' => 'published']),
                    'unpublish' => $product->update(['status' => 'draft']),
                };
            }
        });
    }

    public function syncRelations(Product $product, array $data): void
    {
        if (isset($data['tags'])) {
            $product->tags()->sync($data['tags']);
        }

        if (isset($data['sections'])) {
            $product->sections()->sync($data['sections']);
        }

        if (isset($data['variants'])) {
            Log::info('Variants data received:', $data['variants']);
            $product->productVariants()->delete();

            $createdVariants = 0;
            foreach ($data['variants'] as $index => $variant) {
                Log::info("Processing variant $index:", $variant);

                if (! isset($variant['product_attribute_id'], $variant['value'])) {
                    Log::info("Skipping variant $index - missing required fields");
                    continue;
                }

                if (empty($variant['product_attribute_id']) || empty($variant['value'])) {
                    Log::info("Skipping variant $index - empty values");
                    continue;
                }

                $product->productVariants()->create([
                    'product_attribute_id' => $variant['product_attribute_id'],
                    'value'                => $variant['value'],
                    'additional_price'     => $variant['additional_price'] ?? null,
                    'quantity'             => $variant['quantity'] ?? null,
                ]);
                $createdVariants++;
            }
            Log::info("Created $createdVariants variants for product {$product->id}");
        } else {
            Log::info('No variants data in request');
        }
    }
}

<?php

namespace Modules\Catalog\Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class ProductImageSeeder extends Seeder
{
    /**
     * Safe image seeder: assigns fallback Unsplash cover + gallery images ONLY to products
     * that do not already have a real http(s) cover image or gallery images.
     * Preserves all curated images seeded by ProductSeeder, ShowcaseProductsSeeder, and RealCatalogMLSeeder.
     */
    public function run(): void
    {
        $this->command?->info('ProductImageSeeder: Backfilling missing product cover & gallery images...');

        $updatedCount = 0;

        Product::withTrashed()->withCount('images')->chunkById(100, function ($products) use (&$updatedCount) {
            foreach ($products as $product) {
                $hasHttpCover = is_string($product->image) && str_starts_with($product->image, 'http');

                if (!$hasHttpCover) {
                    $product->update([
                        'image' => $product->getTestingImageUrl(0),
                    ]);
                    $updatedCount++;
                }

                if ($product->images_count === 0) {
                    $coverUrl = $hasHttpCover ? $product->image : $product->getTestingImageUrl(0);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'path'       => $coverUrl,
                        'position'   => 1,
                        'is_cover'   => true,
                    ]);

                    for ($i = 1; $i < 3; $i++) {
                        ProductImage::create([
                            'product_id' => $product->id,
                            'path'       => $product->getTestingImageUrl($i),
                            'position'   => $i + 1,
                            'is_cover'   => false,
                        ]);
                    }
                }
            }
        });

        Cache::flush();

        $this->command?->info("ProductImageSeeder: Backfilled {$updatedCount} products (existing curated images preserved).");
    }
}

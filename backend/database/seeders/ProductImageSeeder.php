<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    /**
     * Seed every product with a cover image link and a 4-image gallery of direct high-resolution product links.
     */
    public function run(): void
    {
        $this->command?->info('Assigning testing product image links to all products...');

        $products = Product::withTrashed()->get();
        $updatedCount = 0;

        foreach ($products as $product) {
            $coverUrl = $product->getTestingImageUrl(0);

            // Set direct cover image URL on product table
            $product->update([
                'image' => $coverUrl,
            ]);

            // Create 4 gallery image links per product
            $product->images()->delete();
            for ($i = 0; $i < 4; $i++) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path'       => $product->getTestingImageUrl($i),
                    'position'   => $i + 1,
                    'is_cover'   => $i === 0,
                ]);
            }

            $updatedCount++;
        }

        \Illuminate\Support\Facades\Cache::flush();

        $this->command?->info("Successfully seeded direct testing image links (cover + 4 gallery images) for {$updatedCount} products!");
    }
}
<?php

namespace Modules\Catalog\Database\Seeders;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    /**
     * Seeds category-aware, realistic variants (Color / Size / Storage / Format)
     * for products that do not already have variants (idempotent).
     */
    public function run(): void
    {
        $colorAttr   = ProductAttribute::firstOrCreate(['name' => 'Color']);
        $sizeAttr    = ProductAttribute::firstOrCreate(['name' => 'Size']);
        $storageAttr = ProductAttribute::firstOrCreate(['name' => 'Storage']);
        $formatAttr  = ProductAttribute::firstOrCreate(['name' => 'Format']);

        $seededProducts = 0;

        Product::with(['category', 'productVariants'])->chunkById(100, function ($products) use ($colorAttr, $sizeAttr, $storageAttr, $formatAttr, &$seededProducts) {
            foreach ($products as $product) {
                if ($product->productVariants->isNotEmpty()) {
                    continue;
                }

                $categoryName = $product->category?->name ?? '';
                $colors = is_array($product->color) && !empty($product->color) ? $product->color : ['Black', 'Silver'];
                $sizes  = is_array($product->size) && !empty($product->size) ? $product->size : ['Standard'];

                if (in_array($categoryName, ['Smartphones', 'Laptops'], true)) {
                    foreach (array_slice($colors, 0, 2) as $c) {
                        ProductVariant::create([
                            'product_id'           => $product->id,
                            'product_attribute_id' => $colorAttr->id,
                            'value'                => $c,
                            'additional_price'     => 0.00,
                            'quantity'             => rand(8, 35),
                        ]);
                    }
                    foreach (array_slice($sizes, 0, 2) as $idx => $s) {
                        ProductVariant::create([
                            'product_id'           => $product->id,
                            'product_attribute_id' => $storageAttr->id,
                            'value'                => $s,
                            'additional_price'     => $idx * 100.00,
                            'quantity'             => rand(5, 25),
                        ]);
                    }
                } elseif (in_array($categoryName, ['Fiction', 'Non-Fiction', "Children's Books", 'Academic'], true)) {
                    foreach (array_slice($sizes, 0, 2) as $idx => $fmt) {
                        ProductVariant::create([
                            'product_id'           => $product->id,
                            'product_attribute_id' => $formatAttr->id,
                            'value'                => $fmt,
                            'additional_price'     => $idx * 12.00,
                            'quantity'             => rand(15, 60),
                        ]);
                    }
                } else {
                    foreach (array_slice($colors, 0, 2) as $c) {
                        ProductVariant::create([
                            'product_id'           => $product->id,
                            'product_attribute_id' => $colorAttr->id,
                            'value'                => $c,
                            'additional_price'     => 0.00,
                            'quantity'             => rand(10, 40),
                        ]);
                    }
                    if ($sizes !== ['Standard']) {
                        foreach (array_slice($sizes, 0, 2) as $s) {
                            ProductVariant::create([
                                'product_id'           => $product->id,
                                'product_attribute_id' => $sizeAttr->id,
                                'value'                => $s,
                                'additional_price'     => 0.00,
                                'quantity'             => rand(10, 40),
                            ]);
                        }
                    }
                }

                $seededProducts++;
            }
        });

        $this->command?->info("ProductVariantSeeder: Seeded realistic category-aware variants for {$seededProducts} products!");
    }
}

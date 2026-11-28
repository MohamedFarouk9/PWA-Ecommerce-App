<?php

namespace Modules\Catalog\Database\Seeders;

use App\Models\Product;
use App\Models\ProductAttribute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductDetailsSeeder extends Seeder
{
    /**
     * Safe backfill seeder: fills ONLY missing brand, SKU, product_code, view_count,
     * color/size, or specifications without ever overwriting curated real-world catalog data.
     */
    public function run(): void
    {
        $brandAttr    = ProductAttribute::firstOrCreate(['name' => 'Brand']);
        $materialAttr = ProductAttribute::firstOrCreate(['name' => 'Material']);
        $warrantyAttr = ProductAttribute::firstOrCreate(['name' => 'Warranty']);

        $updated = 0;

        Product::with('specifications')->chunkById(100, function ($products) use ($brandAttr, $materialAttr, $warrantyAttr, &$updated) {
            foreach ($products as $product) {
                $dirty = [];

                if (empty($product->brand)) {
                    $dirty['brand'] = 'Generic';
                }

                if (empty($product->product_code)) {
                    $dirty['product_code'] = 'PROD-' . strtoupper(Str::random(8));
                }

                if (empty($product->sku)) {
                    $slug = Str::upper(Str::slug($product->brand ?: 'ITEM'));
                    $dirty['sku'] = "SKU-{$slug}-{$product->id}";
                }

                if (empty($product->view_count)) {
                    $dirty['view_count'] = rand(100, 3500);
                }

                if (empty($product->color)) {
                    $dirty['color'] = ['Black', 'White'];
                }

                if (empty($product->size)) {
                    $dirty['size'] = ['Standard'];
                }

                if (!empty($dirty)) {
                    $product->update($dirty);
                    $updated++;
                }

                // Only backfill basic specs if the product has none yet
                if ($product->specifications->isEmpty()) {
                    $product->specifications()->syncWithoutDetaching([
                        $brandAttr->id    => ['value' => $product->brand ?: 'Standard', 'unit' => null, 'position' => 1],
                        $materialAttr->id => ['value' => 'Standard Quality', 'unit' => null, 'position' => 2],
                        $warrantyAttr->id => ['value' => '1 Year Limited', 'unit' => null, 'position' => 3],
                    ]);
                }
            }
        });

        $this->command?->info("ProductDetailsSeeder: Safely backfilled {$updated} products without overwriting curated specs.");
    }
}

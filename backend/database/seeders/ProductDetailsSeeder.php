<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductSpecification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure attribute types exist
        $brandAttr = ProductAttribute::firstOrCreate(['name' => 'Brand']);
        $colorAttr = ProductAttribute::firstOrCreate(['name' => 'Color']);
        $sizeAttr = ProductAttribute::firstOrCreate(['name' => 'Size']);
        $materialAttr = ProductAttribute::firstOrCreate(['name' => 'Material']);
        $batteryAttr = ProductAttribute::firstOrCreate(['name' => 'Battery Capacity']);
        $warrantyAttr = ProductAttribute::firstOrCreate(['name' => 'Warranty']);
        $dimensionsAttr = ProductAttribute::firstOrCreate(['name' => 'Dimensions']);
        $weightAttr = ProductAttribute::firstOrCreate(['name' => 'Weight']);

        // Get products (take a sample for demo)
        $products = Product::where('product_status_id', 1)->take(100)->get();

        foreach ($products as $product) {
            // Assign brand
            if (!$product->brand) {
                $brands = ['Apple', 'Samsung', 'Sony', 'Dell', 'Nike', 'Adidas', 'bose', 'Logitech'];
                $product->update(['brand' => $brands[array_rand($brands)]]);
            }

            // Assign product code
            if (!$product->product_code) {
                $product->update([
                    'product_code' => 'PROD-' . strtoupper(Str::random(8)),
                ]);
            }

            // Assign SKU
            if (!$product->sku) {
                $product->update([
                    'sku' => 'SKU-' . $product->id . '-' . Str::lower($product->name),
                ]);
            }

            // Set special price (some products on sale)
            if (rand(1, 10) <= 3 && !$product->special_price) {
                $specialPrice = $product->price * (0.7 + rand(0, 30) / 100);
                $product->update(['special_price' => round($specialPrice, 2)]);
            }

            // Assign color and size options
            $colors = ['Red', 'Blue', 'Green', 'Black', 'White', 'Silver'];
            $sizes = ['Small', 'Medium', 'Large', 'Extra Large', 'XL', 'XXL'];

            $product->update([
                'color' => $colors,
                'size' => $sizes,
            ]);

            // Add specifications for this product
            $specifications = [
                ['attribute_id' => $colorAttr->id, 'value' => $colors[array_rand($colors)], 'unit' => null, 'position' => 1],
                ['attribute_id' => $sizeAttr->id, 'value' => $sizes[array_rand($sizes)], 'unit' => null, 'position' => 2],
                ['attribute_id' => $materialAttr->id, 'value' => 'Premium Materials', 'unit' => null, 'position' => 3],
            ];

            // For electronics, add battery/dimensions
            if (in_array($product->id % 3, [0, 1])) { // ~66% of products
                $specifications[] = [
                    'attribute_id' => $batteryAttr->id,
                    'value' => rand(1000, 10000),
                    'unit' => 'mAh',
                    'position' => 4,
                ];
                $specifications[] = [
                    'attribute_id' => $warrantyAttr->id,
                    'value' => rand(6, 24),
                    'unit' => 'months',
                    'position' => 5,
                ];
            }
            if (in_array($product->id % 2, [0, 1])) { // ~50% of products
                $specifications[] = [
                    'attribute_id' => $dimensionsAttr->id,
                    'value' => rand(10, 30) . ' x ' . rand(15, 40),
                    'unit' => 'cm',
                    'position' => 6,
                ];
                $specifications[] = [
                    'attribute_id' => $weightAttr->id,
                    'value' => rand(100, 1000),
                    'unit' => 'g',
                    'position' => 7,
                ];
            }

            foreach ($specifications as $spec) {
                ProductSpecification::create([
                    'product_id' => $product->id,
                    'product_attribute_id' => $spec['attribute_id'],
                    'value' => $spec['value'],
                    'unit' => $spec['unit'],
                    'position' => $spec['position'],
                ]);
            }
        }

        $this->command->info('Product details seeded successfully!');
    }
}
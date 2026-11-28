<?php

namespace Modules\Catalog\Database\Seeders;

use App\Models\Category;
use Illuminate\Support\Str;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $categories = [
            'Mobiles'            => ['Apple', 'Samsung', 'OPPO'],
            'Computer'           => ['Laptops', 'Desktop'],
            'Electronics'        => ['Smart TV', 'Camera'],
            'TVs & Appliances'   => ['Washing Machine', 'Air Conditioners'],
            'Fashion'            => ['Mens Top Wear', 'Mens Footwear', 'Women Footwear'],
            'Baby & Kids'        => ['Kids Footwear', 'Kids Clothing', 'Baby Care'],
            'Home & Furniture'   => ['Home Decor', 'Bedroom Furniture', 'Living Room Furniture'],
            'Sports, Books'      => ['Health and Nutrition', 'Home Gyms', 'Books'],
            'Mobile Accessories' => ['Mobile Cases', 'HeadPhone'],
        ];

        foreach ($categories as $parent => $subcategories) {
            // Create the parent category
            $parentCategory = Category::create([
                'category_name'  => $parent,
                'slug'           => Str::slug($parent),
                'category_image' => 'default.png',

            ]);

            // Create each subcategory under the parent category
            foreach ($subcategories as $subcategory) {
                Subcategory::create([
                    'subcategory_name' => $subcategory,
                    'category_id'      => $parentCategory->id,
                    'slug'             => Str::slug($subcategory),
                ]);
            }

        }
    }
}

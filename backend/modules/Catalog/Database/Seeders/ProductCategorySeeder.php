<?php

namespace Modules\Catalog\Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Main categories
        $mainCategories = [
            'Electronics' => [
                'description' => 'Latest gadgets and electronic devices',
                'children' => [
                    'Smartphones' => 'Latest mobile phones and accessories',
                    'Laptops' => 'Portable computers and accessories',
                    'Audio' => 'Headphones, speakers, and audio equipment',
                    'Gaming' => 'Gaming consoles and accessories'
                ]
            ],
            'Clothing' => [
                'description' => 'Fashion and apparel for all ages',
                'children' => [
                    'Men\'s Fashion' => 'Clothing and accessories for men',
                    'Women\'s Fashion' => 'Clothing and accessories for women',
                    'Kids\' Fashion' => 'Clothing and accessories for children',
                    'Footwear' => 'Shoes and boots for all ages'
                ]
            ],
            'Home & Kitchen' => [
                'description' => 'Everything for your home',
                'children' => [
                    'Furniture' => 'Home and office furniture',
                    'Kitchen Appliances' => 'Kitchen gadgets and appliances',
                    'Home Decor' => 'Decorative items for your home',
                    'Bedding' => 'Bed sheets, pillows, and comforters'
                ]
            ],
            'Books' => [
                'description' => 'Fiction, non-fiction, and educational books',
                'children' => [
                    'Fiction' => 'Novels and story books',
                    'Non-Fiction' => 'Educational and reference books',
                    'Children\'s Books' => 'Books for young readers',
                    'Academic' => 'Textbooks and study materials'
                ]
            ],
            'Sports & Outdoors' => [
                'description' => 'Sports equipment and outdoor gear',
                'children' => [
                    'Fitness' => 'Exercise and fitness equipment',
                    'Team Sports' => 'Equipment for team sports',
                    'Outdoor Recreation' => 'Camping and hiking gear',
                    'Water Sports' => 'Swimming and water activities equipment'
                ]
            ]
        ];

        // Create main categories and their children
        foreach ($mainCategories as $name => $data) {
            $mainCategory = ProductCategory::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $data['description'],
                'is_active' => true,
                'order' => 0,
                'image' => 'categories/' . Str::slug($name) . '.jpg'
            ]);

            // Create child categories
            foreach ($data['children'] as $childName => $childDescription) {
                ProductCategory::create([
                    'name' => $childName,
                    'slug' => Str::slug($childName),
                    'description' => $childDescription,
                    'parent_id' => $mainCategory->id,
                    'is_active' => true,
                    'order' => 0,
                    'image' => 'categories/' . Str::slug($childName) . '.jpg'
                ]);
            }
        }

        $this->command->info('Product categories seeded successfully!');
    }
}

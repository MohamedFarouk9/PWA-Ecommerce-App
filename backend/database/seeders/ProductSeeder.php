<?php
namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStatus;
use App\Models\Section;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ProductCategory::all();
        $statuses   = ProductStatus::all();

        // Create 20 products
        for ($i = 0; $i < 20; $i++) {
            $price    = fake()->randomFloat(2, 10, 1000);
            $quantity = fake()->numberBetween(0, 100);

            $product = Product::create([
                'name'                => fake()->words(3, true),
                'description'         => fake()->paragraphs(2, true),
                'price'               => $price,
                'quantity'            => $quantity,
                'product_status_id'   => 1,
                'product_category_id' => $categories->random()->id,
            ]);

            $product->update(['image' => $product->getTestingImageUrl(0)]);
        }

        // Assign products to random sections
        $sections = Section::all();
        Product::all()->each(function ($product) use ($sections) {
            $product->sections()->sync($sections->random(rand(1, $sections->count()))->pluck('id')->toArray());
        });

        $this->command->info('Products seeded successfully!');
    }
}

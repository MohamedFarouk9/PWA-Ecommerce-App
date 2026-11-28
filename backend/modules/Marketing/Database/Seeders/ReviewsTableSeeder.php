<?php

namespace Modules\Marketing\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\Product;
use App\Models\User;
use Faker\Factory as Faker;

class ReviewsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    { 
        $faker = Faker::create();

        // Get all products and users
        $products = Product::all();
        $users = User::all();

        // Check if there are products and users
        if ($products->isEmpty() || $users->isEmpty()) {
            $this->command->info('No products or users found. Skipping reviews seeding.');
            return;
        }

        // Create 50 dummy reviews
        for ($i = 0; $i < 50; $i++) {
            Review::create([
                'product_id' => $products->random()->id, // Random product
                'user_id' => $users->random()->id, // Random user
                'comment' => $faker->paragraph, // Random comment
                'rating' => rand(1, 5), // Random rating between 1 and 5
            ]);
        }

        $this->command->info('50 reviews seeded successfully.');
    }
}

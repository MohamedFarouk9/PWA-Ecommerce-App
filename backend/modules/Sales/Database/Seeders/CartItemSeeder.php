<?php
namespace Modules\Sales\Database\Seeders;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // fetch random users and products

        $users    = User::all();
        $products = Product::all();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No users or products found! Please seed them first.');
            return;
        }

        // Create cart items for random users
        foreach ($users as $user) {
            // assgin random products to user's cart
            $randomProducts = $products->random(rand(1, 3));

            foreach ($randomProducts as $product) {
                Cart::create([
                    "user_id"    => $user->id,
                    "product_id" => $product->id,
                    "quantity"   => rand(1, 5), // Random quantity between 1 and 5

                ]);
            }
        }

        $this->command->info('Cart items seeded successfully!');
    }
}

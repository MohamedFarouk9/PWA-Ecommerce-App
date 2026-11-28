<?php
// filepath: e:\PWA-Ecommerce-App\backend\database\seeders\CartSeeder.php
namespace Modules\Sales\Database\Seeders;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $users    = User::where('role', 'user')->get();
        $products = Product::where('quantity', '>', 0)->get();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No users or products found! Please seed them first.');
            return;
        }

        // Add 1-3 items to each user's cart
        foreach ($users as $user) {
            $cartItemCount    = fake()->numberBetween(1, 3);
            $selectedProducts = $products->random($cartItemCount);

            foreach ($selectedProducts as $product) {
                $quantity = fake()->numberBetween(1, min(5, $product->quantity));

                Cart::create([
                    'user_id'    => $user->id,
                    'product_id' => $product->id,
                    'quantity'   => $quantity,
                    'price'      => $product->price, // snapshot price at time of add
                    'meta'       => null,
                ]);
            }
        }

        $this->command->info('Cart items seeded successfully!');
    }
}

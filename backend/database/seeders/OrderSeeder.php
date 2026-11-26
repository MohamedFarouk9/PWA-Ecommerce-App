<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Generates a realistic large-scale e-commerce order dataset (~1,720 orders, ~4,500+ order_items):
     *   1. ~1,600 normal multi-item orders driven by 10 shopper personas (strong co-purchase clusters
     *      for Collaborative Filtering Recommendation ML).
     *   2. ~120 realistic high-risk / fraudulent orders (~7% rate) with authentic fraud signals
     *      (new account age, unverified email, mismatched billing/shipping address, high cart spike,
     *      rapid burst velocity, high-value electronics) for Payment Fraud & Risk Scoring ML.
     *
     * Idempotent: skips if persona orders already exist.
     */
    public function run(): void
    {
        if (Order::where('order_number', 'LIKE', 'ORD-PERS-%')->exists()) {
            $this->command?->info('OrderSeeder: Persona & fraud ML orders already seeded — skipping.');
            return;
        }

        $faker = Faker::create();
        $users = User::where('role', 'user')->get();

        if ($users->isEmpty()) {
            $this->command?->warn('OrderSeeder: No customer users found — run UserSeeder first.');
            return;
        }

        // Group products by category name for persona-driven basket generation
        $productsByCategory = Product::with('category')
            ->get()
            ->groupBy(fn (Product $p) => $p->category?->name ?? 'Other');

        if ($productsByCategory->isEmpty()) {
            $this->command?->warn('OrderSeeder: No products found — run ProductSeeder first.');
            return;
        }

        // 10 realistic shopper personas (each persona buys 2-4 items from correlated categories)
        $personas = [
            'tech_pro'        => ['Smartphones', 'Laptops', 'Audio'],
            'pc_gamer'        => ['Gaming', 'Laptops', 'Audio', 'Home Decor'],
            'dev_home_office' => ['Laptops', 'Furniture', 'Academic', 'Audio'],
            'runner_fitness'  => ['Fitness', 'Footwear', "Men's Fashion"],
            'team_athlete'    => ['Team Sports', 'Footwear', 'Fitness'],
            'outdoor_camper'  => ['Outdoor Recreation', 'Water Sports', 'Footwear'],
            'home_chef'       => ['Kitchen Appliances', 'Home Decor', 'Non-Fiction'],
            'home_makeover'   => ['Furniture', 'Home Decor', 'Bedding'],
            'avid_reader'     => ['Fiction', 'Non-Fiction', 'Academic', "Children's Books"],
            'fashion_shopper' => ["Men's Fashion", "Women's Fashion", "Kids' Fashion", 'Footwear'],
        ];
        $personaKeys = array_keys($personas);

        $normalPaymentMethods = ['stripe', 'credit_card', 'paymob', 'myfatoorah', 'debit_card', 'paypal'];
        $normalStatuses       = ['delivered', 'delivered', 'delivered', 'shipped', 'processing', 'pending'];

        $this->command?->info('OrderSeeder: Generating 1,600 persona-driven multi-item orders...');

        $normalOrdersCount = 1600;
        $totalItemsCreated = 0;

        for ($i = 1; $i <= $normalOrdersCount; $i++) {
            $user = $users->random();

            // Assign a consistent primary persona per user (so each user has coherent taste!)
            $personaKey = $personaKeys[$user->id % count($personaKeys)];
            // 80% stick to their persona, 20% cross-browse another persona
            if (rand(1, 100) > 80) {
                $personaKey = $personaKeys[array_rand($personaKeys)];
            }
            $preferredCategories = $personas[$personaKey];

            // Pick 2 to 4 distinct products from the persona's categories
            $basketSize = rand(2, 4);
            $basketProducts = collect();
            $attempts = 0;
            while ($basketProducts->count() < $basketSize && $attempts < 12) {
                $attempts++;
                $cat = $preferredCategories[array_rand($preferredCategories)];
                $pool = $productsByCategory->get($cat);
                if ($pool && $pool->isNotEmpty()) {
                    $picked = $pool->random();
                    if (!$basketProducts->contains('id', $picked->id)) {
                        $basketProducts->push($picked);
                    }
                }
            }

            if ($basketProducts->isEmpty()) {
                continue;
            }

            // Order date after user registration
            $userCreated = $user->created_at ? Carbon::parse($user->created_at) : Carbon::now()->subDays(90);
            $daysSinceJoin = max(1, (int) $userCreated->diffInDays(Carbon::now()));
            $orderDate = Carbon::now()->subDays(rand(0, min($daysSinceJoin, 365)))->subHours(rand(0, 23));

            $status = $normalStatuses[array_rand($normalStatuses)];
            $paymentStatus = ($status === 'pending') ? 'unpaid' : 'paid';
            $address = trim("{$user->address}, {$user->city}, {$user->state}, {$user->country}");

            $totalAmount = 0.0;
            $itemRows = [];

            foreach ($basketProducts as $product) {
                $qty = rand(1, 100) <= 85 ? 1 : 2;
                $unitPrice = (float) ($product->special_price ?? $product->price);
                $lineTotal = round($unitPrice * $qty, 2);
                $totalAmount += $lineTotal;

                $itemRows[] = [
                    'product_id' => $product->id,
                    'quantity'   => $qty,
                    'price'      => $unitPrice,
                    'total'      => $lineTotal,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ];
            }

            $order = Order::create([
                'user_id'          => $user->id,
                'order_number'     => sprintf('ORD-PERS-%05d', $i),
                'status'           => $status,
                'payment_status'   => $paymentStatus,
                'total_amount'     => round($totalAmount, 2),
                'payment_method'   => $normalPaymentMethods[array_rand($normalPaymentMethods)],
                'shipping_address' => $address,
                'billing_address'  => $address, // Matching address = normal low-risk signal
                'notes'            => "Persona:{$personaKey}",
                'shipped_at'       => in_array($status, ['shipped', 'delivered'], true) ? $orderDate->copy()->addDays(1) : null,
                'delivered_at'     => $status === 'delivered' ? $orderDate->copy()->addDays(rand(2, 5)) : null,
                'created_at'       => $orderDate,
                'updated_at'       => $orderDate,
            ]);

            foreach ($itemRows as &$row) {
                $row['order_id'] = $order->id;
            }
            unset($row);

            OrderItem::insert($itemRows);
            $totalItemsCreated += count($itemRows);
        }

        // =========================================================================
        // PART 2: 120 Realistic High-Risk / Fraudulent Orders (for Feature #3 ML)
        // Signals: brand-new/unverified accounts, mismatched shipping vs billing,
        // high-value electronics quantity spikes, late-night bursts, refunded/cancelled.
        // =========================================================================
        $this->command?->info('OrderSeeder: Generating 120 high-risk / fraud-labeled orders for Risk Scoring ML...');

        $newUsers = $users->filter(fn (User $u) => $u->created_at && Carbon::parse($u->created_at)->gt(Carbon::now()->subDays(14)));
        if ($newUsers->isEmpty()) {
            $newUsers = $users->take(20);
        }

        $highValueCategories = ['Smartphones', 'Laptops', 'Gaming', 'Audio'];
        $fraudOrdersCount = 120;

        for ($f = 1; $f <= $fraudOrdersCount; $f++) {
            $user = $newUsers->random();
            $burstTime = Carbon::now()->subDays(rand(0, 60))->setHour(rand(0, 4))->setMinute(rand(0, 59));

            $basketProducts = collect();
            $itemCount = rand(2, 4);
            for ($k = 0; $k < $itemCount; $k++) {
                $cat = $highValueCategories[array_rand($highValueCategories)];
                $pool = $productsByCategory->get($cat);
                if ($pool && $pool->isNotEmpty()) {
                    $picked = $pool->random();
                    if (!$basketProducts->contains('id', $picked->id)) {
                        $basketProducts->push($picked);
                    }
                }
            }

            if ($basketProducts->isEmpty()) {
                continue;
            }

            $totalAmount = 0.0;
            $itemRows = [];

            foreach ($basketProducts as $product) {
                // Fraud pattern: unusual quantity spikes (2-4 high-ticket items)
                $qty = rand(2, 4);
                $unitPrice = (float) $product->price;
                $lineTotal = round($unitPrice * $qty, 2);
                $totalAmount += $lineTotal;

                $itemRows[] = [
                    'product_id' => $product->id,
                    'quantity'   => $qty,
                    'price'      => $unitPrice,
                    'total'      => $lineTotal,
                    'created_at' => $burstTime,
                    'updated_at' => $burstTime,
                ];
            }

            $billingAddress  = trim("{$user->address}, {$user->city}, {$user->state}, {$user->country}");
            $shippingAddress = $faker->streetAddress() . ', ' . $faker->city() . ', ' . $faker->country(); // Mismatched!

            $order = Order::create([
                'user_id'          => $user->id,
                'order_number'     => sprintf('ORD-RISK-%04d', $f),
                'status'           => rand(1, 100) <= 75 ? 'cancelled' : 'pending',
                'payment_status'   => rand(1, 100) <= 65 ? 'refunded' : 'unpaid',
                'total_amount'     => round($totalAmount, 2),
                'payment_method'   => rand(1, 100) <= 70 ? 'credit_card' : 'stripe',
                'shipping_address' => $shippingAddress,
                'billing_address'  => $billingAddress,
                'notes'            => 'FLAGGED_HIGH_RISK:address_mismatch_velocity_spike',
                'shipped_at'       => null,
                'delivered_at'     => null,
                'created_at'       => $burstTime,
                'updated_at'       => $burstTime,
            ]);

            foreach ($itemRows as &$row) {
                $row['order_id'] = $order->id;
            }
            unset($row);

            OrderItem::insert($itemRows);
            $totalItemsCreated += count($itemRows);
        }

        $this->command?->info("OrderSeeder: Seeded {$normalOrdersCount} persona orders + {$fraudOrdersCount} high-risk orders ({$totalItemsCreated} total order_items)!");
    }
}

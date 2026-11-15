<?php

namespace App\Console\Commands\Database;

use App\Models\ProductCategory;
use App\Models\ProductStatus;
use App\Models\Section;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SeedHighLoadDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'db:seed-high-load 
                            {--users=500 : Number of test users to generate}
                            {--products=5000 : Number of products to generate}
                            {--orders=2000 : Number of orders to generate}
                            {--reviews=5000 : Number of reviews to generate}
                            {--chunk=500 : Batch chunk size for bulk database inserts}';

    /**
     * The console command description.
     */
    protected $description = 'High-performance bulk seeder to populate enterprise-scale datasets for stress-testing and load benchmarking';

    public function handle(): int
    {
        $startTime = microtime(true);
        $this->info('================================================================');
        $this->info('  HIGH-LOAD ENTERPRISE DATASET SEEDER (Optimized Chunk Engine)  ');
        $this->info('================================================================');

        $userCount = (int) $this->option('users');
        $productCount = (int) $this->option('products');
        $orderCount = (int) $this->option('orders');
        $reviewCount = (int) $this->option('reviews');
        $chunkSize = (int) $this->option('chunk');

        DB::disableQueryLog();

        // 1. Ensure Prerequisites
        $this->ensurePrerequisites();

        $categoryIds = DB::table('product_categories')->pluck('id')->toArray();
        $statusIds = DB::table('product_statuses')->pluck('id')->toArray();
        $sectionIds = DB::table('sections')->pluck('id')->toArray();

        // 2. Bulk Seed Users
        $userIds = $this->seedUsers($userCount, $chunkSize);

        // 3. Bulk Seed Products & Sections
        $productIds = $this->seedProducts($productCount, $categoryIds, $statusIds, $sectionIds, $chunkSize);

        // 4. Bulk Seed Orders & Order Items
        $this->seedOrders($orderCount, $userIds, $productIds, $chunkSize);

        // 5. Bulk Seed Reviews
        $this->seedReviews($reviewCount, $userIds, $productIds, $chunkSize);

        $totalSeconds = round(microtime(true) - $startTime, 2);
        $peakMemory = round(memory_get_peak_usage(true) / 1024 / 1024, 2);

        $this->newLine();
        $this->info("✓ Successfully generated enterprise dataset in {$totalSeconds}s (Peak Memory: {$peakMemory}MB)!");
        $this->table(
            ['Entity', 'Total Records Seeded'],
            [
                ['Users', number_format($userCount)],
                ['Products', number_format($productCount)],
                ['Orders', number_format($orderCount)],
                ['Order Items (est.)', number_format($orderCount * 3)],
                ['Product Reviews', number_format($reviewCount)],
            ]
        );

        return Command::SUCCESS;
    }

    private function ensurePrerequisites(): void
    {
        // Statuses
        if (DB::table('product_statuses')->count() === 0) {
            DB::table('product_statuses')->insert([
                ['name' => 'published', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'draft', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'archived', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Sections
        if (DB::table('sections')->count() === 0) {
            DB::table('sections')->insert([
                ['name' => 'featured', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'new_arrival', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'best_seller', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Categories
        if (DB::table('product_categories')->count() === 0) {
            $categories = [
                ['name' => 'Electronics', 'slug' => 'electronics', 'icon' => 'fa-laptop'],
                ['name' => 'Smartphones & Tablets', 'slug' => 'smartphones', 'icon' => 'fa-mobile-alt'],
                ['name' => 'Computers & Accessories', 'slug' => 'computers', 'icon' => 'fa-desktop'],
                ['name' => 'Audio & Headphones', 'slug' => 'audio', 'icon' => 'fa-headphones'],
                ['name' => 'Fashion & Apparel', 'slug' => 'fashion', 'icon' => 'fa-tshirt'],
                ['name' => 'Home & Kitchen', 'slug' => 'home-kitchen', 'icon' => 'fa-home'],
                ['name' => 'Gaming Gear', 'slug' => 'gaming', 'icon' => 'fa-gamepad'],
                ['name' => 'Health & Fitness', 'slug' => 'health-fitness', 'icon' => 'fa-heartbeat'],
            ];
            foreach ($categories as &$c) {
                $c['created_at'] = now();
                $c['updated_at'] = now();
            }
            DB::table('product_categories')->insert($categories);
        }
    }

    private function seedUsers(int $count, int $chunkSize): array
    {
        $this->line("Seeding <info>{$count}</info> users in batches of {$chunkSize}...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $defaultPassword = Hash::make('password123'); // Computed once for speed
        $now = now()->toDateTimeString();

        // Ensure default admin & test customer exist
        if (!DB::table('users')->where('email', 'admin@example.com')->exists()) {
            DB::table('users')->insert([
                'name' => 'System Administrator',
                'email' => 'admin@example.com',
                'password' => $defaultPassword,
                'role' => 'admin',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (!DB::table('users')->where('email', 'customer@example.com')->exists()) {
            DB::table('users')->insert([
                'name' => 'Standard Customer',
                'email' => 'customer@example.com',
                'password' => $defaultPassword,
                'role' => 'user',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $chunks = ceil($count / $chunkSize);
        for ($c = 0; $c < $chunks; $c++) {
            $batch = [];
            $currentLimit = min($chunkSize, $count - ($c * $chunkSize));

            for ($i = 0; $i < $currentLimit; $i++) {
                $idx = ($c * $chunkSize) + $i + 1;
                $batch[] = [
                    'name' => "User {$idx}",
                    'email' => "user{$idx}_" . Str::random(5) . "@example.com",
                    'phone' => '+1' . str_pad((string) mt_rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT),
                    'password' => $defaultPassword,
                    'address' => "{$idx} Innovation Way, Suite " . mt_rand(100, 999),
                    'city' => 'Seattle',
                    'state' => 'WA',
                    'country' => 'USA',
                    'role' => 'user',
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('users')->insert($batch);
            $bar->advance($currentLimit);
        }

        $bar->finish();
        $this->newLine();

        return DB::table('users')->pluck('id')->toArray();
    }

    private function seedProducts(int $count, array $categoryIds, array $statusIds, array $sectionIds, int $chunkSize): array
    {
        $this->line("Seeding <info>{$count}</info> products with sections & attributes...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $brands = ['Apple', 'Dell', 'Sony', 'Samsung', 'Logitech', 'Bose', 'Asus', 'Nike', 'Anker', 'Lenovo'];
        $categories = ['Pro', 'Ultra', 'Wireless', 'Ergonomic', 'Max', 'Smart', 'Elite', 'Lite', 'Plus', 'Gaming'];
        $items = ['Laptop', 'Headphones', 'Monitor', 'Keyboard', 'Mouse', 'Tablet', 'Smartwatch', 'Earbuds', 'Speaker', 'Backpack'];

        $now = now()->toDateTimeString();
        $chunks = ceil($count / $chunkSize);

        for ($c = 0; $c < $chunks; $c++) {
            $batch = [];
            $currentLimit = min($chunkSize, $count - ($c * $chunkSize));

            for ($i = 0; $i < $currentLimit; $i++) {
                $brand = $brands[array_rand($brands)];
                $adjective = $categories[array_rand($categories)];
                $item = $items[array_rand($items)];
                $modelCode = strtoupper(Str::random(4));

                $name = "{$brand} {$adjective} {$item} {$modelCode}";
                $price = round(mt_rand(1999, 199999) / 100, 2); // $19.99 to $1999.99
                $quantity = mt_rand(5, 500);

                $batch[] = [
                    'product_category_id' => $categoryIds[array_rand($categoryIds)],
                    'product_status_id' => $statusIds[array_rand($statusIds)],
                    'name' => $name,
                    'description' => "High performance {$name} featuring industry-leading specifications, durable construction, and energy-efficient design.",
                    'price' => $price,
                    'quantity' => $quantity,
                    'image' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('products')->insert($batch);
            $bar->advance($currentLimit);
        }

        $bar->finish();
        $this->newLine();

        $allProductIds = DB::table('products')->pluck('id')->toArray();

        // Assign to sections in bulk
        if (!empty($sectionIds) && !empty($allProductIds)) {
            $this->line("Assigning products to homepage display sections...");
            $pivotBatch = [];
            foreach (array_slice($allProductIds, 0, min(1000, count($allProductIds))) as $pid) {
                $pivotBatch[] = [
                    'product_id' => $pid,
                    'section_id' => $sectionIds[array_rand($sectionIds)],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            foreach (array_chunk($pivotBatch, 500) as $chunk) {
                DB::table('product_section')->insert($chunk);
            }
        }

        return $allProductIds;
    }

    private function seedOrders(int $count, array $userIds, array $productIds, int $chunkSize): void
    {
        $this->line("Seeding <info>{$count}</info> customer orders and line-item snapshots...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $statuses = ['delivered', 'shipped', 'processing', 'pending', 'cancelled'];
        $paymentStatuses = ['paid', 'paid', 'paid', 'unpaid', 'refunded'];
        $gateways = ['stripe', 'paymob', 'myfatoorah'];

        $now = now()->toDateTimeString();
        $chunks = ceil($count / $chunkSize);

        // Fetch sample product pricing snapshot
        $sampleProducts = DB::table('products')
            ->select('id', 'price')
            ->whereIn('id', array_slice($productIds, 0, min(2000, count($productIds))))
            ->get()
            ->keyBy('id')
            ->toArray();

        $sampleProductIds = array_keys($sampleProducts);

        for ($c = 0; $c < $chunks; $c++) {
            $currentLimit = min($chunkSize, $count - ($c * $chunkSize));

            for ($i = 0; $i < $currentLimit; $i++) {
                $uid = $userIds[array_rand($userIds)];
                $orderNumber = 'ORD-' . strtoupper(Str::random(10));
                $status = $statuses[array_rand($statuses)];
                $paymentStatus = ($status === 'delivered' || $status === 'shipped' || $status === 'processing')
                    ? 'paid'
                    : $paymentStatuses[array_rand($paymentStatuses)];

                $itemCount = mt_rand(1, 4);
                $totalAmount = 0.0;
                $orderItems = [];

                for ($it = 0; $it < $itemCount; $it++) {
                    $pid = $sampleProductIds[array_rand($sampleProductIds)];
                    $unitPrice = (float) ($sampleProducts[$pid]->price ?? 49.99);
                    $qty = mt_rand(1, 3);
                    $lineTotal = round($unitPrice * $qty, 2);
                    $totalAmount += $lineTotal;

                    $orderItems[] = [
                        'product_id' => $pid,
                        'quantity' => $qty,
                        'price' => $unitPrice,
                        'total' => $lineTotal,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                $orderId = DB::table('orders')->insertGetId([
                    'user_id' => $uid,
                    'order_number' => $orderNumber,
                    'status' => $status,
                    'payment_status' => $paymentStatus,
                    'total_amount' => $totalAmount,
                    'payment_method' => $gateways[array_rand($gateways)],
                    'shipping_address' => '123 E-Commerce Blvd, Tech District',
                    'billing_address' => '123 E-Commerce Blvd, Tech District',
                    'notes' => 'Bulk simulated benchmark order',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                foreach ($orderItems as &$item) {
                    $item['order_id'] = $orderId;
                }

                DB::table('order_items')->insert($orderItems);
            }

            $bar->advance($currentLimit);
        }

        $bar->finish();
        $this->newLine();
    }

    private function seedReviews(int $count, array $userIds, array $productIds, int $chunkSize): void
    {
        $this->line("Seeding <info>{$count}</info> product reviews & ratings...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $comments = [
            'Outstanding quality and extremely fast delivery. Exceeded all my expectations!',
            'Good product overall. Matches description and functions as expected.',
            'Five stars! The build quality is premium and battery life is impressive.',
            'Decent value for the price. Would recommend to colleagues and friends.',
            'Solid performance, sleek ergonomics, and dependable everyday reliability.',
            'Very impressed with customer support and warranty coverage. 10/10.',
        ];

        $now = now()->toDateTimeString();
        $chunks = ceil($count / $chunkSize);

        for ($c = 0; $c < $chunks; $c++) {
            $batch = [];
            $currentLimit = min($chunkSize, $count - ($c * $chunkSize));

            for ($i = 0; $i < $currentLimit; $i++) {
                $batch[] = [
                    'product_id' => $productIds[array_rand($productIds)],
                    'user_id' => $userIds[array_rand($userIds)],
                    'rating' => mt_rand(3, 5),
                    'comment' => $comments[array_rand($comments)],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('product_reviews')->insert($batch);
            $bar->advance($currentLimit);
        }

        $bar->finish();
        $this->newLine();
    }
}

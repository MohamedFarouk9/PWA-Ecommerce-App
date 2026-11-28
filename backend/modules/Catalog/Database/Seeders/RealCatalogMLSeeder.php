<?php

namespace Modules\Catalog\Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductStatus;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RealCatalogMLSeeder extends Seeder
{
    /**
     * Seeds 43 authentic, real-world products covering all 20 categories with full specs,
     * high-resolution images, and realistic multi-item co-purchase orders for ML training
     * (Content-Based TF-IDF + Collaborative Filtering Recommendation Engine).
     */
    public function run(): void
    {
        $this->command?->info('Seeding Real-World Multi-Category ML Catalog (43 products + specs + co-purchase orders)...');

        $publishedStatus = ProductStatus::firstOrCreate(['name' => 'published']);
        $productsByCode = [];

        foreach ($this->catalogData() as $item) {
            $category = ProductCategory::where('slug', Str::slug($item['category']))->first();
            if (!$category) {
                $category = ProductCategory::firstOrCreate(
                    ['slug' => Str::slug($item['category'])],
                    ['name' => $item['category'], 'is_active' => true, 'order' => 0]
                );
            }

            $coverImage = $item['images'][0];

            $product = Product::updateOrCreate(
                ['product_code' => $item['product_code']],
                [
                    'name'                => $item['name'],
                    'brand'               => $item['brand'],
                    'sku'                 => 'SKU-' . strtoupper(Str::slug($item['brand'])) . '-' . $item['product_code'],
                    'short_description'   => $item['short_description'],
                    'description'         => $item['description'],
                    'price'               => $item['price'],
                    'special_price'       => $item['special_price'] ?? null,
                    'quantity'            => $item['quantity'] ?? rand(15, 80),
                    'product_category_id' => $category->id,
                    'product_status_id'   => $publishedStatus->id,
                    'color'               => $item['color'] ?? ['Default'],
                    'size'                => $item['size'] ?? ['Standard'],
                    'image'               => $coverImage,
                    'view_count'          => rand(200, 4500),
                ]
            );

            $productsByCode[$item['product_code']] = $product;

            // Attach sections
            foreach ($item['sections'] ?? ['featured'] as $sectionName) {
                $section = Section::firstOrCreate(
                    ['name' => $sectionName],
                    ['label' => Str::title(str_replace('_', ' ', $sectionName))]
                );
                $product->sections()->syncWithoutDetaching([$section->id]);
            }

            // Attach gallery images
            $product->images()->delete();
            foreach ($item['images'] as $idx => $imgUrl) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path'       => $imgUrl,
                    'position'   => $idx + 1,
                    'is_cover'   => $idx === 0,
                ]);
            }

            // Attach structured specifications
            foreach ($item['specifications'] as $pos => $spec) {
                $attribute = ProductAttribute::firstOrCreate(['name' => $spec['attribute']]);
                $product->specifications()->syncWithoutDetaching([
                    $attribute->id => [
                        'value'    => (string) $spec['value'],
                        'unit'     => $spec['unit'] ?? null,
                        'position' => $pos + 1,
                    ],
                ]);
            }
        }

        // Seed realistic co-purchase baskets for Collaborative Filtering ML training
        $this->seedRealisticCoPurchaseOrders($productsByCode);

        $this->command?->info('✓ Seeded ' . count($productsByCode) . ' real products across all categories + ML co-purchase baskets!');
    }

    /**
     * Creates realistic multi-product customer baskets so Collaborative Filtering learns
     * genuine cross-category & same-ecosystem purchase patterns.
     */
    private function seedRealisticCoPurchaseOrders(array $productsByCode): void
    {
        $users = User::where('role', '!=', 'admin')->take(15)->get();
        if ($users->isEmpty()) {
            $users = User::take(5)->get();
        }
        if ($users->isEmpty()) {
            return;
        }

        // Realistic shopping bundles (each bundle repeats 4-8 times across users for strong ML signal)
        $bundles = [
            // Apple & Tech Pro Ecosystem
            ['IPH15PM-TI', 'APP2-USBC', 'MBP14-M3P'],
            ['GS24U-TI', 'SONY-XM5', 'DELL-XPS15'],
            ['MBP14-M3P', 'SONY-XM5', 'OREILLY-DDIA'],
            // Gaming Setup
            ['PS5-SLIM', 'DUALSENSE-WHT', 'ASUS-OLED27'],
            ['SWITCH-OLED', 'SONY-XM5'],
            // Runner & Fitness Bundle
            ['NIKE-ALPHA3', 'GARMIN-265', 'UA-TECH-TEE'],
            ['ADIDAS-UB23', 'BOWFLEX-552', 'UA-TECH-TEE'],
            // Home Office & Productivity
            ['HERMAN-AERON', 'MBP14-M3P', 'CLEAN-ARCH'],
            ['IKEA-MALM-BED', 'BROOKLINEN-LUX', 'PHILIPS-HUE-STR'],
            // Home Chef Bundle
            ['KITCHENAID-ART', 'PHILIPS-AIRFRY', 'DYSON-V15'],
            // Fashion Outfits
            ['LEVIS-511-IND', 'NIKE-ALPHA3', 'RL-OXFORD-WHT'],
            ['ZARA-BLAZER-BLK', 'LULU-ALIGN-BLK', 'ADIDAS-UB23'],
            // Outdoor & Adventure
            ['COLEMAN-TENT4', 'YETI-TUNDRA45', 'GARMIN-265'],
            ['SPEEDO-VENG', 'INTEX-KAYAK'],
            // Book Lovers
            ['DUNE-HC', 'ATOMIC-HABITS', 'OREILLY-DDIA'],
            ['HARRY-POTTER-BOX', 'KIDS-NIKE-AIR'],
        ];

        $createdOrders = 0;
        foreach ($bundles as $bundleIndex => $codes) {
            $repeatCount = 5;
            for ($r = 0; $r < $repeatCount; $r++) {
                $orderNumber = sprintf('ORD-ML-%02d-%02d', $bundleIndex + 1, $r + 1);
                if (Order::where('order_number', $orderNumber)->exists()) {
                    continue;
                }

                $user = $users[($bundleIndex + $r) % $users->count()];
                $itemsToInsert = [];
                $total = 0.0;

                foreach ($codes as $code) {
                    if (!isset($productsByCode[$code])) {
                        continue;
                    }
                    $prod = $productsByCode[$code];
                    $unitPrice = ($prod->special_price && $prod->special_price < $prod->price)
                        ? (float) $prod->special_price
                        : (float) $prod->price;
                    $total += $unitPrice;
                    $itemsToInsert[] = [
                        'product_id' => $prod->id,
                        'quantity'   => 1,
                        'price'      => $unitPrice,
                        'total'      => $unitPrice,
                        'created_at' => now()->subDays(rand(1, 60)),
                        'updated_at' => now(),
                    ];
                }

                if (empty($itemsToInsert)) {
                    continue;
                }

                $order = Order::create([
                    'user_id'          => $user->id,
                    'order_number'     => $orderNumber,
                    'status'           => 'delivered',
                    'payment_status'   => 'paid',
                    'total_amount'     => round($total, 2),
                    'payment_method'   => ['stripe', 'paymob', 'myfatoorah'][($bundleIndex + $r) % 3],
                    'shipping_address' => '123 Commerce Ave, Tech City',
                    'billing_address'  => '123 Commerce Ave, Tech City',
                    'created_at'       => now()->subDays(rand(1, 60)),
                ]);

                foreach ($itemsToInsert as &$row) {
                    $row['order_id'] = $order->id;
                }
                OrderItem::insert($itemsToInsert);
                $createdOrders++;
            }
        }

        $this->command?->info("  ✓ Created {$createdOrders} realistic co-purchase orders for Collaborative Filtering.");
    }

    /**
     * 40 real-world products covering every child category in ProductCategorySeeder.
     */
    private function catalogData(): array
    {
        return [
            // ======================== 1. SMARTPHONES ========================
            [
                'name' => 'Apple iPhone 15 Pro Max 256GB - Natural Titanium',
                'brand' => 'Apple',
                'product_code' => 'IPH15PM-TI',
                'category' => 'Smartphones',
                'price' => 1199.00,
                'special_price' => 1129.00,
                'color' => ['Natural Titanium', 'Blue Titanium', 'Black Titanium'],
                'size' => ['256GB', '512GB', '1TB'],
                'sections' => ['featured', 'best_seller'],
                'short_description' => 'Aerospace-grade titanium smartphone with A17 Pro chip, 48MP 5x telephoto camera, and USB-C.',
                'description' => '<p>Forged in titanium with the groundbreaking A17 Pro chip, customizable Action button, and the most powerful iPhone camera system featuring 5x optical zoom.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Display', 'value' => '6.7 Super Retina XDR OLED 120Hz', 'unit' => 'inches'],
                    ['attribute' => 'Processor', 'value' => 'Apple A17 Pro (3nm)', 'unit' => null],
                    ['attribute' => 'Main Camera', 'value' => '48MP + 12MP + 12MP (5x Telephoto)', 'unit' => null],
                    ['attribute' => 'Battery', 'value' => '4441', 'unit' => 'mAh'],
                    ['attribute' => 'Connectivity', 'value' => '5G, Wi-Fi 6E, USB-C 3', 'unit' => null],
                ],
            ],
            [
                'name' => 'Samsung Galaxy S24 Ultra 5G 512GB - Titanium Gray',
                'brand' => 'Samsung',
                'product_code' => 'GS24U-TI',
                'category' => 'Smartphones',
                'price' => 1299.99,
                'special_price' => 1199.99,
                'color' => ['Titanium Gray', 'Titanium Black', 'Titanium Violet'],
                'size' => ['256GB', '512GB', '1TB'],
                'sections' => ['featured', 'new_arrival'],
                'short_description' => 'Flagship Android smartphone with Galaxy AI, 200MP quad camera, Snapdragon 8 Gen 3, and S Pen.',
                'description' => '<p>Unleash Galaxy AI with Circle to Search, Live Translate, 200MP pro-grade zoom, titanium armor frame, and integrated S Pen stylus.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1598327105666-5b89351aff97?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Display', 'value' => '6.8 Dynamic AMOLED 2X 120Hz', 'unit' => 'inches'],
                    ['attribute' => 'Processor', 'value' => 'Snapdragon 8 Gen 3 for Galaxy', 'unit' => null],
                    ['attribute' => 'Main Camera', 'value' => '200MP Quad Telephoto', 'unit' => null],
                    ['attribute' => 'RAM', 'value' => '12', 'unit' => 'GB'],
                    ['attribute' => 'Battery', 'value' => '5000', 'unit' => 'mAh'],
                ],
            ],

            // ======================== 2. LAPTOPS ========================
            [
                'name' => 'Apple MacBook Pro 14" M3 Pro 18GB/512GB - Space Black',
                'brand' => 'Apple',
                'product_code' => 'MBP14-M3P',
                'category' => 'Laptops',
                'price' => 1999.00,
                'special_price' => 1849.00,
                'color' => ['Space Black', 'Silver'],
                'size' => ['14-inch'],
                'sections' => ['featured', 'best_seller'],
                'short_description' => 'Pro workstation laptop with Apple M3 Pro chip, Liquid Retina XDR display, and 18-hour battery.',
                'description' => '<p>Built for developers, video editors, and creators. Features 11-core CPU, 14-core GPU, 18GB unified memory, and 120Hz ProMotion mini-LED display.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Processor', 'value' => 'Apple M3 Pro (11-Core CPU / 14-Core GPU)', 'unit' => null],
                    ['attribute' => 'Memory', 'value' => '18', 'unit' => 'GB Unified'],
                    ['attribute' => 'Storage', 'value' => '512', 'unit' => 'GB SSD'],
                    ['attribute' => 'Display', 'value' => '14.2 Liquid Retina XDR 120Hz', 'unit' => 'inches'],
                    ['attribute' => 'Battery Life', 'value' => '18', 'unit' => 'hours'],
                ],
            ],
            [
                'name' => 'Dell XPS 15 9530 OLED Intel i9 RTX 4070 - Platinum Silver',
                'brand' => 'Dell',
                'product_code' => 'DELL-XPS15',
                'category' => 'Laptops',
                'price' => 2499.00,
                'special_price' => 2249.00,
                'color' => ['Platinum Silver'],
                'size' => ['15.6-inch'],
                'sections' => ['featured'],
                'short_description' => 'Creator laptop with 3.5K OLED touchscreen, Intel Core i9-13900H, 32GB DDR5, and RTX 4070 GPU.',
                'description' => '<p>CNC-machined aluminum chassis with carbon fiber palm rest, stunning 3.5K OLED InfinityEdge display, and dedicated NVIDIA RTX 4070 graphics.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Processor', 'value' => 'Intel Core i9-13900H', 'unit' => null],
                    ['attribute' => 'Graphics', 'value' => 'NVIDIA GeForce RTX 4070 8GB', 'unit' => null],
                    ['attribute' => 'Memory', 'value' => '32', 'unit' => 'GB DDR5'],
                    ['attribute' => 'Storage', 'value' => '1', 'unit' => 'TB NVMe SSD'],
                    ['attribute' => 'Display', 'value' => '15.6 3.5K OLED Touch', 'unit' => 'inches'],
                ],
            ],

            // ======================== 3. AUDIO ========================
            [
                'name' => 'Sony WH-1000XM5 Wireless Noise Canceling Headphones',
                'brand' => 'Sony',
                'product_code' => 'SONY-XM5',
                'category' => 'Audio',
                'price' => 399.99,
                'special_price' => 348.00,
                'color' => ['Black', 'Silver', 'Midnight Blue'],
                'size' => ['One Size'],
                'sections' => ['featured', 'best_seller'],
                'short_description' => 'Industry-leading ANC over-ear Bluetooth headphones with 8 microphones and 30-hour battery.',
                'description' => '<p>Powered by two processors controlling 8 microphones for unprecedented noise cancellation, crystal-clear hands-free calling, and LDAC Hi-Res Audio.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1546435770-a3e426bf472b?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Driver Unit', 'value' => '30', 'unit' => 'mm'],
                    ['attribute' => 'Battery Life', 'value' => '30', 'unit' => 'hours (ANC On)'],
                    ['attribute' => 'Bluetooth', 'value' => '5.2 Multipoint + LDAC', 'unit' => null],
                    ['attribute' => 'Weight', 'value' => '250', 'unit' => 'g'],
                ],
            ],
            [
                'name' => 'Apple AirPods Pro (2nd Gen) USB-C MagSafe',
                'brand' => 'Apple',
                'product_code' => 'APP2-USBC',
                'category' => 'Audio',
                'price' => 249.00,
                'special_price' => 199.00,
                'color' => ['White'],
                'size' => ['One Size'],
                'sections' => ['best_seller'],
                'short_description' => 'Active Noise Cancellation earbuds with Apple H2 chip, Adaptive Audio, and IP54 dust/water resistance.',
                'description' => '<p>Re-engineered with the H2 chip for up to 2x more Active Noise Cancellation, Conversation Awareness, Personalized Spatial Audio, and USB-C MagSafe Charging Case.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1588423771073-b8903fbb85b5?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Chip', 'value' => 'Apple H2 Headphone Chip', 'unit' => null],
                    ['attribute' => 'Battery Life', 'value' => '6h (30h with Case)', 'unit' => null],
                    ['attribute' => 'Water Resistance', 'value' => 'IP54', 'unit' => null],
                    ['attribute' => 'Charging', 'value' => 'USB-C / MagSafe', 'unit' => null],
                ],
            ],

            // ======================== 4. GAMING ========================
            [
                'name' => 'Sony PlayStation 5 Slim Console (1TB Disc Edition)',
                'brand' => 'PlayStation',
                'product_code' => 'PS5-SLIM',
                'category' => 'Gaming',
                'price' => 499.99,
                'special_price' => 469.99,
                'color' => ['White'],
                'size' => ['1TB'],
                'sections' => ['featured', 'best_seller'],
                'short_description' => 'Next-gen 4K 120Hz gaming console with ultra-high-speed 1TB SSD, ray tracing, and DualSense controller.',
                'description' => '<p>Experience lightning-fast loading with an ultra-high-speed 1TB SSD, deeper immersion with haptic feedback, adaptive triggers, and Tempest 3D AudioTech.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1606813907291-d86efa9b94db?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1607853202273-797f1c22a38e?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Storage', 'value' => '1', 'unit' => 'TB Custom NVMe SSD'],
                    ['attribute' => 'Resolution', 'value' => 'Up to 4K 120Hz HDR', 'unit' => null],
                    ['attribute' => 'Optical Drive', 'value' => '4K UHD Blu-ray', 'unit' => null],
                    ['attribute' => 'Controller', 'value' => 'DualSense Wireless Included', 'unit' => null],
                ],
            ],
            [
                'name' => 'ASUS ROG Swift 27" 240Hz OLED Gaming Monitor (PG27AQDM)',
                'brand' => 'ASUS',
                'product_code' => 'ASUS-OLED27',
                'category' => 'Gaming',
                'price' => 899.00,
                'special_price' => 799.00,
                'color' => ['Black'],
                'size' => ['27-inch'],
                'sections' => ['new_arrival'],
                'short_description' => '27-inch 1440p OLED esports monitor with 240Hz refresh rate, 0.03ms GTG response, and G-SYNC.',
                'description' => '<p>Anti-glare micro-texture OLED panel delivering infinite contrast, 99% DCI-P3 color accuracy, and custom heatsink thermal management.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Panel', 'value' => '27" QHD OLED (2560x1440)', 'unit' => null],
                    ['attribute' => 'Refresh Rate', 'value' => '240', 'unit' => 'Hz'],
                    ['attribute' => 'Response Time', 'value' => '0.03', 'unit' => 'ms'],
                    ['attribute' => 'Sync', 'value' => 'NVIDIA G-SYNC Compatible', 'unit' => null],
                ],
            ],
            [
                'name' => 'PlayStation DualSense Wireless Controller - Cosmic White',
                'brand' => 'PlayStation',
                'product_code' => 'DUALSENSE-WHT',
                'category' => 'Gaming',
                'price' => 69.99,
                'special_price' => 59.99,
                'color' => ['White', 'Midnight Black', 'Cobalt Blue'],
                'size' => ['Standard'],
                'sections' => ['best_seller'],
                'short_description' => 'Official PS5 & PC wireless controller with haptic feedback, adaptive triggers, and built-in microphone.',
                'description' => '<p>Discover a deeper, highly immersive gaming experience with dynamic adaptive triggers and precision dual actuators.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1592840496694-26d035b52b48?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Connectivity', 'value' => 'Bluetooth 5.1 / USB-C', 'unit' => null],
                    ['attribute' => 'Compatibility', 'value' => 'PS5, PC, Mac, Mobile', 'unit' => null],
                    ['attribute' => 'Battery', 'value' => '1560', 'unit' => 'mAh'],
                ],
            ],
            [
                'name' => 'Nintendo Switch OLED Model - White Joy-Con',
                'brand' => 'Nintendo',
                'product_code' => 'SWITCH-OLED',
                'category' => 'Gaming',
                'price' => 349.99,
                'special_price' => 329.99,
                'color' => ['White', 'Neon Red/Blue'],
                'size' => ['64GB'],
                'sections' => ['featured'],
                'short_description' => 'Hybrid handheld and TV console featuring a vibrant 7-inch OLED screen and wide adjustable stand.',
                'description' => '<p>Play at home on the TV or on-the-go with a vibrant 7-inch OLED display, enhanced audio, and 64GB internal storage.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1578303512597-81e6cc155b3e?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Display', 'value' => '7.0 OLED Multi-Touch', 'unit' => 'inches'],
                    ['attribute' => 'Storage', 'value' => '64', 'unit' => 'GB (microSD expandable)'],
                    ['attribute' => 'Battery Life', 'value' => '4.5 - 9', 'unit' => 'hours'],
                ],
            ],

            // ======================== 5. MEN'S FASHION ========================
            [
                'name' => 'Levi\'s 511 Slim Fit Stretch Denim Jeans - Dark Indigo',
                'brand' => 'Levi\'s',
                'product_code' => 'LEVIS-511-IND',
                'category' => "Men's Fashion",
                'price' => 69.50,
                'special_price' => 54.99,
                'color' => ['Dark Indigo', 'Black', 'Light Wash'],
                'size' => ['30x30', '32x32', '34x32', '36x32'],
                'sections' => ['best_seller'],
                'short_description' => 'Modern slim-fit men\'s jeans engineered with Levi\'s Flex stretch denim for all-day comfort.',
                'description' => '<p>Sits below the waist with a slim cut from hip to ankle. Crafted with sustainably sourced cotton and advanced stretch technology.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1542272604-787c3835535d?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Material', 'value' => '99% Cotton, 1% Elastane', 'unit' => null],
                    ['attribute' => 'Fit', 'value' => 'Slim Fit', 'unit' => null],
                    ['attribute' => 'Care', 'value' => 'Machine Wash Cold', 'unit' => null],
                ],
            ],
            [
                'name' => 'Polo Ralph Lauren Classic Fit Oxford Button-Down Shirt',
                'brand' => 'Ralph Lauren',
                'product_code' => 'RL-OXFORD-WHT',
                'category' => "Men's Fashion",
                'price' => 115.00,
                'special_price' => 95.00,
                'color' => ['White', 'Sky Blue', 'Navy'],
                'size' => ['S', 'M', 'L', 'XL', 'XXL'],
                'sections' => ['featured'],
                'short_description' => 'Garment-washed 100% cotton Oxford shirt with signature embroidered Pony logo.',
                'description' => '<p>Timeless smart-casual staple cut for a relaxed silhouette with a buttoned placket, split back yoke, and breathable woven cotton.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Fabric', 'value' => '100% Combed Cotton Oxford', 'unit' => null],
                    ['attribute' => 'Collar', 'value' => 'Button-Down Point Collar', 'unit' => null],
                    ['attribute' => 'Fit', 'value' => 'Classic Fit', 'unit' => null],
                ],
            ],

            // ======================== 6. WOMEN'S FASHION ========================
            [
                'name' => 'Zara Tailored Double-Breasted Wool Blend Blazer - Black',
                'brand' => 'Zara',
                'product_code' => 'ZARA-BLAZER-BLK',
                'category' => "Women's Fashion",
                'price' => 129.00,
                'special_price' => 99.00,
                'color' => ['Black', 'Camel', 'Ivory'],
                'size' => ['XS', 'S', 'M', 'L'],
                'sections' => ['featured', 'new_arrival'],
                'short_description' => 'Structured double-breasted women\'s blazer with lapel collar, gold embossed buttons, and full lining.',
                'description' => '<p>Effortlessly transitions from office to evening wear. Features pronounced shoulders, front flap pockets, and breathable viscose-wool blend.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Outer Shell', 'value' => '54% Polyester, 44% Wool, 2% Elastane', 'unit' => null],
                    ['attribute' => 'Closure', 'value' => 'Double-Breasted Gold Buttons', 'unit' => null],
                    ['attribute' => 'Lining', 'value' => '100% Viscose', 'unit' => null],
                ],
            ],
            [
                'name' => 'Lululemon Align High-Rise 25" Yoga Leggings - Black',
                'brand' => 'Lululemon',
                'product_code' => 'LULU-ALIGN-BLK',
                'category' => "Women's Fashion",
                'price' => 98.00,
                'special_price' => null,
                'color' => ['Black', 'True Navy', 'Espresso'],
                'size' => ['XS', 'S', 'M', 'L'],
                'sections' => ['best_seller'],
                'short_description' => 'Buttery-soft Nulu fabric high-rise leggings with four-way stretch and hidden waistband pocket.',
                'description' => '<p>Designed for yoga and everyday comfort with weightless, sweat-wicking Nulu fabric that moves seamlessly with you.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1506629082955-511b1aa562c8?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Fabric', 'value' => '81% Nylon, 19% Lycra Elastane (Nulu)', 'unit' => null],
                    ['attribute' => 'Inseam', 'value' => '25', 'unit' => 'inches'],
                    ['attribute' => 'Rise', 'value' => 'High-Rise', 'unit' => null],
                ],
            ],

            // ======================== 7. KIDS' FASHION ========================
            [
                'name' => 'Nike Kids Air Max 270 Grade School Sneakers',
                'brand' => 'Nike',
                'product_code' => 'KIDS-NIKE-AIR',
                'category' => "Kids' Fashion",
                'price' => 110.00,
                'special_price' => 89.99,
                'color' => ['Black/White', 'Crimson/White'],
                'size' => ['3Y', '4Y', '5Y', '6Y'],
                'sections' => ['new_arrival'],
                'short_description' => 'Lightweight kids sneakers featuring Nike\'s signature Max Air heel unit and breathable mesh upper.',
                'description' => '<p>All-day playground comfort with a stretchy inner sleeve for a sock-like fit and durable rubber outsole traction.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1514989940723-e8e51635b782?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Cushioning', 'value' => '270-degree Max Air Heel', 'unit' => null],
                    ['attribute' => 'Upper', 'value' => 'Breathable Knit Mesh', 'unit' => null],
                    ['attribute' => 'Age Group', 'value' => 'Kids (7–12 Years)', 'unit' => null],
                ],
            ],
            [
                'name' => 'Gap Kids Organic Cotton Fleece Hoodie Set',
                'brand' => 'Gap',
                'product_code' => 'GAP-KIDS-HOOD',
                'category' => "Kids' Fashion",
                'price' => 49.99,
                'special_price' => 39.99,
                'color' => ['Heather Gray', 'Navy', 'Rose'],
                'size' => ['4-5Y', '6-7Y', '8-9Y', '10-11Y'],
                'sections' => ['featured'],
                'short_description' => 'Ultra-soft brushed organic cotton fleece hoodie and matching joggers set for everyday wear.',
                'description' => '<p>Cozy two-piece set made from 100% organic cotton with reinforced knees, kangaroo pocket, and elastic drawstring waist.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Material', 'value' => '100% Organic Cotton Fleece', 'unit' => null],
                    ['attribute' => 'Pieces', 'value' => '2 (Hoodie + Joggers)', 'unit' => null],
                ],
            ],

            // ======================== 8. FOOTWEAR ========================
            [
                'name' => 'Nike Air Zoom Alphafly NEXT% 3 Marathon Running Shoes',
                'brand' => 'Nike',
                'product_code' => 'NIKE-ALPHA3',
                'category' => 'Footwear',
                'price' => 285.00,
                'special_price' => 259.00,
                'color' => ['Bright Crimson', 'Volt/Black'],
                'size' => ['US 8', 'US 9', 'US 10', 'US 11', 'US 12'],
                'sections' => ['featured', 'new_arrival'],
                'short_description' => 'Elite carbon-plated marathon racing shoe with dual Air Zoom pods and full-length ZoomX foam.',
                'description' => '<p>Engineered for marathon speed with a wider carbon fiber Flyplate, AtomKnit 3.0 breathable upper, and maximum energy return.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Midsole', 'value' => 'ZoomX Foam + Dual Air Zoom', 'unit' => null],
                    ['attribute' => 'Plate', 'value' => 'Full-Length Carbon Fiber Flyplate', 'unit' => null],
                    ['attribute' => 'Weight', 'value' => '215', 'unit' => 'g'],
                    ['attribute' => 'Drop', 'value' => '8', 'unit' => 'mm'],
                ],
            ],
            [
                'name' => 'Adidas Ultraboost Light 23 Running Shoes - Core Black',
                'brand' => 'Adidas',
                'product_code' => 'ADIDAS-UB23',
                'category' => 'Footwear',
                'price' => 190.00,
                'special_price' => 149.00,
                'color' => ['Core Black', 'Cloud White'],
                'size' => ['US 8', 'US 9', 'US 10', 'US 11'],
                'sections' => ['best_seller'],
                'short_description' => '30% lighter BOOST cushioning daily trainer with Primeknit+ upper and Continental rubber grip.',
                'description' => '<p>Responsive Light BOOST midsole paired with Linear Energy Push system and all-weather Continental rubber outsole.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Midsole', 'value' => 'Light BOOST', 'unit' => null],
                    ['attribute' => 'Outsole', 'value' => 'Continental Rubber', 'unit' => null],
                    ['attribute' => 'Upper', 'value' => 'Primeknit+ Recycled Yarn', 'unit' => null],
                ],
            ],

            // ======================== 9. FURNITURE ========================
            [
                'name' => 'Herman Miller Aeron Ergonomic Office Chair (Size B)',
                'brand' => 'Herman Miller',
                'product_code' => 'HERMAN-AERON',
                'category' => 'Furniture',
                'price' => 1495.00,
                'special_price' => 1349.00,
                'color' => ['Graphite', 'Carbon'],
                'size' => ['Size B (Medium)'],
                'sections' => ['featured'],
                'short_description' => 'Iconic ergonomic mesh chair with PostureFit SL lumbar support and 12-year warranty.',
                'description' => '<p>8Z Pellicle elastomeric suspension provides 8 zones of varying tension for breathability and pressure-free all-day coding and desk work.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1580480055273-228ff5388ef8?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Lumbar Support', 'value' => 'Adjustable PostureFit SL', 'unit' => null],
                    ['attribute' => 'Mesh', 'value' => '8Z Pellicle Breathable Suspension', 'unit' => null],
                    ['attribute' => 'Weight Capacity', 'value' => '159', 'unit' => 'kg'],
                    ['attribute' => 'Warranty', 'value' => '12', 'unit' => 'years'],
                ],
            ],
            [
                'name' => 'IKEA MALM Queen Storage Bed Frame - Oak Veneer',
                'brand' => 'IKEA',
                'product_code' => 'IKEA-MALM-BED',
                'category' => 'Furniture',
                'price' => 549.00,
                'special_price' => 499.00,
                'color' => ['White Stained Oak', 'Black-Brown'],
                'size' => ['Queen', 'King'],
                'sections' => ['best_seller'],
                'short_description' => 'Minimalist Scandinavian queen bed frame with 4 large under-bed rolling storage drawers.',
                'description' => '<p>Clean modern lines with real wood veneer finish, adjustable bed sides for varying mattress thicknesses, and generous hidden storage.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Dimensions', 'value' => '211 x 168 x 100', 'unit' => 'cm'],
                    ['attribute' => 'Storage', 'value' => '4 Under-bed Rolling Drawers', 'unit' => null],
                    ['attribute' => 'Material', 'value' => 'Real Oak Veneer & Steel', 'unit' => null],
                ],
            ],

            // ======================== 10. KITCHEN APPLIANCES ========================
            [
                'name' => 'KitchenAid Artisan Series 5-Quart Tilt-Head Stand Mixer',
                'brand' => 'KitchenAid',
                'product_code' => 'KITCHENAID-ART',
                'category' => 'Kitchen Appliances',
                'price' => 449.99,
                'special_price' => 379.99,
                'color' => ['Empire Red', 'Onyx Black', 'Pistachio'],
                'size' => ['5-Quart'],
                'sections' => ['featured', 'best_seller'],
                'short_description' => '10-speed all-metal stand mixer with 5-quart stainless steel bowl, dough hook, and 59-point planetary mixing.',
                'description' => '<p>Durable die-cast metal construction with 10+ optional hub attachments for pasta making, meat grinding, and bakery-quality doughs.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Bowl Capacity', 'value' => '4.8', 'unit' => 'L (5-Quart)'],
                    ['attribute' => 'Speeds', 'value' => '10', 'unit' => null],
                    ['attribute' => 'Power', 'value' => '325', 'unit' => 'Watts'],
                    ['attribute' => 'Construction', 'value' => 'Die-Cast Zinc Metal', 'unit' => null],
                ],
            ],
            [
                'name' => 'Philips Premium Airfryer XXL 7.3L with Rapid Air Technology',
                'brand' => 'Philips',
                'product_code' => 'PHILIPS-AIRFRY',
                'category' => 'Kitchen Appliances',
                'price' => 299.99,
                'special_price' => 249.99,
                'color' => ['Black'],
                'size' => ['7.3L XXL'],
                'sections' => ['best_seller'],
                'short_description' => 'Family-size digital air fryer with Fat Removal technology and 7 one-touch cooking presets.',
                'description' => '<p>Fry, bake, grill, and roast with up to 90% less fat using Philips patented starfish Rapid Air swirl heating.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1584269600464-37b1b58a9fe7?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Capacity', 'value' => '7.3', 'unit' => 'Liters (6 Portions)'],
                    ['attribute' => 'Power', 'value' => '2225', 'unit' => 'Watts'],
                    ['attribute' => 'Dishwasher Safe', 'value' => 'Yes (QuickClean Basket)', 'unit' => null],
                ],
            ],
            [
                'name' => 'Dyson V15 Detect Absolute Cordless Stick Vacuum',
                'brand' => 'Dyson',
                'product_code' => 'DYSON-V15',
                'category' => 'Kitchen Appliances',
                'price' => 749.99,
                'special_price' => 649.99,
                'color' => ['Yellow/Nickel'],
                'size' => ['Standard'],
                'sections' => ['featured'],
                'short_description' => 'Intelligent laser-illuminated cordless vacuum with 230AW suction, piezo dust counter, and HEPA filtration.',
                'description' => '<p>Fluffy Optic cleaner head reveals invisible microscopic dust on hard floors while automatically adapting suction power in real time.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1558317374-067fb5f30001?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Suction Power', 'value' => '230', 'unit' => 'AW'],
                    ['attribute' => 'Runtime', 'value' => '60', 'unit' => 'minutes'],
                    ['attribute' => 'Filtration', 'value' => 'Whole-Machine HEPA', 'unit' => null],
                ],
            ],

            // ======================== 11. HOME DECOR ========================
            [
                'name' => 'Philips Hue White & Color Ambiance Smart LED Lightstrip 2m',
                'brand' => 'Philips Hue',
                'product_code' => 'PHILIPS-HUE-STR',
                'category' => 'Home Decor',
                'price' => 89.99,
                'special_price' => 74.99,
                'color' => ['RGB 16M Colors'],
                'size' => ['2 Meters'],
                'sections' => ['new_arrival'],
                'short_description' => 'Flexible smart ambient LED strip with 16 million colors, Matter support, and Alexa/HomeKit control.',
                'description' => '<p>Shape, cut, and extend smart lighting behind desks, TVs, and shelves with 1,700 lumens of rich dynamic color scenes.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Brightness', 'value' => '1700', 'unit' => 'Lumens'],
                    ['attribute' => 'Connectivity', 'value' => 'Bluetooth, Zigbee, Matter', 'unit' => null],
                    ['attribute' => 'Lifespan', 'value' => '25,000', 'unit' => 'hours'],
                ],
            ],
            [
                'name' => 'West Elm Mid-Century Arched Brass Floor Mirror 65"',
                'brand' => 'West Elm',
                'product_code' => 'WESTELM-MIRROR',
                'category' => 'Home Decor',
                'price' => 399.00,
                'special_price' => 349.00,
                'color' => ['Antique Brass', 'Matte Black'],
                'size' => ['65 x 24 in'],
                'sections' => ['featured'],
                'short_description' => 'Full-length modern arched standing mirror framed in brushed antique brass metal.',
                'description' => '<p>Elevates bedrooms and entryways with distortion-free shatterproof glass and both leaning easel and wall-mount hardware.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1618220179428-22790b461013?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Dimensions', 'value' => '165 x 61', 'unit' => 'cm'],
                    ['attribute' => 'Frame', 'value' => 'Brushed Aluminum Brass Finish', 'unit' => null],
                ],
            ],

            // ======================== 12. BEDDING ========================
            [
                'name' => 'Brooklinen Luxe Core 480-Thread-Count Sateen Sheet Set',
                'brand' => 'Brooklinen',
                'product_code' => 'BROOKLINEN-LUX',
                'category' => 'Bedding',
                'price' => 189.00,
                'special_price' => 159.00,
                'color' => ['White', 'Cream', 'Graphite'],
                'size' => ['Queen', 'King'],
                'sections' => ['best_seller'],
                'short_description' => 'OEKO-TEX certified 100% long-staple cotton sateen sheets with buttery luminous hotel finish.',
                'description' => '<p>Includes 1 fitted sheet, 1 flat sheet, and 2 pillowcases woven with a 480 thread count for year-round softness and breathability.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Material', 'value' => '100% Long-Staple Cotton Sateen', 'unit' => null],
                    ['attribute' => 'Thread Count', 'value' => '480', 'unit' => 'TC'],
                    ['attribute' => 'Certification', 'value' => 'OEKO-TEX Standard 100', 'unit' => null],
                ],
            ],
            [
                'name' => 'Tempur-Pedic TEMPUR-Cloud Cooling Memory Foam Pillow',
                'brand' => 'Tempur-Pedic',
                'product_code' => 'TEMPUR-PILLOW',
                'category' => 'Bedding',
                'price' => 119.00,
                'special_price' => 94.00,
                'color' => ['White'],
                'size' => ['Queen', 'King'],
                'sections' => ['featured'],
                'short_description' => 'Adaptive pressure-relieving memory foam pillow with removable machine-washable cooling cover.',
                'description' => '<p>Engineered to contour precisely to your head, neck, and shoulders for back, side, and stomach sleepers.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1584100936595-c0654b55a2e2?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Core', 'value' => 'Extra-Soft TEMPUR Memory Foam', 'unit' => null],
                    ['attribute' => 'Cover', 'value' => 'Washable Cooling Knit', 'unit' => null],
                ],
            ],

            // ======================== 13. FICTION ========================
            [
                'name' => 'Dune (Deluxe Hardcover Collector\'s Edition) by Frank Herbert',
                'brand' => 'Penguin Classics',
                'product_code' => 'DUNE-HC',
                'category' => 'Fiction',
                'price' => 45.00,
                'special_price' => 34.99,
                'color' => ['Blue Stained Edges'],
                'size' => ['Hardcover'],
                'sections' => ['best_seller'],
                'short_description' => 'Deluxe collector\'s hardcover of the greatest sci-fi epic ever written with metallic foil stamping.',
                'description' => '<p>Set on the desert planet Arrakis, Dune tells the unforgettable story of Paul Atreides, interstellar politics, ecology, and destiny.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Author', 'value' => 'Frank Herbert', 'unit' => null],
                    ['attribute' => 'Pages', 'value' => '688', 'unit' => 'pages'],
                    ['attribute' => 'Format', 'value' => 'Deluxe Hardcover', 'unit' => null],
                ],
            ],
            [
                'name' => 'Project Hail Mary by Andy Weir (Hardcover)',
                'brand' => 'Ballantine Books',
                'product_code' => 'HAIL-MARY-HC',
                'category' => 'Fiction',
                'price' => 28.99,
                'special_price' => 21.99,
                'color' => ['Standard'],
                'size' => ['Hardcover'],
                'sections' => ['featured'],
                'short_description' => 'Bestselling hard sci-fi thriller from the author of The Martian about a lone astronaut saving Earth.',
                'description' => '<p>Ryland Grace wakes up millions of miles from home with no memory and must rely on science and an unexpected ally to prevent extinction.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Author', 'value' => 'Andy Weir', 'unit' => null],
                    ['attribute' => 'Pages', 'value' => '496', 'unit' => 'pages'],
                ],
            ],

            // ======================== 14. NON-FICTION ========================
            [
                'name' => 'Atomic Habits by James Clear (Hardcover)',
                'brand' => 'Avery Publishing',
                'product_code' => 'ATOMIC-HABITS',
                'category' => 'Non-Fiction',
                'price' => 27.00,
                'special_price' => 18.99,
                'color' => ['Standard'],
                'size' => ['Hardcover'],
                'sections' => ['best_seller'],
                'short_description' => '#1 New York Times bestseller on building tiny 1% daily habits that compound into extraordinary results.',
                'description' => '<p>Practical, science-backed framework for forming good habits, breaking bad ones, and mastering the tiny behaviors that lead to remarkable outcomes.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1589829085413-56de8ae18c73?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Author', 'value' => 'James Clear', 'unit' => null],
                    ['attribute' => 'Pages', 'value' => '320', 'unit' => 'pages'],
                    ['attribute' => 'Language', 'value' => 'English', 'unit' => null],
                ],
            ],

            // ======================== 15. CHILDREN'S BOOKS ========================
            [
                'name' => 'Harry Potter Complete 7-Book Boxed Set by J.K. Rowling',
                'brand' => 'Scholastic',
                'product_code' => 'HARRY-POTTER-BOX',
                'category' => "Children's Books",
                'price' => 99.99,
                'special_price' => 69.99,
                'color' => ['Collector Trunk Box'],
                'size' => ['7-Book Box Set'],
                'sections' => ['featured', 'best_seller'],
                'short_description' => 'Complete 7-volume paperback boxed collection of the magical Harry Potter saga.',
                'description' => '<p>Includes all seven adventures from The Sorcerer\'s Stone through The Deathly Hallows in a decorative keepsake slipcase.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1495446815901-a7297e633e8d?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Author', 'value' => 'J.K. Rowling', 'unit' => null],
                    ['attribute' => 'Volumes', 'value' => '7 Books', 'unit' => null],
                    ['attribute' => 'Reading Age', 'value' => '8 - 14 years', 'unit' => null],
                ],
            ],

            // ======================== 16. ACADEMIC ========================
            [
                'name' => 'Designing Data-Intensive Applications by Martin Kleppmann',
                'brand' => "O'Reilly Media",
                'product_code' => 'OREILLY-DDIA',
                'category' => 'Academic',
                'price' => 65.99,
                'special_price' => 49.99,
                'color' => ['Paperback'],
                'size' => ['616 Pages'],
                'sections' => ['featured', 'best_seller'],
                'short_description' => 'The definitive software engineering bible on distributed systems, databases, replication, and message queues.',
                'description' => '<p>Deep dive into storage engines, transactions, partitioning, stream processing, RabbitMQ/Kafka patterns, and building reliable scalable backends.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1532012197267-da84d127e765?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Author', 'value' => 'Martin Kleppmann', 'unit' => null],
                    ['attribute' => 'Publisher', 'value' => "O'Reilly Media", 'unit' => null],
                    ['attribute' => 'Pages', 'value' => '616', 'unit' => 'pages'],
                    ['attribute' => 'Topic', 'value' => 'Distributed Systems & Databases', 'unit' => null],
                ],
            ],
            [
                'name' => 'Clean Architecture by Robert C. Martin (Uncle Bob)',
                'brand' => 'Pearson',
                'product_code' => 'CLEAN-ARCH',
                'category' => 'Academic',
                'price' => 49.99,
                'special_price' => 38.99,
                'color' => ['Paperback'],
                'size' => ['432 Pages'],
                'sections' => ['featured'],
                'short_description' => 'Craftsman\'s guide to SOLID principles, dependency inversion, boundaries, and maintainable software architecture.',
                'description' => '<p>Essential reference for senior backend and full-stack engineers designing decoupled, testable, long-lived applications.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Author', 'value' => 'Robert C. Martin', 'unit' => null],
                    ['attribute' => 'Pages', 'value' => '432', 'unit' => 'pages'],
                ],
            ],

            // ======================== 17. FITNESS ========================
            [
                'name' => 'Garmin Forerunner 265 AMOLED GPS Running Smartwatch',
                'brand' => 'Garmin',
                'product_code' => 'GARMIN-265',
                'category' => 'Fitness',
                'price' => 449.99,
                'special_price' => 399.99,
                'color' => ['Black', 'Aqua', 'White'],
                'size' => ['46mm'],
                'sections' => ['featured', 'new_arrival'],
                'short_description' => 'Brilliant AMOLED GPS running watch with Training Readiness, HRV status, and 13-day battery life.',
                'description' => '<p>Multi-band SatIQ GPS precision, wrist-based running power, sleep coaching, and built-in music storage for Spotify & Apple Music.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Display', 'value' => '1.3 AMOLED Touchscreen', 'unit' => 'inches'],
                    ['attribute' => 'Battery', 'value' => '13', 'unit' => 'days'],
                    ['attribute' => 'GPS', 'value' => 'Multi-Band SatIQ GNSS', 'unit' => null],
                    ['attribute' => 'Water Rating', 'value' => '5 ATM (Swim Ready)', 'unit' => null],
                ],
            ],
            [
                'name' => 'Bowflex SelectTech 552 Adjustable Dumbbells (Pair)',
                'brand' => 'Bowflex',
                'product_code' => 'BOWFLEX-552',
                'category' => 'Fitness',
                'price' => 429.00,
                'special_price' => 379.00,
                'color' => ['Black/Red'],
                'size' => ['5 - 52.5 lbs'],
                'sections' => ['best_seller'],
                'short_description' => 'Space-saving dial-adjustable dumbbell pair replacing 15 sets of weights from 5 to 52.5 lbs each.',
                'description' => '<p>Turn the dial to switch smoothly between 15 weight increments in seconds for full-body home strength training.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Weight Range', 'value' => '5 to 52.5', 'unit' => 'lbs per dumbbell'],
                    ['attribute' => 'Settings', 'value' => '15 Dial Increments', 'unit' => null],
                ],
            ],
            [
                'name' => 'Under Armour Tech 2.0 Quick-Dry Training T-Shirt',
                'brand' => 'Under Armour',
                'product_code' => 'UA-TECH-TEE',
                'category' => 'Fitness',
                'price' => 25.00,
                'special_price' => 19.99,
                'color' => ['Black', 'Academy Blue', 'Carbon Heather'],
                'size' => ['S', 'M', 'L', 'XL'],
                'sections' => ['best_seller'],
                'short_description' => 'Ultra-lightweight sweat-wicking gym shirt with anti-odor technology and streamlined athletic fit.',
                'description' => '<p>UA Tech fabric dries remarkably fast and feels natural against the skin during intense runs and strength sessions.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Fabric', 'value' => '100% Quick-Dry Polyester', 'unit' => null],
                    ['attribute' => 'Fit', 'value' => 'Loose Athletic', 'unit' => null],
                ],
            ],

            // ======================== 18. TEAM SPORTS ========================
            [
                'name' => 'Wilson Evolution Official Indoor Game Basketball (29.5")',
                'brand' => 'Wilson',
                'product_code' => 'WILSON-EVO',
                'category' => 'Team Sports',
                'price' => 79.95,
                'special_price' => 69.95,
                'color' => ['Classic Orange'],
                'size' => ['Size 7 (29.5")'],
                'sections' => ['best_seller'],
                'short_description' => 'The #1 indoor high-school and college game basketball with Evo Microfiber Composite Leather.',
                'description' => '<p>Cushion Core Carcass delivers exceptional softness, consistent bounce, and superior moisture-wicking grip.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Size', 'value' => '7 (Official 29.5 in)', 'unit' => null],
                    ['attribute' => 'Cover', 'value' => 'Evo Microfiber Composite Leather', 'unit' => null],
                    ['attribute' => 'Surface', 'value' => 'Indoor Hardwood', 'unit' => null],
                ],
            ],
            [
                'name' => 'Adidas UEFA Champions League Pro Official Match Ball',
                'brand' => 'Adidas',
                'product_code' => 'ADIDAS-UCL-PRO',
                'category' => 'Team Sports',
                'price' => 165.00,
                'special_price' => 139.00,
                'color' => ['White/Star Graphic'],
                'size' => ['Size 5'],
                'sections' => ['featured'],
                'short_description' => 'FIFA Quality Pro thermally bonded seamless soccer match ball for predictable flight and touch.',
                'description' => '<p>High-grade butyl bladder and seamless polyurethane cover engineered for tournament-level precision in all weather conditions.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1614632537197-38a17061c2bd?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Certification', 'value' => 'FIFA Quality Pro', 'unit' => null],
                    ['attribute' => 'Construction', 'value' => 'Thermally Bonded Seamless', 'unit' => null],
                ],
            ],

            // ======================== 19. OUTDOOR RECREATION ========================
            [
                'name' => 'Coleman Sundome 4-Person WeatherTec Camping Tent',
                'brand' => 'Coleman',
                'product_code' => 'COLEMAN-TENT4',
                'category' => 'Outdoor Recreation',
                'price' => 119.99,
                'special_price' => 94.99,
                'color' => ['Spruce Green', 'Navy'],
                'size' => ['4-Person'],
                'sections' => ['featured'],
                'short_description' => '10-minute setup waterproof dome camping tent with welded corners, rainfly, and 35 mph wind rating.',
                'description' => '<p>WeatherTec system keeps water out with inverted seams, while large windows and a ground vent ensure superior airflow.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Capacity', 'value' => '4 Persons (1 Queen Airbed)', 'unit' => null],
                    ['attribute' => 'Dimensions', 'value' => '9 x 7 ft (Center Height 4 ft 11 in)', 'unit' => null],
                    ['attribute' => 'Setup Time', 'value' => '10', 'unit' => 'minutes'],
                ],
            ],
            [
                'name' => 'YETI Tundra 45 Rotomolded Hard Cooler - White',
                'brand' => 'YETI',
                'product_code' => 'YETI-TUNDRA45',
                'category' => 'Outdoor Recreation',
                'price' => 325.00,
                'special_price' => 299.00,
                'color' => ['White', 'Tan', 'Charcoal'],
                'size' => ['45 Quart'],
                'sections' => ['best_seller'],
                'short_description' => 'Virtually indestructible rotomolded outdoor cooler with 3-inch PermaFrost insulation and bear-proof latches.',
                'description' => '<p>Keeps ice frozen for days on camping trips, road trips, and beach weekends. Holds up to 54 cans.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1523987355523-c7b5b0dd90a7?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Capacity', 'value' => '54 Cans / 37 lbs Ice', 'unit' => null],
                    ['attribute' => 'Insulation', 'value' => '3-Inch PermaFrost Polyurethane', 'unit' => null],
                ],
            ],

            // ======================== 20. WATER SPORTS ========================
            [
                'name' => 'Speedo Vanquisher 2.0 Mirrored Anti-Fog Swim Goggles',
                'brand' => 'Speedo',
                'product_code' => 'SPEEDO-VENG',
                'category' => 'Water Sports',
                'price' => 24.99,
                'special_price' => 19.99,
                'color' => ['Silver Mirror', 'Blue Mirror', 'Black'],
                'size' => ['Adult One Size'],
                'sections' => ['best_seller'],
                'short_description' => 'America\'s #1 racing and lap swimming goggles with UV protection, anti-fog lenses, and 4 nosepieces.',
                'description' => '<p>Low-profile panoramic lenses reduce glare outdoors while cushioned silicone seals lock out water comfortably.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1530549387789-4c1017266635?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Lens', 'value' => 'Mirrored UV400 Anti-Fog', 'unit' => null],
                    ['attribute' => 'Nosepieces', 'value' => '4 Interchangeable Sizes', 'unit' => null],
                ],
            ],
            [
                'name' => 'Intex Explorer K2 2-Person Inflatable Kayak Set with Oars',
                'brand' => 'Intex',
                'product_code' => 'INTEX-KAYAK',
                'category' => 'Water Sports',
                'price' => 159.99,
                'special_price' => 129.99,
                'color' => ['High-Visibility Yellow'],
                'size' => ['2-Person (400 lb cap)'],
                'sections' => ['new_arrival'],
                'short_description' => 'Heavy-duty puncture-resistant vinyl 2-person inflatable kayak complete with 86" aluminum oars and pump.',
                'description' => '<p>Removable skeg for directional stability on lakes and mild rivers, adjustable inflatable seats with backrests, and quick Boston valves.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=800&q=80',
                ],
                'specifications' => [
                    ['attribute' => 'Capacity', 'value' => '2 Persons / 180 kg (400 lbs)', 'unit' => null],
                    ['attribute' => 'Included', 'value' => '2 Aluminum Oars + High-Output Pump + Bag', 'unit' => null],
                ],
            ],
        ];
    }
}

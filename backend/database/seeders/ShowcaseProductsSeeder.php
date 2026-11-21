<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStatus;
use App\Models\ProductImage;
use App\Models\ProductAttribute;
use App\Models\ProductSpecification;
use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ShowcaseProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating 10 Showcase Products with Real Images...');

        // Ensure prerequisites
        $this->ensurePrerequisites();

        // Define 10 showcase products
        $showcaseProducts = [
            [
                'name' => 'MacBook Pro 16" M3 Max - Space Black',
                'description' => '<h3>Supercharged for Pros</h3><p>The MacBook Pro 16" with M3 Max chip delivers unprecedented performance for the most demanding pro workflows. With up to 128GB unified memory and up to 22 hours of battery life, it\'s the most powerful Mac laptop ever.</p><h4>Key Features</h4><ul><li>M3 Max chip with up to 16-core CPU and 40-core GPU</li><li>16.2-inch Liquid Retina XDR display</li><li>Up to 128GB unified memory</li><li>Up to 8TB SSD storage</li><li>22 hours battery life</li><li>ProMotion technology with 120Hz refresh rate</li></ul>',
                'short_description' => 'The ultimate pro laptop with M3 Max chip, stunning Liquid Retina XDR display, and all-day battery life.',
                'price' => 3499.00,
                'special_price' => 3299.00,
                'quantity' => 25,
                'brand' => 'Apple',
                'product_code' => 'MBP16-M3MAX-SB',
                'sku' => 'SKU-APPLE-MBP16-M3MAX-SB',
                'color' => ['Space Black', 'Silver'],
                'size' => ['16-inch'],
                'category' => 'Laptops',
                'status' => 'published',
                'sections' => ['featured', 'best_seller'],
                'images' => 4,
                'specifications' => [
                    ['attribute' => 'Processor', 'value' => 'Apple M3 Max', 'unit' => null],
                    ['attribute' => 'CPU Cores', 'value' => '16', 'unit' => 'cores'],
                    ['attribute' => 'GPU Cores', 'value' => '40', 'unit' => 'cores'],
                    ['attribute' => 'Memory', 'value' => '48', 'unit' => 'GB'],
                    ['attribute' => 'Storage', 'value' => '1', 'unit' => 'TB'],
                    ['attribute' => 'Display', 'value' => '16.2', 'unit' => 'inches'],
                    ['attribute' => 'Resolution', 'value' => '3456 x 2234', 'unit' => 'px'],
                    ['attribute' => 'Battery Life', 'value' => '22', 'unit' => 'hours'],
                    ['attribute' => 'Weight', 'value' => '2.15', 'unit' => 'kg'],
                    ['attribute' => 'Operating System', 'value' => 'macOS Sonoma', 'unit' => null],
                ],
            ],
            [
                'name' => 'Samsung Galaxy S24 Ultra 5G - Titanium Gray',
                'description' => '<h3>Galaxy AI is Here</h3><p>Experience the new era of mobile AI with Galaxy S24 Ultra. Circle to Search, Live Translate, and Generative Edit transform how you interact with your phone. The 200MP camera captures incredible detail, and the built-in S Pen lets you write, sketch, and control your phone remotely.</p><h4>Key Features</h4><ul><li>6.8" Dynamic AMOLED 2X display</li><li>Snapdragon 8 Gen 3 for Galaxy</li><li>200MP + 50MP + 12MP + 10MP quad camera</li><li>5,000mAh battery with 45W charging</li><li>Built-in S Pen</li><li>Titanium frame with Gorilla Glass Armor</li></ul>',
                'short_description' => 'Flagship smartphone with Galaxy AI, 200MP camera, S Pen, and titanium design.',
                'price' => 1299.99,
                'special_price' => 1199.99,
                'quantity' => 50,
                'brand' => 'Samsung',
                'product_code' => 'S24U-5G-TG',
                'sku' => 'SKU-SAMSUNG-S24U-5G-TG',
                'color' => ['Titanium Gray', 'Titanium Black', 'Titanium Violet', 'Titanium Yellow'],
                'size' => ['256GB', '512GB', '1TB'],
                'category' => 'Smartphones',
                'status' => 'published',
                'sections' => ['featured', 'new_arrival'],
                'images' => 4,
                'specifications' => [
                    ['attribute' => 'Display', 'value' => '6.8', 'unit' => 'inches'],
                    ['attribute' => 'Processor', 'value' => 'Snapdragon 8 Gen 3', 'unit' => null],
                    ['attribute' => 'RAM', 'value' => '12', 'unit' => 'GB'],
                    ['attribute' => 'Main Camera', 'value' => '200', 'unit' => 'MP'],
                    ['attribute' => 'Battery', 'value' => '5000', 'unit' => 'mAh'],
                    ['attribute' => 'Charging', 'value' => '45', 'unit' => 'W'],
                    ['attribute' => '5G', 'value' => 'Yes', 'unit' => null],
                    ['attribute' => 'S Pen', 'value' => 'Included', 'unit' => null],
                    ['attribute' => 'Weight', 'value' => '232', 'unit' => 'g'],
                ],
            ],
            [
                'name' => 'Sony WH-1000XM5 Wireless Headphones - Midnight Blue',
                'description' => '<h3>Industry-Leading Noise Cancellation</h3><p>The WH-1000XM5 headphones redefine distraction-free listening with Dual Noise Sensor technology and Integrated Processor V1. Experience crystal-clear calls with Precise Voice Pickup Technology, and enjoy 30-hour battery life with quick charging.</p><h4>Key Features</h4><ul><li>Industry-leading noise cancellation</li><li>30-hour battery life (NC on)</li><li>3-minute charge = 3 hours playback</li><li>Multipoint connection</li><li>Speak-to-Chat automatically pauses music</li><li>Lightweight comfort (250g)</li></ul>',
                'short_description' => 'Best-in-class noise canceling headphones with 30-hour battery and premium sound quality.',
                'price' => 399.99,
                'special_price' => 349.99,
                'quantity' => 75,
                'brand' => 'Sony',
                'product_code' => 'WH1000XM5-MB',
                'sku' => 'SKU-SONY-WH1000XM5-MB',
                'color' => ['Midnight Blue', 'Black', 'Silver'],
                'size' => ['One Size'],
                'category' => 'Audio',
                'status' => 'published',
                'sections' => ['best_seller'],
                'images' => 3,
                'specifications' => [
                    ['attribute' => 'Driver Unit', 'value' => '30', 'unit' => 'mm'],
                    ['attribute' => 'Frequency Response', 'value' => '4-40,000', 'unit' => 'Hz'],
                    ['attribute' => 'Battery Life (NC On)', 'value' => '30', 'unit' => 'hours'],
                    ['attribute' => 'Battery Life (NC Off)', 'value' => '40', 'unit' => 'hours'],
                    ['attribute' => 'Quick Charge', 'value' => '3 min = 3 hrs', 'unit' => null],
                    ['attribute' => 'Bluetooth Version', 'value' => '5.2', 'unit' => null],
                    ['attribute' => 'Weight', 'value' => '250', 'unit' => 'g'],
                    ['attribute' => 'Noise Cancellation', 'value' => 'Dual Noise Sensor', 'unit' => null],
                ],
            ],
            [
                'name' => 'Nike Air Zoom Alphafly NEXT% 3 - Bright Crimson',
                'description' => '<h3>Fastest Marathon Racing Shoe</h3><p>The Alphafly NEXT% 3 is designed for record-breaking speed. With two Zoom Air pods, a full-length carbon fiber Flyplate, and AtomKnit 3.0 upper, it delivers maximum energy return and propulsion for elite marathon runners.</p><h4>Key Features</h4><ul><li>Dual Zoom Air units in forefoot</li><li>Full-length carbon fiber Flyplate</li><li>AtomKnit 3.0 engineered upper</li><li>ZoomX foam for maximum energy return</li><li>8mm offset</li><li>Designed for marathon racing</li></ul>',
                'short_description' => 'Elite marathon racing shoe with dual Zoom Air, carbon Flyplate, and AtomKnit upper.',
                'price' => 285.00,
                'special_price' => 259.00,
                'quantity' => 30,
                'brand' => 'Nike',
                'product_code' => 'AFN3-BC',
                'sku' => 'SKU-NIKE-AFN3-BC',
                'color' => ['Bright Crimson', 'White/Black', 'Volt/Black'],
                'size' => ['US 7', 'US 7.5', 'US 8', 'US 8.5', 'US 9', 'US 9.5', 'US 10', 'US 10.5', 'US 11', 'US 12'],
                'category' => 'Fitness',
                'status' => 'published',
                'sections' => ['featured', 'new_arrival'],
                'images' => 4,
                'specifications' => [
                    ['attribute' => 'Weight (US 9)', 'value' => '215', 'unit' => 'g'],
                    ['attribute' => 'Offset', 'value' => '8', 'unit' => 'mm'],
                    ['attribute' => 'Midsole', 'value' => 'ZoomX Foam', 'unit' => null],
                    ['attribute' => 'Plate', 'value' => 'Carbon Fiber Flyplate', 'unit' => null],
                    ['attribute' => 'Air Units', 'value' => '2x Zoom Air', 'unit' => null],
                    ['attribute' => 'Upper', 'value' => 'AtomKnit 3.0', 'unit' => null],
                    ['attribute' => 'Use', 'value' => 'Marathon Racing', 'unit' => null],
                ],
            ],
            [
                'name' => 'Dell XPS 15 9530 - Platinum Silver',
                'description' => '<h3>Creator-Class Performance</h3><p>The XPS 15 combines stunning 3.5K OLED display with 13th Gen Intel Core processors and NVIDIA RTX 4070 graphics. Perfect for video editing, 3D rendering, and creative workflows. CNC machined aluminum with carbon fiber palm rest.</p><h4>Key Features</h4><ul><li>15.6" 3.5K OLED Touch Display</li><li>Intel Core i9-13900H</li><li>NVIDIA RTX 4070 8GB GDDR6</li><li>32GB DDR5 RAM</li><li>1TB PCIe SSD</li><li>Windows 11 Pro</li></ul>',
                'short_description' => 'Premium creator laptop with 3.5K OLED, Intel i9, RTX 4070, and precision craftsmanship.',
                'price' => 2499.00,
                'special_price' => 2299.00,
                'quantity' => 20,
                'brand' => 'Dell',
                'product_code' => 'XPS15-9530-PS',
                'sku' => 'SKU-DELL-XPS15-9530-PS',
                'color' => ['Platinum Silver'],
                'size' => ['15.6-inch'],
                'category' => 'Laptops',
                'status' => 'published',
                'sections' => ['best_seller'],
                'images' => 3,
                'specifications' => [
                    ['attribute' => 'Processor', 'value' => 'Intel Core i9-13900H', 'unit' => null],
                    ['attribute' => 'Graphics', 'value' => 'NVIDIA RTX 4070', 'unit' => null],
                    ['attribute' => 'VRAM', 'value' => '8', 'unit' => 'GB GDDR6'],
                    ['attribute' => 'Memory', 'value' => '32', 'unit' => 'GB DDR5'],
                    ['attribute' => 'Storage', 'value' => '1', 'unit' => 'TB SSD'],
                    ['attribute' => 'Display', 'value' => '15.6" 3.5K OLED Touch', 'unit' => null],
                    ['attribute' => 'Weight', 'value' => '1.86', 'unit' => 'kg'],
                    ['attribute' => 'Battery', 'value' => '86', 'unit' => 'Wh'],
                    ['attribute' => 'OS', 'value' => 'Windows 11 Pro', 'unit' => null],
                ],
            ],
            [
                'name' => 'Bose QuietComfort Ultra Earbuds - Moonstone Blue',
                'description' => '<h3>Immersive Audio Like Never Before</h3><p>Experience spatial audio with CustomTune technology that auto-adjusts to your ears. The best noise cancellation in any earbuds, with 6-hour battery life (24 with case) and IPX4 water resistance. Simple Touch controls and multipoint Bluetooth.</p><h4>Key Features</h4><ul><li>Immersive Spatial Audio</li><li>CustomTune auto-calibration</li><li>Best-in-class noise cancellation</li><li>6 hours battery (24 with case)</li><li>IPX4 water resistant</li><li>Multipoint Bluetooth 5.3</li></ul>',
                'short_description' => 'Premium earbuds with spatial audio, CustomTune tech, and best-in-class noise cancellation.',
                'price' => 299.00,
                'special_price' => 279.00,
                'quantity' => 60,
                'brand' => 'Bose',
                'product_code' => 'QCUE-MB',
                'sku' => 'SKU-BOSE-QCUE-MB',
                'color' => ['Moonstone Blue', 'Black', 'White Smoke'],
                'size' => ['One Size'],
                'category' => 'Audio',
                'status' => 'published',
                'sections' => ['featured'],
                'images' => 3,
                'specifications' => [
                    ['attribute' => 'Driver', 'value' => '9.3', 'unit' => 'mm'],
                    ['attribute' => 'Battery (Earbuds)', 'value' => '6', 'unit' => 'hours'],
                    ['attribute' => 'Battery (Case)', 'value' => '24', 'unit' => 'hours'],
                    ['attribute' => 'Quick Charge', 'value' => '20 min = 2 hrs', 'unit' => null],
                    ['attribute' => 'Bluetooth', 'value' => '5.3', 'unit' => null],
                    ['attribute' => 'Water Resistance', 'value' => 'IPX4', 'unit' => null],
                    ['attribute' => 'Weight (Each)', 'value' => '6.2', 'unit' => 'g'],
                    ['attribute' => 'Noise Cancellation', 'value' => 'Adaptive', 'unit' => null],
                ],
            ],
            [
                'name' => 'Logitech MX Master 3S Wireless Mouse - Graphite',
                'description' => '<h3>Master Your Flow</h3><p>The MX Master 3S features 8,000 DPI sensor tracking on glass, quiet clicks (90% less noise), and MagSpeed electromagnetic scrolling. Connect up to 3 devices via Bluetooth or Logi Bolt. Ergonomic sculpted shape with thumb wheel for horizontal navigation.</p><h4>Key Features</h4><ul><li>8,000 DPI any-surface tracking</li><li>Quiet clicks - 90% noise reduction</li><li>MagSpeed electromagnetic scroll wheel</li><li>Up to 70 days battery life</li><li>USB-C quick charging</li><li>Flow cross-computer control</li></ul>',
                'short_description' => 'Advanced wireless mouse with 8K DPI glass tracking, quiet clicks, and MagSpeed scrolling.',
                'price' => 99.99,
                'special_price' => 89.99,
                'quantity' => 100,
                'brand' => 'Logitech',
                'product_code' => 'MXM3S-GR',
                'sku' => 'SKU-LOGITECH-MXM3S-GR',
                'color' => ['Graphite', 'Pale Grey'],
                'size' => ['One Size'],
                'category' => 'Laptops',
                'status' => 'published',
                'sections' => ['best_seller'],
                'images' => 3,
                'specifications' => [
                    ['attribute' => 'Sensor', 'value' => '8,000 DPI', 'unit' => null],
                    ['attribute' => 'Tracking', 'value' => 'Any surface including glass', 'unit' => null],
                    ['attribute' => 'Battery Life', 'value' => '70', 'unit' => 'days'],
                    ['attribute' => 'Quick Charge', 'value' => '1 min = 3 hrs', 'unit' => null],
                    ['attribute' => 'Connectivity', 'value' => 'Bluetooth / Logi Bolt', 'unit' => null],
                    ['attribute' => 'Devices', 'value' => 'Up to 3', 'unit' => null],
                    ['attribute' => 'Weight', 'value' => '141', 'unit' => 'g'],
                    ['attribute' => 'Buttons', 'value' => '7', 'unit' => null],
                ],
            ],
            [
                'name' => 'ASUS ROG Swift OLED PG27AQDM - 27" 240Hz Gaming Monitor',
                'description' => '<h3>OLED Gaming Perfection</h3><p>The 27" 1440p OLED panel delivers 0.03ms response time and 240Hz refresh rate with G-SYNC compatibility. 1,500,000:1 contrast ratio, true blacks, and 99% DCI-P3 color. Custom heatsink and intelligent voltage optimization prevent burn-in.</p><h4>Key Features</h4><ul><li>27" QHD (2560x1440) OLED</li><li>240Hz refresh rate</li><li>0.03ms response time</li><li>G-SYNC Compatible</li><li>99% DCI-P3</li><li>Anti-glare micro-texture coating</li></ul>',
                'short_description' => '27" OLED gaming monitor with 240Hz, 0.03ms response, G-SYNC, and true blacks.',
                'price' => 999.00,
                'special_price' => 899.00,
                'quantity' => 15,
                'brand' => 'ASUS',
                'product_code' => 'PG27AQDM',
                'sku' => 'SKU-ASUS-PG27AQDM',
                'color' => ['Black'],
                'size' => ['27-inch'],
                'category' => 'Laptops',
                'status' => 'published',
                'sections' => ['new_arrival'],
                'images' => 3,
                'specifications' => [
                    ['attribute' => 'Panel Type', 'value' => 'OLED', 'unit' => null],
                    ['attribute' => 'Size', 'value' => '27', 'unit' => 'inches'],
                    ['attribute' => 'Resolution', 'value' => '2560 x 1440', 'unit' => 'px'],
                    ['attribute' => 'Refresh Rate', 'value' => '240', 'unit' => 'Hz'],
                    ['attribute' => 'Response Time', 'value' => '0.03', 'unit' => 'ms'],
                    ['attribute' => 'Contrast Ratio', 'value' => '1,500,000:1', 'unit' => null],
                    ['attribute' => 'Color Gamut', 'value' => '99% DCI-P3', 'unit' => null],
                    ['attribute' => 'HDR', 'value' => 'HDR10', 'unit' => null],
                    ['attribute' => 'Ports', 'value' => 'HDMI 2.0 x2, DP 1.4, USB Hub', 'unit' => null],
                ],
            ],
            [
                'name' => 'Apple Watch Series 9 45mm - Midnight Aluminum',
                'description' => '<h3>Your Essential Companion</h3><p>The Apple Watch Series 9 features the S9 SiP for faster performance, double tap gesture, brighter 2000-nit display, and precision finding for iPhone. Advanced health sensors track heart rate, blood oxygen, temperature, and ECG. 18-hour battery life with fast charging.</p><h4>Key Features</h4><ul><li>S9 SiP with 64-bit dual-core processor</li><li>Double tap gesture</li><li>2000-nit Always-On Retina display</li><li>Blood oxygen, ECG, temperature sensing</li><li>Precision Finding for iPhone</li><li>18-hour battery life</li><li>watchOS 10</li></ul>',
                'short_description' => 'Advanced smartwatch with S9 chip, double tap gesture, bright display, and comprehensive health tracking.',
                'price' => 429.00,
                'special_price' => 399.00,
                'quantity' => 40,
                'brand' => 'Apple',
                'product_code' => 'AW9-45-MA',
                'sku' => 'SKU-APPLE-AW9-45-MA',
                'color' => ['Midnight', 'Starlight', 'Silver', 'Pink', 'Red'],
                'size' => ['41mm', '45mm'],
                'category' => 'Smartphones',
                'status' => 'published',
                'sections' => ['featured', 'best_seller'],
                'images' => 4,
                'specifications' => [
                    ['attribute' => 'Case Size', 'value' => '45', 'unit' => 'mm'],
                    ['attribute' => 'Case Material', 'value' => 'Aluminum', 'unit' => null],
                    ['attribute' => 'Display', 'value' => 'Always-On Retina LTPO OLED', 'unit' => null],
                    ['attribute' => 'Brightness', 'value' => '2000', 'unit' => 'nits'],
                    ['attribute' => 'Chip', 'value' => 'S9 SiP', 'unit' => null],
                    ['attribute' => 'Battery Life', 'value' => '18', 'unit' => 'hours'],
                    ['attribute' => 'Water Resistance', 'value' => '50', 'unit' => 'm'],
                    ['attribute' => 'Sensors', 'value' => 'Heart Rate, Blood O2, ECG, Temp', 'unit' => null],
                    ['attribute' => 'Connectivity', 'value' => 'GPS + Cellular / GPS', 'unit' => null],
                ],
            ],
            [
                'name' => 'Dyson V15 Detect Absolute - Yellow/Nickel',
                'description' => '<h3>Scientifically Proven Deep Clean</h3><p>The Dyson V15 Detect reveals invisible dust with laser illumination. Piezo sensor counts and sizes particles, auto-adjusting suction power. 230AW suction, 60-minute runtime, and HEPA filtration captures 99.99% of particles down to 0.3 microns. Includes 5 tools for whole-home cleaning.</p><h4>Key Features</h4><ul><li>Laser dust detection</li><li>Piezo sensor with auto-suction</li><li>230AW maximum suction</li><li>60-minute runtime</li><li>HEPA filtration</li><li>5 included tools</li><li>LCD screen shows particle count</li></ul>',
                'short_description' => 'Intelligent cordless vacuum with laser dust detection, piezo sensor, and 230AW suction.',
                'price' => 749.99,
                'special_price' => 699.99,
                'quantity' => 25,
                'brand' => 'Dyson',
                'product_code' => 'V15DA-YN',
                'sku' => 'SKU-DYSON-V15DA-YN',
                'color' => ['Yellow/Nickel', 'Nickel/Blue'],
                'size' => ['One Size'],
                'category' => 'Home & Kitchen',
                'status' => 'published',
                'sections' => ['featured'],
                'images' => 4,
                'specifications' => [
                    ['attribute' => 'Suction Power', 'value' => '230', 'unit' => 'AW'],
                    ['attribute' => 'Runtime', 'value' => '60', 'unit' => 'minutes'],
                    ['attribute' => 'Motor', 'value' => 'Dyson Hyperdymium', 'unit' => null],
                    ['attribute' => 'Filtration', 'value' => 'HEPA - 99.99% at 0.3 microns', 'unit' => null],
                    ['attribute' => 'Dust Detection', 'value' => 'Laser + Piezo Sensor', 'unit' => null],
                    ['attribute' => 'Weight', 'value' => '3.0', 'unit' => 'kg'],
                    ['attribute' => 'Bin Volume', 'value' => '0.76', 'unit' => 'L'],
                    ['attribute' => 'Charge Time', 'value' => '4.5', 'unit' => 'hours'],
                    ['attribute' => 'Tools Included', 'value' => '5', 'unit' => null],
                ],
            ],
        ];

        foreach ($showcaseProducts as $productData) {
            // Get category
            $category = ProductCategory::where('slug', Str::slug($productData['category']))->first();
            $status = ProductStatus::where('name', $productData['status'])->first();

            if (!$category || !$status) {
                $this->command->warn("Category or Status not found for: {$productData['name']}");
                continue;
            }

            // Create or update product
            $product = Product::updateOrCreate(
                ['product_code' => $productData['product_code']],
                [
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'short_description' => $productData['short_description'],
                    'price' => $productData['price'],
                    'special_price' => $productData['special_price'] ?? null,
                    'quantity' => $productData['quantity'],
                    'product_category_id' => $category->id,
                    'product_status_id' => $status->id,
                    'brand' => $productData['brand'],
                    'product_code' => $productData['product_code'],
                    'sku' => $productData['sku'],
                    'color' => $productData['color'],
                    'size' => $productData['size'],
                    'view_count' => rand(500, 5000),
                ]
            );

            $this->command->info("Product: {$product->name} (ID: {$product->id})");

            // Assign to sections
            foreach ($productData['sections'] as $sectionSlug) {
                $section = Section::where('name', $sectionSlug)->first();
                if ($section) {
                    $product->sections()->syncWithoutDetaching([$section->id]);
                }
            }

            // Download and create images
            $this->createProductImages($product, $productData['images']);

            // Create specifications
            foreach ($productData['specifications'] as $index => $spec) {
                $attribute = ProductAttribute::firstOrCreate(['name' => $spec['attribute']]);

                // Use the pivot table through the relationship
                $product->specifications()->syncWithoutDetaching([
                    $attribute->id => [
                        'value' => $spec['value'],
                        'unit' => $spec['unit'],
                        'position' => $index + 1,
                    ]
                ]);
            }
        }

        $this->command->info('Showcase Products created successfully!');
    }

    /**
     * Ensure prerequisites exist
     */
    private function ensurePrerequisites(): void
    {
        // Product Statuses
        foreach (['published', 'draft', 'archived'] as $name) {
            ProductStatus::firstOrCreate(['name' => $name]);
        }

        // Sections
        foreach (['featured', 'new_arrival', 'best_seller'] as $name) {
            Section::firstOrCreate(
                ['name' => $name],
                ['name' => ucwords(str_replace('_', ' ', $name))]
            );
        }

        // Storage
        Storage::disk('public')->makeDirectory('products');
    }

    /**
     * Create product images using curated high-resolution product links
     */
    private function createProductImages(Product $product, int $count): void
    {
        // Delete existing images for this product
        $product->images()->delete();

        $coverUrl = $product->getTestingImageUrl(0);
        $product->update(['image' => $coverUrl]);

        for ($i = 0; $i < $count; $i++) {
            $url = $product->getTestingImageUrl($i);

            ProductImage::create([
                'product_id' => $product->id,
                'path'       => $url,
                'position'   => $i + 1,
                'is_cover'   => $i === 0,
            ]);
        }
    }

    /**
     * Create placeholder image
     */
    private function createPlaceholder(string $path, string $text): void
    {
        $fullPath = storage_path('app/public/' . $path);
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (function_exists('imagecreatetruecolor')) {
            $image = imagecreatetruecolor(800, 800);
            $hash = crc32($text);
            $red = min(200, (($hash & 0xFF0000) >> 16) + 55);
            $green = min(200, (($hash & 0x00FF00) >> 8) + 55);
            $blue = min(200, ($hash & 0x0000FF) + 55);

            $background = imagecolorallocate($image, $red, $green, $blue);
            imagefilledrectangle($image, 0, 0, 800, 800, $background);

            $textColor = imagecolorallocate($image, 255, 255, 255);
            $fontSize = 5;
            $textWidth = imagefontwidth($fontSize) * strlen($text);
            $textHeight = imagefontheight($fontSize);
            $x = (800 - $textWidth) / 2;
            $y = (800 - $textHeight) / 2;
            imagestring($image, $fontSize, $x, $y, $text, $textColor);

            imagepng($image, $fullPath);
            imagedestroy($image);
        } else {
            file_put_contents($fullPath, '');
        }

        // Still create the DB record
        $productId = (int) explode('-', basename($path))[0];
        ProductImage::create([
            'product_id' => $productId,
            'path' => $path,
            'position' => 1,
            'is_cover' => true,
        ]);
    }
}
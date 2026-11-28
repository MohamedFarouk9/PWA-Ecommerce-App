<?php

namespace Modules\Catalog\Database\Seeders;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductStatus;
use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Generates 500 realistic, domain-accurate products across all 20 child categories
     * (25 products per category) with real brands, realistic pricing, structured specs,
     * and curated category-matched Unsplash images — zero Lorem Ipsum.
     *
     * Idempotent via updateOrCreate on `product_code`.
     */
    public function run(): void
    {
        $this->command?->info('ProductSeeder: Seeding 500 realistic multi-category catalog products...');

        $publishedStatus = ProductStatus::firstOrCreate(['name' => 'published']);
        $sectionsMap = [
            'featured'    => Section::firstOrCreate(['name' => 'featured'], ['label' => 'Featured'])->id,
            'best_seller' => Section::firstOrCreate(['name' => 'best_seller'], ['label' => 'Best Seller'])->id,
            'new_arrival' => Section::firstOrCreate(['name' => 'new_arrival'], ['label' => 'New Arrival'])->id,
            'top_rated'   => Section::firstOrCreate(['name' => 'top_rated'], ['label' => 'Top Rated'])->id,
        ];

        $attributeCache = [];
        $getAttributeId = function (string $name) use (&$attributeCache): int {
            if (!isset($attributeCache[$name])) {
                $attributeCache[$name] = ProductAttribute::firstOrCreate(['name' => $name])->id;
            }
            return $attributeCache[$name];
        };

        $blueprints = $this->categoryBlueprints();
        $totalCreated = 0;

        foreach ($blueprints as $categoryName => $config) {
            $category = ProductCategory::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName, 'is_active' => true, 'order' => 0]
            );

            $images = $config['images'];
            $count = 0;

            foreach ($config['models'] as $modelIdx => $model) {
                foreach ($config['editions'] as $edIdx => $edition) {
                    if ($count >= 25) {
                        break 2;
                    }

                    $count++;
                    $totalCreated++;

                    $brand = $model['brand'];
                    $baseName = $model['name'];
                    $fullName = trim("{$brand} {$baseName} {$edition['suffix']}");
                    $code = 'CAT-' . strtoupper(substr(Str::slug($categoryName, ''), 0, 6)) . '-' . str_pad((string) $count, 3, '0', STR_PAD_LEFT);

                    $price = round($model['base_price'] * $edition['price_mult'], 2);
                    $hasSale = ($count % 3 === 0);
                    $specialPrice = $hasSale ? round($price * (0.80 + (($count % 12) / 100)), 2) : null;

                    $coverImage = $images[($modelIdx + $edIdx) % count($images)];
                    $secondImage = $images[($modelIdx + $edIdx + 1) % count($images)];

                    $shortDesc = str_replace(
                        ['{brand}', '{model}', '{edition}', '{feature}'],
                        [$brand, $baseName, $edition['label'], $model['feature']],
                        $config['short_template']
                    );

                    $fullDesc = '<p>' . $shortDesc . '</p><p>' . str_replace(
                        ['{brand}', '{model}', '{edition}', '{feature}', '{spec_highlight}'],
                        [$brand, $baseName, $edition['label'], $model['feature'], $edition['spec_highlight']],
                        $config['desc_template']
                    ) . '</p>';

                    $product = Product::updateOrCreate(
                        ['product_code' => $code],
                        [
                            'name'                => $fullName,
                            'brand'               => $brand,
                            'sku'                 => 'SKU-' . strtoupper(Str::slug($brand)) . '-' . $code,
                            'short_description'   => $shortDesc,
                            'description'         => $fullDesc,
                            'price'               => $price,
                            'special_price'       => $specialPrice,
                            'quantity'            => 10 + (($count * 7 + $modelIdx * 3) % 90),
                            'product_category_id' => $category->id,
                            'product_status_id'   => $publishedStatus->id,
                            'color'               => $config['colors'],
                            'size'                => $config['sizes'],
                            'image'               => $coverImage,
                            'view_count'          => 150 + (($count * 173 + $modelIdx * 97) % 4800),
                        ]
                    );

                    // Attach 1-2 sections deterministically
                    $assignedSections = [$sectionsMap['featured']];
                    if ($count % 2 === 0) {
                        $assignedSections[] = $sectionsMap['best_seller'];
                    } elseif ($count % 5 === 0) {
                        $assignedSections[] = $sectionsMap['new_arrival'];
                    }
                    $product->sections()->syncWithoutDetaching($assignedSections);

                    // Seed gallery only if empty
                    if ($product->images()->count() === 0) {
                        ProductImage::create([
                            'product_id' => $product->id,
                            'path'       => $coverImage,
                            'position'   => 1,
                            'is_cover'   => true,
                        ]);
                        ProductImage::create([
                            'product_id' => $product->id,
                            'path'       => $secondImage,
                            'position'   => 2,
                            'is_cover'   => false,
                        ]);
                    }

                    // Attach structured specs (idempotent sync)
                    $syncSpecs = [];
                    $pos = 1;
                    $mergedSpecs = array_merge($model['specs'], $edition['specs']);
                    foreach ($mergedSpecs as $spec) {
                        $attrId = $getAttributeId($spec['attribute']);
                        $syncSpecs[$attrId] = [
                            'value'    => $spec['value'],
                            'unit'     => $spec['unit'] ?? null,
                            'position' => $pos++,
                        ];
                    }
                    $product->specifications()->syncWithoutDetaching($syncSpecs);
                }
            }
        }

        $this->command?->info("ProductSeeder: Seeded {$totalCreated} realistic products across 20 categories!");
    }

    /**
     * Structured real-world catalog blueprints for all 20 child categories.
     * 5 real product lines × 5 realistic editions/configurations = 25 products per category (500 total).
     */
    private function categoryBlueprints(): array
    {
        return [
            'Smartphones' => [
                'colors' => ['Titanium Black', 'Silver', 'Midnight Blue', 'Emerald Green'],
                'sizes'  => ['128GB', '256GB', '512GB'],
                'short_template' => '{brand} {model} ({edition}) featuring {feature} and 5G flagship performance.',
                'desc_template'  => 'Engineered by {brand}, the {model} in {edition} delivers {feature}, {spec_highlight}, all-day battery life, and fast USB-C charging.',
                'images' => [
                    'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1598327105666-5b89351aff97?auto=format&fit=crop&w=800&q=80',
                ],
                'models' => [
                    ['brand' => 'Apple',   'name' => 'iPhone 15',          'base_price' => 799,  'feature' => 'Super Retina XDR OLED display and A16 Bionic chip', 'specs' => [['attribute' => 'Processor', 'value' => 'Apple A16 Bionic'], ['attribute' => 'OS', 'value' => 'iOS 18']]],
                    ['brand' => 'Samsung', 'name' => 'Galaxy S24+',        'base_price' => 999,  'feature' => 'QHD+ Dynamic AMOLED 2X 120Hz display and Galaxy AI', 'specs' => [['attribute' => 'Processor', 'value' => 'Snapdragon 8 Gen 3'], ['attribute' => 'OS', 'value' => 'Android 14']]],
                    ['brand' => 'Google',  'name' => 'Pixel 9',            'base_price' => 799,  'feature' => 'Tensor G4 AI processor and 50MP Octa PD camera', 'specs' => [['attribute' => 'Processor', 'value' => 'Google Tensor G4'], ['attribute' => 'OS', 'value' => 'Android 15']]],
                    ['brand' => 'OnePlus', 'name' => '12R 5G',             'base_price' => 499,  'feature' => '100W SuperVOOC charging and 5500mAh battery', 'specs' => [['attribute' => 'Processor', 'value' => 'Snapdragon 8 Gen 2'], ['attribute' => 'Battery', 'value' => '5500', 'unit' => 'mAh']]],
                    ['brand' => 'Xiaomi',  'name' => '14 Ultra Leica',     'base_price' => 1199, 'feature' => 'Leica Summilux quad 50MP variable-aperture camera', 'specs' => [['attribute' => 'Camera', 'value' => 'Quad 50MP Leica'], ['attribute' => 'Display', 'value' => '6.73 LTPO AMOLED', 'unit' => 'in']]],
                ],
                'editions' => [
                    ['suffix' => '128GB Unlocked',     'label' => '128GB Unlocked',     'price_mult' => 1.00, 'spec_highlight' => '128GB NVMe storage with dual eSIM', 'specs' => [['attribute' => 'Storage', 'value' => '128', 'unit' => 'GB']]],
                    ['suffix' => '256GB Unlocked',     'label' => '256GB Unlocked',     'price_mult' => 1.10, 'spec_highlight' => '256GB high-speed UFS storage', 'specs' => [['attribute' => 'Storage', 'value' => '256', 'unit' => 'GB']]],
                    ['suffix' => '512GB Pro Edition',  'label' => '512GB Pro Edition',  'price_mult' => 1.22, 'spec_highlight' => '512GB creator storage for 4K ProRes video', 'specs' => [['attribute' => 'Storage', 'value' => '512', 'unit' => 'GB']]],
                    ['suffix' => '1TB Creator Bundle', 'label' => '1TB Creator Bundle', 'price_mult' => 1.35, 'spec_highlight' => '1TB maximum storage with MagSafe charger', 'specs' => [['attribute' => 'Storage', 'value' => '1', 'unit' => 'TB']]],
                    ['suffix' => 'Global Dual-SIM',    'label' => 'Global Dual-SIM',    'price_mult' => 1.05, 'spec_highlight' => 'worldwide 5G mmWave & Sub-6 roaming bands', 'specs' => [['attribute' => 'Network', 'value' => '5G Dual-SIM Unlocked']]],
                ],
            ],

            'Laptops' => [
                'colors' => ['Space Gray', 'Silver', 'Obsidian Black'],
                'sizes'  => ['14-inch', '15.6-inch', '16-inch'],
                'short_template' => '{brand} {model} {edition} ultrabook with {feature} for developers and creators.',
                'desc_template'  => 'Built for demanding workflows, the {brand} {model} ({edition}) pairs {feature} with {spec_highlight} and whisper-quiet thermal cooling.',
                'images' => [
                    'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?auto=format&fit=crop&w=800&q=80',
                ],
                'models' => [
                    ['brand' => 'Apple',  'name' => 'MacBook Air M3',         'base_price' => 1099, 'feature' => 'fanless M3 silicon and 18-hour battery life', 'specs' => [['attribute' => 'Processor', 'value' => 'Apple M3'], ['attribute' => 'OS', 'value' => 'macOS']]],
                    ['brand' => 'Dell',   'name' => 'XPS 15 OLED',            'base_price' => 1599, 'feature' => '3.5K InfinityEdge OLED touchscreen and CNC aluminum chassis', 'specs' => [['attribute' => 'Display', 'value' => '15.6 3.5K OLED', 'unit' => 'in'], ['attribute' => 'Processor', 'value' => 'Intel Core Ultra 7']]],
                    ['brand' => 'Lenovo', 'name' => 'ThinkPad X1 Carbon G12', 'base_price' => 1649, 'feature' => 'MIL-STD-810H carbon-fiber build and legendary keyboard', 'specs' => [['attribute' => 'Weight', 'value' => '1.09', 'unit' => 'kg'], ['attribute' => 'Processor', 'value' => 'Intel Core Ultra 7 vPro']]],
                    ['brand' => 'ASUS',   'name' => 'ROG Zephyrus G16',       'base_price' => 1899, 'feature' => '240Hz ROG Nebula OLED and NVIDIA GeForce RTX 4070', 'specs' => [['attribute' => 'GPU', 'value' => 'NVIDIA RTX 4070 8GB'], ['attribute' => 'Refresh Rate', 'value' => '240', 'unit' => 'Hz']]],
                    ['brand' => 'HP',     'name' => 'Spectre x360 14',        'base_price' => 1399, 'feature' => '2-in-1 convertible 2.8K OLED touch display with MPP stylus', 'specs' => [['attribute' => 'Form Factor', 'value' => '2-in-1 Convertible'], ['attribute' => 'Display', 'value' => '14 2.8K OLED', 'unit' => 'in']]],
                ],
                'editions' => [
                    ['suffix' => '16GB / 512GB SSD', 'label' => '16GB / 512GB', 'price_mult' => 1.00, 'spec_highlight' => '16GB LPDDR5X RAM and 512GB PCIe Gen4 SSD', 'specs' => [['attribute' => 'RAM', 'value' => '16', 'unit' => 'GB'], ['attribute' => 'Storage', 'value' => '512', 'unit' => 'GB']]],
                    ['suffix' => '16GB / 1TB SSD',   'label' => '16GB / 1TB',   'price_mult' => 1.12, 'spec_highlight' => '16GB RAM and fast 1TB NVMe SSD', 'specs' => [['attribute' => 'RAM', 'value' => '16', 'unit' => 'GB'], ['attribute' => 'Storage', 'value' => '1', 'unit' => 'TB']]],
                    ['suffix' => '32GB / 1TB SSD',   'label' => '32GB / 1TB',   'price_mult' => 1.25, 'spec_highlight' => '32GB RAM tailored for Docker, ML, and 4K editing', 'specs' => [['attribute' => 'RAM', 'value' => '32', 'unit' => 'GB'], ['attribute' => 'Storage', 'value' => '1', 'unit' => 'TB']]],
                    ['suffix' => '32GB / 2TB SSD',   'label' => '32GB / 2TB',   'price_mult' => 1.38, 'spec_highlight' => '32GB RAM with massive 2TB Gen4 NVMe storage', 'specs' => [['attribute' => 'RAM', 'value' => '32', 'unit' => 'GB'], ['attribute' => 'Storage', 'value' => '2', 'unit' => 'TB']]],
                    ['suffix' => '64GB Workstation', 'label' => '64GB / 2TB',   'price_mult' => 1.55, 'spec_highlight' => '64GB workstation memory for local AI & compilation', 'specs' => [['attribute' => 'RAM', 'value' => '64', 'unit' => 'GB'], ['attribute' => 'Storage', 'value' => '2', 'unit' => 'TB']]],
                ],
            ],

            'Audio' => [
                'colors' => ['Matte Black', 'Platinum Silver', 'Midnight Blue', 'Sandstone'],
                'sizes'  => ['Standard'],
                'short_template' => '{brand} {model} {edition} delivering {feature} with studio-grade acoustic clarity.',
                'desc_template'  => 'Experience immersive sound with the {brand} {model} ({edition}), engineered with {feature} and {spec_highlight}.',
                'images' => [
                    'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1583394838336-acd977736f90?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1546435770-a3e426bf472b?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?auto=format&fit=crop&w=800&q=80',
                ],
                'models' => [
                    ['brand' => 'Sony',       'name' => 'WF-1000XM5 Earbuds',      'base_price' => 279, 'feature' => 'dual-processor Active Noise Cancelling and LDAC Hi-Res audio', 'specs' => [['attribute' => 'Driver', 'value' => '8.4mm Dynamic Driver X'], ['attribute' => 'ANC', 'value' => 'Adaptive Multi-Mic ANC']]],
                    ['brand' => 'Bose',       'name' => 'QuietComfort Ultra',      'base_price' => 429, 'feature' => 'CustomTune spatial audio and class-leading noise cancellation', 'specs' => [['attribute' => 'Battery', 'value' => '24', 'unit' => 'hours'], ['attribute' => 'Audio', 'value' => 'Bose Immersive Spatial Audio']]],
                    ['brand' => 'Sennheiser', 'name' => 'Momentum 4 Wireless',     'base_price' => 349, 'feature' => 'audiophile 42mm transducers and 60-hour battery life', 'specs' => [['attribute' => 'Battery', 'value' => '60', 'unit' => 'hours'], ['attribute' => 'Codec', 'value' => 'aptX Adaptive / AAC']]],
                    ['brand' => 'JBL',        'name' => 'Charge 5 Wi-Fi Speaker',  'base_price' => 199, 'feature' => 'IP67 waterproof rugged build and deep dual passive radiators', 'specs' => [['attribute' => 'Waterproof', 'value' => 'IP67 Dust & Waterproof'], ['attribute' => 'Battery', 'value' => '20', 'unit' => 'hours']]],
                    ['brand' => 'Sonos',      'name' => 'Era 300 Dolby Atmos',     'base_price' => 449, 'feature' => 'six directional drivers for true room-filling spatial audio', 'specs' => [['attribute' => 'Connectivity', 'value' => 'Wi-Fi 6, Bluetooth 5.0, AirPlay 2'], ['attribute' => 'Audio', 'value' => 'Dolby Atmos Spatial']]],
                ],
                'editions' => [
                    ['suffix' => 'Standard Edition',  'label' => 'Standard',        'price_mult' => 1.00, 'spec_highlight' => 'multipoint Bluetooth 5.3 pairing', 'specs' => [['attribute' => 'Connectivity', 'value' => 'Bluetooth 5.3 Multipoint']]],
                    ['suffix' => 'Travel Case Pack',  'label' => 'Travel Bundle',   'price_mult' => 1.08, 'spec_highlight' => 'hard-shell travel case and airplane adapter', 'specs' => [['attribute' => 'Included', 'value' => 'Hard Travel Case + USB-C Cable']]],
                    ['suffix' => 'Studio Edition',    'label' => 'Studio Tuned',    'price_mult' => 1.15, 'spec_highlight' => 'flat reference EQ profile and braided AUX cable', 'specs' => [['attribute' => 'Tuning', 'value' => 'Reference Studio EQ']]],
                    ['suffix' => 'Midnight Limited',  'label' => 'Limited Finish',  'price_mult' => 1.05, 'spec_highlight' => 'anodized matte midnight aluminum accents', 'specs' => [['attribute' => 'Finish', 'value' => 'Anodized Matte Edition']]],
                    ['suffix' => 'Dual Pack Bundle',  'label' => 'Stereo Pair',     'price_mult' => 1.85, 'spec_highlight' => 'matched stereo pair for dual-listener or room setup', 'specs' => [['attribute' => 'Pack', 'value' => '2-Unit Stereo Bundle']]],
                ],
            ],

            'Gaming' => [
                'colors' => ['Carbon Black', 'Glacier White', 'RGB Matte'],
                'sizes'  => ['Standard'],
                'short_template' => '{brand} {model} {edition} built for competitive esports with {feature}.',
                'desc_template'  => 'Dominate every lobby with the {brand} {model} ({edition}), featuring {feature} and {spec_highlight}.',
                'images' => [
                    'https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1593305841991-05c297ba4575?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1612287230202-1ff1d85d1bdf?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=800&q=80',
                ],
                'models' => [
                    ['brand' => 'Razer',       'name' => 'DeathAdder V3 Pro Mouse',  'base_price' => 149, 'feature' => '63g ultra-lightweight shell and 30K Focus Pro optical sensor', 'specs' => [['attribute' => 'Sensor', 'value' => '30,000 DPI Optical'], ['attribute' => 'Weight', 'value' => '63', 'unit' => 'g']]],
                    ['brand' => 'Logitech G',  'name' => 'PRO X TKL Rapid Keyboard', 'base_price' => 189, 'feature' => 'magnetic analog Rapid Trigger switches and Lightspeed wireless', 'specs' => [['attribute' => 'Switches', 'value' => 'Magnetic Analog Rapid Trigger'], ['attribute' => 'Layout', 'value' => 'Tenkeyless (TKL)']]],
                    ['brand' => 'SteelSeries', 'name' => 'Arctis Nova Pro Wireless', 'base_price' => 349, 'feature' => 'hot-swappable dual batteries and 360-degree Spatial Audio', 'specs' => [['attribute' => 'Connectivity', 'value' => '2.4GHz + Bluetooth Simultaneous'], ['attribute' => 'ANC', 'value' => '4-Mic Hybrid ANC']]],
                    ['brand' => 'Nintendo',    'name' => 'Switch OLED Console',      'base_price' => 349, 'feature' => '7-inch vibrant OLED screen and wired LAN dock', 'specs' => [['attribute' => 'Display', 'value' => '7.0 OLED', 'unit' => 'in'], ['attribute' => 'Storage', 'value' => '64', 'unit' => 'GB']]],
                    ['brand' => 'LG UltraGear','name' => '27" 240Hz OLED Monitor',   'base_price' => 799, 'feature' => '0.03ms GtG response time and NVIDIA G-SYNC Compatible', 'specs' => [['attribute' => 'Refresh Rate', 'value' => '240', 'unit' => 'Hz'], ['attribute' => 'Response Time', 'value' => '0.03', 'unit' => 'ms']]],
                ],
                'editions' => [
                    ['suffix' => 'Esports Edition',  'label' => 'Esports',      'price_mult' => 1.00, 'spec_highlight' => 'zero-latency 1000Hz polling', 'specs' => [['attribute' => 'Polling Rate', 'value' => '1000', 'unit' => 'Hz']]],
                    ['suffix' => '8K HyperPolling',  'label' => '8K Dongle',    'price_mult' => 1.12, 'spec_highlight' => 'true 8000Hz wireless HyperPolling dongle included', 'specs' => [['attribute' => 'Polling Rate', 'value' => '8000', 'unit' => 'Hz']]],
                    ['suffix' => 'White Edition',    'label' => 'Glacier White','price_mult' => 1.04, 'spec_highlight' => 'clean matte Glacier White tournament finish', 'specs' => [['attribute' => 'Finish', 'value' => 'Glacier White']]],
                    ['suffix' => 'Pro Streamer Kit', 'label' => 'Streamer Kit', 'price_mult' => 1.22, 'spec_highlight' => 'bundled desk mat and braided coiled cable', 'specs' => [['attribute' => 'Bundle', 'value' => 'Pro Streamer Accessories']]],
                    ['suffix' => 'Collector RGB',    'label' => 'RGB Sync',     'price_mult' => 1.09, 'spec_highlight' => 'per-zone 16.8M color customizable RGB lighting', 'specs' => [['attribute' => 'Lighting', 'value' => '16.8M Addressable RGB']]],
                ],
            ],

            "Men's Fashion" => [
                'colors' => ['Navy', 'Charcoal', 'Olive', 'Black', 'Stone'],
                'sizes'  => ['S', 'M', 'L', 'XL', 'XXL'],
                'short_template' => '{brand} {model} ({edition}) crafted with {feature} for timeless everyday style.',
                'desc_template'  => 'Tailored for comfort and durability, the {brand} {model} in {edition} features {feature} and {spec_highlight}.',
                'images' => [
                    'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=800&q=80',
                ],
                'models' => [
                    ['brand' => 'Ralph Lauren', 'name' => 'Classic Mesh Polo Shirt',  'base_price' => 110, 'feature' => 'breathable 100% cotton piqué weave and signature pony embroidery', 'specs' => [['attribute' => 'Material', 'value' => '100% Cotton Pique']]],
                    ['brand' => 'Carhartt',     'name' => 'Detroit Duck Work Jacket', 'base_price' => 139, 'feature' => '12oz firm-hand ring-spun cotton duck canvas and blanket lining', 'specs' => [['attribute' => 'Material', 'value' => '12oz Cotton Duck Canvas']]],
                    ['brand' => 'Uniqlo',       'name' => 'Extra Fine Merino Sweater','base_price' => 49,  'feature' => '19.5-micron 100% Australian merino wool with machine-washable finish', 'specs' => [['attribute' => 'Material', 'value' => '100% Merino Wool']]],
                    ['brand' => 'Patagonia',    'name' => 'Better Sweater Fleece',    'base_price' => 139, 'feature' => 'recycled polyester sweater-knit fleece with Fair Trade certification', 'specs' => [['attribute' => 'Material', 'value' => '100% Recycled Polyester Fleece']]],
                    ['brand' => 'Banana Republic','name' => 'Traveler Slim Chino',    'base_price' => 98,  'feature' => 'high-recovery Italian stretch cotton twill for all-day movement', 'specs' => [['attribute' => 'Material', 'value' => '98% Cotton / 2% Elastane']]],
                ],
                'editions' => [
                    ['suffix' => 'Slim Fit',       'label' => 'Slim Fit',       'price_mult' => 1.00, 'spec_highlight' => 'modern tapered silhouette', 'specs' => [['attribute' => 'Fit', 'value' => 'Slim Fit']]],
                    ['suffix' => 'Regular Fit',    'label' => 'Regular Fit',    'price_mult' => 1.00, 'spec_highlight' => 'classic comfortable drape', 'specs' => [['attribute' => 'Fit', 'value' => 'Regular Fit']]],
                    ['suffix' => 'Relaxed Fit',    'label' => 'Relaxed Fit',    'price_mult' => 1.03, 'spec_highlight' => 'roomy street-ready cut', 'specs' => [['attribute' => 'Fit', 'value' => 'Relaxed Fit']]],
                    ['suffix' => 'All-Season',     'label' => 'All-Season',     'price_mult' => 1.08, 'spec_highlight' => 'moisture-wicking year-round weight', 'specs' => [['attribute' => 'Season', 'value' => 'All-Season']]],
                    ['suffix' => 'Heritage Wash',  'label' => 'Heritage Wash',  'price_mult' => 1.12, 'spec_highlight' => 'garment-dyed vintage softness', 'specs' => [['attribute' => 'Finish', 'value' => 'Garment-Dyed Vintage Wash']]],
                ],
            ],

            "Women's Fashion" => [
                'colors' => ['Ivory', 'Camel', 'Black', 'Sage Green', 'Burgundy'],
                'sizes'  => ['XS', 'S', 'M', 'L', 'XL'],
                'short_template' => '{brand} {model} ({edition}) designed with {feature} for effortless elegance.',
                'desc_template'  => 'Refined and versatile, the {brand} {model} in {edition} combines {feature} with {spec_highlight}.',
                'images' => [
                    'https://images.unsplash.com/photo-1539533018447-63fcce2678e3?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1485968579580-b6d095142e6e?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=800&q=80',
                ],
                'models' => [
                    ['brand' => 'Zara',        'name' => 'Tailored Wool Blend Coat',   'base_price' => 189, 'feature' => 'structured notch lapel and double-breasted Italian wool blend', 'specs' => [['attribute' => 'Material', 'value' => '75% Wool / 25% Polyamide']]],
                    ['brand' => 'Lululemon',   'name' => 'Align High-Rise Legging',    'base_price' => 98,  'feature' => 'buttery-soft weightless Nulu four-way stretch fabric', 'specs' => [['attribute' => 'Fabric', 'value' => 'Nulu 4-Way Stretch']]],
                    ['brand' => 'Mango',       'name' => 'Satin Midi Slip Dress',      'base_price' => 89,  'feature' => 'fluid bias-cut lustrous satin with adjustable straps', 'specs' => [['attribute' => 'Style', 'value' => 'Bias-Cut Midi Dress']]],
                    ['brand' => 'Aritzia',     'name' => 'Super Puff Down Jacket',     'base_price' => 250, 'feature' => '700+ fill-power responsibly sourced goose down rated to -30C', 'specs' => [['attribute' => 'Insulation', 'value' => '700+ Fill Goose Down']]],
                    ['brand' => 'Everlane',    'name' => 'Grade-A Cashmere Crewneck',  'base_price' => 168, 'feature' => 'GCS-certified plush 2-ply Inner Mongolian cashmere', 'specs' => [['attribute' => 'Material', 'value' => '100% Grade-A Cashmere']]],
                ],
                'editions' => [
                    ['suffix' => 'Classic Cut',     'label' => 'Classic',       'price_mult' => 1.00, 'spec_highlight' => 'flattering everyday proportions', 'specs' => [['attribute' => 'Fit', 'value' => 'Classic']]],
                    ['suffix' => 'Petite Length',   'label' => 'Petite',        'price_mult' => 1.00, 'spec_highlight' => 'proportioned specifically for 5\'4" and under', 'specs' => [['attribute' => 'Length', 'value' => 'Petite']]],
                    ['suffix' => 'Tall Length',     'label' => 'Tall',          'price_mult' => 1.04, 'spec_highlight' => 'extended sleeve and hem tailoring', 'specs' => [['attribute' => 'Length', 'value' => 'Tall']]],
                    ['suffix' => 'Luxe Capsule',    'label' => 'Luxe Capsule',  'price_mult' => 1.15, 'spec_highlight' => 'upgraded brushed satin lining and horn buttons', 'specs' => [['attribute' => 'Collection', 'value' => 'Luxe Capsule']]],
                    ['suffix' => 'Spring Pastel',   'label' => 'Seasonal Edit', 'price_mult' => 1.05, 'spec_highlight' => 'limited seasonal color palette', 'specs' => [['attribute' => 'Season', 'value' => 'Spring / Summer']]],
                ],
            ],

            "Kids' Fashion" => [
                'colors' => ['Sky Blue', 'Coral Pink', 'Sunny Yellow', 'Forest Green'],
                'sizes'  => ['4T', '6Y', '8Y', '10Y', '12Y'],
                'short_template' => '{brand} {model} ({edition}) made with {feature} for active play.',
                'desc_template'  => 'Built to survive playground adventures and frequent washes, the {brand} {model} ({edition}) offers {feature} and {spec_highlight}.',
                'images' => [
                    'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1503919545889-aef636e10ad4?auto=format&fit=crop&w=800&q=80',
                ],
                'models' => [
                    ['brand' => 'Gap Kids',      'name' => 'Organic Cotton Hoodie',    'base_price' => 38, 'feature' => 'ultra-soft 100% organic brushed fleece and kangaroo pocket', 'specs' => [['attribute' => 'Material', 'value' => '100% Organic Cotton']]],
                    ['brand' => 'The North Face','name' => 'Youth Resolve Rain Jacket','base_price' => 75, 'feature' => 'waterproof breathable DryVent 2L shell with reflective logo', 'specs' => [['attribute' => 'Weatherproof', 'value' => 'DryVent 2L Waterproof']]],
                    ['brand' => 'Nike Kids',     'name' => 'Dri-FIT Tracksuit Set',    'base_price' => 65, 'feature' => 'sweat-wicking athletic knit jacket and tapered joggers', 'specs' => [['attribute' => 'Technology', 'value' => 'Nike Dri-FIT']]],
                    ['brand' => 'Carter\'s',     'name' => 'Cozy Pajama 4-Piece Set',  'base_price' => 32, 'feature' => 'snug-fit ribbed 100% cotton sleepwear with playful prints', 'specs' => [['attribute' => 'Pack', 'value' => '4-Piece Set']]],
                    ['brand' => 'Adidas Kids',   'name' => 'Grand Court Sneakers',     'base_price' => 48, 'feature' => 'hook-and-loop easy straps with cushioned Cloudfoam sockliner', 'specs' => [['attribute' => 'Closure', 'value' => 'Hook-and-Loop Straps']]],
                ],
                'editions' => [
                    ['suffix' => 'Everyday Pack',   'label' => 'Everyday',      'price_mult' => 1.00, 'spec_highlight' => 'tagless itch-free neck label', 'specs' => [['attribute' => 'Care', 'value' => 'Machine Washable']]],
                    ['suffix' => 'Playproof',       'label' => 'Reinforced',    'price_mult' => 1.08, 'spec_highlight' => 'double-stitched knees and elbows', 'specs' => [['attribute' => 'Durability', 'value' => 'Reinforced Seams']]],
                    ['suffix' => 'Value 2-Pack',    'label' => '2-Pack Bundle', 'price_mult' => 1.65, 'spec_highlight' => 'two coordinating colorways in one pack', 'specs' => [['attribute' => 'Bundle', 'value' => '2-Pack Value']]],
                    ['suffix' => 'Cozy Fleece',     'label' => 'Winter Lined',  'price_mult' => 1.15, 'spec_highlight' => 'warm sherpa micro-fleece interior', 'specs' => [['attribute' => 'Lining', 'value' => 'Micro-Fleece']]],
                    ['suffix' => 'Back-to-School',  'label' => 'School Edition','price_mult' => 1.05, 'spec_highlight' => 'stain-resistant easy-care finish', 'specs' => [['attribute' => 'Finish', 'value' => 'Stain-Resistant']]],
                ],
            ],

            'Footwear' => [
                'colors' => ['Triple Black', 'Cloud White', 'Volt/Grey', 'Navy/Red'],
                'sizes'  => ['US 7', 'US 8', 'US 9', 'US 10', 'US 11', 'US 12'],
                'short_template' => '{brand} {model} ({edition}) engineered with {feature} for responsive cushioning.',
                'desc_template'  => 'Step into all-day energy return with the {brand} {model} ({edition}), featuring {feature} and {spec_highlight}.',
                'images' => [
                    'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=800&q=80',
                ],
                'models' => [
                    ['brand' => 'Nike',        'name' => 'Air Zoom Pegasus 41',   'base_price' => 140, 'feature' => 'ReactX foam midsole and dual Air Zoom units', 'specs' => [['attribute' => 'Cushioning', 'value' => 'ReactX + Dual Air Zoom']]],
                    ['brand' => 'Adidas',      'name' => 'Ultraboost Light',      'base_price' => 190, 'feature' => '30% lighter Light BOOST midsole and Primeknit+ upper', 'specs' => [['attribute' => 'Midsole', 'value' => 'Light BOOST Foam']]],
                    ['brand' => 'New Balance', 'name' => '990v6 Made in USA',     'base_price' => 200, 'feature' => 'FuelCell foam cushioning with premium pigskin suede overlays', 'specs' => [['attribute' => 'Upper', 'value' => 'Suede & Breathable Mesh']]],
                    ['brand' => 'HOKA',        'name' => 'Clifton 9 Running Shoe','base_price' => 145, 'feature' => 'featherweight CMEVA max-cushion rocker geometry', 'specs' => [['attribute' => 'Drop', 'value' => '5', 'unit' => 'mm']]],
                    ['brand' => 'Timberland',  'name' => '6-Inch Waterproof Boot','base_price' => 198, 'feature' => 'seam-sealed Better Leather nubuck and 400g PrimaLoft insulation', 'specs' => [['attribute' => 'Waterproof', 'value' => 'Seam-Sealed Leather']]],
                ],
                'editions' => [
                    ['suffix' => 'Standard Width', 'label' => 'Standard (D)', 'price_mult' => 1.00, 'spec_highlight' => 'anatomical standard D-width fit', 'specs' => [['attribute' => 'Width', 'value' => 'Standard (D)']]],
                    ['suffix' => 'Wide Fit (2E)',  'label' => 'Wide (2E)',    'price_mult' => 1.02, 'spec_highlight' => 'extra forefoot room for wide feet', 'specs' => [['attribute' => 'Width', 'value' => 'Wide (2E)']]],
                    ['suffix' => 'GORE-TEX',       'label' => 'GORE-TEX',     'price_mult' => 1.18, 'spec_highlight' => 'waterproof breathable GORE-TEX membrane', 'specs' => [['attribute' => 'Waterproof', 'value' => 'GORE-TEX Membrane']]],
                    ['suffix' => 'SE Reflective',  'label' => 'Reflective',   'price_mult' => 1.08, 'spec_highlight' => '360-degree high-visibility night reflectivity', 'specs' => [['attribute' => 'Safety', 'value' => '3M Reflective Accents']]],
                    ['suffix' => 'Pro Carbon',     'label' => 'Race Edition', 'price_mult' => 1.28, 'spec_highlight' => 'stiff propulsion plate for race-day speed', 'specs' => [['attribute' => 'Plate', 'value' => 'Carbon Propulsion Plate']]],
                ],
            ],

            'Furniture' => [
                'colors' => ['Walnut', 'Natural Oak', 'Matte Black', 'Charcoal Fabric'],
                'sizes'  => ['Standard'],
                'short_template' => '{brand} {model} ({edition}) crafted with {feature} for ergonomic living and workspaces.',
                'desc_template'  => 'Upgrade your interior with the {brand} {model} ({edition}), combining {feature} with {spec_highlight}.',
                'images' => [
                    'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1592078615290-033ee584e267?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1538688525198-9b88f6f53126?auto=format&fit=crop&w=800&q=80',
                ],
                'models' => [
                    ['brand' => 'Steelcase',  'name' => 'Leap V2 Ergonomic Chair', 'base_price' => 999,  'feature' => 'LiveBack dynamic lumbar technology and 4D adjustable armrests', 'specs' => [['attribute' => 'Warranty', 'value' => '12 Years']]],
                    ['brand' => 'UPLIFT Desk','name' => 'V2 Standing Desk',        'base_price' => 699,  'feature' => 'dual-motor quiet height adjustment from 25.3" to 50.9"', 'specs' => [['attribute' => 'Lift Capacity', 'value' => '355', 'unit' => 'lbs']]],
                    ['brand' => 'IKEA',       'name' => 'KIVIK 3-Seat Sofa',       'base_price' => 849,  'feature' => 'deep pocket-spring memory foam cushions and washable covers', 'specs' => [['attribute' => 'Seating', 'value' => '3-Seater']]],
                    ['brand' => 'Article',    'name' => 'Seno Solid Oak Dining Table','base_price' => 1099,'feature' => 'FSC-certified solid European oak top with mid-century tapered legs', 'specs' => [['attribute' => 'Material', 'value' => 'Solid European Oak']]],
                    ['brand' => 'CB2',        'name' => 'Stairway Wall Bookcase',  'base_price' => 399,  'feature' => 'powder-coated architectural steel frame with 6 minimalist shelves', 'specs' => [['attribute' => 'Shelves', 'value' => '6 Tiers']]],
                ],
                'editions' => [
                    ['suffix' => 'Compact 48"',    'label' => 'Compact',       'price_mult' => 0.92, 'spec_highlight' => 'space-saving apartment footprint', 'specs' => [['attribute' => 'Size Class', 'value' => 'Compact']]],
                    ['suffix' => 'Standard 60"',   'label' => 'Standard',      'price_mult' => 1.00, 'spec_highlight' => 'balanced everyday proportions', 'specs' => [['attribute' => 'Size Class', 'value' => 'Standard']]],
                    ['suffix' => 'Executive 72"',  'label' => 'Executive',     'price_mult' => 1.18, 'spec_highlight' => 'spacious dual-monitor or family scale', 'specs' => [['attribute' => 'Size Class', 'value' => 'Executive / Large']]],
                    ['suffix' => 'Walnut Finish',  'label' => 'Walnut',        'price_mult' => 1.10, 'spec_highlight' => 'hand-rubbed American black walnut veneer', 'specs' => [['attribute' => 'Finish', 'value' => 'American Walnut']]],
                    ['suffix' => 'Pro Bundle',     'label' => 'Accessory Kit', 'price_mult' => 1.15, 'spec_highlight' => 'includes cable management tray and felt floor glides', 'specs' => [['attribute' => 'Included', 'value' => 'Cable Tray & Glides']]],
                ],
            ],

            'Kitchen Appliances' => [
                'colors' => ['Brushed Stainless', 'Matte Black', 'Empire Red', 'Cream'],
                'sizes'  => ['Standard'],
                'short_template' => '{brand} {model} ({edition}) with {feature} for restaurant-quality home cooking.',
                'desc_template'  => 'Cook faster and smarter with the {brand} {model} ({edition}), equipped with {feature} and {spec_highlight}.',
                'images' => [
                    'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1584269600464-37b1b58a9fe7?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=800&q=80',
                ],
                'models' => [
                    ['brand' => 'Breville',   'name' => 'Barista Express Impress', 'base_price' => 899, 'feature' => 'integrated conical burr grinder and assisted precision tamping', 'specs' => [['attribute' => 'Pressure', 'value' => '15', 'unit' => 'Bar']]],
                    ['brand' => 'Vitamix',    'name' => 'A3500 Ascent Blender',    'base_price' => 649, 'feature' => '2.2-HP commercial-grade motor with 5 touchscreen programs', 'specs' => [['attribute' => 'Power', 'value' => '1500', 'unit' => 'Watts']]],
                    ['brand' => 'Ninja',      'name' => 'Foodi DualZone Air Fryer','base_price' => 199, 'feature' => 'two independent 5-qt baskets with Smart Finish sync cooking', 'specs' => [['attribute' => 'Capacity', 'value' => '10', 'unit' => 'Quarts']]],
                    ['brand' => 'Instant Pot','name' => 'Pro 10-in-1 Pressure Cooker','base_price' => 149,'feature' => '28 customizable smart programs and easy-grip stainless inner pot', 'specs' => [['attribute' => 'Functions', 'value' => '10-in-1 Multi-Cooker']]],
                    ['brand' => 'Fellow',     'name' => 'Stagg EKG Pro Kettle',    'base_price' => 195, 'feature' => 'to-the-degree PID temperature control and precision pour-over spout', 'specs' => [['attribute' => 'Capacity', 'value' => '0.9', 'unit' => 'Liters']]],
                ],
                'editions' => [
                    ['suffix' => 'Stainless Steel', 'label' => 'Stainless',    'price_mult' => 1.00, 'spec_highlight' => 'fingerprint-resistant brushed stainless body', 'specs' => [['attribute' => 'Finish', 'value' => 'Brushed Stainless']]],
                    ['suffix' => 'Matte Black',     'label' => 'Matte Black',  'price_mult' => 1.04, 'spec_highlight' => 'modern powder-coated matte black finish', 'specs' => [['attribute' => 'Finish', 'value' => 'Matte Black']]],
                    ['suffix' => 'Chef Bundle',     'label' => 'Chef Bundle',  'price_mult' => 1.15, 'spec_highlight' => 'includes recipe book and stainless accessories', 'specs' => [['attribute' => 'Bundle', 'value' => 'Accessory Kit Included']]],
                    ['suffix' => 'XL Family Size',  'label' => 'Family XL',    'price_mult' => 1.18, 'spec_highlight' => 'expanded capacity for 6 to 8 servings', 'specs' => [['attribute' => 'Capacity Class', 'value' => 'Family XL']]],
                    ['suffix' => 'Smart Wi-Fi',     'label' => 'App Connected','price_mult' => 1.12, 'spec_highlight' => 'smartphone notifications and remote scheduling', 'specs' => [['attribute' => 'Connectivity', 'value' => 'Wi-Fi App Control']]],
                ],
            ],

            'Home Decor' => [
                'colors' => ['Warm Brass', 'Matte Black', 'Terracotta', 'Ivory Ceramic'],
                'sizes'  => ['Small', 'Medium', 'Large'],
                'short_template' => '{brand} {model} ({edition}) featuring {feature} to elevate any room.',
                'desc_template'  => 'Thoughtfully designed by {brand}, the {model} ({edition}) blends {feature} with {spec_highlight}.',
                'images' => [
                    'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1618220179428-22790b461013?auto=format&fit=crop&w=800&q=80',
                ],
                'models' => [
                    ['brand' => 'Nanoleaf',  'name' => 'Shapes Hexagon Light Panels', 'base_price' => 199, 'feature' => 'touch-reactive modular RGBW smart wall panels with Music Sync', 'specs' => [['attribute' => 'Smart Home', 'value' => 'Matter / HomeKit / Alexa']]],
                    ['brand' => 'Ruggable',  'name' => 'Washable Persian Area Rug',   'base_price' => 279, 'feature' => 'stain-resistant two-piece machine-washable woven rug system', 'specs' => [['attribute' => 'Care', 'value' => 'Machine Washable']]],
                    ['brand' => 'Diptyque',  'name' => 'Baies Scented Luxury Candle', 'base_price' => 74,  'feature' => 'blackcurrant leaves and Bulgarian rose with 60-hour burn time', 'specs' => [['attribute' => 'Burn Time', 'value' => '60', 'unit' => 'hours']]],
                    ['brand' => 'Govee',     'name' => 'Lyra RGBIC Smart Floor Lamp', 'base_price' => 149, 'feature' => 'slim minimalist corner silhouette with 64 dynamic lighting scenes', 'specs' => [['attribute' => 'Brightness', 'value' => '1500', 'unit' => 'Lumens']]],
                    ['brand' => 'Crate&Barrel','name' => 'Artisan Fluted Ceramic Vase','base_price' => 68, 'feature' => 'hand-thrown matte stoneware with organic architectural ridges', 'specs' => [['attribute' => 'Material', 'value' => 'Hand-Thrown Stoneware']]],
                ],
                'editions' => [
                    ['suffix' => 'Starter Set',    'label' => 'Starter',    'price_mult' => 1.00, 'spec_highlight' => 'ready to display right out of the box', 'specs' => [['attribute' => 'Edition', 'value' => 'Starter']]],
                    ['suffix' => 'Large Statement','label' => 'Large',      'price_mult' => 1.25, 'spec_highlight' => 'oversized focal-point dimensions', 'specs' => [['attribute' => 'Scale', 'value' => 'Statement Large']]],
                    ['suffix' => 'Warm Brass',     'label' => 'Brass',      'price_mult' => 1.08, 'spec_highlight' => 'hand-brushed warm satin brass accents', 'specs' => [['attribute' => 'Finish', 'value' => 'Warm Brass']]],
                    ['suffix' => 'Nordic Minimal', 'label' => 'Nordic',     'price_mult' => 1.05, 'spec_highlight' => 'Scandinavian neutral matte texture', 'specs' => [['attribute' => 'Style', 'value' => 'Scandinavian Minimal']]],
                    ['suffix' => 'Duo Value Set',  'label' => 'Set of 2',   'price_mult' => 1.75, 'spec_highlight' => 'coordinated pair for symmetrical styling', 'specs' => [['attribute' => 'Quantity', 'value' => 'Set of 2']]],
                ],
            ],

            'Bedding' => [
                'colors' => ['Crisp White', 'Oatmeal', 'Slate Gray', 'Sage'],
                'sizes'  => ['Twin', 'Full', 'Queen', 'King'],
                'short_template' => '{brand} {model} ({edition}) crafted with {feature} for hotel-grade sleep.',
                'desc_template'  => 'Sleep cooler and deeper with the {brand} {model} ({edition}), featuring {feature} and {spec_highlight}.',
                'images' => [
                    'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1584100936595-c0654b55a2e2?auto=format&fit=crop&w=800&q=80',
                ],
                'models' => [
                    ['brand' => 'Parachute',  'name' => 'Brushed Linen Sheet Set',     'base_price' => 249, 'feature' => '100% European flax linen garment-washed for airy softness', 'specs' => [['attribute' => 'Material', 'value' => '100% European Flax Linen']]],
                    ['brand' => 'Casper',     'name' => 'Humidity-Fighting Duvet',     'base_price' => 229, 'feature' => 'merino wool moisture layer and ethically sourced down fill', 'specs' => [['attribute' => 'Fill', 'value' => 'Down + Merino Wool']]],
                    ['brand' => 'Coop Sleep', 'name' => 'Original Adjustable Pillow',  'base_price' => 75,  'feature' => 'CertiPUR-US shredded memory foam customizable loft', 'specs' => [['attribute' => 'Certification', 'value' => 'CertiPUR-US & GREENGUARD Gold']]],
                    ['brand' => 'Bearaby',    'name' => 'Cotton Napper Weighted Blanket','base_price' => 199,'feature' => 'breathable hand-knitted organic cotton with zero glass beads', 'specs' => [['attribute' => 'Weight', 'value' => '15', 'unit' => 'lbs']]],
                    ['brand' => 'Boll & Branch','name' => 'Signature Hemmed Duvet Cover','base_price' => 269,'feature' => 'Fair Trade 100% organic long-staple cotton four-over-one weave', 'specs' => [['attribute' => 'Certification', 'value' => 'GOTS Organic & Fair Trade']]],
                ],
                'editions' => [
                    ['suffix' => 'Queen Size',       'label' => 'Queen',       'price_mult' => 1.00, 'spec_highlight' => 'tailored for standard 60x80" Queen beds', 'specs' => [['attribute' => 'Bed Size', 'value' => 'Queen']]],
                    ['suffix' => 'King Size',        'label' => 'King',        'price_mult' => 1.15, 'spec_highlight' => 'generously cut for 76x80" King beds', 'specs' => [['attribute' => 'Bed Size', 'value' => 'King']]],
                    ['suffix' => 'California King',  'label' => 'Cal King',    'price_mult' => 1.18, 'spec_highlight' => 'extra-long California King fit', 'specs' => [['attribute' => 'Bed Size', 'value' => 'California King']]],
                    ['suffix' => 'Cooling Edition',  'label' => 'Cooling',     'price_mult' => 1.12, 'spec_highlight' => 'moisture-wicking weave for hot sleepers', 'specs' => [['attribute' => 'Thermal', 'value' => 'Cooling Breathable']]],
                    ['suffix' => 'Complete Bundle',  'label' => 'Full Bundle', 'price_mult' => 1.35, 'spec_highlight' => 'includes matching shams and extra pillowcases', 'specs' => [['attribute' => 'Included', 'value' => 'Extra Pillow Shams']]],
                ],
            ],

            'Fiction' => [
                'colors' => ['Standard'],
                'sizes'  => ['Paperback', 'Hardcover', 'Collector Edition'],
                'short_template' => '{brand} presents {model} ({edition}) — {feature}.',
                'desc_template'  => 'An unforgettable modern classic published by {brand}: {model} ({edition}) brings {feature} with {spec_highlight}.',
                'images' => [
                    'https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=800&q=80',
                ],
                'models' => [
                    ['brand' => 'Tor Books',      'name' => 'The Three-Body Problem by Cixin Liu',   'base_price' => 18, 'feature' => 'Hugo Award-winning first-contact hard sci-fi epic', 'specs' => [['attribute' => 'Genre', 'value' => 'Science Fiction']]],
                    ['brand' => 'Orbit Books',    'name' => 'The Way of Kings by Brandon Sanderson', 'base_price' => 24, 'feature' => 'monumental high-fantasy opening of The Stormlight Archive', 'specs' => [['attribute' => 'Genre', 'value' => 'Epic Fantasy']]],
                    ['brand' => 'Vintage Books',  'name' => '1984 by George Orwell',                 'base_price' => 16, 'feature' => 'timeless dystopian masterpiece on surveillance and truth', 'specs' => [['attribute' => 'Genre', 'value' => 'Dystopian Classic']]],
                    ['brand' => 'HarperCollins',  'name' => 'The Alchemist by Paulo Coelho',         'base_price' => 17, 'feature' => 'beloved international fable about following your Personal Legend', 'specs' => [['attribute' => 'Genre', 'value' => 'Literary Fiction']]],
                    ['brand' => 'Del Rey',        'name' => 'Red Rising by Pierce Brown',            'base_price' => 19, 'feature' => 'adrenaline-fueled Martian rebellion sci-fi saga', 'specs' => [['attribute' => 'Genre', 'value' => 'Sci-Fi Thriller']]],
                ],
                'editions' => [
                    ['suffix' => 'Trade Paperback',   'label' => 'Paperback',        'price_mult' => 1.00, 'spec_highlight' => 'portable acid-free matte paperback', 'specs' => [['attribute' => 'Format', 'value' => 'Paperback']]],
                    ['suffix' => 'Hardcover Edition', 'label' => 'Hardcover',        'price_mult' => 1.55, 'spec_highlight' => 'smyth-sewn cloth hardcover with dust jacket', 'specs' => [['attribute' => 'Format', 'value' => 'Hardcover']]],
                    ['suffix' => 'Illustrated Deluxe','label' => 'Illustrated',      'price_mult' => 2.10, 'spec_highlight' => 'full-color endpapers, ribbon marker, and gilded edges', 'specs' => [['attribute' => 'Format', 'value' => 'Deluxe Illustrated']]],
                    ['suffix' => 'Anniversary Edition','label' => 'Anniversary',     'price_mult' => 1.75, 'spec_highlight' => 'includes new author foreword and bonus essay', 'specs' => [['attribute' => 'Bonus', 'value' => 'Author Foreword']]],
                    ['suffix' => 'Signed Bookplate',  'label' => 'Collector Signed', 'price_mult' => 2.40, 'spec_highlight' => 'numbered collector bookplate edition', 'specs' => [['attribute' => 'Edition', 'value' => 'Collector Bookplate']]],
                ],
            ],

            'Non-Fiction' => [
                'colors' => ['Standard'],
                'sizes'  => ['Paperback', 'Hardcover'],
                'short_template' => '{brand} publishes {model} ({edition}) — {feature}.',
                'desc_template'  => 'Essential reading for high performers and lifelong learners: {model} ({edition}) explores {feature} with {spec_highlight}.',
                'images' => [
                    'https://images.unsplash.com/photo-1589829085413-56de8ae18c73?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=800&q=80',
                ],
                'models' => [
                    ['brand' => 'Harriman House',  'name' => 'The Psychology of Money by Morgan Housel', 'base_price' => 20, 'feature' => '19 timeless lessons on wealth, compounding, and behavior', 'specs' => [['attribute' => 'Topic', 'value' => 'Personal Finance']]],
                    ['brand' => 'Grand Central',   'name' => 'Deep Work by Cal Newport',                 'base_price' => 22, 'feature' => 'rules for distraction-free focus in a noisy digital world', 'specs' => [['attribute' => 'Topic', 'value' => 'Productivity']]],
                    ['brand' => 'Harper',          'name' => 'Sapiens by Yuval Noah Harari',             'base_price' => 24, 'feature' => 'sweeping brief history of humankind from Stone Age to AI', 'specs' => [['attribute' => 'Topic', 'value' => 'History & Anthropology']]],
                    ['brand' => 'Farrar Straus',   'name' => 'Thinking, Fast and Slow by Daniel Kahneman','base_price' => 23,'feature' => 'Nobel Prize insights into cognitive bias and decision-making', 'specs' => [['attribute' => 'Topic', 'value' => 'Behavioral Psychology']]],
                    ['brand' => 'Currency',        'name' => 'Zero to One by Peter Thiel',               'base_price' => 21, 'feature' => 'contrarian startup playbook on building category-defining tech', 'specs' => [['attribute' => 'Topic', 'value' => 'Startups & Business']]],
                ],
                'editions' => [
                    ['suffix' => 'Paperback',         'label' => 'Paperback',       'price_mult' => 1.00, 'spec_highlight' => 'crisp typography and wide annotation margins', 'specs' => [['attribute' => 'Format', 'value' => 'Paperback']]],
                    ['suffix' => 'Hardcover',         'label' => 'Hardcover',       'price_mult' => 1.45, 'spec_highlight' => 'durable library-grade hardcover binding', 'specs' => [['attribute' => 'Format', 'value' => 'Hardcover']]],
                    ['suffix' => 'Workbook Bundle',   'label' => 'Book + Workbook', 'price_mult' => 1.75, 'spec_highlight' => 'paired with structured reflection exercises', 'specs' => [['attribute' => 'Bundle', 'value' => 'Includes Action Workbook']]],
                    ['suffix' => 'Revised Edition',   'label' => 'Updated Edition', 'price_mult' => 1.20, 'spec_highlight' => 'updated chapters with new case studies', 'specs' => [['attribute' => 'Edition', 'value' => 'Revised & Expanded']]],
                    ['suffix' => 'International Ed.', 'label' => 'Global Edition',  'price_mult' => 1.05, 'spec_highlight' => 'unabridged international English printing', 'specs' => [['attribute' => 'Language', 'value' => 'English Unabridged']]],
                ],
            ],

            "Children's Books" => [
                'colors' => ['Full Color'],
                'sizes'  => ['Hardcover', 'Board Book'],
                'short_template' => '{brand} brings {model} ({edition}) — {feature} for young readers.',
                'desc_template'  => 'Spark imagination at storytime with {model} ({edition}), featuring {feature} and {spec_highlight}.',
                'images' => [
                    'https://images.unsplash.com/photo-1495446815901-a7297e633e8d?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=800&q=80',
                ],
                'models' => [
                    ['brand' => 'Puffin Books',   'name' => 'Matilda by Roald Dahl',                    'base_price' => 14, 'feature' => 'beloved tale of a brilliant young girl with Quentin Blake art', 'specs' => [['attribute' => 'Reading Age', 'value' => '7 - 12 years']]],
                    ['brand' => 'Disney Hyperion','name' => 'Percy Jackson: The Lightning Thief',       'base_price' => 16, 'feature' => 'action-packed modern Greek mythology adventure', 'specs' => [['attribute' => 'Reading Age', 'value' => '8 - 14 years']]],
                    ['brand' => 'World of Eric Carle','name' => 'The Very Hungry Caterpillar',          'base_price' => 12, 'feature' => 'iconic die-cut collage pages teaching counting and days', 'specs' => [['attribute' => 'Reading Age', 'value' => '1 - 5 years']]],
                    ['brand' => 'HarperCollins',  'name' => 'Where the Wild Things Are',                'base_price' => 18, 'feature' => 'Caldecott Medal classic by Maurice Sendak', 'specs' => [['attribute' => 'Reading Age', 'value' => '4 - 8 years']]],
                    ['brand' => 'Scholastic',     'name' => 'The Wild Robot by Peter Brown',            'base_price' => 16, 'feature' => 'heartwarming story of Roz the robot surviving on a wild island', 'specs' => [['attribute' => 'Reading Age', 'value' => '8 - 12 years']]],
                ],
                'editions' => [
                    ['suffix' => 'Paperback',          'label' => 'Paperback',     'price_mult' => 1.00, 'spec_highlight' => 'lightweight backpack-friendly paperback', 'specs' => [['attribute' => 'Format', 'value' => 'Paperback']]],
                    ['suffix' => 'Picture Hardcover',  'label' => 'Hardcover',     'price_mult' => 1.45, 'spec_highlight' => 'thick glossy color illustrations', 'specs' => [['attribute' => 'Format', 'value' => 'Hardcover']]],
                    ['suffix' => 'Board Book',         'label' => 'Board Book',    'price_mult' => 1.15, 'spec_highlight' => 'rounded toddler-safe sturdy board pages', 'specs' => [['attribute' => 'Format', 'value' => 'Board Book']]],
                    ['suffix' => 'Gift Slipcase',      'label' => 'Gift Edition',  'price_mult' => 1.85, 'spec_highlight' => 'keepsake presentation slipcase with sticker sheet', 'specs' => [['attribute' => 'Edition', 'value' => 'Gift Slipcase']]],
                    ['suffix' => 'Read-Along Audio',   'label' => 'Book + Audio',  'price_mult' => 1.60, 'spec_highlight' => 'includes narrated read-along audio download code', 'specs' => [['attribute' => 'Bonus', 'value' => 'Audio Read-Along']]],
                ],
            ],

            'Academic' => [
                'colors' => ['Standard'],
                'sizes'  => ['Paperback', 'Hardcover'],
                'short_template' => '{brand} reference textbook: {model} ({edition}) covering {feature}.',
                'desc_template'  => 'Trusted by universities and senior engineers worldwide, {model} ({edition}) covers {feature} with {spec_highlight}.',
                'images' => [
                    'https://images.unsplash.com/photo-1532012197267-da84d127e765?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&w=800&q=80',
                ],
                'models' => [
                    ['brand' => "O'Reilly Media", 'name' => 'Hands-On Machine Learning with Scikit-Learn', 'base_price' => 69, 'feature' => 'practical Python ML pipelines, TF-IDF, XGBoost, and deep learning', 'specs' => [['attribute' => 'Subject', 'value' => 'Machine Learning & Python']]],
                    ['brand' => 'MIT Press',      'name' => 'Introduction to Algorithms (CLRS)',           'base_price' => 95, 'feature' => 'comprehensive graph theory, dynamic programming, and data structures', 'specs' => [['attribute' => 'Subject', 'value' => 'Algorithms & CS']]],
                    ['brand' => 'ByteByteGo',     'name' => 'System Design Interview Vol. 1 & 2',          'base_price' => 45, 'feature' => 'distributed caching, message queues, payment systems, and scaling', 'specs' => [['attribute' => 'Subject', 'value' => 'System Design']]],
                    ['brand' => 'Addison-Wesley', 'name' => 'Domain-Driven Design by Eric Evans',          'base_price' => 59, 'feature' => 'bounded contexts, aggregates, and enterprise software modeling', 'specs' => [['attribute' => 'Subject', 'value' => 'Software Architecture']]],
                    ['brand' => 'Springer',       'name' => 'An Introduction to Statistical Learning',     'base_price' => 65, 'feature' => 'regression, classification, resampling, and tree-based ML methods', 'specs' => [['attribute' => 'Subject', 'value' => 'Data Science & Statistics']]],
                ],
                'editions' => [
                    ['suffix' => '3rd Edition',       'label' => '3rd Edition',     'price_mult' => 1.00, 'spec_highlight' => 'updated code examples and diagrams', 'specs' => [['attribute' => 'Edition', 'value' => '3rd Edition']]],
                    ['suffix' => '4th Edition Color', 'label' => '4th Full-Color',  'price_mult' => 1.22, 'spec_highlight' => 'full-color syntax highlighting and architecture charts', 'specs' => [['attribute' => 'Printing', 'value' => 'Full Color']]],
                    ['suffix' => 'Hardcover Library', 'label' => 'Hardcover',       'price_mult' => 1.35, 'spec_highlight' => 'lay-flat sewn hardcover for desk reference', 'specs' => [['attribute' => 'Format', 'value' => 'Hardcover']]],
                    ['suffix' => 'Student Edition',   'label' => 'Student Value',   'price_mult' => 0.85, 'spec_highlight' => 'affordable softcover with exercise solutions', 'specs' => [['attribute' => 'Edition', 'value' => 'Student Edition']]],
                    ['suffix' => 'Print + Lab Guide', 'label' => 'With Lab Manual', 'price_mult' => 1.28, 'spec_highlight' => 'bundled with hands-on GitHub lab companion', 'specs' => [['attribute' => 'Bundle', 'value' => 'Includes Lab Workbook']]],
                ],
            ],

            'Fitness' => [
                'colors' => ['Black', 'Charcoal', 'Cobalt Blue', 'Crimson'],
                'sizes'  => ['Standard'],
                'short_template' => '{brand} {model} ({edition}) engineered with {feature} for peak training.',
                'desc_template'  => 'Elevate every workout with the {brand} {model} ({edition}), designed with {feature} and {spec_highlight}.',
                'images' => [
                    'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&w=800&q=80',
                ],
                'models' => [
                    ['brand' => 'Therabody', 'name' => 'Theragun PRO Percussive Massager', 'base_price' => 399, 'feature' => 'QuietForce brushless motor with 60 lbs stall force and OLED screen', 'specs' => [['attribute' => 'Amplitude', 'value' => '16', 'unit' => 'mm']]],
                    ['brand' => 'Manduka',   'name' => 'PRO 6mm Yoga Mat',                 'base_price' => 138, 'feature' => 'ultra-dense closed-cell hygienic cushioning with lifetime guarantee', 'specs' => [['attribute' => 'Thickness', 'value' => '6', 'unit' => 'mm']]],
                    ['brand' => 'Concept2',  'name' => 'RowErg Indoor Rower PM5',          'base_price' => 990, 'feature' => 'air-resistance flywheel and Bluetooth PM5 performance monitor', 'specs' => [['attribute' => 'Monitor', 'value' => 'PM5 Bluetooth & ANT+']]],
                    ['brand' => 'WHOOP',     'name' => '4.0 Recovery Fitness Band',        'base_price' => 239, 'feature' => '24/7 HRV, sleep staging, strain coaching, and screenless design', 'specs' => [['attribute' => 'Sensors', 'value' => 'HRV, SpO2, Skin Temp']]],
                    ['brand' => 'Rogue',     'name' => 'Powder-Coat Cast Iron Kettlebell', 'base_price' => 85,  'feature' => 'single-piece ductile iron pour with textured chalk-friendly grip', 'specs' => [['attribute' => 'Material', 'value' => 'Ductile Cast Iron']]],
                ],
                'editions' => [
                    ['suffix' => 'Standard Kit',    'label' => 'Standard',     'price_mult' => 1.00, 'spec_highlight' => 'ready for home or commercial gym use', 'specs' => [['attribute' => 'Grade', 'value' => 'Home & Studio']]],
                    ['suffix' => 'Pro Gym Edition', 'label' => 'Commercial',   'price_mult' => 1.18, 'spec_highlight' => 'heavy-duty commercial warranty and carry bag', 'specs' => [['attribute' => 'Grade', 'value' => 'Commercial Pro']]],
                    ['suffix' => 'Travel Compact',  'label' => 'Travel Size',  'price_mult' => 0.90, 'spec_highlight' => 'lightweight packable dimensions for athletes on tour', 'specs' => [['attribute' => 'Portability', 'value' => 'Travel Compact']]],
                    ['suffix' => 'Bundle + Bands',  'label' => 'Training Pack','price_mult' => 1.12, 'spec_highlight' => 'bundled with fabric resistance loops and towel', 'specs' => [['attribute' => 'Bundle', 'value' => 'Resistance Bands Included']]],
                    ['suffix' => 'Athlete Limited', 'label' => 'Limited',      'price_mult' => 1.08, 'spec_highlight' => 'matte stealth colorway with laser-etched logo', 'specs' => [['attribute' => 'Finish', 'value' => 'Stealth Matte']]],
                ],
            ],

            'Team Sports' => [
                'colors' => ['Match White', 'Classic Orange', 'Pro Blue'],
                'sizes'  => ['Official Size'],
                'short_template' => '{brand} {model} ({edition}) built with {feature} for match-day performance.',
                'desc_template'  => 'Trusted by clubs and leagues, the {brand} {model} ({edition}) offers {feature} and {spec_highlight}.',
                'images' => [
                    'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1614632537197-38a17061c2bd?auto=format&fit=crop&w=800&q=80',
                ],
                'models' => [
                    ['brand' => 'Nike',     'name' => 'Mercurial Vapor 16 Elite FG',  'base_price' => 260, 'feature' => '3/4-length Air Zoom unit and tacky Gripknit upper', 'specs' => [['attribute' => 'Surface', 'value' => 'Firm Ground (FG)']]],
                    ['brand' => 'Molten',   'name' => 'BG4500 FIBA Basketball',       'base_price' => 75,  'feature' => 'FIBA-approved 12-panel premium composite leather', 'specs' => [['attribute' => 'Certification', 'value' => 'FIBA Approved']]],
                    ['brand' => 'Mikasa',   'name' => 'V200W Olympic Volleyball',     'base_price' => 89,  'feature' => 'double-dimpled aerodynamic microfiber surface for stable flight', 'specs' => [['attribute' => 'Certification', 'value' => 'FIVB Official']]],
                    ['brand' => 'Babolat',  'name' => 'Pure Aero 98 Tennis Racket',   'base_price' => 279, 'feature' => 'NF2-Tech flax vibration dampening and spin-focused open pattern', 'specs' => [['attribute' => 'Weight', 'value' => '305', 'unit' => 'g']]],
                    ['brand' => 'Adidas',   'name' => 'Predator Pro Goalkeeper Gloves','base_price' => 130,'feature' => 'URG 2.0 latex palm grip and flexible knit backhand', 'specs' => [['attribute' => 'Grip', 'value' => 'URG 2.0 Match Latex']]],
                ],
                'editions' => [
                    ['suffix' => 'Official Match', 'label' => 'Match Grade',   'price_mult' => 1.00, 'spec_highlight' => 'tournament-certified specifications', 'specs' => [['attribute' => 'Level', 'value' => 'Match Official']]],
                    ['suffix' => 'Training Pack',  'label' => 'Training',      'price_mult' => 0.82, 'spec_highlight' => 'extra-durable reinforced training construction', 'specs' => [['attribute' => 'Level', 'value' => 'Club Training']]],
                    ['suffix' => 'Pro + Pump Kit', 'label' => 'Kit Bundle',    'price_mult' => 1.12, 'spec_highlight' => 'includes mesh carry sack and pressure gauge pump', 'specs' => [['attribute' => 'Bundle', 'value' => 'Pump & Carry Bag']]],
                    ['suffix' => 'Youth League',   'label' => 'Youth Size',    'price_mult' => 0.88, 'spec_highlight' => 'scaled weight and dimensions for junior players', 'specs' => [['attribute' => 'Age Group', 'value' => 'Youth League']]],
                    ['suffix' => 'Team 3-Pack',    'label' => '3-Pack',        'price_mult' => 2.55, 'spec_highlight' => 'coach value pack of 3 matched units', 'specs' => [['attribute' => 'Quantity', 'value' => '3-Unit Team Pack']]],
                ],
            ],

            'Outdoor Recreation' => [
                'colors' => ['Forest Green', 'Coyote Tan', 'Slate Blue', 'Burnt Orange'],
                'sizes'  => ['Standard'],
                'short_template' => '{brand} {model} ({edition}) engineered with {feature} for backcountry reliability.',
                'desc_template'  => 'Built for rugged trails and alpine camps, the {brand} {model} ({edition}) combines {feature} with {spec_highlight}.',
                'images' => [
                    'https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1523987355523-c7b5b0dd90a7?auto=format&fit=crop&w=800&q=80',
                ],
                'models' => [
                    ['brand' => 'Osprey',      'name' => 'Atmos AG 65 Backpacking Pack', 'base_price' => 340, 'feature' => 'Anti-Gravity 3D suspended mesh harness and integrated raincover', 'specs' => [['attribute' => 'Volume', 'value' => '65', 'unit' => 'Liters']]],
                    ['brand' => 'Jetboil',     'name' => 'Flash Cooking System Stove',   'base_price' => 129, 'feature' => 'FluxRing heat exchanger boiling 500ml water in 100 seconds', 'specs' => [['attribute' => 'Boil Time', 'value' => '100', 'unit' => 'seconds']]],
                    ['brand' => 'Black Diamond','name' => 'Spot 400-R Headlamp',         'base_price' => 65,  'feature' => 'IP67 waterproof 400-lumen rechargeable beam with red night vision', 'specs' => [['attribute' => 'Brightness', 'value' => '400', 'unit' => 'Lumens']]],
                    ['brand' => 'Therm-a-Rest','name' => 'NeoAir XLite NXT Sleeping Pad','base_price' => 210, 'feature' => '4.5 R-value year-round warmth weighing only 13 ounces', 'specs' => [['attribute' => 'R-Value', 'value' => '4.5']]],
                    ['brand' => 'Hydro Flask', 'name' => 'Trail Series 32oz Bottle',     'base_price' => 50,  'feature' => '25% lighter TempShield double-wall vacuum stainless steel', 'specs' => [['attribute' => 'Capacity', 'value' => '32', 'unit' => 'oz']]],
                ],
                'editions' => [
                    ['suffix' => 'Standard Trail',  'label' => 'Standard',     'price_mult' => 1.00, 'spec_highlight' => 'three-season trail durability', 'specs' => [['attribute' => 'Season', 'value' => '3-Season']]],
                    ['suffix' => 'Ultralight',      'label' => 'Ultralight',   'price_mult' => 1.15, 'spec_highlight' => 'ripstop ultralight construction for thru-hikers', 'specs' => [['attribute' => 'Class', 'value' => 'Ultralight']]],
                    ['suffix' => '4-Season Alpine', 'label' => 'Alpine',       'price_mult' => 1.24, 'spec_highlight' => 'reinforced cold-weather alpine rating', 'specs' => [['attribute' => 'Season', 'value' => '4-Season Alpine']]],
                    ['suffix' => 'Basecamp XL',     'label' => 'Basecamp',     'price_mult' => 1.18, 'spec_highlight' => 'expanded comfort size for car camping', 'specs' => [['attribute' => 'Use', 'value' => 'Car & Basecamp']]],
                    ['suffix' => 'Expedition Kit',  'label' => 'Expedition',   'price_mult' => 1.28, 'spec_highlight' => 'includes dry sack and field repair kit', 'specs' => [['attribute' => 'Bundle', 'value' => 'Dry Sack + Repair Kit']]],
                ],
            ],

            'Water Sports' => [
                'colors' => ['Ocean Blue', 'Neon Yellow', 'Coral', 'Graphite'],
                'sizes'  => ['S/M', 'L/XL', 'One Size'],
                'short_template' => '{brand} {model} ({edition}) engineered with {feature} for open water and pool sessions.',
                'desc_template'  => 'Built for aquatic performance, the {brand} {model} ({edition}) pairs {feature} with {spec_highlight}.',
                'images' => [
                    'https://images.unsplash.com/photo-1530549387789-4c1017266635?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=800&q=80',
                ],
                'models' => [
                    ['brand' => 'iROCKER',  'name' => 'Cruiser 10\'6" Inflatable SUP', 'base_price' => 549, 'feature' => 'triple-layer military-grade PVC stability and carbon paddle', 'specs' => [['attribute' => 'Length', 'value' => '10 ft 6 in']]],
                    ['brand' => 'Arena',    'name' => 'Cobra Ultra Swipe Goggles',     'base_price' => 78,  'feature' => '10x longer Swipe anti-fog hydrodynamic racing lenses', 'specs' => [['attribute' => 'Certification', 'value' => 'World Aquatics Approved']]],
                    ['brand' => 'O\'Neill', 'name' => 'Reactor-2 3/2mm Wetsuit',       'base_price' => 149, 'feature' => 'UltraFlex neoprene with wind-proof Smoothskin chest panel', 'specs' => [['attribute' => 'Thickness', 'value' => '3/2', 'unit' => 'mm']]],
                    ['brand' => 'Cressi',   'name' => 'Palau Snorkel & Fin Set',       'base_price' => 95,  'feature' => 'tempered-glass wide-view mask, dry snorkel, and adjustable fins', 'specs' => [['attribute' => 'Kit', 'value' => 'Mask + Dry Snorkel + Fins']]],
                    ['brand' => 'NRS',      'name' => 'Chinook Fishing PFD Life Vest', 'base_price' => 139, 'feature' => 'USCG Type III PlushFit foam with 7 gear pockets and mesh back', 'specs' => [['attribute' => 'Certification', 'value' => 'USCG Type III']]],
                ],
                'editions' => [
                    ['suffix' => 'Standard Edition', 'label' => 'Standard',     'price_mult' => 1.00, 'spec_highlight' => 'saltwater- and chlorine-resistant materials', 'specs' => [['attribute' => 'Resistance', 'value' => 'Saltwater & Chlorine Safe']]],
                    ['suffix' => 'Pro Racing',       'label' => 'Pro Race',     'price_mult' => 1.18, 'spec_highlight' => 'low-drag competition profile', 'specs' => [['attribute' => 'Level', 'value' => 'Competition Pro']]],
                    ['suffix' => 'Touring Package',  'label' => 'Touring Kit',  'price_mult' => 1.22, 'spec_highlight' => 'includes waterproof dry bag and coiled leash', 'specs' => [['attribute' => 'Bundle', 'value' => 'Dry Bag + Leash']]],
                    ['suffix' => 'UV50+ Polarized',  'label' => 'Sun Shield',   'price_mult' => 1.10, 'spec_highlight' => 'enhanced UV50+ glare and sun protection', 'specs' => [['attribute' => 'UV Rating', 'value' => 'UPF / UV50+']]],
                    ['suffix' => 'Family 2-Pack',    'label' => '2-Person Pack','price_mult' => 1.80, 'spec_highlight' => 'paired set for two paddlers or swimmers', 'specs' => [['attribute' => 'Quantity', 'value' => '2-Pack Bundle']]],
                ],
            ],
        ];
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImageService
{
    /**
     * Image source configurations
     */
    private const IMAGE_SOURCES = [
        'picsum' => [
            'base_url' => 'https://picsum.photos',
            'categories' => [
                'electronics' => ['tech', 'computer', 'phone', 'laptop', 'gadget'],
                'fashion' => ['clothing', 'shoes', 'fashion', 'wear'],
                'home' => ['home', 'kitchen', 'furniture', 'decor'],
                'sports' => ['sports', 'fitness', 'gym', 'running'],
                'beauty' => ['beauty', 'cosmetics', 'skincare'],
                'books' => ['books', 'reading', 'library'],
                'toys' => ['toys', 'games', 'play'],
                'automotive' => ['car', 'automotive', 'vehicle'],
                'default' => ['product', 'item', 'object'],
            ],
        ],
        'unsplash' => [
            'base_url' => 'https://source.unsplash.com',
            'categories' => [
                'electronics' => ['technology', 'electronics', 'gadgets'],
                'fashion' => ['fashion', 'clothing', 'apparel'],
                'home' => ['home', 'interior', 'furniture'],
                'sports' => ['fitness', 'sports', 'exercise'],
                'beauty' => ['beauty', 'makeup', 'skincare'],
                'books' => ['books', 'reading'],
                'toys' => ['toys', 'games'],
                'automotive' => ['cars', 'automotive'],
                'default' => ['products', 'shopping'],
            ],
        ],
    ];

    /**
     * Download and save image from URL
     */
    public function downloadAndSaveImage(string $url, string $path): bool
    {
        try {
            $response = Http::timeout(30)->get($url);

            if ($response->successful()) {
                Storage::disk('public')->put($path, $response->body());
                return true;
            }

            Log::warning("Failed to download image from {$url}: {$response->status()}");
            return false;
        } catch (\Exception $e) {
            Log::error("Error downloading image from {$url}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Get image URL for a product based on category
     */
    public function getImageUrlForCategory(string $category, int $width = 800, int $height = 800, int $seed = null): string
    {
        $source = 'picsum'; // Using Picsum for reliable free images
        $categories = self::IMAGE_SOURCES[$source]['categories'];
        $keywords = $categories[$category] ?? $categories['default'];
        $keyword = $keywords[array_rand($keywords)];

        $seedParam = $seed ? "?seed={$seed}" : '';
        return "https://picsum.photos/seed/{$keyword}-{$seed}/{$width}/{$height}.jpg{$seedParam}";
    }

    /**
     * Get multiple image URLs for a product
     */
    public function getProductImageUrls(string $category, int $count = 4, int $productId = null): array
    {
        $urls = [];
        $baseSeed = $productId ? $productId * 1000 : rand(1000, 9999);

        for ($i = 0; $i < $count; $i++) {
            $seed = $baseSeed + $i;
            $urls[] = $this->getImageUrlForCategory($category, 800, 800, $seed);
        }

        return $urls;
    }

    /**
     * Determine category from product name/description
     */
    public function determineCategoryFromProduct(string $name, string $description = ''): string
    {
        $text = strtolower($name . ' ' . $description);

        $categoryKeywords = [
            'electronics' => ['laptop', 'phone', 'computer', 'tablet', 'headphone', 'speaker', 'monitor', 'keyboard', 'mouse', 'camera', 'watch', 'smart', 'electronic', 'gadget', 'tech', 'usb', 'charger', 'cable', 'battery', 'power'],
            'fashion' => ['shirt', 'pants', 'dress', 'shoes', 'sneakers', 'boots', 'jacket', 'coat', 'sweater', 'hoodie', 'jeans', 'fashion', 'clothing', 'wear', 'apparel', 't-shirt', 'top'],
            'home' => ['furniture', 'chair', 'table', 'sofa', 'bed', 'lamp', 'decor', 'kitchen', 'home', 'house', 'interior', 'pillow', 'blanket', 'curtain', 'rug', 'mirror'],
            'sports' => ['fitness', 'gym', 'running', 'sport', 'exercise', 'yoga', 'weight', 'dumbbell', 'treadmill', 'bike', 'ball', 'tennis', 'football', 'basketball', 'outdoor'],
            'beauty' => ['beauty', 'makeup', 'skincare', 'cosmetic', 'cream', 'serum', 'lipstick', 'foundation', 'mascara', 'perfume', 'fragrance', 'lotion', 'cleanser'],
            'books' => ['book', 'novel', 'reading', 'magazine', 'comic', 'textbook', 'guide', 'dictionary', 'encyclopedia'],
            'toys' => ['toy', 'game', 'puzzle', 'lego', 'doll', 'action figure', 'board game', 'card game', 'play', 'kids', 'children'],
            'automotive' => ['car', 'auto', 'vehicle', 'tire', 'wheel', 'engine', 'oil', 'brake', 'battery', 'automotive'],
        ];

        foreach ($categoryKeywords as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    return $category;
                }
            }
        }

        return 'default';
    }

    /**
     * Seed images for all products
     */
    public function seedProductImages(): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'details' => [],
        ];

        // Import Product and ProductImage models here to avoid circular dependency
        $productModel = app(\App\Models\Product::class);
        $imageModel = app(\App\Models\ProductImage::class);

        // Ensure storage directory exists
        Storage::disk('public')->makeDirectory('products');

        $products = $productModel::where('product_status_id', 1)->get();

        foreach ($products as $product) {
            // Skip if product already has images
            if ($product->images()->exists()) {
                $results['skipped']++;
                continue;
            }

            $category = $this->determineCategoryFromProduct($product->name, $product->description);
            $imageUrls = $this->getProductImageUrls($category, 4, $product->id);

            $successCount = 0;

            foreach ($imageUrls as $index => $url) {
                $filename = "products/{$product->id}-image-{$index}-" . Str::random(8) . ".jpg";
                $fullPath = storage_path('app/public/' . $filename);

                // Ensure directory exists
                $directory = dirname($fullPath);
                if (!is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }

                // Download and save
                if ($this->downloadAndSaveImage($url, $filename)) {
                    $imageModel::create([
                        'product_id' => $product->id,
                        'path' => $filename,
                        'position' => $index + 1,
                        'is_cover' => $index === 0,
                    ]);
                    $successCount++;
                }
            }

            if ($successCount > 0) {
                $results['success']++;
                $results['details'][] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'images_downloaded' => $successCount,
                    'category' => $category,
                ];
            } else {
                $results['failed']++;
                $results['details'][] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'error' => 'Failed to download any images',
                ];
            }
        }

        return $results;
    }

    /**
     * Create placeholder image if download fails
     */
    public function createPlaceholderImage(string $path, int $width = 800, int $height = 800, string $text = 'Product Image'): bool
    {
        if (!function_exists('imagecreatetruecolor')) {
            // GD not available, create empty file
            $fullPath = storage_path('app/public/' . $path);
            $directory = dirname($fullPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            file_put_contents($fullPath, '');
            return true;
        }

        try {
            $image = imagecreatetruecolor($width, $height);

            // Generate a nice color based on path
            $hash = crc32($path);
            $red = ($hash & 0xFF0000) >> 16;
            $green = ($hash & 0x00FF00) >> 8;
            $blue = $hash & 0x0000FF;

            // Make colors softer
            $red = min(200, $red + 55);
            $green = min(200, $green + 55);
            $blue = min(200, $blue + 55);

            $background = imagecolorallocate($image, $red, $green, $blue);
            imagefilledrectangle($image, 0, 0, $width, $height, $background);

            // Add text
            $textColor = imagecolorallocate($image, 255, 255, 255);
            $fontSize = 5;
            $textWidth = imagefontwidth($fontSize) * strlen($text);
            $textHeight = imagefontheight($fontSize);
            $x = ($width - $textWidth) / 2;
            $y = ($height - $textHeight) / 2;
            imagestring($image, $fontSize, $x, $y, $text, $textColor);

            $fullPath = storage_path('app/public/' . $path);
            $directory = dirname($fullPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            imagepng($image, $fullPath);
            imagedestroy($image);

            return true;
        } catch (\Exception $e) {
            Log::error("Error creating placeholder: {$e->getMessage()}");
            return false;
        }
    }
}
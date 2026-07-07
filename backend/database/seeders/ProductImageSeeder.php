<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        Storage::disk('public')->makeDirectory('products');

        Product::chunk(50, function ($products) {
            foreach ($products as $product) {
                if ($product->images()->exists()) {
                    continue;
                }

                $colors = ['ff6b6b', '6bc4ff', '6bff99', 'ffd56b', 'a76fff'];

                for ($position = 0; $position < 3; $position++) {
                    $path = "products/{$product->id}-image-{$position}.png";
                    $fullPath = public_path('storage/' . $path);
                    $directory = dirname($fullPath);

                    if (! is_dir($directory)) {
                        mkdir($directory, 0755, true);
                    }

                    if (function_exists('imagecreatetruecolor')) {
                        $image = imagecreatetruecolor(800, 800);
                        $hex = $colors[$position % count($colors)];
                        $red = hexdec(substr($hex, 0, 2));
                        $green = hexdec(substr($hex, 2, 2));
                        $blue = hexdec(substr($hex, 4, 2));
                        $background = imagecolorallocate($image, $red, $green, $blue);
                        imagefilledrectangle($image, 0, 0, 800, 800, $background);
                        $textColor = imagecolorallocate($image, 255, 255, 255);
                        imagestring($image, 5, 20, 20, "Product {$product->id} - Image " . ($position + 1), $textColor);
                        imagepng($image, $fullPath);
                        imagedestroy($image);
                    } else {
                        file_put_contents($fullPath, '');
                    }

                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $path,
                        'position' => $position + 1,
                        'is_cover' => $position === 0,
                    ]);
                }
            }
        });
    }
}

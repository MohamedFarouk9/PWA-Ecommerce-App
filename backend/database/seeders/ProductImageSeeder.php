<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImageSeeder extends Seeder
{
    private ImageService $imageService;

    public function __construct()
    {
        parent::__construct();
        $this->imageService = app(ImageService::class);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Product Image Seeding with Real Images...');

        // Ensure storage directory exists
        Storage::disk('public')->makeDirectory('products');

        $results = $this->imageService->seedProductImages();

        $this->command->info("Image Seeding Complete!");
        $this->command->info("Successfully seeded: {$results['success']} products");
        $this->command->info("Failed: {$results['failed']} products");
        $this->command->info("Skipped (already had images): {$results['skipped']} products");

        if (!empty($results['details'])) {
            $this->command->table(
                ['Product ID', 'Product Name', 'Images Downloaded', 'Category'],
                array_map(function ($detail) {
                    return [
                        $detail['product_id'] ?? 'N/A',
                        $detail['product_name'] ?? 'N/A',
                        $detail['images_downloaded'] ?? ($detail['error'] ?? 'N/A'),
                        $detail['category'] ?? 'N/A',
                    ];
                }, $results['details'])
            );
        }
    }
}
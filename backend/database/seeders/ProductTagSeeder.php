<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductTag;
use Illuminate\Database\Seeder;

class ProductTagSeeder extends Seeder
{
    /**
     * Seeds smart, data-driven merchandising + domain tags (idempotent via firstOrCreate + syncWithoutDetaching).
     * Rich semantic tags improve both storefront filtering and ML TF-IDF content similarity.
     */
    public function run(): void
    {
        $tagNames = [
            'New Arrival',
            'Best Seller',
            'Sale',
            'Featured',
            'Premium',
            'Trending',
            'Top Rated',
            'Tech & Gadgets',
            'Wireless',
            'Gaming Gear',
            'Fashion Essential',
            'Home Upgrade',
            'Smart Home',
            'Bestseller Book',
            'Fitness & Wellness',
            'Outdoor Adventure',
        ];

        $tagIds = [];
        foreach ($tagNames as $name) {
            $tagIds[$name] = ProductTag::firstOrCreate(['name' => $name])->id;
        }

        $categoryTagMap = [
            'Smartphones'        => ['Tech & Gadgets', 'Wireless'],
            'Laptops'            => ['Tech & Gadgets', 'Premium'],
            'Audio'              => ['Tech & Gadgets', 'Wireless'],
            'Gaming'             => ['Tech & Gadgets', 'Gaming Gear'],
            "Men's Fashion"      => ['Fashion Essential'],
            "Women's Fashion"    => ['Fashion Essential'],
            "Kids' Fashion"      => ['Fashion Essential'],
            'Footwear'           => ['Fashion Essential', 'Fitness & Wellness'],
            'Furniture'          => ['Home Upgrade'],
            'Kitchen Appliances' => ['Home Upgrade'],
            'Home Decor'         => ['Home Upgrade', 'Smart Home'],
            'Bedding'            => ['Home Upgrade'],
            'Fiction'            => ['Bestseller Book'],
            'Non-Fiction'        => ['Bestseller Book'],
            "Children's Books"   => ['Bestseller Book'],
            'Academic'           => ['Bestseller Book'],
            'Fitness'            => ['Fitness & Wellness'],
            'Team Sports'        => ['Fitness & Wellness'],
            'Outdoor Recreation' => ['Outdoor Adventure'],
            'Water Sports'       => ['Outdoor Adventure'],
        ];

        $taggedCount = 0;

        Product::with('category')->chunkById(100, function ($products) use ($tagIds, $categoryTagMap, &$taggedCount) {
            foreach ($products as $product) {
                $attachIds = [];

                // Data-driven merchandising tags
                if ($product->special_price && $product->special_price < $product->price) {
                    $attachIds[] = $tagIds['Sale'];
                }
                if ($product->price >= 500) {
                    $attachIds[] = $tagIds['Premium'];
                }
                if ($product->view_count >= 3000) {
                    $attachIds[] = $tagIds['Best Seller'];
                    $attachIds[] = $tagIds['Trending'];
                } elseif ($product->view_count >= 1500) {
                    $attachIds[] = $tagIds['Top Rated'];
                } else {
                    $attachIds[] = $tagIds['New Arrival'];
                }

                // Semantic domain tags from category
                $catName = $product->category?->name ?? '';
                foreach ($categoryTagMap[$catName] ?? ['Featured'] as $domainTag) {
                    if (isset($tagIds[$domainTag])) {
                        $attachIds[] = $tagIds[$domainTag];
                    }
                }

                $product->tags()->syncWithoutDetaching(array_unique($attachIds));
                $taggedCount++;
            }
        });

        $this->command?->info("ProductTagSeeder: Applied semantic & data-driven tags to {$taggedCount} products!");
    }
}

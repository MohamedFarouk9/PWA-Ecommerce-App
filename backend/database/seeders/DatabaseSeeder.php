<?php
namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,

            // Core product structure
            ProductStatusSeeder::class,
            ProductCategorySeeder::class,
            ProductAttributeSeeder::class,
            SectionSeeder::class,

            // Products and related data (553 real-world products across all 20 categories — zero Lorem Ipsum)
            ProductSeeder::class,             // 500 realistic multi-category products + specs + Unsplash images
            ShowcaseProductsSeeder::class,    // 10 flagship showcase products with deep specs
            RealCatalogMLSeeder::class,       // 43 curated real-world products + 80 flagship co-purchase baskets
            ProductDetailsSeeder::class,      // Safe backfill for any missing brand/SKU/specs
            ProductImageSeeder::class,        // Safe backfill for any missing images (preserves curated images)
            ProductVariantSeeder::class,      // Category-aware variants (Color/Size/Storage/Format)
            ProductTagSeeder::class,          // Semantic & data-driven merchandising tags
            ProductReviewSeeder::class,       // Realistic non-Lorem-Ipsum reviews & rating curve

            // Other data
            CartSeeder::class,
            ContactSeeder::class,
            SiteSettingSeeder::class,
            SliderSeeder::class,

            // Notification seeders
            NotificationTemplateSeeder::class,
            OrderSeeder::class,
            NotificationSeeder::class,
            NotificationLogSeeder::class,
            NotificationSettingsSeeder::class,
        ]);
    }
}
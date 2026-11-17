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

            // Products and related data
            ProductSeeder::class,
            ProductDetailsSeeder::class,      // NEW: Brand, SKU, color, size, specs
            ShowcaseProductsSeeder::class,    // NEW: 10 showcase products with real images
            ProductImageSeeder::class,        // Enhanced with real images from Picsum
            ProductVariantSeeder::class,
            ProductTagSeeder::class,
            ProductReviewSeeder::class,

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
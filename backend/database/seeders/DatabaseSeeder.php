<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AccessControl\Database\Seeders\RolePermissionSeeder;
use Modules\Catalog\Database\Seeders\ProductAttributeSeeder;
use Modules\Catalog\Database\Seeders\ProductCategorySeeder;
use Modules\Catalog\Database\Seeders\ProductDetailsSeeder;
use Modules\Catalog\Database\Seeders\ProductImageSeeder;
use Modules\Catalog\Database\Seeders\ProductSeeder;
use Modules\Catalog\Database\Seeders\ProductStatusSeeder;
use Modules\Catalog\Database\Seeders\ProductTagSeeder;
use Modules\Catalog\Database\Seeders\ProductVariantSeeder;
use Modules\Catalog\Database\Seeders\RealCatalogMLSeeder;
use Modules\Catalog\Database\Seeders\SectionSeeder;
use Modules\Catalog\Database\Seeders\ShowcaseProductsSeeder;
use Modules\Catalog\Database\Seeders\SliderSeeder;
use Modules\Core\Database\Seeders\SiteSettingSeeder;
use Modules\Customers\Database\Seeders\UserSeeder;
use Modules\Marketing\Database\Seeders\ContactSeeder;
use Modules\Marketing\Database\Seeders\NotificationLogSeeder;
use Modules\Marketing\Database\Seeders\NotificationSeeder;
use Modules\Marketing\Database\Seeders\NotificationSettingsSeeder;
use Modules\Marketing\Database\Seeders\NotificationTemplateSeeder;
use Modules\Marketing\Database\Seeders\ProductReviewSeeder;
use Modules\Sales\Database\Seeders\CartSeeder;
use Modules\Sales\Database\Seeders\OrderSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database across all Modular Monolith domains.
     */
    public function run(): void
    {
        $this->call([
            // Access Control & Customers
            RolePermissionSeeder::class,
            UserSeeder::class,

            // Catalog Structure & Real-World Multi-Category Products (553 products)
            ProductStatusSeeder::class,
            ProductCategorySeeder::class,
            ProductAttributeSeeder::class,
            SectionSeeder::class,
            ProductSeeder::class,
            ShowcaseProductsSeeder::class,
            RealCatalogMLSeeder::class,
            ProductDetailsSeeder::class,
            ProductImageSeeder::class,
            ProductVariantSeeder::class,
            ProductTagSeeder::class,
            SliderSeeder::class,

            // Sales & Orders (1,720 persona + fraud-labeled orders)
            CartSeeder::class,
            OrderSeeder::class,

            // Marketing, Reviews & Notifications
            ProductReviewSeeder::class,
            ContactSeeder::class,
            NotificationTemplateSeeder::class,
            NotificationSeeder::class,
            NotificationLogSeeder::class,
            NotificationSettingsSeeder::class,

            // Core Platform Settings
            SiteSettingSeeder::class,
        ]);
    }
}

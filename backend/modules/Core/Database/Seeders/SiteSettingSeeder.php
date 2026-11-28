<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\SiteSetting;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'site_name' => 'E-Commerce Store',
            'site_description' => 'Your one-stop shop for all your needs',
            'contact_email' => 'contact@example.com',
            'contact_phone' => '+1 234 567 8900',
            'address' => '123 Commerce Street, Business City, 12345',
            'facebook_url' => 'https://facebook.com/yourstore',
            'twitter_url' => 'https://twitter.com/yourstore',
            'instagram_url' => 'https://instagram.com/yourstore',
            'linkedin_url' => 'https://linkedin.com/company/yourstore',
            'currency' => 'USD',
            'currency_symbol' => '$',
            'tax_rate' => '10',
            'shipping_fee' => '5.00',
            'min_order_amount' => '50.00',
            'maintenance_mode' => 'false',
            'default_language' => 'en',
            'timezone' => 'UTC',
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::firstOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        $this->command?->info('Site settings seeded successfully!');
    }
}

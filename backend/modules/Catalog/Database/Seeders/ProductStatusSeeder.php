<?php

namespace Modules\Catalog\Database\Seeders;

use App\Models\ProductStatus;
use Illuminate\Database\Seeder;

class ProductStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'draft'],
            ['name' => 'published'],
            ['name' => 'archived'],
        ];

        foreach ($statuses as $status) {
            ProductStatus::create($status);
        }

        $this->command->info('Product statuses seeded successfully!');
    }
}

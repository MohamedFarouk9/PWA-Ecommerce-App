<?php

namespace Modules\Catalog\Database\Seeders;

use App\Models\ProductAttribute;
use Illuminate\Database\Seeder;

class ProductAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'Color',
            'Size',
            'Material',
            'Weight',
            'Dimensions',
            'Brand',
            'Model',
            'Style',
            'Pattern',
            'Capacity',
        ];

        foreach ($attributes as $attribute) {
            ProductAttribute::create(['name' => $attribute]);
        }

        $this->command->info('Product attributes seeded successfully!');
    }
}

<?php

namespace Modules\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Section;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['name' => 'featured', 'label' => 'Featured'],
            ['name' => 'new_arrival', 'label' => 'New Arrival'],
            ['name' => 'best_seller', 'label' => 'Best Seller'],
        ];
        foreach ($sections as $section) {
            Section::firstOrCreate(['name' => $section['name']], $section);
        }
    }
}

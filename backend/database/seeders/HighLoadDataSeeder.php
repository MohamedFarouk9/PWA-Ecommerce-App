<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class HighLoadDataSeeder extends Seeder
{
    /**
     * Run the high-load database seeds.
     */
    public function run(): void
    {
        $this->command->info('Running High-Load Dataset Seeder...');
        Artisan::call('db:seed-high-load', [
            '--users' => 500,
            '--products' => 5000,
            '--orders' => 2000,
            '--reviews' => 5000,
            '--chunk' => 500,
        ], $this->command->getOutput());
    }
}

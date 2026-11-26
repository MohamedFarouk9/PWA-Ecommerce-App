<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Seeds admin accounts + 150 realistic customers with varied account ages
     * (from brand-new accounts to 2-year loyal buyers) for ML Recommendation
     * and Fraud Risk Scoring datasets.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Ensure Passport Personal Access Client exists for API login/register
        if (DB::table('oauth_personal_access_clients')->count() === 0) {
            Artisan::call('passport:client', [
                '--personal' => true,
                '--name' => 'PWA Ecommerce Personal Access Client',
                '--no-interaction' => true,
            ]);
        }

        // Create Super Admin User (idempotent)
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'phone' => '1234567891',
                'password' => 'superadmin123',
                'role' => 'admin',
                'status' => 'active',
                'address' => '1 Admin Plaza',
                'city' => 'Admin City',
                'state' => 'Admin State',
                'country' => 'United States',
                'email_verified_at' => Carbon::now(),
            ]
        );
        if (!$superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole('super_admin');
        }

        // Create Admin User (idempotent)
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'phone' => '1234567890',
                'password' => 'admin123',
                'role' => 'admin',
                'status' => 'active',
                'address' => '123 Admin Street',
                'city' => 'Admin City',
                'state' => 'Admin State',
                'country' => 'United States',
                'email_verified_at' => Carbon::now(),
            ]
        );
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Seed up to 150 realistic customers (idempotent top-up)
        $targetCustomers = 150;
        $existingCustomers = User::where('role', 'user')->count();
        $toCreate = max(0, $targetCustomers - $existingCustomers);

        if ($toCreate === 0) {
            $this->command?->info("UserSeeder: {$existingCustomers} customers already exist — skipping.");
            return;
        }

        $this->command?->info("UserSeeder: Creating {$toCreate} realistic customers with varied account ages...");

        $locations = [
            ['city' => 'New York', 'state' => 'NY', 'country' => 'United States'],
            ['city' => 'San Francisco', 'state' => 'CA', 'country' => 'United States'],
            ['city' => 'Austin', 'state' => 'TX', 'country' => 'United States'],
            ['city' => 'Chicago', 'state' => 'IL', 'country' => 'United States'],
            ['city' => 'Seattle', 'state' => 'WA', 'country' => 'United States'],
            ['city' => 'London', 'state' => 'Greater London', 'country' => 'United Kingdom'],
            ['city' => 'Toronto', 'state' => 'ON', 'country' => 'Canada'],
            ['city' => 'Berlin', 'state' => 'Berlin', 'country' => 'Germany'],
            ['city' => 'Cairo', 'state' => 'Cairo', 'country' => 'Egypt'],
            ['city' => 'Dubai', 'state' => 'Dubai', 'country' => 'United Arab Emirates'],
        ];

        for ($i = 0; $i < $toCreate; $i++) {
            $loc = $locations[array_rand($locations)];

            // Account age distribution:
            // 15% brand-new (0-6 days) — realistic new signups + fraud risk testing
            // 35% recent (7-120 days)
            // 50% established loyal buyers (121-730 days)
            $roll = rand(1, 100);
            if ($roll <= 15) {
                $createdAt = Carbon::now()->subDays(rand(0, 6))->subHours(rand(0, 23));
                $isVerified = rand(1, 100) <= 50;
            } elseif ($roll <= 50) {
                $createdAt = Carbon::now()->subDays(rand(7, 120));
                $isVerified = rand(1, 100) <= 85;
            } else {
                $createdAt = Carbon::now()->subDays(rand(121, 730));
                $isVerified = rand(1, 100) <= 95;
            }

            $user = User::create([
                'name' => $faker->name(),
                'email' => $faker->unique()->safeEmail(),
                'phone' => $faker->numerify('+1-###-###-####'),
                'password' => 'user123',
                'role' => 'user',
                'status' => rand(1, 100) <= 92 ? 'active' : 'inactive',
                'address' => $faker->streetAddress(),
                'city' => $loc['city'],
                'state' => $loc['state'],
                'country' => $loc['country'],
                'email_verified_at' => $isVerified ? $createdAt->copy()->addMinutes(rand(5, 180)) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $user->assignRole('user');
        }

        $this->command?->info("UserSeeder: Total customers now " . User::where('role', 'user')->count() . ".");
    }
}

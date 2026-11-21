<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Carbon\Carbon;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Ensure Passport Personal Access Client exists for API login/register
        if (\Illuminate\Support\Facades\DB::table('oauth_personal_access_clients')->count() === 0) {
            \Illuminate\Support\Facades\Artisan::call('passport:client', [
                '--personal' => true,
                '--name' => 'PWA Ecommerce Personal Access Client',
                '--no-interaction' => true,
            ]);
        }

        // Create Super Admin User
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'phone' => '1234567891',
            'password' => 'superadmin123',
            'role' => 'admin',
            'status' => 'active',
            'address' => '1 Admin Plaza',
            'city' => 'Admin City',
            'state' => 'Admin State',
            'email_verified_at' => Carbon::now(),
            'country' => 'Admin Country',
            'created_at' => now(),
            'updated_at' => now(),

        ]);
        $superAdmin->assignRole('super_admin');

        // Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '1234567890',
            'password' => 'admin123',
            'role' => 'admin',
            'status' => 'active',
            'address' => '123 Admin Street',
            'city' => 'Admin City',
            'state' => 'Admin State',
            'email_verified_at' => Carbon::now(),
            'country' => 'Admin Country',
            'created_at' => now(),
            'updated_at' => now(),

        ]);
        $admin->assignRole('admin');

        // Create 10 Regular Users (Customers)
        for ($i = 0; $i < 10; $i++) {
            $user = User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->phoneNumber,
                'password' => 'user123',
                'role' => 'user',
                'status' => $faker->randomElement(['active', 'inactive']),
                'address' => $faker->address,
                'city' => $faker->city,
                'state' => $faker->state,
                'country' => $faker->country,
                'email_verified_at' => $faker->optional()->dateTimeThisYear(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $user->assignRole('user');
        }
    }
}

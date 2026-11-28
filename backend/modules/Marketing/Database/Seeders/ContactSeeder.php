<?php

namespace Modules\Marketing\Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'user')->get();

        if ($users->isEmpty()) {
            $this->command->warn('No users found! Please seed users first.');
            return;
        }

        // Create contact messages for each user
        foreach ($users as $user) {
            Contact::create([
                'name' => $user->name,
                'email' => $user->email,
                'message' => fake()->paragraphs(2, true),
                'user_id' => $user->id,
            ]);
        }

        // Create anonymous contact messages
        $anonymousNames = [
            'John Smith',
            'Sarah Johnson',
            'Michael Brown',
            'Emily Davis',
            'David Wilson'
        ];

        foreach ($anonymousNames as $index => $name) {
            Contact::create([
                'name' => $name,
                'email' => 'contact' . ($index + 1) . '@example.com',
                'message' => fake()->paragraphs(2, true),
            ]);
        }

        $this->command->info('Contact messages seeded successfully!');
    }
}

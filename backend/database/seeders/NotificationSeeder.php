<?php

namespace Database\Seeders;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;


class NotificationSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run() {

        $faker = Faker::create();

        $users = User::all();

        // If no users exist, create a test user
        if ($users->isEmpty()) {
            $user = User::create([
                'name'     => 'Test User',
                'email'    => 'test@example.com',
                'password' => bcrypt('password'),
            ]);
            $users = collect([$user]);
        }

        // Create notifications for each user
        foreach ($users as $user) {
            Notification::create([
                'user_id'   => $user->id,
                'type'      => 'welcome',
                'title'     => 'Welcome to Our Application',
                'message'   => 'Thank you for joining us, ' . $user->name . '!',
                'status'    => $faker->randomElement(['pending', 'sent', 'read']),
                'channel'   => $faker->randomElement(['email', 'realtime']),
                'read_at'   => $faker->boolean(30) ? now()->subDays($faker->numberBetween(0, 30)) : null,
                'sent_at'   => $faker->boolean(80) ? now()->subDays($faker->numberBetween(0, 30)) : null,
                'data'      => json_encode(['user_name' => $user->name]),
            ]);

            Notification::create([
                'user_id'   => $user->id,
                'type'      => 'promotional',
                'title'     => 'New Feature Released',
                'message'   => 'Check out the latest features in our application.',
                'status'    => $faker->randomElement(['pending', 'sent', 'read']),
                'channel'   => $faker->randomElement(['email', 'realtime']),
                'read_at'   => $faker->boolean(30) ? now()->subDays($faker->numberBetween(0, 30)) : null,
                'sent_at'   => $faker->boolean(80) ? now()->subDays($faker->numberBetween(0, 30)) : null,
                'data'      => json_encode(['feature' => 'new_dashboard']),
            ]);
        }

        $this->command->info('Notifications seeded successfully!');
    }

}

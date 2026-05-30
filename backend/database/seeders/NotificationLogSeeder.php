<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\NotificationLogs;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class NotificationLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        $notifications = Notification::all();

        // If no notifications exist, create some
        if ($notifications->isEmpty()) {
            $this->call(NotificationSeeder::class);
            $notifications = Notification::all();
        }

        $channels = ['email', 'database', 'broadcast', 'sms'];
        $statuses = ['sent', 'failed', 'pending', 'bounced'];

        // Create logs for each notification
        foreach ($notifications as $notification) {
            for ($i = 0; $i < $faker->numberBetween(1, 3); $i++) {
                NotificationLogs::create([
                    'notification_id' => $notification->id,
                    'channel'         => $faker->randomElement($channels),
                    'status'          => $faker->randomElement($statuses),
                    'response'        => $faker->optional(0.7)->text(100),
                    'error_message'   => $faker->optional(0.3)->text(50),
                    'attempt'         => $faker->numberBetween(1, 3),
                    'sent_at'         => $faker->optional(0.8)->dateTimeThisYear(),
                ]);
            }
        }
    }
}

<?php

namespace Modules\Marketing\Database\Seeders;

use App\Models\NotificationSettings;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();

        // If no users exist, create test users
        if ($users->isEmpty()) {
            $users = User::factory(5)->create();
        }

        // Create notification settings for each user
        foreach ($users as $user) {
            NotificationSettings::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'email_enabled' => true,
                    'sms_enabled' => false,
                    'realtime_enabled' => true,
                    'notification_types' => json_encode([
                        'orders' => true,
                        'promotions' => false,
                        'updates' => true,
                        'messages' => true,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}

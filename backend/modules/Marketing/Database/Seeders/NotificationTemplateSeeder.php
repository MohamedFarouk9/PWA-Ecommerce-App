<?php

namespace Modules\Marketing\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationTemplate;

class NotificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        NotificationTemplate::updateOrCreate(
            [
                'type' => 'order.created',
            ],
            [
                'name'      => 'Order Confirmation',
                'subject'   => 'Your Order #{order_id} Has Been Created',
                'body'      => 'Hello {user_name}, thank you for your order worth {total_amount}.',
                'variables' => ['order_id', 'user_name', 'total_amount'],
            ]
        );
    }
}

<?php

namespace Modules\Sales\Listeners;

use App\Services\Notifications\NotificationService;

class SendOrderCreatedNotification
{
    public function __construct(private NotificationService $notificationService) {}

    public function handle(object $event): void
    {
        $this->notificationService->send(
            'order.created',
            $event->order->user_id,
            [
                'order_id'  => $event->order->id,
                'user_name' => $event->order->user->name,
                'amount'    => $event->order->total_amount,
            ],
            ['email', 'realtime']
        );
    }
}

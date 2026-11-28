<?php

namespace Modules\Sales\Services;

use Modules\Sales\Models\Order;

class OrderTimelineService
{
    public function build(Order $order): array
    {
        $statuses = [
            'pending' => [
                'label'     => 'Order Placed',
                'icon'      => 'check',
                'completed' => true,
                'date'      => $order->created_at,
            ],
            'processing' => [
                'label'     => 'Processing',
                'icon'      => 'cog',
                'completed' => in_array($order->status, ['processing', 'shipped', 'delivered']),
                'date'      => null,
            ],
            'shipped' => [
                'label'     => 'Shipped',
                'icon'      => 'truck',
                'completed' => in_array($order->status, ['shipped', 'delivered']),
                'date'      => $order->shipped_at,
            ],
            'delivered' => [
                'label'     => 'Delivered',
                'icon'      => 'home',
                'completed' => $order->status === 'delivered',
                'date'      => $order->delivered_at,
            ],
        ];

        if ($order->status === 'cancelled') {
            $statuses['cancelled'] = [
                'label'     => 'Cancelled',
                'icon'      => 'times',
                'completed' => true,
                'date'      => $order->updated_at,
            ];
        }

        return $statuses;
    }
}

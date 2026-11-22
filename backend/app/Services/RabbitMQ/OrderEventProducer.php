<?php

namespace App\Services\RabbitMQ;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderEventProducer
{
    public const EXCHANGE = 'orders.topic';
    public const ROUTING_KEY_PLACED = 'order.placed';

    public function __construct(
        private RabbitMQProducer $producer
    ) {}

    /**
     * Publish a single `order.placed` event to `orders.topic`.
     * RabbitMQ automatically fans out this 1 message to 5 independent queues:
     *   1. orders.email.queue     (Confirmation email)
     *   2. orders.invoice.queue   (Invoice generation & storage)
     *   3. orders.inventory.queue (Low-stock alerts, cache invalidation & ES stock sync)
     *   4. orders.admin.queue     (Admin dashboard notification)
     *   5. orders.analytics.queue (Daily revenue, AOV & top-product counters)
     */
    public function publishOrderPlaced(Order $order): bool
    {
        $order->loadMissing(['user', 'items.product']);

        $payload = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'user_id' => $order->user_id,
            'customer_name' => $order->user?->name ?? 'Customer',
            'customer_email' => $order->user?->email ?? '',
            'total_amount' => (float) $order->total_amount,
            'discount_amount' => (float) ($order->discount_amount ?? 0),
            'coupon_code' => $order->coupon_code,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'placed_at' => ($order->created_at ?? now())->toIso8601String(),
            'items' => $order->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name ?? "Product #{$item->product_id}",
                'quantity' => (int) $item->quantity,
                'price' => (float) $item->price,
                'total' => (float) $item->total,
            ])->values()->all(),
        ];

        try {
            return $this->producer->publish(
                self::EXCHANGE,
                self::ROUTING_KEY_PLACED,
                $payload
            );
        } catch (\Throwable $e) {
            Log::warning('RabbitMQ order.placed publish skipped — broker unreachable', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

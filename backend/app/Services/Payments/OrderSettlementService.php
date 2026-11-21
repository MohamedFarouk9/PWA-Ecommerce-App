<?php

namespace App\Services\Payments;

use App\Events\Notifications\OrderCreated;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderSettlementService
{
    /**
     * Atomically settle a paid order:
     * 1. Lock order row (`lockForUpdate`) to guard against concurrent webhook + sync double-settlement.
     * 2. Lock product rows in ascending `product_id` order to prevent MySQL deadlocks.
     * 3. Decrement inventory, transition order to `paid`/`processing`, and clear user cart.
     * 4. Dispatch async `OrderCreated` notification event AFTER transaction commits.
     */
    public function settle(Order $order, string $gateway): bool
    {
        $settled = false;

        DB::transaction(function () use ($order, $gateway, &$settled) {
            $lockedOrder = Order::with('items')
                ->where('id', $order->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedOrder || $lockedOrder->payment_status === 'paid') {
                return;
            }

            // Deterministic lock ordering prevents cross-transaction deadlocks
            $sortedItems = $lockedOrder->items->sortBy('product_id');
            foreach ($sortedItems as $item) {
                $product = Product::where('id', $item->product_id)
                    ->lockForUpdate()
                    ->first();

                if ($product) {
                    $product->decrement('quantity', min($product->quantity, $item->quantity));
                }
            }

            $lockedOrder->update([
                'payment_status' => 'paid',
                'status' => 'processing',
                'payment_method' => $gateway,
            ]);

            Cart::where('user_id', $lockedOrder->user_id)->delete();
            $settled = true;
        });

        if ($settled) {
            $this->dispatchOrderCreatedEvent($order);
        }

        return $settled;
    }

    /**
     * Mark an order as refunded after upstream gateway confirms refund.
     */
    public function markRefunded(Order $order): void
    {
        $order->update([
            'payment_status' => 'refunded',
            'status' => 'cancelled',
        ]);
    }

    private function dispatchOrderCreatedEvent(Order $order): void
    {
        try {
            event(new OrderCreated($order->fresh()->load('user')));
        } catch (\Throwable $e) {
            Log::warning('OrderCreated notification event dispatch failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

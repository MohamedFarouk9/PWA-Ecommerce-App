<?php

namespace App\Console\Commands\Worker;

use App\Services\RabbitMQ\RabbitMQConsumer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OrderAnalyticsWorkerCommand extends Command
{
    protected $signature = 'worker:order-analytics';
    protected $description = 'Consume orders.analytics.queue to aggregate real-time daily revenue, order count, and top-product sales metrics';

    public function handle(): int
    {
        $queue = 'orders.analytics.queue';
        $this->info("Starting Order Analytics Worker on [{$queue}]...");

        $consumer = new RabbitMQConsumer($queue);

        try {
            // Consume messages using the RabbitMQ consumer
            $consumer->consume(function (array $envelope): bool {
                $payload = $envelope['data'] ?? $envelope;
                $orderId = (int) ($payload['order_id'] ?? 0);
                $orderNumber = (string) ($payload['order_number'] ?? '');
                $amount = (float) ($payload['total_amount'] ?? 0);
                $items = $payload['items'] ?? [];

                if ($orderId <= 0) {
                    return false;
                }

                // Idempotency guard so redelivered messages never double-count revenue
                $dedupKey = "analytics:processed:order:{$orderId}";
                if (Cache::has($dedupKey)) {
                    return true;
                }

                $dayKey = now()->format('Y-m-d');
                $ordersCountKey = "analytics:daily:{$dayKey}:orders";
                $revenueKey = "analytics:daily:{$dayKey}:revenue";

                $newOrderCount = ((int) Cache::get($ordersCountKey, 0)) + 1;
                $newRevenue = round(((float) Cache::get($revenueKey, 0)) + $amount, 2);

                Cache::put($ordersCountKey, $newOrderCount, now()->addDays(30));
                Cache::put($revenueKey, $newRevenue, now()->addDays(30));

                foreach ($items as $item) {
                    $pid = (int) ($item['product_id'] ?? 0);
                    $qty = (int) ($item['quantity'] ?? 0);
                    if ($pid > 0 && $qty > 0) {
                        $prodKey = "analytics:product:{$pid}:sold";
                        Cache::put($prodKey, ((int) Cache::get($prodKey, 0)) + $qty, now()->addDays(30));
                    }
                }

                Cache::put($dedupKey, true, now()->addDays(7));

                Log::info('analytics.order_placed', [
                    'order_id' => $orderId,
                    'order_number' => $orderNumber,
                    'amount' => $amount,
                    'daily_orders' => $newOrderCount,
                    'daily_revenue' => $newRevenue,
                    'payment_method' => $payload['payment_method'] ?? null,
                ]);

                $this->info("  ✓ Analytics updated for {$orderNumber} | Daily Orders: {$newOrderCount} | Daily Revenue: \${$newRevenue}");
                return true;
            });
        } catch (\Throwable $e) {
            $this->error("OrderAnalyticsWorker failed: {$e->getMessage()}");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

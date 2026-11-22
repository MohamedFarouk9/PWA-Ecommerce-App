<?php

namespace App\Console\Commands\Worker;

use App\Models\Product;
use App\Services\RabbitMQ\RabbitMQConsumer;
use App\Services\Search\ElasticsearchService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OrderInventoryWorkerCommand extends Command
{
    protected $signature = 'worker:order-inventory';
    protected $description = 'Consume orders.inventory.queue to reconcile stock caches, check low-stock thresholds, and sync stock to Elasticsearch';

    private const LOW_STOCK_THRESHOLD = 5;

    public function handle(ElasticsearchService $elasticsearchService): int
    {
        $queue = 'orders.inventory.queue';
        $this->info("Starting Order Inventory Reconciliation Worker on [{$queue}]...");

        $consumer = new RabbitMQConsumer($queue);

        try {
            $consumer->consume(function (array $envelope) use ($elasticsearchService): bool {
                $payload = $envelope['data'] ?? $envelope;
                $items = $payload['items'] ?? [];
                $orderNumber = (string) ($payload['order_number'] ?? 'N/A');

                if (empty($items)) {
                    return true;
                }

                foreach ($items as $item) {
                    $productId = (int) ($item['product_id'] ?? 0);
                    $product = $productId > 0 ? Product::with(['category', 'status'])->find($productId) : null;
                    if (!$product) {
                        continue;
                    }

                    // 1. Check low-stock alert threshold
                    if ((int) $product->quantity <= self::LOW_STOCK_THRESHOLD) {
                        Log::warning("LOW STOCK ALERT: Product #{$product->id} ({$product->name}) has only {$product->quantity} left!", [
                            'order_number' => $orderNumber,
                            'remaining_quantity' => $product->quantity,
                        ]);
                        $this->warn("  ⚠ Low Stock Alert: {$product->name} (qty: {$product->quantity})");
                    }

                    // 2. Sync updated stock quantity to Elasticsearch
                    $elasticsearchService->indexProduct($product);
                }

                // 3. Invalidate homepage/product caches so storefront reflects fresh stock
                Cache::forget('api.sections.homepage');

                $this->info("  ✓ Inventory reconciled & search synced for Order {$orderNumber}");
                return true;
            });
        } catch (\Throwable $e) {
            $this->error("OrderInventoryWorker failed: {$e->getMessage()}");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

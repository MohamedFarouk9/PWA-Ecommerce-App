<?php

namespace App\Console\Commands\Worker;

use App\Models\Order;
use App\Services\RabbitMQ\RabbitMQConsumer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OrderInvoiceWorkerCommand extends Command
{
    protected $signature = 'worker:order-invoice';
    protected $description = 'Consume orders.invoice.queue to asynchronously render and archive order invoices';

    public function handle(): int
    {
        $queue = 'orders.invoice.queue';
        $this->info("Starting Order Invoice Worker on [{$queue}]...");

        $consumer = new RabbitMQConsumer($queue);

        try {
            $consumer->consume(function (array $envelope): bool {
                $payload = $envelope['data'] ?? $envelope;
                $orderId = (int) ($payload['order_id'] ?? 0);

                if ($orderId <= 0) {
                    Log::warning('OrderInvoiceWorker received invalid order_id — routing to DLQ', $payload);
                    return false;
                }

                $order = Order::with(['items.product.category', 'user'])->find($orderId);
                if (!$order) {
                    return true;
                }

                $html = view('admin.orders.invoice', compact('order'))->render();
                $invoicePath = "invoices/{$order->order_number}.html";
                Storage::disk('public')->put($invoicePath, $html);

                Log::info("Invoice generated asynchronously for Order {$order->order_number}", [
                    'path' => $invoicePath,
                ]);
                $this->info("  ✓ Generated invoice: storage/app/public/{$invoicePath}");

                return true;
            });
        } catch (\Throwable $e) {
            $this->error("OrderInvoiceWorker failed: {$e->getMessage()}");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

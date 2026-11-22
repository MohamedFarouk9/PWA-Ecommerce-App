<?php

namespace App\Console\Commands\Worker;

use App\Models\Notification;
use App\Models\User;
use App\Services\RabbitMQ\RabbitMQConsumer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class OrderAdminWorkerCommand extends Command
{
    protected $signature = 'worker:order-admin';
    protected $description = 'Consume orders.admin.queue to notify all store administrators of new orders';

    public function handle(): int
    {
        $queue = 'orders.admin.queue';
        $this->info("Starting Admin Order Notification Worker on [{$queue}]...");

        $consumer = new RabbitMQConsumer($queue);

        try {
            $consumer->consume(function (array $envelope): bool {
                $payload = $envelope['data'] ?? $envelope;
                $orderNumber = (string) ($payload['order_number'] ?? '');
                $customerName = (string) ($payload['customer_name'] ?? 'Customer');
                $amount = number_format((float) ($payload['total_amount'] ?? 0), 2);

                if ($orderNumber === '') {
                    Log::warning('OrderAdminWorker received payload without order_number — routing to DLQ', $payload);
                    return false;
                }

                $admins = User::where('role', 'admin')->get();
                $title = "New Order Placed: {$orderNumber}";
                $message = "{$customerName} placed order {$orderNumber} for \${$amount}.";

                foreach ($admins as $admin) {
                    // Idempotent insert: prevents duplicate admin notifications on message redelivery
                    Notification::firstOrCreate(
                        [
                            'user_id' => $admin->id,
                            'title' => $title,
                        ],
                        [
                            'type' => 'order_placed',
                            'message' => $message,
                            'is_read' => false,
                        ]
                    );
                }

                $this->info("  ✓ Notified {$admins->count()} admin(s) about Order {$orderNumber}");
                return true;
            });
        } catch (\Throwable $e) {
            $this->error("OrderAdminWorker failed: {$e->getMessage()}");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

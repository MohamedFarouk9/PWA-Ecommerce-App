<?php

namespace App\Console\Commands\Worker;

use App\Models\Order;
use App\Services\RabbitMQ\RabbitMQConsumer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderEmailWorkerCommand extends Command
{
    protected $signature = 'worker:order-email';
    protected $description = 'Consume orders.email.queue to send asynchronous order confirmation emails';

    public function handle(): int
    {
        $queue = 'orders.email.queue';
        $this->info("Starting Order Confirmation Email Worker on [{$queue}]...");

        $consumer = new RabbitMQConsumer($queue);

        try {
            $consumer->consume(function (array $envelope): bool {
                $payload = $envelope['data'] ?? $envelope;
                $orderId = (int) ($payload['order_id'] ?? 0);
                $email = (string) ($payload['customer_email'] ?? '');

                if ($orderId <= 0 || $email === '') {
                    Log::warning('OrderEmailWorker received invalid payload — routing to DLQ', $payload);
                    return false;
                }

                $order = Order::with(['user', 'items.product'])->find($orderId);
                if (!$order) {
                    return true; // Order removed, nothing to email
                }

                $subject = "Order Confirmation — {$order->order_number}";
                $body = sprintf(
                    "<h2>Thank you for your order, %s!</h2><p>Order <strong>%s</strong> has been confirmed for <strong>$%s</strong> via %s.</p>",
                    e($order->user?->name ?? 'Customer'),
                    e($order->order_number),
                    number_format((float) $order->total_amount, 2),
                    strtoupper((string) $order->payment_method)
                );

                try {
                    Mail::html($body, function ($message) use ($email, $subject) {
                        $message->to($email)->subject($subject);
                    });
                } catch (\Throwable $mailErr) {
                    // Log SMTP failure in local dev without losing message visibility
                    Log::warning("SMTP send failed for Order #{$order->order_number}: {$mailErr->getMessage()}");
                }

                $this->info("  ✓ Confirmation email processed for Order {$order->order_number} -> {$email}");
                return true;
            });
        } catch (\Throwable $e) {
            $this->error("OrderEmailWorker failed: {$e->getMessage()}");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

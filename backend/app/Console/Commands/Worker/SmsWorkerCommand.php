<?php

namespace App\Console\Commands\Worker;

use App\Models\NotificationLogs;
use App\Services\RabbitMQ\RabbitMQConsumer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SmsWorkerCommand extends Command
{
    protected $signature = 'worker:sms';
    protected $description = 'Start RabbitMQ consumer for outbound SMS notifications';

    public function handle(): int
    {
        $this->info('Starting SMS Notification Worker...');
        Log::info('SMS Notification Worker started.');

        $consumer = new RabbitMQConsumer('notifications.sms.queue');

        try {
            $consumer->consume(function ($data) {
                $phone = $data['phone'] ?? ($data['payload']['phone'] ?? null);
                $message = $data['message'] ?? ($data['payload']['message'] ?? 'Order update');

                Log::info('Dispatching outbound SMS notification', [
                    'recipient' => $phone,
                    'message' => $message,
                ]);

                // Record audit log
                NotificationLogs::create([
                    'notification_id' => $data['notification_id'] ?? null,
                    'channel' => 'sms',
                    'status' => 'delivered',
                    'response' => json_encode(['phone' => $phone, 'status' => 'sent']),
                    'sent_at' => now(),
                ]);

                return true;
            });
        } catch (\Throwable $e) {
            $this->error("SMS Worker error: {$e->getMessage()}");
            Log::error("SMS Worker failed", ['error' => $e->getMessage()]);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

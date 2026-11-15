<?php

namespace App\Console\Commands\Worker;

use App\Models\NotificationLogs;
use App\Services\RabbitMQ\RabbitMQConsumer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DatabaseWorkerCommand extends Command
{
    protected $signature = 'worker:database';
    protected $description = 'Start RabbitMQ consumer for persistent database notification logs';

    public function handle(): int
    {
        $this->info('Starting RabbitMQ Database Audit Worker...');
        Log::info('Database Notification Worker started.');

        $consumer = new RabbitMQConsumer('notifications.database.queue');

        try {
            $consumer->consume(function ($data) {
                Log::info('Persisting notification log to database', ['data' => $data]);

                NotificationLogs::create([
                    'notification_id' => $data['notification_id'] ?? null,
                    'channel' => $data['channel'] ?? 'database',
                    'status' => $data['status'] ?? 'delivered',
                    'recipient_email' => $data['recipient_email'] ?? ($data['payload']['email'] ?? null),
                    'response' => json_encode($data),
                    'sent_at' => now(),
                ]);

                return true;
            });
        } catch (\Throwable $e) {
            $this->error("Database Worker encountered an error: {$e->getMessage()}");
            Log::error("Database Worker error: {$e->getMessage()}");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

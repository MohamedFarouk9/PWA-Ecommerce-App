<?php

namespace App\Console\Commands\RabbitMQ;

use App\Services\RabbitMQ\RabbitMQConnection;
use Illuminate\Console\Command;
use PhpAmqpLib\Wire\AMQPTable;

class SetupRabbitMQCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'rabbitmq:setup';

    /**
     * The console command description.
     */
    protected $description = 'Provision and version-control RabbitMQ exchanges, queues, DLQs, and topic bindings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting RabbitMQ topology provisioning...');

        try {
            $channel = RabbitMQConnection::getChannel('setup');

            // 1. Declare Exchanges
            $exchanges = config('rabbitmq.exchanges', []);
            foreach ($exchanges as $group => $items) {
                if (isset($items['type'])) {
                    $channel->exchange_declare($group, $items['type'], false, $items['durable'] ?? true, false);
                    $this->line("Declared exchange: <info>{$group}</info> ({$items['type']})");
                } elseif (is_array($items)) {
                    foreach ($items as $name => $config) {
                        $channel->exchange_declare($name, $config['type'] ?? 'topic', false, $config['durable'] ?? true, false);
                        $this->line("Declared exchange: <info>{$name}</info> ({$config['type']})");
                    }
                }
            }

            // 2. Declare Queues (with Dead-Letter & TTL Arguments)
            $queues = config('rabbitmq.queues', []);
            foreach ($queues as $name => $config) {
                $args = null;
                if (!empty($config['arguments'])) {
                    $args = new AMQPTable($config['arguments']);
                }

                $channel->queue_declare(
                    $name,
                    false,
                    $config['durable'] ?? true,
                    false,
                    false,
                    false,
                    $args
                );
                $this->line("Declared queue: <info>{$name}</info>");
            }

            // 3. Bind Queues to Exchanges
            $bindings = config('rabbitmq.bindings', []);
            foreach ($bindings as $binding) {
                $channel->queue_bind(
                    $binding['queue'],
                    $binding['exchange'],
                    $binding['routing_key']
                );
                $this->line("Bound <info>{$binding['queue']}</info> -> <comment>{$binding['exchange']}</comment> [{$binding['routing_key']}]");
            }

            $this->info('✓ RabbitMQ messaging topology successfully provisioned!');
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('Failed to configure RabbitMQ topology: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

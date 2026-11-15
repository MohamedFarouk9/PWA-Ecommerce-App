<?php
namespace App\Services\RabbitMQ;

use App\Services\RabbitMQ\RabbitMQConnection;
use Exception;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQProducer
{
    private RabbitMQConnection $connection;

    public function __construct()
    {
        $this->connection = new RabbitMQConnection();
    }

    /**
     * Publish message to RabbitMQ
     *
     * @param string $exchange
     * @param string $routingKey
     * @param array $data
     * @param array $options
     */

    public function publish(string $exchange, string $routingKey, array $data, array $options = []): bool
    {
        try {
            $channel = RabbitMQConnection::getChannel($exchange);

            // Declare exchange if not exists
            $channel->exchange_declare(
                $exchange,
                $this->getExchangeType($exchange),
                false,  //  passive=false create if not exists
                true,   // durable=true survives broker restart
                false   // auto_delete=false not deleted when last queue unbound
            );

            // Prepare message
            $messageBody = json_encode([
                'id'        => uniqid(),
                'timestamp' => now()->toIso8601String(),
                'data'      => $data,
            ]);

            $properties = array_merge([
                'content_type'   => 'application/json',
                'delivery_mode'  => AMQPMessage::DELIVERY_MODE_PERSISTENT, // persistent Message saved to disk
                'correlation_id' => uniqid(),  // Used for tracing / request-reply
            ], $options);

            // Create AMQP message object
            $message = new AMQPMessage($messageBody, $properties);

            // Publish message to exchange with routing key
            $channel->basic_publish($message, $exchange, $routingKey);

            // What happens? Producer → Exchange → Routing Key → Queue(s)

            Log::info("Message published to {$exchange}", [
                'routing_key' => $routingKey,
                'message_id'  => $message->get('correlation_id'),
            ]);

            return true;
        } catch (Exception $e) {
            Log::error("Failed to publish message to {$exchange}: " . $e->getMessage());
            throw $e;
        }

    }

    /**
     * Publish notification event
     */
    public function publishNotification(string $type, array $data): bool
    {
        return $this->publish('notifications.topic', "notification.{$type}", [
            'type'    => $type,
            'user_id' => $data['user_id'] ?? null,
            'payload' => $data,
        ]);
    }

    /**
     * Publish email task
     */
    public function publishEmail(array $emailData): bool
    {
        return $this->publish(
            'notifications.topic',
            'notification.email.send',
            $emailData
        );
    }

    /**
     * Bulk publish messages
     */
    public function publishBatch(string $exchange, array $messages): int
    {
        $count = 0;
        foreach ($messages as $routingKey => $data) {
            if ($this->publish($exchange, $routingKey, $data)) {
                $count++;
            }
        }

        return $count;
    }

    // Get exchange type from config

    private function getExchangeType(string $exchange): string
    {
        //Flexible (topic / direct / fanout) 
        return config("rabbitmq.exchanges.{$exchange}.type", 'topic');
    }

}

<?php

namespace App\Listeners\Orders;

use App\Events\Notifications\OrderCreated;
use App\Services\RabbitMQ\OrderEventProducer;

class PublishOrderPlacedToRabbitMQ
{
    public function __construct(
        private OrderEventProducer $producer
    ) {}

    /**
     * Triggered after atomic order settlement commits.
     * Publishes 1 message to RabbitMQ `orders.topic` -> fans out to 5 consumer queues.
     */
    public function handle(OrderCreated $event): void
    {
        $this->producer->publishOrderPlaced($event->order);
    }
}

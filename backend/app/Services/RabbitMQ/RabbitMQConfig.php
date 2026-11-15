<?php

namespace App\Services\RabbitMQ;

class RabbitMQConfig
{
    // Exchange Names
    public const EXCHANGE_NOTIFICATIONS_TOPIC = 'notifications.topic';
    public const EXCHANGE_EMAILS_DIRECT = 'emails.direct';
    public const EXCHANGE_REALTIME_FANOUT = 'realtime.fanout';

    // Queue Names
    public const QUEUE_EMAIL = 'notifications.email.queue';
    public const QUEUE_SMS = 'notifications.sms.queue';
    public const QUEUE_REALTIME = 'notifications.realtime.queue';
    public const QUEUE_DATABASE = 'notifications.database.queue';
    public const QUEUE_DLQ = 'notifications.dlq';

    // Routing Keys
    public const ROUTING_EMAIL_SEND = 'notifications.email.send';
    public const ROUTING_SMS_SEND = 'notifications.sms.send';
    public const ROUTING_ORDER_CREATED = 'notifications.order.created';

    public static function getHost(): string
    {
        return config('rabbitmq.host', 'localhost');
    }

    public static function getPort(): int
    {
        return (int) config('rabbitmq.port', 5672);
    }

    public static function getUsername(): string
    {
        return config('rabbitmq.username', 'guest');
    }

    public static function getPassword(): string
    {
        return config('rabbitmq.password', 'guest');
    }

    public static function getVhost(): string
    {
        return config('rabbitmq.vhost', '/');
    }
}


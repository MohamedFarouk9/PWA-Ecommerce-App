<?php

return [
    'host'               => env('RABBITMQ_HOST', 'localhost'),
    'port'               => env('RABBITMQ_PORT', 5672),
    'username'           => env('RABBITMQ_USERNAME', 'guest'),
    'password'           => env('RABBITMQ_PASSWORD', 'guest'),
    'vhost'              => env('RABBITMQ_VHOST', '/'),

    'connection_timeout' => 10,
    'read_write_timeout' => 30,
    'heartbeat'          => 60,

    'pool'               => [
        'size'    => 10,
        'timeout' => 30,
    ],

    // Exchanges (Topic, Direct, Fanout + Dead-Letter Exchanges)
    'exchanges'          => [
        'notifications' => [
            'notifications.topic' => ['type' => 'topic', 'durable' => true, 'auto_delete' => false],
            'emails.direct'       => ['type' => 'direct', 'durable' => true, 'auto_delete' => false],
            'realtime.fanout'     => ['type' => 'fanout', 'durable' => true, 'auto_delete' => false],
            'dlx.notifications'   => ['type' => 'direct', 'durable' => true, 'auto_delete' => false],
        ],
        'orders' => [
            'orders.topic' => ['type' => 'topic', 'durable' => true, 'auto_delete' => false],
            'dlx.orders'   => ['type' => 'direct', 'durable' => true, 'auto_delete' => false],
        ],
    ],

    // Queues
    'queues'             => [
        // Notifications
        'notifications.email.queue' => [
            'durable'   => true,
            'arguments' => [
                'x-dead-letter-exchange'    => 'dlx.notifications',
                'x-dead-letter-routing-key' => 'notifications.email.deadletter',
                'x-message-ttl'             => 3600000,
            ],
        ],
        'notifications.sms.queue' => [
            'durable'   => true,
            'arguments' => ['x-dead-letter-exchange' => 'dlx.notifications'],
        ],
        'notifications.realtime.queue'   => ['durable' => false, 'arguments' => []],
        'notifications.database.queue'   => ['durable' => true, 'arguments' => []],
        'notifications.deadletter.queue' => ['durable' => true, 'arguments' => []],

        // Order Placed Fan-Out Queues (1 event -> 5 independent consumers)
        'orders.email.queue' => [
            'durable'   => true,
            'arguments' => [
                'x-dead-letter-exchange'    => 'dlx.orders',
                'x-dead-letter-routing-key' => 'orders.deadletter',
                'x-message-ttl'             => 86400000,
            ],
        ],
        'orders.invoice.queue' => [
            'durable'   => true,
            'arguments' => [
                'x-dead-letter-exchange'    => 'dlx.orders',
                'x-dead-letter-routing-key' => 'orders.deadletter',
                'x-message-ttl'             => 86400000,
            ],
        ],
        'orders.inventory.queue' => [
            'durable'   => true,
            'arguments' => [
                'x-dead-letter-exchange'    => 'dlx.orders',
                'x-dead-letter-routing-key' => 'orders.deadletter',
                'x-message-ttl'             => 86400000,
            ],
        ],
        'orders.admin.queue' => [
            'durable'   => true,
            'arguments' => [
                'x-dead-letter-exchange'    => 'dlx.orders',
                'x-dead-letter-routing-key' => 'orders.deadletter',
                'x-message-ttl'             => 86400000,
            ],
        ],
        'orders.analytics.queue' => [
            'durable'   => true,
            'arguments' => [
                'x-dead-letter-exchange'    => 'dlx.orders',
                'x-dead-letter-routing-key' => 'orders.deadletter',
                'x-message-ttl'             => 86400000,
            ],
        ],
        'orders.deadletter.queue' => ['durable' => true, 'arguments' => []],
    ],

    // Bindings (Queue -> Exchange -> Routing Key)
    'bindings' => [
        // Notifications
        ['queue' => 'notifications.email.queue', 'exchange' => 'notifications.topic', 'routing_key' => 'notifications.email.*'],
        ['queue' => 'notifications.sms.queue', 'exchange' => 'notifications.topic', 'routing_key' => 'notifications.sms.*'],
        ['queue' => 'notifications.realtime.queue', 'exchange' => 'realtime.fanout', 'routing_key' => '#'],
        ['queue' => 'notifications.database.queue', 'exchange' => 'notifications.topic', 'routing_key' => 'notifications.*'],
        ['queue' => 'notifications.deadletter.queue', 'exchange' => 'dlx.notifications', 'routing_key' => 'notifications.email.deadletter'],

        // Order Placed Fan-Out (all 5 queues receive every `order.placed` event)
        ['queue' => 'orders.email.queue', 'exchange' => 'orders.topic', 'routing_key' => 'order.placed'],
        ['queue' => 'orders.invoice.queue', 'exchange' => 'orders.topic', 'routing_key' => 'order.placed'],
        ['queue' => 'orders.inventory.queue', 'exchange' => 'orders.topic', 'routing_key' => 'order.placed'],
        ['queue' => 'orders.admin.queue', 'exchange' => 'orders.topic', 'routing_key' => 'order.placed'],
        ['queue' => 'orders.analytics.queue', 'exchange' => 'orders.topic', 'routing_key' => 'order.placed'],
        ['queue' => 'orders.deadletter.queue', 'exchange' => 'dlx.orders', 'routing_key' => 'orders.deadletter'],
    ],

    // Worker QoS Prefetch & Retry Settings
    'workers' => [
        'email'                  => ['prefetch_count' => 10, 'timeout' => 30, 'max_retries' => 3, 'retry_delay' => 5],
        'sms'                    => ['prefetch_count' => 20, 'timeout' => 15, 'max_retries' => 2, 'retry_delay' => 3],
        'realtime'               => ['prefetch_count' => 50, 'timeout' => 10, 'max_retries' => 1, 'retry_delay' => 1],
        'database'               => ['prefetch_count' => 5, 'timeout' => 60, 'max_retries' => 0, 'retry_delay' => 10],
        'orders.email.queue'     => ['prefetch_count' => 10, 'timeout' => 30, 'max_retries' => 3],
        'orders.invoice.queue'   => ['prefetch_count' => 5, 'timeout' => 45, 'max_retries' => 3],
        'orders.inventory.queue' => ['prefetch_count' => 10, 'timeout' => 20, 'max_retries' => 3],
        'orders.admin.queue'     => ['prefetch_count' => 20, 'timeout' => 15, 'max_retries' => 2],
        'orders.analytics.queue' => ['prefetch_count' => 50, 'timeout' => 10, 'max_retries' => 1],
    ],
];

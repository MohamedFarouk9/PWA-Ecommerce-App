<?php

return [
    'host'               => env('RABBITMQ_HOST', 'localhost'), // RabbitMQ server hostname/IP
    'port'               => env('RABBITMQ_PORT', 5672),        // Default AMQP port
    'username'           => env('RABBITMQ_USERNAME', 'guest'), // Authentication username
    'password'           => env('RABBITMQ_PASSWORD', 'guest'), // Authentication password
    'vhost'              => env('RABBITMQ_VHOST', '/'),        // Virtual host (like a namespace)

                                //Connection Parameters
    'connection_timeout' => 10, // Seconds to establish connection
    'read_write_timeout' => 30, // I/O operation timeout
    'heartbeat'          => 60, // Heartbeat interval (keeps connection alive)
    /*Heartbeat: Prevents firewalls from dropping idle connections. If no activity for 60s, sends a heartbeat frame */

    // Connection pool settings
    'pool'               => [
        'size'    => 10, // Number of concurrent connections
        'timeout' => 30, // Timeout for acquiring a connection from the pool in seconds
    ],

    // Exchange and queue settings configuration
    'exchanges'          => [
        'notifications' => [
            'notifications.topic' => [
                'type'        => 'topic', // Routes based on pattern matching (notifications.topic)
                'durable'     => true,    // Durable exchange survives broker restarts
                'auto_delete' => false,   // Delete when no queues bound
            ],
             // Email sending - MUST survive restarts!
            'emails.direct'       => [
                'type'        => 'direct', // Routes based on exact routing key matching (emails.direct)
                'durable'     => true,     // ✓ If server crashes, emails still get sent
             // 'durable' => false,        // ✗ If server crashes, no big deal
                'auto_delete' => false,
            ],
            'realtime.fanout'     => [
                'type'        => 'fanout', // Broadcasts to ALL bound queues (realtime.fanout)
                'durable'     => true,
                'auto_delete' => false,
            ],
        ],
    ],

    // Queue configurations
    'queues'             => [
        'notifications.email.queue'    => [
            'durable'   => true,
            'arguments' => [
                //Dead Letter Exchange (DLX): When messages fail (rejected/expired), they go to DLX for analysis.
                'x-dead-letter-exchange'    => 'dlx.notifications',              // DLX for failed messages
                'x-dead-letter-routing-key' => 'notifications.email.deadletter', // Routing for DL
                'x-message-ttl'             => 3600000,                          // Messages expire after 1h (ms)
            ],
        ],
        'notifications.sms.queue'      => [
            'durable'   => true,
            'arguments' => [
                'x-dead-letter-exchange' => 'dlx.notifications',
            ],
        ],
        'notifications.realtime.queue' => [
            'durable'   => false,
            'arguments' => [],
        ],
        'notifications.database.queue' => [
            'durable' => true,
            'arguments' => [],
        ],
    ],

    // Bindings (Queue -> Exchange -> Routing Key)
    'bindings' => [
        // Email Queue Bindings
        [
            'queue'        => 'notifications.email.queue',
            'exchange'     => 'notifications.topic',
            'routing_key'  => 'notifications.email.*', // Pattern matching
        ],
        // SMS Queue Bindings
        [
            'queue'        => 'notifications.sms.queue',
            'exchange'     => 'notifications.topic',
            'routing_key'  => 'notifications.sms.*',
        ],
        // Realtime Queue Bindings
        [
            'queue'        => 'notifications.realtime.queue',
            'exchange'     => 'realtime.fanout',
            'routing_key'  => '#', // Fanout ignores routing keys
        ],
        // Database Queue Bindings
        [
            'queue'        => 'notifications.database.queue',
            'exchange'     => 'notifications.topic',
            'routing_key'  => 'notifications.*',
        ],
    ],

    // Worker settings
    'workers' => [
        'email' => [
            'prefetch_count' => 10, // Number of messages to fetch at once (before acknowledging)
            'timeout'        => 30, // Max processing time per message in seconds
            'max_retries'    => 3,  // Retry attempts on failure
            'retry_delay'   => 5,   // Seconds between retries
        ],
        'sms' => [
            'prefetch_count' => 20,
            'timeout'        => 15,
            'max_retries'    => 2,
            'retry_delay'   => 3,
        ],
        'realtime' => [
            'prefetch_count' => 50,
            'timeout'        => 10,
            'max_retries'    => 1,
            'retry_delay'   => 1,
        ],
        'database' => [
            'prefetch_count' => 5,
            'timeout'        => 60,
            'max_retries'    => 0,  // Don't retry, save to DLQ
            'retry_delay'   => 10,
        ],
    ],

];



/*Durable = true:


RabbitMQ Restart → Looks for saved files → Loads everything → Continues
           ↓
Messages are saved to: /var/lib/rabbitmq/mnesia/


Durable = false:

RabbitMQ Restart → No saved files → Starts fresh → Empty queues
           ↓
Messages are only in RAM → POOF! Gone forever
 */

//Exchange Types

// 'type' => 'topic',     // Routes based on pattern matching (notifications.*)
// 'type' => 'direct',    // Routes based on exact routing key match
// 'type' => 'fanout',    // Broadcasts to ALL bound queues
// 'durable' => true,     // Survives broker restart
// 'auto_delete' => false // Delete when no queues bound

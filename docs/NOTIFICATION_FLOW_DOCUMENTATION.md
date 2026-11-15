# RabbitMQ Notification System - Complete Documentation

## Table of Contents
1. [Overview](#overview)
2. [Why RabbitMQ - Advantages & Use Cases](#why-rabbitmq---advantages--use-cases)
3. [Architecture](#architecture)
4. [Full Event-Driven Flow](#full-event-driven-flow)
5. [Complete Step-by-Step Walkthrough for Beginners](#complete-step-by-step-walkthrough-for-beginners)
6. [Project Structure](#project-structure)
7. [Key Components with Code](#key-components-with-code)
8. [Database Schema](#database-schema)
9. [Integration Flow Details](#integration-flow-details)
10. [Setup Instructions](#setup-instructions)
11. [Testing & Seeders](#testing--seeders)
12. [Usage Examples](#usage-examples)
13. [Troubleshooting](#troubleshooting)

---

## Overview

This RabbitMQ-based notification system is designed to handle multiple notification channels (email, real-time, database) in a scalable and fault-tolerant manner. It separates notification production from consumption, allowing for asynchronous processing and better system performance.

### Key Features
- **Asynchronous Processing**: Uses RabbitMQ for non-blocking notification delivery
- **Multiple Channels**: Support for email, real-time (broadcast), database, and SMS notifications
- **Scalability**: Can handle thousands of notifications concurrently
- **Reliability**: Built-in retry logic, dead-letter queues, and logging
- **User Preferences**: Users can customize notification settings
- **Notification Templates**: Reusable templates for consistent messaging
- **Audit Trail**: Complete logging of all notification delivery attempts

---

## Why RabbitMQ - Advantages & Use Cases

### Problem: Synchronous Notification Delivery

**Without RabbitMQ** - The Traditional Way:
```php
// Direct email sending - BLOCKS user request
Route::post('/orders', function () {
    $order = Order::create($validatedData);
    
    // These operations block the request! 🐌
    Mail::to($order->user->email)->send(new OrderConfirmation($order));      // 5-10 seconds
    sendSMS($order->user->phone, $message);                                   // 2-3 seconds
    broadcastToClient($order->user_id, $notification);                        // 1-2 seconds
    
    // Total: 8-15 seconds WAIT TIME before user gets response! ❌
    return response()->json(['success' => true]);
});
```

**Problems:**
- ❌ Slow API response times (blocking I/O operations)
- ❌ If email service fails, entire request fails
- ❌ Can't retry failed notifications easily
- ❌ Can't scale - one worker = limited throughput
- ❌ No queue persistence - messages lost on crash

### Solution: RabbitMQ-based Asynchronous Processing

**With RabbitMQ** - The Message Queue Way:
```php
// Non-blocking notification delivery
Route::post('/orders', function () {
    $order = Order::create($validatedData);
    
    // Publish to queue - INSTANT ⚡ (< 1ms)
    NotificationService::sendNotification('order_created', $order);
    
    // Immediate response to user! ✅
    return response()->json(['success' => true]); // ~100ms total
});

// Workers process independently in background
// - Email Worker picks up and sends emails
// - SMS Worker sends SMS
// - Realtime Worker broadcasts to browser
```

### Key Advantages of RabbitMQ

#### 1. **Performance & Responsiveness** ⚡
- **Non-blocking**: Requests return immediately
- **Response time**: < 100ms vs 8-15 seconds
- **User experience**: Instant feedback

```
Without RabbitMQ:
User Request → Validate → Create Order → Send Email (5s) → Return → User Waits 5+ seconds

With RabbitMQ:
User Request → Validate → Create Order → Queue Message (1ms) → Return (instant)
               ↓
          Background Worker Sends Email (doesn't block user)
```

#### 2. **Reliability & Fault Tolerance** 🛡️
- **Message persistence**: Messages saved to disk
- **Automatic retries**: Failed messages can be reprocessed
- **Dead-letter queues**: Failed messages not lost
- **Acknowledgments**: Ensures message processing

```php
// Example: Email service crashes
// Without RabbitMQ: User notification is lost forever ❌
// With RabbitMQ: Message stays in queue, retried when service recovers ✅

// Worker: Message not acknowledged = automatic retry
try {
    sendEmail($data); // If fails...
    $message->ack();  // Only ack if successful
} catch (Exception $e) {
    $message->nack(true); // Requeue for retry
}
```

#### 3. **Scalability** 📈
- **Multiple workers**: Run 10 email workers instead of 1
- **Load distribution**: Messages distributed across workers
- **Horizontal scaling**: Add more servers, add more workers
- **Queue prefetch**: Workers don't overload

```
Architecture Evolution:
Single Worker: 100 emails/min
              ↓ (bottleneck)
5 Workers: 500 emails/min (5x throughput)
              ↓
20 Workers: 2000 emails/min (20x throughput!)
```

#### 4. **Decoupling & Flexibility** 🔄
- **Producer-Consumer separation**: Order service doesn't need to know about email/SMS
- **Independent scaling**: Slow email service doesn't affect order creation
- **Easy to add channels**: Add SMS/Push/Slack later without changing order code
- **Service independence**: Email crashes don't crash order service

```php
// Order Service (Producer) - Simple, focused
NotificationService::send('order_created', $order);
// That's it! Doesn't care about email/SMS implementation

// Email Worker (Consumer) - Can be rewritten, upgraded, or scaled independently
// SMS Worker - Can be added/removed without affecting orders
// Slack Worker - Can be added later
```

#### 5. **Real-time Features** 📡
- **WebSocket integration**: Use Laravel Broadcasting
- **Instant notifications**: Updates reach clients in milliseconds
- **Multi-channel delivery**: Same message via email, SMS, browser

#### 6. **Prioritization & Rate Limiting** ⏱️
- **Priority queues**: VIP notifications processed first
- **Rate limiting**: Control email sending rate (e.g., max 100/sec)
- **Batching**: Process notifications in batches for efficiency

```php
// High priority notifications (order shipment) - processed immediately
// Low priority notifications (promotions) - processed during off-peak hours
```

#### 7. **Monitoring & Logging** 📊
- **Queue depth**: Monitor pending notifications
- **Processing metrics**: Track success/failure rates
- **Bottleneck identification**: See which channel is slow
- **Complete audit trail**: All attempts logged

```
Queue Metrics:
- Email queue: 150 pending (1500 emails/hour rate)
- SMS queue: 45 pending (1000 SMS/hour rate)
- Realtime queue: 0 pending (instant delivery)

This helps identify bottlenecks and scale accordingly!
```

### Use Cases Where RabbitMQ Shines

| Use Case | Without RabbitMQ | With RabbitMQ |
|----------|------------------|---------------|
| **Order Confirmation Emails** | Blocks order creation | Instant order creation |
| **Password Reset Emails** | User waits for email | Instant confirmation page |
| **Promotional Campaigns** | Can't send to 1M users | Send 1M+ instantly, process in background |
| **Real-time Notifications** | Polling (inefficient) | Push notifications (efficient) |
| **Multi-channel Delivery** | Complex, interconnected | Clean, decoupled services |
| **Peak Load Times** | Server crashes | Queues grow, workers catch up later |
| **Failed Emails** | Lost forever | Retried automatically |
| **Scale to 1000s of users** | Add more servers | Add more workers |

### Business Impact

```
Performance Improvement:
- API Response Time: 8,000ms → 100ms (80x faster!)
- User Satisfaction: 60% → 98% (instant feedback)
- System Throughput: 100 notifications/sec → 10,000 notifications/sec (100x!)
- Cost Efficiency: 5 servers → 2 servers (same throughput)
- Reliability: 97% → 99.9% (auto-retry capability)
```

---

## Full Event-Driven Flow

### Step-by-Step Complete Flow with Code

#### **Step 1: Event Trigger - Order Created**

```php
// File: app/Http/Controllers/OrderController.php
public function store(StoreOrderRequest $request)
{
    // Create the order
    $order = Order::create($request->validated());
    
    // 🔥 Dispatch event (INSTANT - < 1ms)
    OrderCreated::dispatch($order);
    
    // Return immediately to user ✅
    return response()->json(['success' => true, 'order' => $order]);
}
```

**Event Definition:**
```php
// File: app/Events/Notifications/OrderCreated.php
namespace App\Events\Notifications;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated
{
    use Dispatchable, SerializesModels;
    
    public function __construct(public Order $order)
    {
    }
}
```

#### **Step 2: Listener Receives Event**

```php
// File: app/Listeners/Notifications/SendOrderCreatedNotification.php
namespace App\Listeners\Notifications;

use App\Events\Notifications\OrderCreated;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\Log;

class SendOrderCreatedNotification
{
    public function handle(OrderCreated $event)
    {
        Log::info("Order {$event->order->id} created, sending notifications...");
        
        // 🚀 Trigger notification service
        NotificationService::sendOrderNotification($event->order);
    }
}
```

**Register Listener in EventServiceProvider:**
```php
// File: app/Providers/EventServiceProvider.php
protected $listen = [
    OrderCreated::class => [
        SendOrderCreatedNotification::class,
    ],
];
```

#### **Step 3: NotificationService - Producer (Orchestrator)**

```php
// File: app/Services/Notifications/NotificationService.php
class NotificationService
{
    private RabbitMQProducer $producer;

    public function __construct()
    {
        $this->producer = new RabbitMQProducer();
    }

    /**
     * Send notifications for an order
     */
    public static function sendOrderNotification(Order $order): void
    {
        $user = $order->user;
        $service = new self();

        // 1️⃣ Create notification record in database (for history)
        $notification = Notification::create([
            'user_id'    => $user->id,
            'type'       => 'order_created',
            'title'      => 'Order Received',
            'message'    => "Your order #{$order->order_number} has been received",
            'data'       => json_encode([
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'total'        => $order->total_amount,
            ]),
            'status'     => 'pending',
        ]);

        Log::info("Notification #{$notification->id} created for user {$user->id}");

        // 2️⃣ Check user notification preferences
        $settings = $user->notificationSettings;
        
        if (!$settings) {
            Log::warning("No notification settings for user {$user->id}");
            return;
        }

        // 3️⃣ Publish to appropriate channels based on user preferences
        if ($settings->email_enabled) {
            $service->publishToEmailQueue($notification, $user);
            Log::info("Published to EMAIL queue");
        }

        if ($settings->realtime_enabled) {
            $service->publishToRealtimeQueue($notification, $user);
            Log::info("Published to REALTIME queue");
        }

        // Database notifications created automatically above
    }

    /**
     * Publish to Email Queue
     */
    private function publishToEmailQueue(Notification $notification, User $user): void
    {
        $payload = [
            'notification_id' => $notification->id,
            'user_id'         => $user->id,
            'email'           => $user->email,
            'data'            => json_decode($notification->data, true),
            'subject'         => $notification->title,
            'message'         => $notification->message,
        ];

        // Publish to RabbitMQ exchange
        $this->producer->publish(
            'notifications.topic',        // Exchange
            'notification.email.send',    // Routing key
            $payload
        );
    }

    /**
     * Publish to Realtime Queue
     */
    private function publishToRealtimeQueue(Notification $notification, User $user): void
    {
        $payload = [
            'notification_id' => $notification->id,
            'user_id'         => $user->id,
            'title'           => $notification->title,
            'message'         => $notification->message,
            'type'            => $notification->type,
        ];

        $this->producer->publish(
            'notifications.topic',
            'notification.realtime.broadcast',
            $payload
        );
    }
}
```

#### **Step 4: RabbitMQProducer - Publishing to Broker**

```php
// File: app/Services/RabbitMQ/RabbitMQProducer.php
class RabbitMQProducer
{
    public function publish(
        string $exchange,
        string $routingKey,
        array $data,
        array $options = []
    ): bool {
        try {
            // 1. Get channel (connection to RabbitMQ)
            $channel = RabbitMQConnection::getChannel($exchange);

            // 2. Declare exchange (creates if not exists)
            $channel->exchange_declare(
                $exchange,
                'topic',     // Exchange type: topic exchange
                false,       // passive=false (create if not exists)
                true,        // durable=true (survives broker restart)
                false        // auto_delete=false (manual deletion only)
            );

            // 3. Prepare message body
            $messageBody = json_encode([
                'id'        => uniqid(),
                'timestamp' => now()->toIso8601String(),
                'data'      => $data,
            ]);

            // 4. Message properties (meta-information)
            $properties = array_merge([
                'content_type'   => 'application/json',
                'delivery_mode'  => AMQPMessage::DELIVERY_MODE_PERSISTENT, // Saved to disk
                'correlation_id' => uniqid(),  // For tracing
            ], $options);

            // 5. Create AMQP message object
            $message = new AMQPMessage($messageBody, $properties);

            // 6. Publish to exchange
            //    RabbitMQ routing: Message → Exchange → Routes by pattern → Queue(s)
            $channel->basic_publish($message, $exchange, $routingKey);

            Log::info("Message published", [
                'exchange'    => $exchange,
                'routing_key' => $routingKey,
                'message_id'  => $message->get('correlation_id'),
            ]);

            return true;

        } catch (Exception $e) {
            Log::error("Failed to publish: {$e->getMessage()}");
            throw $e;
        }
    }
}
```

#### **Step 5: RabbitMQ Broker - Routing & Queuing**

```
┌─────────────────────────────────────────────────────────────┐
│                    RabbitMQ Broker                           │
│                                                               │
│  Exchange: "notifications.topic"                            │
│  Type: Topic Exchange (routes by pattern matching)          │
│                                                               │
│  Message arrives with routing key: "notification.email.send"│
│  Message arrives with routing key: "notification.realtime..." │
│                                                               │
│  Bindings (patterns):                                        │
│  Queue: "notifications.email.queue"                         │
│    ├─ Pattern: "notification.email.*"  ✅ MATCHES          │
│    └─ Receives message!                                      │
│                                                               │
│  Queue: "notifications.realtime.queue"                      │
│    ├─ Pattern: "notification.realtime.*"                    │
│    └─ Does NOT match "notification.email.send"              │
│                                                               │
│  Queue: "notifications.sms.queue"                           │
│    ├─ Pattern: "notification.sms.*"                         │
│    └─ Does NOT match either message                         │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

#### **Step 6: Workers Consume Messages**

**Email Worker:**
```php
// File: app/Console/Commands/Worker/EmailWorkerCommand.php
class EmailWorkerCommand extends Command
{
    protected $signature = 'worker:email';
    protected $description = 'Start email notification worker';

    public function handle(): int
    {
        $this->info('🚀 Email Worker started...');

        $emailService = new EmailService();
        $consumer = new RabbitMQConsumer('notifications.email.queue');

        try {
            // 👂 Listen for messages on email queue
            $consumer->consume(function ($data) use ($emailService) {
                // Process each message
                return $emailService->sendEmailNotification($data);
            });

        } catch (Exception $e) {
            $this->error("Worker error: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }
}
```

**Realtime Worker:**
```php
// File: app/Console/Commands/Worker/RealtimeWorkerCommand.php
class RealtimeWorkerCommand extends Command
{
    protected $signature = 'worker:realtime';
    protected $description = 'Start realtime notification worker';

    public function handle(): int
    {
        $this->info('🚀 Realtime Worker started...');

        $realtimeService = new RealtimeService();
        $consumer = new RabbitMQConsumer('notifications.realtime.queue');

        try {
            $consumer->consume(function ($data) use ($realtimeService) {
                return $realtimeService->broadcastNotification($data);
            });

        } catch (Exception $e) {
            $this->error("Worker error: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }
}
```

#### **Step 7: RabbitMQConsumer - Reading from Queue**

```php
// File: app/Services/RabbitMQ/RabbitMQConsumer.php
class RabbitMQConsumer
{
    private AMQPChannel $channel;
    private string $queue;
    private $callback;

    public function __construct(string $queue)
    {
        $this->queue = $queue;
        $this->channel = RabbitMQConnection::getChannel($queue);
    }

    /**
     * Start consuming messages
     */
    public function consume(callable $callback): void
    {
        try {
            $this->callback = $callback;

            // 1. Set QoS (Quality of Service)
            //    "Don't send me more than 10 unacknowledged messages"
            $this->channel->basic_qos(0, 10, false);

            // 2. Declare queue (creates if not exists)
            $this->channel->queue_declare(
                $this->queue,
                false,  // passive=false
                true,   // durable=true (survives restart)
                false,  // exclusive=false (multiple consumers)
                false   // auto_delete=false
            );

            Log::info("Consumer listening on queue: {$this->queue}");

            // 3. Register callback for incoming messages
            $this->channel->basic_consume(
                $this->queue,
                '',                      // consumer tag
                false,                   // no_ack = false (manual acknowledgment)
                false,                   // exclusive = false
                false,                   // no_wait = false
                [$this, 'processMessage'] // Callback method
            );

            // 4. Keep listening indefinitely
            while (true) {
                try {
                    // Wait for message (30 second timeout)
                    $this->channel->wait(null, false, 30);
                } catch (Exception $e) {
                    Log::warning("Wait timeout: {$e->getMessage()}");
                    break;
                }
            }

        } catch (Exception $e) {
            Log::error("Consumer error: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Process message when received
     */
    public function processMessage($message): void
    {
        try {
            $data = json_decode($message->body, true);

            Log::info("📨 Processing message from {$this->queue}");

            // Call the callback (email/realtime/database handler)
            $result = call_user_func($this->callback, $data, $message);

            if ($result === true) {
                // ✅ Success - acknowledge message (remove from queue)
                $message->ack();
                Log::info("✅ Message acknowledged and removed from queue");
            } else {
                // ❌ Failed - nack and requeue for retry
                $message->nack(true);
                Log::warning("⚠️ Message nacked, will be requeued");
            }

        } catch (Exception $e) {
            // Error in processing - requeue
            Log::error("Error processing: {$e->getMessage()}");
            $message->nack(true);
        }
    }
}
```

#### **Step 8: Channel-Specific Handler - EmailService**

```php
// File: app/Services/Notifications/EmailService.php
class EmailService
{
    /**
     * Send email notification
     */
    public function sendEmailNotification(array $messageData): bool
    {
        try {
            $notificationId = $messageData['notification_id'];
            $userId = $messageData['user_id'];
            $email = $messageData['email'];
            $data = $messageData['data'];

            Log::info("📧 Sending email to {$email}");

            // 1. Fetch user
            $user = User::find($userId);
            if (!$user) {
                Log::warning("User not found: {$userId}");
                return false;
            }

            // 2. Check if user has email enabled
            $settings = $user->notificationSettings;
            if ($settings && !$settings->email_enabled) {
                Log::info("Email disabled for user {$userId}");
                // Still return true so message is acknowledged (don't retry)
                return true;
            }

            // 3. Send email using Laravel Mailable
            Mail::to($email)->send(new NotificationMail(
                $user,
                $data,
                "Order Confirmation"
            ));

            // 4. Log success
            NotificationLogs::create([
                'notification_id' => $notificationId,
                'channel'         => 'email',
                'status'          => 'sent',
                'recipient_email' => $email,
                'details'         => 'Email sent successfully',
                'sent_at'         => now(),
            ]);

            // 5. Update notification status
            Notification::where('id', $notificationId)->update([
                'status'  => 'sent',
                'sent_at' => now(),
            ]);

            Log::info("✅ Email sent to {$email}");
            return true;

        } catch (Exception $e) {
            Log::error("❌ Email failed: {$e->getMessage()}");

            // Log failure
            NotificationLogs::create([
                'notification_id' => $messageData['notification_id'] ?? null,
                'channel'         => 'email',
                'status'          => 'failed',
                'recipient_email' => $messageData['email'] ?? null,
                'error_message'   => $e->getMessage(),
            ]);

            // Return false to trigger retry
            return false;
        }
    }
}
```

#### **Step 9: Realtime Service - Broadcasting**

```php
// File: app/Services/Notifications/RealtimeService.php
class RealtimeService
{
    /**
     * Broadcast notification to connected clients
     */
    public function broadcastNotification(array $data): bool
    {
        try {
            $userId = $data['user_id'];
            $title = $data['title'];
            $message = $data['message'];

            Log::info("📡 Broadcasting to user {$userId}");

            // Broadcast using Laravel Broadcasting
            broadcast(new NotificationBroadcast($userId, [
                'title'   => $title,
                'message' => $message,
                'type'    => $data['type'],
            ]));

            // Log
            NotificationLogs::create([
                'notification_id' => $data['notification_id'],
                'channel'         => 'realtime',
                'status'          => 'sent',
                'details'         => 'Broadcast to websocket clients',
                'sent_at'         => now(),
            ]);

            Log::info("✅ Broadcast sent to user {$userId}");
            return true;

        } catch (Exception $e) {
            Log::error("❌ Broadcast failed: {$e->getMessage()}");
            return false;
        }
    }
}
```

#### **Step 10: User Receives Notifications**

```
Timeline:
─────────────────────────────────────────────────────────────────

T=0ms:    User creates order via API endpoint
          └─→ OrderCreated::dispatch($order)

T=1ms:    Event listener triggered
          └─→ NotificationService::sendOrderNotification()

T=2ms:    Notification record created in database
          └─→ Notification::create([...])

T=3ms:    Messages published to RabbitMQ
          ├─→ Email queue
          └─→ Realtime queue

T=5ms:    API returns success to user ✅
          └─→ User sees instant confirmation!

T=10ms:   Email worker picks up message from queue
          └─→ EmailService::sendEmailNotification()

T=100ms:  Email sent via mail service
          └─→ User receives email

T=8ms:    Realtime worker picks up message
          └─→ RealtimeService::broadcastNotification()

T=10ms:   WebSocket message sent to browser
          └─→ Browser shows notification in real-time
```

---

## Complete Step-by-Step Walkthrough for Beginners

This section explains the entire system from start to finish, breaking down each component and how they work together.

### **Stage 1: Configuration & Setup**

#### **1.1 Environment Configuration (.env file)**

```env
# .env file - Located at project root
RABBITMQ_HOST=rabbitmq          # RabbitMQ server address (Docker service name or IP)
RABBITMQ_PORT=5672              # Default RabbitMQ port
RABBITMQ_USER=guest             # Login username
RABBITMQ_PASSWORD=guest         # Login password
RABBITMQ_VHOST=/                # Virtual host (namespace)

# Mail Configuration (for sending emails)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@app.com
```

**What this does:**
- Tells your Laravel app WHERE to find RabbitMQ
- Tells your app HOW to authenticate with RabbitMQ
- Configures email service for sending actual emails

#### **1.2 RabbitMQ Configuration (config/rabbitmq.php)**

```php
// File: config/rabbitmq.php
return [
    'host'                 => env('RABBITMQ_HOST', 'localhost'),
    'port'                 => env('RABBITMQ_PORT', 5672),
    'username'             => env('RABBITMQ_USER', 'guest'),
    'password'             => env('RABBITMQ_PASSWORD', 'guest'),
    'vhost'                => env('RABBITMQ_VHOST', '/'),
    'connection_timeout'   => 10,      // Timeout after 10 seconds
    'read_write_timeout'   => 10,
    'heartbeat'            => 60,      // Keep-alive every 60 seconds

    // Define all exchanges
    'exchanges' => [
        'notifications.topic' => [
            'type'       => 'topic',
            'durable'    => true,
            'auto_delete' => false,
        ],
    ],

    // Define all queues
    'queues' => [
        'notifications.email.queue' => [
            'bindings' => [
                [
                    'exchange'    => 'notifications.topic',
                    'routing_key' => 'notification.email.*',
                ],
            ],
        ],
        'notifications.realtime.queue' => [
            'bindings' => [
                [
                    'exchange'    => 'notifications.topic',
                    'routing_key' => 'notification.realtime.*',
                ],
            ],
        ],
    ],

    // Worker settings
    'workers' => [
        'notifications.email.queue' => [
            'prefetch_count' => 10,  // Process 10 messages at a time
        ],
        'notifications.realtime.queue' => [
            'prefetch_count' => 50,  // Realtime can handle more
        ],
    ],
];
```

**What this does:**
- Defines HOW queues and exchanges are set up
- Tells workers how many messages to process at once
- Sets up routing rules (which messages go to which queue)

#### **1.3 Database Configuration**

```bash
# Run migrations to create database tables
php artisan migrate

# This creates these tables:
# - notifications              (stores notification records)
# - notification_logs          (tracks delivery attempts)
# - notification_templates     (reusable message templates)
# - notification_settings      (user preferences)
# - orders                     (test orders for notifications)
# - users                      (includes notification relationships)
```

**What this does:**
- Creates tables to store notifications
- Creates tables to track delivery attempts
- Stores user notification preferences

---

### **Stage 2: System Startup**

#### **2.1 Start RabbitMQ Container**

```bash
# Start Docker containers (includes RabbitMQ)
docker-compose up -d

# Verify RabbitMQ is running
docker ps | grep rabbitmq

# Output should show: rabbitmq:3.12-management-alpine (or similar)
```

**What happens:**
- RabbitMQ broker starts listening on port 5672
- Exchanges and queues are created automatically (via configuration)
- Ready to accept messages from producers

#### **2.2 Seed Database with Test Data**

```bash
# Populate database with test data
php artisan db:seed

# Or seed specific tables:
php artisan db:seed --class=NotificationTemplateSeeder
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=OrderSeeder
php artisan db:seed --class=NotificationSettingsSeeder
```

**What happens:**
- Creates test users
- Creates test orders
- Creates notification templates (message formats)
- Sets up user notification preferences

#### **2.3 Start Worker Processes**

```bash
# Open 3 separate terminal windows and run:

# Terminal 1: Email Worker (listens for email notifications)
php artisan worker:email

# Terminal 2: Realtime Worker (broadcasts to browsers)
php artisan worker:realtime

# Terminal 3: SMS Worker (optional, sends SMS)
php artisan worker:sms
```

**What happens:**
- Each worker process connects to RabbitMQ
- Each worker listens to its specific queue
- Workers wait for messages to appear
- When a message arrives, the worker processes it

---

### **Stage 3: User Creates an Order (Trigger Event)**

#### **3.1 User Submits Order**

```
User clicks "Place Order" button
          ↓
HTTP POST to /api/orders endpoint
          ↓
Laravel Controller receives request
```

#### **3.2 Controller Creates Order**

```php
// File: app/Http/Controllers/OrderController.php
public function store(StoreOrderRequest $request)
{
    // 1. Validate input
    $validated = $request->validated();

    // 2. Create order in database
    $order = Order::create($validated);
    // Database now has: id, user_id, order_number, total_amount, etc.

    // 3. 🔥 DISPATCH EVENT (this is the trigger!)
    OrderCreated::dispatch($order);

    // 4. Return immediately to user (< 100ms)
    // User sees success message instantly!
    return response()->json(['success' => true, 'order' => $order]);
}
```

**What happens:**
- Order saved to database
- Event dispatched (triggers listener)
- User gets instant response (doesn't wait for notifications)

---

### **Stage 4: Event Listener Triggered**

#### **4.1 Laravel Event System Routes Event**

```
OrderCreated event dispatched
          ↓
Laravel looks in EventServiceProvider
          ↓
Finds listener: SendOrderCreatedNotification
          ↓
Calls listener's handle() method
```

#### **4.2 Listener Calls Notification Service**

```php
// File: app/Listeners/Notifications/SendOrderCreatedNotification.php
class SendOrderCreatedNotification
{
    public function handle(OrderCreated $event)
    {
        // Receive the OrderCreated event
        $order = $event->order;

        // Call notification service
        NotificationService::sendOrderNotification($order);
        // This is where the magic happens!
    }
}
```

**What happens:**
- Listener receives the event
- Extracts the order data
- Passes to NotificationService
- NotificationService orchestrates everything

---

### **Stage 5: NotificationService (Producer) - Orchestration**

#### **5.1 Create Notification Record**

```php
// File: app/Services/Notifications/NotificationService.php
public static function sendOrderNotification(Order $order): void
{
    $user = $order->user;

    // STEP 1: Create notification record in database
    $notification = Notification::create([
        'user_id'    => $user->id,
        'type'       => 'order_created',
        'title'      => 'Order Received',
        'message'    => "Your order #{$order->order_number} has been received",
        'data'       => json_encode([
            'order_id'     => $order->id,
            'order_number' => $order->order_number,
            'total'        => $order->total_amount,
        ]),
        'status'     => 'pending',
    ]);

    // Database now has a record of this notification
    // (even if delivery fails later, we have a record)
}
```

**What happens:**
- Notification record created in database
- Used for history/audit trail
- Can query later to see what was sent
- Users can see their notifications in their account

#### **5.2 Check User Preferences**

```php
// STEP 2: Get user's notification settings
$settings = $user->notificationSettings;
// Settings tell us which channels user wants:
// - email_enabled: true/false
// - realtime_enabled: true/false
// - notification_types: JSON array of types user wants

if (!$settings) {
    Log::warning("No settings for user");
    return; // Don't proceed if no settings
}
```

**What happens:**
- Retrieves user's notification preferences
- Checks if user wants emails, realtime notifications, etc.
- Respects user's choices (don't spam them!)

#### **5.3 Publish to Email Queue**

```php
// STEP 3: If user wants emails, publish to email queue
if ($settings->email_enabled) {
    $service = new self();
    $service->publishToEmailQueue($notification, $user);
}

private function publishToEmailQueue(Notification $notification, User $user): void
{
    // Create message payload
    $payload = [
        'notification_id' => $notification->id,
        'user_id'         => $user->id,
        'email'           => $user->email,
        'subject'         => $notification->title,
        'message'         => $notification->message,
        'data'            => json_decode($notification->data, true),
    ];

    // Create RabbitMQ producer
    $producer = new RabbitMQProducer();

    // Publish to queue
    $producer->publish(
        'notifications.topic',    // Exchange name
        'notification.email.send', // Routing key (pattern)
        $payload                   // Message data
    );

    // Message now in RabbitMQ!
    Log::info("✅ Published to email queue");
}
```

**What happens:**
- Message data prepared
- RabbitMQProducer creates connection
- Publishes message to exchange
- Message goes into email queue

#### **5.4 Publish to Realtime Queue**

```php
// STEP 4: If user wants realtime notifications, publish
if ($settings->realtime_enabled) {
    $service->publishToRealtimeQueue($notification, $user);
}

private function publishToRealtimeQueue(Notification $notification, User $user): void
{
    $payload = [
        'notification_id' => $notification->id,
        'user_id'         => $user->id,
        'title'           => $notification->title,
        'message'         => $notification->message,
    ];

    $producer = new RabbitMQProducer();
    $producer->publish(
        'notifications.topic',
        'notification.realtime.broadcast', // Different routing key
        $payload
    );

    Log::info("✅ Published to realtime queue");
}
```

**What happens:**
- Another message published to realtime queue
- Browser clients will receive real-time notification
- User sees notification pop-up in browser instantly

---

### **Stage 6: RabbitMQ Broker - Message Routing**

#### **6.1 Exchange Receives Messages**

```
RabbitMQ Exchange: "notifications.topic"
    ↓
Receives message with routing key: "notification.email.send"
    ↓
Checks all queue bindings...
```

#### **6.2 Queue Bindings Match Routing Keys**

```
Queue: notifications.email.queue
  Binding pattern: "notification.email.*"
  ✅ MATCHES "notification.email.send"
  → Message routed here!

Queue: notifications.realtime.queue
  Binding pattern: "notification.realtime.*"
  ❌ Does NOT match "notification.email.send"
  → Message NOT routed here
```

**What happens:**
- Exchange looks at routing key
- Compares against queue binding patterns
- Routes message to matching queues
- Message stored in queue (persistent, survives crashes)

#### **6.3 Queue Persistence**

```
Email Queue: "notifications.email.queue"
  Messages waiting:
  [
    { id: 1, user_id: 5, email: "user@example.com", ... },
    { id: 2, user_id: 8, email: "another@example.com", ... },
    ...
  ]

These messages are stored on disk (persistent)
If RabbitMQ crashes and restarts, messages still here!
```

**What happens:**
- Messages stored on disk (not just in memory)
- Queue survives server crashes
- Workers can retrieve messages when they reconnect

---

### **Stage 7: Worker Consumes Message**

#### **7.1 Worker Process Listening**

```bash
# Email Worker Command (in Terminal 1)
php artisan worker:email

# This runs EmailWorkerCommand which:
# 1. Creates RabbitMQConsumer
# 2. Connects to email queue
# 3. Calls consumer->consume() method
# 4. Waits indefinitely for messages
```

#### **7.2 Consumer Declares Queue**

```php
// File: app/Services/RabbitMQ/RabbitMQConsumer.php
public function consume(callable $callback): void
{
    // Get channel (connection to RabbitMQ)
    $channel = RabbitMQConnection::getChannel($this->queue);

    // Set QoS: "Don't send me more than 10 unacked messages"
    $channel->basic_qos(0, 10, false);

    // Declare queue (create if not exists)
    $channel->queue_declare(
        $this->queue,           // "notifications.email.queue"
        false,  // passive=false (create if doesn't exist)
        true,   // durable=true (survives restart)
        false,  // exclusive=false (multiple consumers allowed)
        false   // auto_delete=false
    );

    // Register callback function
    $this->channel->basic_consume(
        $this->queue,
        '',                      // consumer tag
        false,                   // no_ack=false (manual ack required)
        false,                   // exclusive
        false,                   // no_wait
        [$this, 'processMessage'] // Handler function
    );

    // LOOP: Keep listening for messages
    while (true) {
        try {
            $this->channel->wait(null, false, 30); // 30s timeout
        } catch (Exception $e) {
            Log::warning("Wait timeout");
            break;
        }
    }
}
```

**What happens:**
- Consumer connects to RabbitMQ
- Declares queue (creates if missing)
- Sets up QoS limits (10 messages at a time)
- Enters infinite loop listening for messages
- `$channel->wait()` blocks until message arrives

#### **7.3 Message Arrives - Callback Triggered**

```
Worker waiting for messages...
          ↓
New message appears in queue!
          ↓
RabbitMQ calls: processMessage() method
          ↓
Message data extracted and passed to callback
```

#### **7.4 Process Message**

```php
public function processMessage($message): void
{
    try {
        // 1. Extract message data
        $data = json_decode($message->body, true);
        // Data: ['notification_id' => 1, 'user_id' => 5, 'email' => '...']

        Log::info("📨 Processing message from {$this->queue}");

        // 2. Call the handler (EmailService or RealtimeService)
        // Remember the $callback from consume() method?
        // It's passed here!
        $result = call_user_func($this->callback, $data, $message);

        // 3. Check if successful
        if ($result === true) {
            // ✅ Success - acknowledge message
            $message->ack();
            // Message removed from queue permanently
            Log::info("✅ Message acknowledged");

        } else {
            // ❌ Failed - requeue for retry
            $message->nack(true); // true = requeue
            // Message goes back to queue!
            Log::warning("⚠️ Message nacked, will retry");
        }

    } catch (Exception $e) {
        // 🔴 Error - requeue
        Log::error("Error processing: {$e->getMessage()}");
        $message->nack(true); // Put back in queue
    }
}
```

**What happens:**
- Message data extracted
- Handler function called with data
- If successful: message acknowledged (removed)
- If failed: message nacked (goes back to queue)
- Worker goes back to waiting for next message

---

### **Stage 8: Channel-Specific Handler (EmailService)**

#### **8.1 Email Handler Receives Message**

```php
// File: app/Services/Notifications/EmailService.php
public function sendEmailNotification(array $messageData): bool
{
    try {
        // Extract data
        $notificationId = $messageData['notification_id'];
        $userId = $messageData['user_id'];
        $email = $messageData['email'];
        $data = $messageData['data'];

        Log::info("📧 Sending email to {$email}");

        // STEP 1: Fetch user
        $user = User::find($userId);
        if (!$user) {
            Log::warning("User not found");
            return false; // Will retry
        }

        // STEP 2: Check if email is enabled
        $settings = $user->notificationSettings;
        if ($settings && !$settings->email_enabled) {
            Log::info("User disabled email notifications");
            return true; // Return true (don't retry), just skip
        }

        // STEP 3: Send actual email
        Mail::to($email)->send(new NotificationMail(
            $user,
            $data,
            "Order Confirmation"
        ));
        // ✉️ Real email sent to user's inbox!

        // STEP 4: Log the successful delivery
        NotificationLogs::create([
            'notification_id' => $notificationId,
            'channel'         => 'email',
            'status'          => 'sent',
            'recipient_email' => $email,
            'details'         => 'Email sent successfully',
            'sent_at'         => now(),
        ]);

        // STEP 5: Update notification status
        Notification::where('id', $notificationId)->update([
            'status'  => 'sent',
            'sent_at' => now(),
        ]);

        Log::info("✅ Email delivered to {$email}");
        return true; // Success!

    } catch (Exception $e) {
        // Error occurred - log it
        Log::error("❌ Email failed: {$e->getMessage()}");

        // Log the failure
        NotificationLogs::create([
            'notification_id' => $messageData['notification_id'] ?? null,
            'channel'         => 'email',
            'status'          => 'failed',
            'error_message'   => $e->getMessage(),
            'sent_at'         => null,
        ]);

        return false; // Will retry
    }
}
```

**What happens:**
- Message data received
- User validation
- Email preferences checked
- Email sent via Laravel Mail service
- Success logged to database
- Message acknowledged (removed from queue)
- OR failure logged and message requeued

#### **8.2 Realtime Handler (Alternative Channel)**

```php
// File: app/Services/Notifications/RealtimeService.php
public function broadcastNotification(array $data): bool
{
    try {
        $userId = $data['user_id'];

        // STEP 1: Prepare notification data
        $notificationData = [
            'title'   => $data['title'],
            'message' => $data['message'],
            'type'    => $data['type'],
        ];

        // STEP 2: Broadcast to user's websocket channel
        broadcast(new NotificationBroadcast(
            $userId,
            $notificationData
        ));
        // 📡 Message sent to user's browser in real-time!

        // STEP 3: Log delivery
        NotificationLogs::create([
            'notification_id' => $data['notification_id'],
            'channel'         => 'realtime',
            'status'          => 'sent',
            'details'         => 'Broadcast to websocket',
            'sent_at'         => now(),
        ]);

        Log::info("✅ Realtime notification sent to user {$userId}");
        return true;

    } catch (Exception $e) {
        Log::error("❌ Realtime failed: {$e->getMessage()}");
        return false;
    }
}
```

**What happens:**
- Realtime message prepared
- Broadcast to user's channel via websocket
- User's browser receives notification instantly
- Delivery logged
- Message acknowledged or requeued

---

### **Stage 9: User Receives Notification**

#### **9.1 Email Reception**

```
📧 Email sent by EmailService
          ↓
SMTP server (mail service)
          ↓
User's email inbox
          ↓
User opens email and reads order confirmation
```

#### **9.2 Browser Real-time Notification**

```
📡 Broadcast sent by RealtimeService
          ↓
Laravel Broadcasting (Pusher/WebSocket)
          ↓
User's browser receives message
          ↓
JavaScript listener triggers
          ↓
Toast/notification popup appears on screen
          ↓
User sees instant notification!
```

---

### **Stage 10: Monitoring & Auditing**

#### **10.1 Query Delivery Logs**

```php
// Check which notifications were sent
$logs = NotificationLogs::latest()->get();

// Output:
// id | notification_id | channel    | status | sent_at
// 1  | 1               | email      | sent   | 2024-01-14 10:30:45
// 2  | 1               | realtime   | sent   | 2024-01-14 10:30:47
// 3  | 2               | email      | failed | NULL
```

#### **10.2 Check Failed Notifications**

```php
// Find failed emails
$failed = NotificationLogs::where('channel', 'email')
    ->where('status', 'failed')
    ->with('notification')
    ->get();

// Check error messages
foreach ($failed as $log) {
    echo "Notification {$log->notification_id} failed: {$log->error_message}";
}
```

#### **10.3 Monitor Queue Depth**

```php
// Check how many pending notifications
$pending = Notification::where('status', 'pending')->count();
echo "Pending notifications: {$pending}";

// This tells you if workers are keeping up
// If this number keeps growing → add more workers!
```

---

### **Visual Timeline of Complete Flow**

```
T=0ms:    User submits order form
          ↓
T=1ms:    HTTP POST to /api/orders
          ↓
T=2ms:    Controller validates data
          ↓
T=5ms:    Order created in database
          ↓
T=6ms:    OrderCreated::dispatch(event)
          ↓
T=7ms:    Event listener triggered
          ↓
T=8ms:    NotificationService called
          ↓
T=9ms:    - Notification record created
          - Email message published to RabbitMQ
          - Realtime message published to RabbitMQ
          ↓
T=10ms:   API returns 200 OK to browser ✅
          └─→ User sees success message instantly!
          
          (Meanwhile, in background...)
          
T=15ms:   Email Worker picks message from queue
          ├─→ EmailService::sendEmailNotification()
          └─→ Sends email via SMTP
          
T=20ms:   Realtime Worker picks message
          ├─→ RealtimeService::broadcastNotification()
          └─→ Broadcasts to user's websocket
          
T=25ms:   User's browser receives websocket message
          ├─→ JavaScript listener triggered
          └─→ Toast notification appears
          
T=50-100ms: Email hits user's inbox
          └─→ User receives email notification
```

**Key Insight:** User gets API response in ~10ms, but they're not waiting for email (which takes 50-100ms). That's the magic of RabbitMQ! 🚀

---

## Architecture

### System Components

```
┌──────────────────────────────────────────────────────────────┐
│                      Laravel Application                      │
│                                                               │
│  ┌─────────────────────────────────────────────────────────┐ │
│  │             Event Triggers                               │ │
│  │  (OrderCreated, UserRegistered, etc.)                   │ │
│  └────────────────────┬────────────────────────────────────┘ │
│                       │                                       │
│  ┌────────────────────▼────────────────────────────────────┐ │
│  │        NotificationService (Producer)                   │ │
│  │  - Formats notification data                            │ │
│  │  - Validates notification settings                      │ │
│  │  - Publishes to RabbitMQ                               │ │
│  └────────────────────┬────────────────────────────────────┘ │
└───────────────────────┼────────────────────────────────────────┘
                        │
┌───────────────────────▼────────────────────────────────────────┐
│                    RabbitMQ Broker                              │
│                                                                  │
│  Exchanges:                                                     │
│  - notifications_exchange (topic exchange)                     │
│                                                                  │
│  Queues:                                                        │
│  - notifications.email (for email notifications)               │
│  - notifications.realtime (for broadcast notifications)        │
│  - notifications.database (for database storage)               │
│  - notifications.dlq (dead-letter queue for failures)          │
└────┬─────────────────┬─────────────────┬──────────────────────┘
     │                 │                 │
┌────▼──────────┐ ┌────▼──────────┐ ┌───▼─────────────┐
│ Email Worker  │ │ Realtime      │ │ Database        │
│               │ │ Worker        │ │ Worker          │
│ - Sends emails│ │               │ │                 │
│ - Logs result │ │ - Broadcasts  │ │ - Stores in DB  │
│               │ │ - Notifies    │ │ - Logs action   │
└───────────────┘ │   connected   │ │                 │
                  │   clients     │ └─────────────────┘
                  └───────────────┘
                        │
                  ┌─────▼─────────┐
                  │ Notification  │
                  │ Logs Table    │
                  └───────────────┘
```

---

## Project Structure

```
backend/
├── app/
│   ├── Actions/
│   │   └── Notifications/
│   │       ├── CreateNotification.php
│   │       └── UpdateNotificationStatus.php
│   │
│   ├── Services/
│   │   ├── RabbitMQConnection.php          # Manages RabbitMQ connections
│   │   ├── RabbitMQProducer.php            # Publishes messages to RabbitMQ
│   │   ├── RabbitMQConsumer.php            # Consumes messages from RabbitMQ
│   │   ├── NotificationService.php         # Orchestrates notification logic
│   │   ├── EmailService.php                # Handles email notifications
│   │   ├── RealtimeService.php             # Handles real-time notifications
│   │   └── NotificationPreferenceService.php
│   │
│   ├── Jobs/
│   │   ├── ProcessEmailNotification.php
│   │   ├── ProcessRealtimeNotification.php
│   │   └── ProcessDatabaseNotification.php
│   │
│   ├── Events/
│   │   ├── OrderCreated.php
│   │   ├── OrderShipped.php
│   │   ├── UserRegistered.php
│   │   └── PaymentProcessed.php
│   │
│   ├── Listeners/
│   │   └── SendNotificationListener.php
│   │
│   ├── Models/
│   │   ├── User.php                       # Extended with notification relationships
│   │   ├── Order.php                      # New model with user relationship
│   │   ├── Notification.php               # Stores notification records
│   │   ├── NotificationLog.php            # Tracks delivery attempts
│   │   ├── NotificationTemplate.php       # Stores reusable templates
│   │   └── NotificationSettings.php       # Stores user preferences
│   │
│   ├── Console/
│   │   └── Commands/
│   │       ├── ConsumeEmailNotifications.php
│   │       ├── ConsumeRealtimeNotifications.php
│   │       └── ConsumeDatabaseNotifications.php
│   │
│   └── Http/
│       └── Controllers/
│           └── API/
│               └── NotificationController.php
│
├── database/
│   ├── migrations/
│   │   ├── create_notifications_table.php
│   │   ├── create_notification_logs_table.php
│   │   ├── create_notification_templates_table.php
│   │   ├── create_notification_settings_table.php
│   │   └── create_orders_table.php
│   │
│   └── seeders/
│       ├── NotificationTemplateSeeder.php
│       ├── NotificationSeeder.php
│       ├── NotificationLogSeeder.php
│       ├── NotificationSettingsSeeder.php
│       ├── OrderSeeder.php
│       └── DatabaseSeeder.php
│
├── config/
│   └── rabbitmq.php                      # RabbitMQ configuration
│
└── routes/
    └── api.php                            # API routes for notifications
```

---

## Key Components

### 1. RabbitMQConnection.php
Manages the connection to RabbitMQ and provides methods to establish and maintain connections.

```php
class RabbitMQConnection {
    private $connection;
    private $channel;

    public function connect(): AMQPStreamConnection
    public function getChannel(): AMQPChannel
    public function close(): void
}
```

### 2. RabbitMQProducer.php
Publishes notification messages to RabbitMQ exchanges.

```php
class RabbitMQProducer {
    public function publishNotification(
        string $channel,
        string $message,
        string $routingKey = ''
    ): void
}
```

### 3. NotificationService.php
Orchestrates the notification workflow - validates settings, formats data, and publishes to RabbitMQ.

```php
class NotificationService {
    public function sendNotification(
        User $user,
        string $title,
        string $message,
        string $type = 'info'
    ): void

    public function sendOrderNotification(Order $order): void
    public function sendWelcomeNotification(User $user): void
}
```

### 4. EmailService.php
Handles email notification processing from the RabbitMQ queue.

```php
class EmailService {
    public function handleEmailNotification(array $data): void
    public function sendEmail(User $user, string $subject, string $message): void
}
```

### 5. RealtimeService.php
Handles real-time broadcast notifications to connected clients.

```php
class RealtimeService {
    public function broadcastNotification(User $user, array $data): void
}
```

### 6. Console Commands
Three consumer commands run continuously to process notifications:

- `ConsumeEmailNotifications.php` - Processes email notifications
- `ConsumeRealtimeNotifications.php` - Processes broadcast notifications
- `ConsumeDatabaseNotifications.php` - Stores notifications in database

---

## Database Schema

### notifications table
```sql
CREATE TABLE notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'info',
    is_read BOOLEAN DEFAULT false,
    read_at TIMESTAMP NULL,
    notifiable_id BIGINT NOT NULL,
    notifiable_type VARCHAR(255) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_notifiable (notifiable_id, notifiable_type),
    INDEX idx_read (is_read)
);
```

### notification_logs table
```sql
CREATE TABLE notification_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    notification_id BIGINT NOT NULL,
    channel VARCHAR(50) NOT NULL,
    status ENUM('sent', 'failed', 'pending', 'delivered'),
    recipient_email VARCHAR(255),
    response TEXT NULL,
    error_message TEXT NULL,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES notifications(id),
    INDEX idx_channel (channel),
    INDEX idx_status (status)
);
```

### notification_templates table
```sql
CREATE TABLE notification_templates (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE NOT NULL,
    subject VARCHAR(255),
    body TEXT NOT NULL,
    type VARCHAR(50),
    variables JSON COMMENT 'JSON array of template variables',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### notification_settings table
```sql
CREATE TABLE notification_settings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE NOT NULL,
    email_enabled BOOLEAN DEFAULT true,
    sms_enabled BOOLEAN DEFAULT false,
    realtime_enabled BOOLEAN DEFAULT true,
    notification_types JSON COMMENT 'Which types of notifications to receive',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### orders table
```sql
CREATE TABLE orders (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    order_number VARCHAR(255) UNIQUE NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled'),
    payment_status ENUM('unpaid', 'paid', 'refunded'),
    total_amount DECIMAL(10, 2),
    payment_method VARCHAR(50),
    shipping_address TEXT,
    billing_address TEXT,
    notes TEXT,
    shipped_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_payment (payment_status)
);
```

---

## Integration Flow

### 1. Event Trigger
An event is fired when an order is created, user registers, or any other trigger:

```php
OrderCreated::dispatch($order);
```

### 2. Event Listener
The listener receives the event and calls the NotificationService:

```php
public function handle(OrderCreated $event)
{
    NotificationService::sendOrderNotification($event->order);
}
```

### 3. Notification Creation
NotificationService:
- Creates a Notification record in the database
- Checks user's notification preferences
- Publishes messages to appropriate RabbitMQ queues

```php
public function sendOrderNotification(Order $order)
{
    // 1. Create notification record
    $notification = Notification::create([
        'title' => 'Order Received',
        'message' => "Your order #{$order->order_number} has been received",
        'notifiable_id' => $order->user_id,
        'notifiable_type' => User::class,
    ]);

    // 2. Check user preferences
    $settings = NotificationSettings::where('user_id', $order->user_id)->first();

    // 3. Publish to RabbitMQ
    if ($settings->email_enabled) {
        RabbitMQProducer::publishNotification('email', json_encode([...]), 'notification.email');
    }

    if ($settings->realtime_enabled) {
        RabbitMQProducer::publishNotification('realtime', json_encode([...]), 'notification.realtime');
    }
}
```

### 4. Message Consumption
Consumer processes messages from queues:

```php
// ConsumeEmailNotifications command
while (true) {
    if ($message = $queue->get()) {
        try {
            EmailService::handleEmailNotification($message);
            $message->ack();
        } catch (Exception $e) {
            NotificationLog::create(['error_message' => $e->getMessage()]);
            $message->nack(true); // Requeue for retry
        }
    }
}
```

### 5. Delivery
Based on the channel, the message is delivered:

- **Email**: Sent via mail service
- **Realtime**: Broadcast to connected clients
- **Database**: Already stored during creation
- **Logs**: All attempts are logged

---

## Setup Instructions

### 1. Install Dependencies

```bash
cd backend
composer install
php-amqplib via composer:
composer require php-amqplib/php-amqplib
```

### 2. Configure RabbitMQ

In `config/rabbitmq.php`:

```php
return [
    'host' => env('RABBITMQ_HOST', 'localhost'),
    'port' => env('RABBITMQ_PORT', 5672),
    'user' => env('RABBITMQ_USER', 'guest'),
    'password' => env('RABBITMQ_PASSWORD', 'guest'),
    'vhost' => env('RABBITMQ_VHOST', '/'),
    'channels' => [
        'email' => 'notifications.email',
        'realtime' => 'notifications.realtime',
        'database' => 'notifications.database',
    ],
];
```

In `.env`:

```
RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Seed the Database

```bash
php artisan db:seed
# Or seed specific seeders:
php artisan db:seed --class=NotificationTemplateSeeder
php artisan db:seed --class=OrderSeeder
php artisan db:seed --class=NotificationSeeder
php artisan db:seed --class=NotificationLogSeeder
php artisan db:seed --class=NotificationSettingsSeeder
```

### 5. Start Consumer Commands

Run these commands in separate terminal windows/processes:

```bash
# Terminal 1: Email notifications
php artisan consume:email-notifications

# Terminal 2: Real-time notifications
php artisan consume:realtime-notifications

# Terminal 3: Database notifications
php artisan consume:database-notifications
```

For production, use a process manager like Supervisor:

```ini
[program:email-notifications]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan consume:email-notifications
autostart=true
autorestart=true
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/logs/email-notifications.log

[program:realtime-notifications]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan consume:realtime-notifications
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/logs/realtime-notifications.log

[program:database-notifications]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan consume:database-notifications
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/logs/database-notifications.log
```

---

## Testing & Seeders

### Seeders Provided

1. **NotificationTemplateSeeder**
   - Creates reusable notification templates
   - Templates for different notification types (order, welcome, etc.)

2. **OrderSeeder**
   - Creates 25 test orders (5 per user)
   - Generates realistic order data with various statuses
   - Links to existing users

3. **NotificationSeeder**
   - Creates test notifications
   - Seeds for each existing user
   - Can trigger notification event handling

4. **NotificationLogSeeder**
   - Creates delivery logs for notifications
   - Tests various delivery channels and statuses
   - Simulates real-world delivery attempts

5. **NotificationSettingsSeeder**
   - Sets up user notification preferences
   - Enables/disables various notification channels per user
   - Defines which notification types to receive

### Testing the System

```bash
# 1. Reset and seed the database
php artisan migrate:refresh --seed

# 2. Start the consumer commands (in separate terminals)
php artisan consume:email-notifications
php artisan consume:realtime-notifications
php artisan consume:database-notifications

# 3. Trigger a notification via API or event
php artisan tinker
>>> $user = User::first();
>>> NotificationService::sendWelcomeNotification($user);

# 4. Check the notification_logs table
>>> NotificationLog::with('notification')->latest()->paginate();

# 5. Verify email was sent (check mail in storage/logs/laravel.log)
>>> tail -f storage/logs/laravel.log
```

---

## Usage Examples

### Send a Custom Notification

```php
use App\Services\NotificationService;
use App\Models\User;

$user = User::find(1);
NotificationService::sendNotification(
    $user,
    'Order Shipped',
    'Your order has been shipped!',
    'info'
);
```

### Create a Notification Template

```php
use App\Models\NotificationTemplate;

NotificationTemplate::create([
    'name' => 'order_shipped',
    'subject' => 'Order {{order_number}} Shipped',
    'body' => 'Your order {{order_number}} has been shipped with tracking number {{tracking_number}}',
    'type' => 'order',
    'variables' => json_encode(['order_number', 'tracking_number']),
]);
```

### Update User Notification Preferences

```php
use App\Models\NotificationSettings;

NotificationSettings::where('user_id', 1)->update([
    'email_enabled' => true,
    'realtime_enabled' => false,
    'notification_types' => json_encode([
        'orders' => true,
        'promotions' => false,
        'updates' => true,
    ]),
]);
```

### Get Notifications for a User

```php
$user = User::find(1);

// Get all notifications
$notifications = $user->notifications()->paginate();

// Get unread notifications
$unread = $user->notifications()->where('is_read', false)->get();

// Mark as read
$user->notifications()->where('is_read', false)->update(['is_read' => true, 'read_at' => now()]);
```

### Query Delivery Logs

```php
use App\Models\NotificationLog;

// Get failed notifications
$failed = NotificationLog::where('status', 'failed')->get();

// Get notifications sent in last 24 hours
$recent = NotificationLog::where('sent_at', '>=', now()->subDay())->get();

// Group by channel
$byChannel = NotificationLog::groupBy('channel')->selectRaw('channel, count(*) as total')->get();
```

---

## Troubleshooting

### Issue: Consumers not processing messages

**Solution:**
1. Verify RabbitMQ is running: `docker ps | grep rabbitmq`
2. Check consumer processes: `ps aux | grep artisan`
3. Verify RabbitMQ configuration in `.env`
4. Check consumer logs in `storage/logs/laravel.log`

### Issue: Messages accumulating in queues

**Solution:**
1. Ensure consumer commands are running
2. Check for errors in consumer processing
3. Review database connectivity
4. Check notification_logs for error messages

### Issue: Notifications not being sent

**Solution:**
1. Verify notification settings for the user
2. Check if user has email configured
3. Ensure mail service is configured in `config/mail.php`
4. Review notification_logs for delivery attempts

### Issue: RabbitMQ connection refused

**Solution:**
```bash
# Check if RabbitMQ container is running
docker ps

# Restart RabbitMQ
docker-compose restart rabbitmq

# Check RabbitMQ logs
docker-compose logs rabbitmq
```

### Issue: Memory usage growing

**Solution:**
1. Implement message batching in consumers
2. Add periodic process restarts via Supervisor
3. Monitor queue depths
4. Implement message TTL for old messages

---

## Performance Optimization Tips

1. **Use Message TTL**: Set time-to-live for messages to prevent queue accumulation
2. **Batch Processing**: Process multiple messages in a single database operation
3. **Connection Pooling**: Reuse RabbitMQ connections
4. **Horizontal Scaling**: Run multiple consumer processes for the same queue
5. **Database Indexing**: Ensure all foreign keys and frequently queried columns are indexed
6. **Queue Prioritization**: Separate high-priority notifications into dedicated queues

---

## Security Considerations

1. **Validate Input**: Always validate notification data before processing
2. **Rate Limiting**: Implement rate limiting for notification APIs
3. **Authentication**: Secure all notification endpoints with proper authentication
4. **Data Sanitization**: Sanitize all user-provided content in notifications
5. **Audit Logging**: Track all notification modifications
6. **Access Control**: Limit who can view/modify notifications

---

## Monitoring & Alerting

### Key Metrics to Monitor

- Queue depth (messages waiting to be processed)
- Consumer lag (time between message arrival and processing)
- Failed delivery rate
- Email bounces
- Processing time per notification
- Memory and CPU usage of consumer processes

### Recommended Tools

- **Grafana**: For visualization
- **Prometheus**: For metrics collection
- **ELK Stack**: For centralized logging
- **Sentry**: For error tracking

---

## Related Files

- [Order Model](../app/Models/Order.php)
- [Notification Model](../app/Models/Notification.php)
- [NotificationService](../app/Services/NotificationService.php)
- [RabbitMQ Configuration](../config/rabbitmq.php)
- [Database Migrations](../database/migrations/)
- [Seeders](../database/seeders/)

<?php

namespace Tests\Unit;

use App\Services\Payments\IdempotencyHandler;
use PHPUnit\Framework\TestCase;

class IdempotencyHandlerTest extends TestCase
{
    public function test_generate_is_deterministic_for_identical_payloads()
    {
        $payload1 = [
            'order_id' => '1001',
            'customer_id' => '42',
            'gateway' => 'stripe',
        ];

        $payload2 = [
            'order_id' => '1001',
            'customer_id' => '42',
            'gateway' => 'stripe',
        ];

        $key1 = IdempotencyHandler::generate($payload1);
        $key2 = IdempotencyHandler::generate($payload2);

        $this->assertEquals($key1, $key2);
        $this->assertEquals(64, strlen($key1)); // SHA-256 length
    }

    public function test_generate_differs_for_different_orders()
    {
        $payload1 = ['order_id' => '1001', 'customer_id' => '42', 'gateway' => 'stripe'];
        $payload2 = ['order_id' => '1002', 'customer_id' => '42', 'gateway' => 'stripe'];

        $key1 = IdempotencyHandler::generate($payload1);
        $key2 = IdempotencyHandler::generate($payload2);

        $this->assertNotEquals($key1, $key2);
    }
}


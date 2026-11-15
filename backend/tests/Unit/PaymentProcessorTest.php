<?php

namespace Tests\Unit;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentResultDTO;
use App\Services\Payments\IdempotencyHandler;
use App\Services\Payments\PaymentGatewayFactory;
use App\Services\Payments\PaymentProcessor;
use App\Services\Payments\StripeGateway;
use Mockery;
use PHPUnit\Framework\TestCase;

class PaymentProcessorTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_processor_rejects_zero_or_negative_amount()
    {
        $factory = Mockery::mock(PaymentGatewayFactory::class);
        $idempotency = Mockery::mock(IdempotencyHandler::class);

        $processor = new PaymentProcessor($factory, $idempotency);

        $dto = PaymentRequestDTO::make([
            'amount' => 0,
            'currency' => 'USD',
            'gateway' => 'stripe',
            'order_id' => '100',
            'customer_id' => '1',
        ]);

        $result = $processor->process($dto);

        $this->assertFalse($result->success);
        $this->assertStringContainsString('amount', strtolower($result->errorMessage));
    }

    public function test_processor_executes_valid_payment_via_gateway()
    {
        $factory = Mockery::mock(PaymentGatewayFactory::class);
        $gateway = Mockery::mock(StripeGateway::class);
        $idempotency = Mockery::mock(IdempotencyHandler::class);

        $factory->shouldReceive('create')
            ->with('stripe')
            ->andReturn($gateway);

        $gateway->shouldReceive('charge')
            ->once()
            ->andReturn([
                'success' => true,
                'transaction_id' => 'tx_test_12345',
                'gateway' => 'stripe',
            ]);

        $processor = new PaymentProcessor($factory, $idempotency);

        $dto = PaymentRequestDTO::make([
            'amount' => 99.99,
            'currency' => 'USD',
            'gateway' => 'stripe',
            'order_id' => '200',
            'customer_id' => '5',
        ]);

        $result = $processor->process($dto);

        $this->assertTrue($result->success);
        $this->assertEquals('tx_test_12345', $result->transactionId);
        $this->assertEquals('stripe', $result->gateway);
    }
}


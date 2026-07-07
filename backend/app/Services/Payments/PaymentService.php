<?php

namespace App\Services\Payments;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentResultDTO;

class PaymentService
{
    private ?IdempotencyHandler $idempotencyHandler = null;

    public function __construct(
        private PaymentProcessor $processor,
        private PaymentGatewayFactory $factory,
        ?IdempotencyHandler $idempotencyHandler = null,
    ) {
        $this->idempotencyHandler = $idempotencyHandler;
    }

    /**
     * Process payment with idempotency support
     */
    public function processPayment(array $data, ?string $idempotencyKey = null): PaymentResultDTO
    {
        $paymentRequest = PaymentRequestDTO::make($data);

        return $this->processor->process($paymentRequest, $idempotencyKey);
    }

    /**
     * Generate a unique idempotency key
     */
    public function generateIdempotencyKey(array $data): string
    {
        return IdempotencyHandler::generate($data);
    }

    public function refund(string $gateway, string $transactionId, float $amount): array
    {
        $gatewayInstance = $this->factory->create($gateway);
        return $gatewayInstance->refund($transactionId, $amount);
    }

    public function handleWebhook(string $gateway, array $payload): array
    {
        $gatewayInstance = $this->factory->create($gateway);
        return $gatewayInstance->handleWebhook($payload);
    }
}

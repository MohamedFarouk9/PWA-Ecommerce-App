<?php

namespace App\Services\Payments;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentResultDTO;

class PaymentService
{
    public function __construct(
        private PaymentProcessor $processor,
        private PaymentGatewayFactory $factory,
    ) {}

    public function processPayment(array $data): PaymentResultDTO
    {
        $paymentRequest = PaymentRequestDTO::make($data);
        return $this->processor->process($paymentRequest);
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

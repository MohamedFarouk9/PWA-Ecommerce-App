<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Process a payment charge
     */
    public function charge(array $data): array;

    /**
     * Handle payment webhook
     */
    public function handleWebhook(array $payload): array;

    /**
     * Refund a payment
     */
    public function refund(string $transactionId, float $amount): array;

    /**
     * Get gateway name
     */
    public function getGatewayName(): string;

    /**
     * Validate payment data
     */
    public function validateData(array $data): bool;
}

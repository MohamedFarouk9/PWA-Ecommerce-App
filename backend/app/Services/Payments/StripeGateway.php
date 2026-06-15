<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;

class StripeGateway implements PaymentGatewayInterface
{
    public function charge(array $data): array
    {
        // Your Stripe implementation
        // This should return: ['success' => true, 'transaction_id' => '...']

        return [
            'success' => true,
            'transaction_id' => 'stripe_' . uniqid(),
            'gateway' => 'stripe',
            'status' => 'completed',
        ];
    }

    public function handleWebhook(array $payload): array
    {
        // Handle Stripe webhook
        return ['status' => 'processed'];
    }

    public function refund(string $transactionId, float $amount): array
    {
        // Refund logic
        return ['success' => true];
    }

    public function getGatewayName(): string
    {
        return 'stripe';
    }

    public function validateData(array $data): bool
    {
        return isset($data['amount'], $data['currency']);
    }
}

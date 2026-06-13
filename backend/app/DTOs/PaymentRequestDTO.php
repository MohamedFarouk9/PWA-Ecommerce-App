<?php

namespace App\DTOs;

class PaymentRequestDTO
{
    public function __construct(
        public readonly string $amount,
        public readonly string $currency,
        public readonly string $gateway,
        public readonly string $orderId,
        public readonly string $customerId,
        public readonly array $metadata = [],
        public readonly int $retries = 3,
    ) {}

    public static function make(array $data): self
    {
        return new self(
            amount: $data['amount'],
            currency: $data['currency'] ?? 'USD',
            gateway: $data['gateway']?? 'stripe',
            orderId: $data['order_id'],
            customerId: $data['customer_id'],
            metadata: $data['metadata'] ?? [],
            retries: $data['retries'] ?? 3,
        );
    }
}

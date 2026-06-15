<?php

namespace App\DTOs;

class PaymentResultDTO
{
    public function __construct(
        public readonly bool $success,
        public readonly string $transactionId,
        public readonly string $gateway,
        public readonly string $status, // pending, completed, failed, refunded
        public readonly ?string $errorMessage = null,
        public readonly array $metadata = [],
    ) {}

    public static function success(
        string $transactionId,
        string $gateway,
        array $metadata = []
    ): self {
        return new self(
            success: true,
            transactionId: $transactionId,
            gateway: $gateway,
            status: 'completed',
            metadata: $metadata,
        );
    }

    public static function failed(
        string $gateway,
        string $errorMessage,
        array $metadata = []
    ): self {
        return new self(
            success: false,
            transactionId: '',
            gateway: $gateway,
            status: 'failed',
            errorMessage: $errorMessage,
            metadata: $metadata,
        );
    }
}

<?php

namespace App\Services\Payments\Handlers;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentResultDTO;

class ValidationHandler extends PaymentHandler
{
    public function canHandle(PaymentRequestDTO $request): bool
    {
        return true; // Always validate
    }

    public function handle(PaymentRequestDTO $request): ?PaymentResultDTO
    {
        // Validate amount
        if ($request->amount <= 0) {
            return PaymentResultDTO::failed(
                $request->gateway,
                'Invalid amount. Must be greater than 0.'
            );
        }

        // Validate gateway
        if (empty($request->gateway)) {
            return PaymentResultDTO::failed(
                'unknown',
                'Gateway not specified.'
            );
        }

        // Validate customer
        if (empty($request->customerId)) {
            return PaymentResultDTO::failed(
                $request->gateway,
                'Customer ID is required.'
            );
        }

        // All valid, pass to next handler
        return $this->passToNext($request);
    }
}

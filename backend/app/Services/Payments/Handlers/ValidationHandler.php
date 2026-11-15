<?php

namespace App\Services\Payments\Handlers;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentResultDTO;

class ValidationHandler extends PaymentHandler
{
    public function canHandle(PaymentRequestDTO $request): bool
    {
        return true;
    }

    public function handle(PaymentRequestDTO $request): ?PaymentResultDTO
    {
        if ($request->amount <= 0) {
            return PaymentResultDTO::failed(
                $request->gateway,
                'Invalid payment amount. Amount must be greater than zero.'
            );
        }

        if (empty($request->gateway)) {
            return PaymentResultDTO::failed(
                'unknown',
                'Payment gateway is not specified.'
            );
        }

        if (empty($request->customerId)) {
            return PaymentResultDTO::failed(
                $request->gateway,
                'Customer identification is required.'
            );
        }

        return $this->passToNext($request);
    }
}

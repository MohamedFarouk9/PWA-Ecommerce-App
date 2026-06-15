<?php

namespace App\Services\Payments\Handlers;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentResultDTO;
use Illuminate\Support\Facades\Log;

class LoggingHandler extends PaymentHandler
{
    public function canHandle(PaymentRequestDTO $request): bool
    {
        return true; // Always log
    }

    public function handle(PaymentRequestDTO $request): ?PaymentResultDTO
    {
        Log::info('Payment processing started', [
            'order_id' => $request->orderId,
            'amount' => $request->amount,
            'gateway' => $request->gateway,
        ]);

        return $this->passToNext($request);
    }
}

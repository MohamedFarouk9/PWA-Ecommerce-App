<?php

namespace App\Services\Payments\Handlers;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentResultDTO;
use Illuminate\Support\Facades\Log;

class LoggingHandler extends PaymentHandler
{
    public function canHandle(PaymentRequestDTO $request): bool
    {
        return true;
    }

    public function handle(PaymentRequestDTO $request): ?PaymentResultDTO
    {
        Log::info('Initiating payment request processing', [
            'order_id' => $request->orderId,
            'customer_id' => $request->customerId,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'gateway' => $request->gateway,
        ]);

        return $this->passToNext($request);
    }
}

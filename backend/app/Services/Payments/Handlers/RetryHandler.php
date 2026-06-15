<?php

namespace App\Services\Payments\Handlers;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentResultDTO;
use App\Services\Payments\PaymentGatewayFactory;
use Exception;
use Illuminate\Support\Facades\Log;

class RetryHandler extends PaymentHandler
{
    private int $currentRetry = 0;

    public function __construct(
        private PaymentGatewayFactory $factory,
        private int $maxRetries = 3,
    ) {}

    public function canHandle(PaymentRequestDTO $request): bool
    {
        return $request->retries > 0;
    }

    public function handle(PaymentRequestDTO $request): ?PaymentResultDTO
    {
        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $gateway = $this->factory->create($request->gateway);

                $result = $gateway->charge([
                    'amount' => $request->amount,
                    'currency' => $request->currency,
                    'order_id' => $request->orderId,
                    'customer_id' => $request->customerId,
                ]);

                if ($result['success'] ?? false) {
                    return PaymentResultDTO::success(
                        $result['transaction_id'] ?? '',
                        $request->gateway
                    );
                }

            } catch (Exception $e) {
                Log::warning("Payment attempt $attempt failed", [
                    'order_id' => $request->orderId,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt === $this->maxRetries) {
                    return PaymentResultDTO::failed(
                        $request->gateway,
                        "Failed after $this->maxRetries attempts: " . $e->getMessage()
                    );
                }

                // Wait before retry (exponential backoff)
                usleep(1000 * (1000 * (2 ** ($attempt - 1))));
            }
        }

        return null;
    }
}

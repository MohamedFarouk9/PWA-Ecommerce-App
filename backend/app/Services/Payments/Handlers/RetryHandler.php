<?php

namespace App\Services\Payments\Handlers;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentResultDTO;
use App\Services\Payments\PaymentGatewayFactory;
use Exception;
use Illuminate\Support\Facades\Log;

class RetryHandler extends PaymentHandler
{
    public function __construct(
        private PaymentGatewayFactory $factory,
        private int $maxRetries = 3,
    ) {}

    public function canHandle(PaymentRequestDTO $request): bool
    {
        return true;
    }

    /**
     * Execute gateway charge with smart retry (transient network/5xx/429 only)
     * and exponential backoff + random jitter to prevent thundering herds.
     */
    public function handle(PaymentRequestDTO $request): ?PaymentResultDTO
    {
        $maxAttempts = max(1, min($this->maxRetries, (int) ($request->retries ?? 1)));
        $lastError = 'Payment failed';

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $gateway = $this->factory->create($request->gateway);

                $response = $gateway->charge([
                    'amount' => $request->amount,
                    'currency' => $request->currency,
                    'order_id' => $request->orderId,
                    'customer_id' => $request->customerId,
                    'metadata' => $request->metadata ?? [],
                ]);

                if ($response['success'] ?? false) {
                    $status = $response['status'] ?? 'completed';

                    return $status === 'pending'
                        ? PaymentResultDTO::pending($response['transaction_id'] ?? '', $request->gateway, $response)
                        : PaymentResultDTO::success($response['transaction_id'] ?? '', $request->gateway, $response);
                }

                $lastError = $response['error'] ?? 'Gateway rejected payment.';

                // Never retry deterministic card declines or validation rejections
                if (($response['retryable'] ?? false) === false) {
                    return PaymentResultDTO::failed($request->gateway, $lastError, $response);
                }
            } catch (Exception $e) {
                $lastError = $e->getMessage();
                Log::warning("Payment attempt {$attempt}/{$maxAttempts} failed", [
                    'order_id' => $request->orderId,
                    'gateway' => $request->gateway,
                    'error' => $e->getMessage(),
                ]);
            }

            if ($attempt < $maxAttempts) {
                // Exponential backoff (200ms, 400ms, 800ms) + random jitter (20-100ms)
                $sleepMs = (int) ((2 ** ($attempt - 1)) * 200 + random_int(20, 100));
                usleep($sleepMs * 1000);
            }
        }

        return PaymentResultDTO::failed(
            $request->gateway,
            "Payment failed after {$maxAttempts} attempt(s): {$lastError}"
        );
    }
}

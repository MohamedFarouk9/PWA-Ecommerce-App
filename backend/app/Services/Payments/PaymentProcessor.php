<?php

namespace App\Services\Payments;

use App\Contracts\PaymentProcessorInterface;
use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentResultDTO;
use App\Services\Payments\Handlers\LoggingHandler;
use App\Services\Payments\Handlers\PaymentHandler;
use App\Services\Payments\Handlers\ValidationHandler;
use Exception;
use Illuminate\Support\Facades\Log;

class PaymentProcessor implements PaymentProcessorInterface
{
    private PaymentHandler $chain;
    private ?IdempotencyHandler $idempotencyHandler = null;

    public function __construct(
        private PaymentGatewayFactory $factory,
        ?IdempotencyHandler $idempotencyHandler = null,
    ) {
        $this->idempotencyHandler = $idempotencyHandler ?? new IdempotencyHandler();

        // Linked Chain of Responsibility
        $this->chain = new ValidationHandler();
        $this->chain->setNext(new LoggingHandler());
    }

    public function process(
        PaymentRequestDTO $paymentRequest,
        ?string $idempotencyKey = null
    ): PaymentResultDTO {
        try {
            // Step 1: Check Idempotency Cache
            if ($idempotencyKey) {
                $cachedResult = $this->idempotencyHandler->checkDuplicate($idempotencyKey);

                if ($cachedResult) {
                    Log::info('Idempotent request detected - returning cached result', [
                        'order_id' => $paymentRequest->orderId,
                        'idempotency_key' => $idempotencyKey,
                    ]);

                    return $cachedResult;
                }

                // Verify payload integrity if prior record exists
                if (!$this->idempotencyHandler->validateRequestIntegrity($idempotencyKey, (array) $paymentRequest)) {
                    return PaymentResultDTO::failed(
                        $paymentRequest->gateway,
                        'Idempotency violation: request parameters altered under existing key.'
                    );
                }

                // Record request state as pending with distributed lock concurrency guard
                try {
                    $this->idempotencyHandler->store(
                        $idempotencyKey,
                        $paymentRequest->gateway,
                        $paymentRequest->orderId,
                        $paymentRequest->customerId,
                        (array) $paymentRequest,
                        'pending'
                    );
                } catch (\Illuminate\Database\QueryException $qe) {
                    $cached = $this->idempotencyHandler->checkDuplicate($idempotencyKey);
                    if ($cached) {
                        return $cached;
                    }
                    return PaymentResultDTO::failed(
                        $paymentRequest->gateway,
                        'Payment request is already being processed concurrently. Please wait.'
                    );
                }
            }

            // Step 2: Run Pre-Flight Chain of Responsibility (Validation -> Logging)
            $preFlightResult = $this->chain->handle($paymentRequest);
            if ($preFlightResult && !$preFlightResult->success) {
                if ($idempotencyKey) {
                    $this->idempotencyHandler->markCompleted($idempotencyKey, $preFlightResult);
                }
                return $preFlightResult;
            }

            // Step 3: Execute Payment with Resilient Retry Policy
            $result = $this->executePaymentWithRetry($paymentRequest);

            // Step 4: Record Completion in Idempotency Store
            if ($idempotencyKey) {
                $this->idempotencyHandler->markCompleted($idempotencyKey, $result);
            }

            return $result;

        } catch (Exception $e) {
            Log::error('Unhandled error during payment execution', [
                'order_id' => $paymentRequest->orderId,
                'gateway' => $paymentRequest->gateway,
                'error' => $e->getMessage(),
                'idempotency_key' => $idempotencyKey ?? 'none',
            ]);

            $result = PaymentResultDTO::failed(
                $paymentRequest->gateway,
                'Payment execution encountered an unexpected error: ' . $e->getMessage()
            );

            if ($idempotencyKey) {
                $this->idempotencyHandler->markCompleted($idempotencyKey, $result);
            }

            return $result;
        }
    }

    /**
     * Executes charge against configured gateway with exponential backoff and jitter.
     */
    private function executePaymentWithRetry(PaymentRequestDTO $paymentRequest): PaymentResultDTO
    {
        $maxAttempts = max(1, min(3, (int) ($paymentRequest->retries ?? 1)));
        $lastError = 'Payment failed';

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $gateway = $this->factory->create($paymentRequest->gateway);

                $chargePayload = [
                    'amount' => $paymentRequest->amount,
                    'currency' => $paymentRequest->currency,
                    'order_id' => $paymentRequest->orderId,
                    'customer_id' => $paymentRequest->customerId,
                    'metadata' => $paymentRequest->metadata ?? [],
                ];

                $response = $gateway->charge($chargePayload);

                if ($response['success'] ?? false) {
                    return PaymentResultDTO::success(
                        $response['transaction_id'] ?? '',
                        $paymentRequest->gateway,
                        $response
                    );
                }

                $lastError = $response['error'] ?? 'Gateway rejected payment.';

            } catch (Exception $e) {
                $lastError = $e->getMessage();
                Log::warning("Payment attempt {$attempt}/{$maxAttempts} failed", [
                    'order_id' => $paymentRequest->orderId,
                    'gateway' => $paymentRequest->gateway,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt === $maxAttempts) {
                    break;
                }

                // Exponential backoff with jitter
                $sleepMs = (int) ((2 ** ($attempt - 1)) * 200 + random_int(20, 100));
                usleep($sleepMs * 1000);
            }
        }

        return PaymentResultDTO::failed(
            $paymentRequest->gateway,
            "Payment failed after {$maxAttempts} attempt(s): {$lastError}"
        );
    }
}

<?php

namespace App\Services\Payments;

use App\Contracts\PaymentProcessorInterface;
use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentResultDTO;
use App\Services\Payments\Handlers\LoggingHandler;
use App\Services\Payments\Handlers\PaymentHandler;
use App\Services\Payments\Handlers\RetryHandler;
use App\Services\Payments\Handlers\ValidationHandler;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class PaymentProcessor implements PaymentProcessorInterface
{
    private PaymentHandler $chain;
    private IdempotencyHandler $idempotencyHandler;

    public function __construct(
        private PaymentGatewayFactory $factory,
        ?IdempotencyHandler $idempotencyHandler = null,
    ) {
        $this->idempotencyHandler = $idempotencyHandler ?? new IdempotencyHandler();

        // Full Chain of Responsibility: Validation -> Logging -> Resilient Gateway Execution
        $validation = new ValidationHandler();
        $logging = new LoggingHandler();
        $retry = new RetryHandler($this->factory);

        $validation->setNext($logging)->setNext($retry);
        $this->chain = $validation;
    }

    public function process(
        PaymentRequestDTO $paymentRequest,
        ?string $idempotencyKey = null
    ): PaymentResultDTO {
        try {
            // Step 1: Idempotency Guard (Duplicate check, payload tamper check, concurrency lock)
            if ($idempotencyKey) {
                $guardResult = $this->acquireIdempotencyLock($paymentRequest, $idempotencyKey);
                if ($guardResult !== null) {
                    return $guardResult;
                }
            }

            // Step 2: Execute Chain of Responsibility (Validation -> Logging -> Smart Retry Charge)
            $result = $this->chain->handle($paymentRequest)
                ?? PaymentResultDTO::failed($paymentRequest->gateway, 'Payment pipeline returned no result.');

            // Step 3: Persist Final Outcome in Idempotency Store
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
     * Checks for cached response / payload tampering and inserts a unique `pending` lock row.
     * Returns a PaymentResultDTO if execution should short-circuit, or null to proceed.
     */
    private function acquireIdempotencyLock(
        PaymentRequestDTO $paymentRequest,
        string $idempotencyKey
    ): ?PaymentResultDTO {
        $cachedResult = $this->idempotencyHandler->checkDuplicate($idempotencyKey);
        if ($cachedResult) {
            Log::info('Idempotent request detected - returning cached result', [
                'order_id' => $paymentRequest->orderId,
                'idempotency_key' => $idempotencyKey,
            ]);

            return $cachedResult;
        }

        if (!$this->idempotencyHandler->validateRequestIntegrity($idempotencyKey, (array) $paymentRequest)) {
            return PaymentResultDTO::failed(
                $paymentRequest->gateway,
                'Idempotency violation: request parameters altered under existing key.'
            );
        }

        try {
            $this->idempotencyHandler->store(
                $idempotencyKey,
                $paymentRequest->gateway,
                $paymentRequest->orderId,
                $paymentRequest->customerId,
                (array) $paymentRequest,
                'pending'
            );
        } catch (QueryException) {
            $cached = $this->idempotencyHandler->checkDuplicate($idempotencyKey);

            return $cached ?? PaymentResultDTO::failed(
                $paymentRequest->gateway,
                'Payment request is already being processed concurrently. Please wait.'
            );
        }

        return null;
    }
}

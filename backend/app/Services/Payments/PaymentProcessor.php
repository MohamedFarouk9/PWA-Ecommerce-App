<?php

namespace App\Services\Payments;

use App\Contracts\PaymentProcessorInterface;
use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentResultDTO;
use App\Services\Payments\Handlers\LoggingHandler;
use App\Services\Payments\Handlers\RetryHandler;
use App\Services\Payments\Handlers\ValidationHandler;
use Exception;
use Illuminate\Support\Facades\Log;

class PaymentProcessor implements PaymentProcessorInterface
{
    private array $handlers = [];
    private ?IdempotencyHandler $idempotencyHandler = null;

    public function __construct(
        private PaymentGatewayFactory $factory,
        ?IdempotencyHandler $idempotencyHandler = null,
    ) {
        $this->idempotencyHandler = $idempotencyHandler;
        // Chain of handlers (order matters)
        $this->handlers = [
            new ValidationHandler(),
            new RetryHandler($factory, 3),
            new LoggingHandler(),
        ];
    }

    public function process(
        PaymentRequestDTO $paymentRequest,
        ?string $idempotencyKey = null // NEW parameter
    ): PaymentResultDTO {
        try {
            // Step 1: Check for duplicate request (NEW)
            if ($idempotencyKey) {
                $cachedResult = $this->idempotencyHandler->checkDuplicate($idempotencyKey);

                if ($cachedResult) {
                    Log::info('Idempotent request - returning cached result', [
                        'order_id' => $paymentRequest->orderId,
                        'idempotency_key' => $idempotencyKey,
                    ]);

                    return $cachedResult; // Return immediately!
                }

                // Store as pending (NEW)
                $this->idempotencyHandler->store(
                    $idempotencyKey,
                    $paymentRequest->gateway,
                    $paymentRequest->orderId,
                    $paymentRequest->customerId,
                    (array) $paymentRequest,
                    'pending'
                );
            }

            // Step 2: Execute handler chain
            foreach ($this->handlers as $handler) {
                if (!$handler->canHandle($paymentRequest)) {
                    continue;
                }
                $result = $handler->handle($paymentRequest);
                if ($result) {
                    // Mark as failed in idempotency table (NEW)
                    if ($idempotencyKey) {
                        $this->idempotencyHandler->markCompleted($idempotencyKey, $result);
                    }
                    return $result;
                }
            }

            // Step 3: Execute actual payment
            $result = $this->executePayment($paymentRequest);

            // Step 4: Mark as completed in idempotency table (NEW)
            if ($idempotencyKey) {
                $this->idempotencyHandler->markCompleted($idempotencyKey, $result);
            }

            return $result;

        } catch (Exception $e) {
            Log::error('Payment processing error', [
                'order_id' => $paymentRequest->orderId,
                'gateway' => $paymentRequest->gateway,
                'error' => $e->getMessage(),
                'idempotency_key' => $idempotencyKey ?? 'none',
            ]);

            $result = PaymentResultDTO::failed(
                $paymentRequest->gateway,
                $e->getMessage()
            );

            // Mark as failed (NEW)
            if ($idempotencyKey) {
                $this->idempotencyHandler->markCompleted($idempotencyKey, $result);
            }

            return $result;
        }
    }

    private function executePayment(PaymentRequestDTO $paymentRequest): PaymentResultDTO
    {
        $gateway = $this->factory->create($paymentRequest->gateway);

        $result = $gateway->charge([
            'amount' => $paymentRequest->amount,
            'currency' => $paymentRequest->currency,
            'order_id' => $paymentRequest->orderId,
            'customer_id' => $paymentRequest->customerId,
            'metadata' => $paymentRequest->metadata,
        ]);

        if ($result['success'] ?? false) {
            return PaymentResultDTO::success(
                $result['transaction_id'],
                $paymentRequest->gateway,
                $result
            );
        }

        return PaymentResultDTO::failed(
            $paymentRequest->gateway,
            $result['error'] ?? 'Payment failed'
        );
    }
}

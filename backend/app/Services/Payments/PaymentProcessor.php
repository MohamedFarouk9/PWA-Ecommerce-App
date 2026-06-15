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

    public function __construct(
        private PaymentGatewayFactory $factory,
    ) {
        // Chain of handlers (order matters)
        $this->handlers = [
            new ValidationHandler(),
            new RetryHandler($factory, 3),
            new LoggingHandler(),
        ];
    }

    public function process(PaymentRequestDTO $paymentRequest): PaymentResultDTO
    {
        try {
            // Execute handler chain
            foreach ($this->handlers as $handler) {
                if (!$handler->canHandle($paymentRequest)) {
                    continue;
                }
                $result = $handler->handle($paymentRequest);
                if ($result) {
                    return $result;
                }
            }

            // Execute actual payment
            return $this->executePayment($paymentRequest);

        } catch (Exception $e) {
            Log::error('Payment processing error', [
                'order_id' => $paymentRequest->orderId,
                'gateway' => $paymentRequest->gateway,
                'error' => $e->getMessage(),
            ]);

            return PaymentResultDTO::failed(
                $paymentRequest->gateway,
                $e->getMessage()
            );
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

<?php

namespace App\Contracts;

use App\DTOs\PaymentRequestDTO;

interface PaymentProcessorInterface
{
    /**
     * Process the payment through the workflow
     */
    public function process(PaymentRequestDTO $paymentRequest): PaymentResultDTO;
}

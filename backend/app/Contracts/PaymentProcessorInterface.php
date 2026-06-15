<?php

namespace App\Contracts;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentResultDTO;

interface PaymentProcessorInterface
{
    /**
     * Process the payment through the workflow
     */
    public function process(PaymentRequestDTO $paymentRequest): PaymentResultDTO;
}

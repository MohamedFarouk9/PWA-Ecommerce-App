<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Services\Payments\PaymentService;

class PaymentRepository implements PaymentRepositoryInterface
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Process a payment charge
     */
    public function charge(array $data)
    {
        return $this->paymentService->charge($data);
    }

    /**
     * Handle payment webhook
     */
    public function webhook(array $data)
    {
        return $this->paymentService->webhook($data);
    }
}

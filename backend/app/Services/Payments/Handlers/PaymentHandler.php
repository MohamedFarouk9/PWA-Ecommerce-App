<?php

namespace App\Services\Payments\Handlers;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentResultDTO;

abstract class PaymentHandler
{
    protected ?PaymentHandler $next = null;

    public function setNext(PaymentHandler $handler): PaymentHandler
    {
        $this->next = $handler;
        return $handler;
    }

    abstract public function canHandle(PaymentRequestDTO $request): bool;
    abstract public function handle(PaymentRequestDTO $request): ?PaymentResultDTO;

    protected function passToNext(PaymentRequestDTO $request): ?PaymentResultDTO
    {
        if ($this->next && $this->next->canHandle($request)) {
            return $this->next->handle($request);
        }
        return null;
    }
}

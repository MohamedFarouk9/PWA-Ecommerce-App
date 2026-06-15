<?php

namespace App\Services\Payments\Handlers;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentResultDTO;

abstract class PaymentHandler
{
    protected ?PaymentHandler $next = null;

    public function setNext(PaymentHandler $handler): self
    {
        $this->next = $handler;
        return $this;
    }

    abstract public function canHandle(PaymentRequestDTO $request): bool;
    abstract public function handle(PaymentRequestDTO $request): ?PaymentResultDTO;

    protected function passToNext(PaymentRequestDTO $request): ?PaymentResultDTO
    {
        return $this->next?->handle($request);
    }
}

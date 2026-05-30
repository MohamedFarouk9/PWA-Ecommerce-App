<?php

namespace App\Repositories\Contracts;

interface PaymentRepositoryInterface
{
    public function charge(array $data);
    public function webhook(array $data);
}

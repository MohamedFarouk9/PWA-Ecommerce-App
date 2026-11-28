<?php

namespace Modules\Sales\Actions;

use Modules\Sales\Models\Order;
use Modules\Sales\Repositories\Contracts\OrderRepositoryInterface;

class UpdateOrderStatusAction
{
    public function __construct(private OrderRepositoryInterface $orderRepository) {}

    public function execute(int $orderId, string $status): Order
    {
        return $this->orderRepository->updateStatus($orderId, $status);
    }
}

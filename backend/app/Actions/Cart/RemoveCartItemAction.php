<?php

namespace App\Actions\Cart;

use App\Repositories\Contracts\CartRepositoryInterface;

class RemoveCartItemAction
{
    public function __construct(private CartRepositoryInterface $repo){}

    public function execute(int $id, ?int $userId = null, ?string $sessionId = null)
    {
        return $this->repo->removeItem($id, $userId, $sessionId);
    }

}



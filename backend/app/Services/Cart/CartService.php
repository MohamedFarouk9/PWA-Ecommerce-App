<?php
namespace App\Services\Cart;

use App\Actions\Cart\ClearCartAction;
use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\UpdateCartItemAction;
use App\Actions\Cart\AddCartItemAction;
use App\DTOs\Cart\CartItemData;
use App\Repositories\Contracts\CartRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function __construct(
        private CartRepositoryInterface $repo ,
        private AddCartItemAction $addAcation,
        private UpdateCartItemAction $updateAction,
        private RemoveCartItemAction $removeAction,
        private ClearCartAction $clearAction,
    ){}

    public function getCartItems(?int $userId , ?string $sessionId)
    {
        return $this->repo->getItems($userId , $sessionId);
    }

    public function addItem(CartItemData $data)
    {
        // Example: check stock, calculate price snapshot
        // $product = Product::findOrFail($data->product_id);
        // if ($product->stock < $data->quantity) throw new \Exception('Not enough stock');
        // $data->price = $product->price;
        return $this->addAcation->execute($data);
    }

    public function updateItem(int $id, array $payload, ?int $userId = null, ?string $sessionId = null)
    {
        return DB::transaction(function () use ($id, $payload, $userId, $sessionId) {
            return $this->updateAction->execute($id, $payload, $userId, $sessionId);
        });
    }

    public function removeItem(int $id, ?int $userId = null, ?string $sessionId = null): bool
    {
        return $this->removeAction->execute($id, $userId, $sessionId);
    }

       public function clearCart(?int $userId, ?string $sessionId): int
    {
        return $this->clearAction->execute($userId, $sessionId);
    }
}
